<?php

namespace App\Http\Controllers;

use App\Actions\Puzzle\GetPuzzleQueriesAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Builds and serves the application's XML sitemaps.
 *
 * Three documents are exposed:
 *  - GET /sitemap.xml              all public, parameter-less named routes
 *  - GET /sitemap-the-co-vi.xml    one <url> entry per public puzzle, Vietnamese
 *  - GET /sitemap-the-co-en.xml    one <url> entry per public puzzle, English
 *
 * The two puzzle sitemaps are both handled by the single puzzles()
 * action, parameterised by {locale} (DRY: one action + one
 * locale-aware puzzleUrls() method instead of two near-identical
 * controller methods). Route::whereIn() restricts {locale} to the
 * two supported values, and the controller re-checks it (SRP: the
 * controller doesn't rely on routing alone to stay correct if it's
 * ever wired up differently).
 *
 * All sitemaps share the same "build a Sitemap, then render it as
 * XML" flow (DRY: see buildSitemap()/cachedXmlResponse()), so adding
 * a further sitemap source later just means adding one more
 * `*Urls()` method - the response plumbing doesn't need to change
 * (OCP).
 */
class SitemapController extends Controller
{
    /**
     * How long a generated sitemap is cached for, in seconds.
     * Sitemaps don't need to be real-time; crawlers re-fetch
     * on their own schedule anyway.
     */
    private const CACHE_TTL = 21600; // 6 hours

    /**
     * The only locales the puzzle sitemap is published in.
     * 'vi' is the default/unprefixed locale.
     */
    private const PUZZLE_SITEMAP_LOCALES = ['vi', 'en', 'ja', 'ko', 'zh'];

    private const DEFAULT_LOCALE = 'vi';

    public function __construct(
        private GetPuzzleQueriesAction $puzzleQueries
    ) {
    }

    /**
     * Main sitemap: every public, named, parameter-less GET route.
     */
    public function index(): Response
    {
        return $this->cachedXmlResponse('sitemap.index', function () {
            return $this->buildSitemap($this->routeUrls());
        });
    }

    /**
     * Puzzle sitemap for a single locale (vi or en).
     */
    public function puzzles(string $locale): Response
    {
        abort_unless(in_array($locale, self::PUZZLE_SITEMAP_LOCALES, true), 404);

        return $this->cachedXmlResponse("sitemap.puzzles.$locale", function () use ($locale) {
            return $this->buildSitemap($this->puzzleUrls($locale));
        });
    }

    /**
     * Collect Url tags for every publicly reachable, named route
     * that takes no parameters (dynamic routes like /puzzle/{slug}
     * are covered by their own dedicated sitemaps, e.g. puzzles()).
     *
     * @return Collection<int, Url>
     */
    private function routeUrls(): Collection
    {
        return collect(Route::getRoutes())
            ->filter(function (RoutingRoute $route) {
                return $route->getName()
                    && ! $this->isApiRoute($route)
                    && count($route->signatureParameters()) === 0;
            })
            ->map(function (RoutingRoute $route) {
                return Url::create(route($route->getName()));
            })
            ->unique(function (Url $url) {
                return $url->url;
            })
            ->values();
    }

    /**
     * Collect Url tags for every public puzzle in the given locale,
     * sourced from the existing GetPuzzleQueriesAction so the "what
     * counts as a public, sitemap-eligible puzzle" query lives in
     * exactly one place instead of being re-implemented here.
     *
     * @return Collection<int, Url>
     */
    private function puzzleUrls(string $locale): Collection
    {
        return collect($this->puzzleQueries->getSitemapPuzzles()->items())
            ->map(function ($puzzle) use ($locale) {
                return Url::create($this->localizedPuzzlePath($puzzle->slug, $locale))
                    ->setLastModificationDate($puzzle->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8);
            });
    }

    private function localizedPuzzlePath(string $slug, string $locale): string
    {
        return url('/') . __('/the-co/', [], $locale) . $slug;
    }

    /**
     * The default locale (vi) is served unprefixed; every other
     * locale is served under its own /{locale} segment, matching
     * the existing localized_path()/langXxUrl conventions elsewhere
     * in the app.
     */
    private function localePrefix(string $locale): string
    {
        return $locale === self::DEFAULT_LOCALE ? '' : '/' . $locale;
    }

    private function isApiRoute(RoutingRoute $route): bool
    {
        return str_starts_with($route->uri(), 'api');
    }

    /**
     * @param Collection<int, Url> $urls
     */
    private function buildSitemap(Collection $urls): Sitemap
    {
        $sitemap = Sitemap::create();

        $urls->each(function (Url $url) use ($sitemap) {
            $sitemap->add($url);
        });

        return $sitemap;
    }

    private function cachedXmlResponse(string $cacheKey, \Closure $sitemapFactory): Response
    {
        $xml = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sitemapFactory) {
            return $sitemapFactory()->render();
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
