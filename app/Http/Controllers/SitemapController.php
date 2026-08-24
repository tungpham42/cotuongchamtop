<?php

namespace App\Http\Controllers;

use App\Actions\Puzzle\GetPuzzleQueriesAction;
use App\Models\Article;
use Illuminate\Http\Response;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

/**
 * Builds and serves the application's XML sitemaps.
 *
 * Four documents are exposed:
 *  - GET /sitemap_index.xml               a <sitemap> index listing every document below
 *  - GET /sitemap.xml                     all public, parameter-less named routes
 *  - GET /sitemap-puzzles-{locale}.xml    one <url> entry per public puzzle
 *  - GET /sitemap-articles-{locale}.xml   one <url> entry per published article
 *
 * sitemap_index.xml is the URL you actually submit to search
 * engines; it just points at the other three (times |SITEMAP_LOCALES|
 * for the locale-specific ones) rather than duplicating their
 * contents, so nothing about the child sitemaps needs to change
 * when a new one is added - see sitemapIndex()/buildSitemapIndex().
 *
 * The puzzle and article sitemaps are each handled by a single
 * action, parameterised by {locale} (DRY: one action + one
 * locale-aware *Urls() method instead of one near-identical
 * controller method per locale). Route::whereIn() restricts
 * {locale} to the supported values, and the controller re-checks
 * it (SRP: the controller doesn't rely on routing alone to stay
 * correct if it's ever wired up differently).
 *
 * Articles are locale-aware at the *slug* level (each
 * ArticleTranslation has its own slug), unlike puzzles which share
 * one slug across all locales — so articleUrls() resolves the URL
 * via the named article.show route for that locale (using
 * Article::slugsByLocale()) rather than concatenating a path, and
 * simply skips any article that has no translation for the
 * requested locale.
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
     * The locales the puzzle and article sitemaps are published in.
     * 'vi' is the default/unprefixed locale.
     */
    private const SITEMAP_LOCALES = ['vi', 'en', 'ja', 'ko', 'zh'];

    private const DEFAULT_LOCALE = 'vi';

    public function __construct(
        private GetPuzzleQueriesAction $puzzleQueries
    ) {
    }

    /**
     * Sitemap index: the single URL to submit to search engines.
     * Points at sitemap.xml plus the puzzle and article sitemaps
     * for every supported locale, without duplicating their <url>
     * entries here.
     */
    public function sitemapIndex(): Response
    {
        return $this->cachedXmlResponse('sitemap.sitemapIndex', function () {
            return $this->buildSitemapIndex();
        });
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
        abort_unless(in_array($locale, self::SITEMAP_LOCALES, true), 404);

        return $this->cachedXmlResponse("sitemap.puzzles.$locale", function () use ($locale) {
            return $this->buildSitemap($this->puzzleUrls($locale));
        });
    }

    /**
     * Article sitemap for a single locale.
     */
    public function articles(string $locale): Response
    {
        abort_unless(in_array($locale, self::SITEMAP_LOCALES, true), 404);

        return $this->cachedXmlResponse("sitemap.articles.$locale", function () use ($locale) {
            return $this->buildSitemap($this->articleUrls($locale));
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
     * Collect Url tags for every published article that has a
     * translation in the given locale.
     *
     * Loads `translations` explicitly (on top of the model's
     * default-eager-loaded, current-locale-only `translation`) so
     * Article::slugsByLocale() can resolve every locale's slug
     * without triggering an N+1 query per article. Articles missing
     * a translation for $locale are silently skipped rather than
     * linked with the wrong locale's slug.
     *
     * NOTE: filters on status = 'published' — adjust this to match
     * however ArticleController decides an article is publicly
     * visible if that differs.
     *
     * @return Collection<int, Url>
     */
    private function articleUrls(string $locale): Collection
    {
        $routeName = $this->articleShowRouteName($locale);

        return Article::query()
            ->where('status', 'published')
            ->with('translations')
            ->get()
            ->map(function (Article $article) use ($locale, $routeName) {
                $slug = $article->slugsByLocale()[$locale] ?? null;

                if ($slug === null) {
                    return null;
                }

                return Url::create(route($routeName, ['slug' => $slug]))
                    ->setLastModificationDate($article->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7);
            })
            ->filter()
            ->values();
    }

    /**
     * Mirrors the naming convention used when the article.show
     * routes are registered in web.php: the default locale keeps
     * the unprefixed name, every other locale gets a `{locale}.`
     * prefix.
     */
    private function articleShowRouteName(string $locale): string
    {
        return $locale === self::DEFAULT_LOCALE ? 'article.show' : "{$locale}.article.show";
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
     * Lists sitemap.xml plus every locale's puzzle and article
     * sitemap. New child sitemaps just need one line here - the
     * cachedXmlResponse()/render() plumbing is shared with the
     * child sitemaps themselves via buildSitemap().
     */
    private function buildSitemapIndex(): SitemapIndex
    {
        $sitemapIndex = SitemapIndex::create()
            ->add(route('sitemap.index'));

        foreach (self::SITEMAP_LOCALES as $locale) {
            $sitemapIndex
                ->add(route('sitemap.puzzles', ['locale' => $locale]))
                ->add(route('sitemap.articles', ['locale' => $locale]));
        }

        return $sitemapIndex;
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
