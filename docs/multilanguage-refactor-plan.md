# Multilanguage Refactor Plan

## Current Assessment

The current multilanguage implementation is mostly copy/paste by language instead of a centralized i18n system.

Main issues:

- `routes/web.php` contains repeated route blocks for `vi`, `en`, `ja`, `ko`, and `zh`.
- Room URLs are duplicated by language, for example `/phong`, `/room`, `/rumu`, `/bang`, and `/fangjian`.
- Views are duplicated under `resources/views/en`, `resources/views/ja`, `resources/views/ko`, and `resources/views/zh`.
- Controllers contain duplicated language-specific methods such as `getRoomsEn`, `getRoomsJa`, `anonymousQuickMatchEn`, `anonymousQuickMatchJa`, `sendJa`, `sendKo`, and `postZh`.
- `resources/lang` is barely used for UI copy. Most text is hard-coded directly in route closures, controllers, or Blade views.
- SEO fields such as canonical URLs, alternate URLs, and page titles are manually assembled in many route closures.
- Business logic, routing, translated copy, and SEO metadata are tightly coupled.

## Goals

The target structure should keep the existing URLs where needed for SEO, while reducing implementation to one shared flow:

```text
1 controller method
1 shared view
1 centralized route definition pattern
N translation files
```

Language-specific behavior should come from config and translation files, not duplicated route/controller/view code.

## Progress

- Done: created centralized locale config and localized URL service.
- Done: added locale middleware and shared locale variables for views.
- Done: canonical and hreflang tags now use generated localized URLs, with `x-default` pointing to Vietnamese.
- Done: static `about` and `contact` routes now use generated localized routes.
- Done: room list routes now use one localized route definition while preserving `/sanh-cho`, `/rooms`, `/heya-ichiran`, `/bang-moglog`, and `/fangjianliebiao`.
- Done: room play/watch side routes now use one generated route definition while preserving old room URLs such as `/phong/{code}`, `/room/{code}`, `/rumu/{code}`, `/bang/{code}`, and `/fangjian/{code}`.
- Done: chat comment partials no longer crash when Laravel has already started a session.
- Next: continue collapsing duplicated controller endpoints and Blade views into shared locale-aware controllers/components.

## SEO-Safe Migration Requirement

SEO is important for this project, so the refactor must not remove or casually change existing public URLs.

The goal is to refactor the implementation behind the URLs, not to redesign the URL structure.

Existing public URL patterns should remain valid:

```text
/phong/...
/room/...
/rumu/...
/bang/...
/fangjian/...
/en
/ja
/ko
/zh
```

For example, these URLs should keep working for the same room:

```text
vi: /phong/{code}
en: /room/{code}
ja: /rumu/{code}
ko: /bang/{code}
zh: /fangjian/{code}
```

All of them can be routed to the same controller method internally, but the public URLs should stay stable.

Do not redirect indexed language pages across languages. For example, do not redirect `/room/{code}` to `/phong/{code}` if `/room/{code}` is the English page. It should render English content and expose correct hreflang metadata.

Use 301 redirects only when a URL is truly deprecated, duplicated, or broken, and redirect it to the correct equivalent URL in the same language where possible.

## Target Architecture

### 1. Locale Configuration

Create a dedicated locale config:

```text
config/locales.php
```

Example structure:

```php
return [
    'default' => 'vi',

    'supported' => ['vi', 'en', 'ja', 'ko', 'zh'],

    'prefixes' => [
        'vi' => '',
        'en' => 'en',
        'ja' => 'ja',
        'ko' => 'ko',
        'zh' => 'zh',
    ],

    'slugs' => [
        'room' => [
            'vi' => 'phong',
            'en' => 'room',
            'ja' => 'rumu',
            'ko' => 'bang',
            'zh' => 'fangjian',
        ],
    ],
];
```

This keeps old public URLs possible while removing hard-coded URL decisions from route closures.

For SEO-sensitive routes, keep the exact legacy paths in config:

```php
'paths' => [
    'room.show' => [
        'vi' => 'phong/{code}',
        'en' => 'room/{code}',
        'ja' => 'rumu/{code}',
        'ko' => 'bang/{code}',
        'zh' => 'fangjian/{code}',
    ],
    'room.guest' => [
        'vi' => 'phong/{code}/khach',
        'en' => 'room/{code}/guest',
        'ja' => 'rumu/{code}/geesuto',
        'ko' => 'bang/{code}/bangmun',
        'zh' => 'fangjian/{code}/zhuke',
    ],
],
```

This allows the code to be unified without changing URLs that may already be indexed.

### 2. Locale Middleware

Create middleware such as:

```text
app/Http/Middleware/SetLocale.php
```

Responsibilities:

- Detect locale from route parameters, prefix, or localized slug.
- Validate the locale against `config('locales.supported')`.
- Call `app()->setLocale($locale)`.
- Share common variables with views:
  - `locale`
  - `canonicalUrl`
  - `alternateUrls`
  - supported locale list

### 3. Localized URL Service

Create a service/helper for localized URL generation:

```text
app/Services/LocalizedUrlService.php
```

Responsibilities:

- Generate localized route URLs.
- Generate canonical URLs.
- Generate `hreflang` alternate URLs.
- Encapsulate all slug mapping logic.

Example API:

```php
localized_url('room.show', ['code' => $code], 'en');
alternate_urls('room.show', ['code' => $code]);
canonical_url('room.show', ['code' => $code], $locale);
```

This replaces repeated variables like:

```php
langViUrl
langEnUrl
langJaUrl
langKoUrl
langZhUrl
canonicalUrl
```

### 4. Route Refactor

Replace language-specific route blocks with generated route groups.

Current pattern:

```php
Route::match(['get', 'post'], '/phong/{code}', ...);
Route::match(['get', 'post'], '/room/{code}', ...);
Route::match(['get', 'post'], '/rumu/{code}', ...);
Route::match(['get', 'post'], '/bang/{code}', ...);
Route::match(['get', 'post'], '/fangjian/{code}', ...);
```

Target pattern:

```php
foreach (config('locales.supported') as $locale) {
    Route::middleware("locale:$locale")->group(function () use ($locale) {
        Route::match(['get', 'post'], localized_path($locale, 'room.show'), [RoomController::class, 'show'])
            ->name("{$locale}.room.show");
    });
}
```

The exact helper names can be adjusted, but route duplication should be removed.

### 5. Controller Refactor

Collapse language-specific methods into parameterized methods.

Examples:

```php
anonymousQuickMatch()
anonymousQuickMatchEn()
anonymousQuickMatchJa()
anonymousQuickMatchKo()
anonymousQuickMatchZh()
```

Target:

```php
anonymousQuickMatch(Request $request, ?string $locale = null)
```

Similarly refactor:

- `checkAnonymousMatchStatus*`
- `getRooms*`
- `changePass*`
- `MailController::send*`
- `ChatController::post*`

Translated response text should use:

```php
__('match.waiting')
__('match.found')
__('room.password_changed')
```

### 6. Shared Views

Use one shared Blade view per page or flow.

Current duplicated folders:

```text
resources/views/en
resources/views/ja
resources/views/ko
resources/views/zh
```

Target:

```text
resources/views/room.blade.php
resources/views/roomHost.blade.php
resources/views/roomGuest.blade.php
resources/views/roomRed.blade.php
resources/views/roomBlack.blade.php
```

Hard-coded text should be replaced with translation calls:

```blade
{{ __('room.host') }}
{{ __('room.guest') }}
{{ __('room.red_side') }}
{{ __('room.black_side') }}
```

Do not delete duplicated language views until the matching route/page is fully migrated and tested.

### 7. Translation Files

Create domain-based translation files instead of one giant file.

Recommended structure:

```text
resources/lang/vi/common.php
resources/lang/vi/nav.php
resources/lang/vi/home.php
resources/lang/vi/room.php
resources/lang/vi/puzzle.php
resources/lang/vi/match.php
resources/lang/vi/mail.php
resources/lang/vi/seo.php

resources/lang/en/common.php
resources/lang/en/nav.php
...
resources/lang/ja/common.php
...
resources/lang/ko/common.php
...
resources/lang/zh/common.php
...
```

Example:

```php
// resources/lang/en/room.php
return [
    'host' => 'Host',
    'guest' => 'Guest',
    'red_side' => 'Red side',
    'black_side' => 'Black side',
];
```

SEO titles should also be translated:

```php
// resources/lang/en/seo.php
return [
    'room_host_title' => 'Host - Room: :room',
];
```

Usage:

```php
__('seo.room_host_title', ['room' => $roomName]);
```

### 8. SEO and Hreflang

Move SEO metadata out of route closures.

Target view data:

```php
[
    'headTitle' => __('seo.room_host_title', ['room' => $roomName]),
    'canonicalUrl' => $localizedUrlService->canonical(...),
    'alternateUrls' => $localizedUrlService->alternate(...),
]
```

Target Blade:

```blade
@foreach ($alternateUrls as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
@endforeach
```

Each localized page should expose:

```html
<link rel="canonical" href="https://cotuong.top/current-locale-url">
<link rel="alternate" hreflang="vi" href="https://cotuong.top/phong/abc">
<link rel="alternate" hreflang="en" href="https://cotuong.top/room/abc">
<link rel="alternate" hreflang="ja" href="https://cotuong.top/rumu/abc">
<link rel="alternate" hreflang="ko" href="https://cotuong.top/bang/abc">
<link rel="alternate" hreflang="zh" href="https://cotuong.top/fangjian/abc">
<link rel="alternate" hreflang="x-default" href="https://cotuong.top/phong/abc">
```

The canonical URL should normally point to the current locale URL, not always to Vietnamese, when each language page has unique translated content. The `hreflang` set tells search engines that these are equivalent localized versions.

### 8.1 Sitemap Strategy

The sitemap should preserve all important localized URLs.

Recommended options:

```text
/sitemap.xml
/sitemap-vi.xml
/sitemap-en.xml
/sitemap-ja.xml
/sitemap-ko.xml
/sitemap-zh.xml
```

The main sitemap can reference the per-locale sitemaps. Each localized URL should include alternate language metadata where practical.

During migration, do not drop existing localized URLs from the sitemap until the replacement URL has a clear 301 redirect and has been validated.

### 9. AMP Handling

Do not refactor AMP at the same time as the main web flow.

Recommended approach:

1. Finish shared locale handling for normal web pages.
2. Decide whether AMP is still required.
3. If AMP is required, migrate AMP layouts to the same locale/config/translation system.
4. If AMP is no longer required, add clear redirects or canonical handling.

## Migration Strategy

Avoid one huge rewrite. Use incremental migration by page/flow.

### Phase 1: Foundation

- Add `config/locales.php`.
- Add `SetLocale` middleware.
- Add `LocalizedUrlService`.
- Add base translation files for all supported locales.
- Add route helpers for localized slugs.
- Add legacy URL mapping for SEO-sensitive paths before changing any route implementation.

### Phase 2: Low-Risk Pages

Migrate simple pages first:

- Home
- About
- Contact
- Terms
- Privacy

These pages validate the new routing, layout, translation, canonical, and hreflang flow with low business risk.

### Phase 3: Shared Layouts

Refactor shared layouts and partials:

- Header
- Footer
- Language switcher
- Head/meta partial
- Common scripts/styles partials

This removes a large amount of duplication before touching game logic.

### Phase 4: Room Flow

Migrate room-related pages:

- Room host
- Room guest
- Room random
- Room watch
- Red side
- Black side

Refactor route/controller/view together for this flow because room pages have many language-specific URLs and SEO variables.

### Phase 5: Actions and APIs

Collapse duplicated action methods:

- Anonymous quick match
- Match status polling
- Chat post
- Change room password
- Mail invite/send

Responses and validation messages should use translation keys.

### Phase 6: Puzzle and AI Pages

Migrate more complex pages after room flow is stable:

- Puzzle
- Puzzle AI
- Puzzle list
- Board AI
- AI difficulty pages

### Phase 7: Delete Old Duplicates

After each migrated flow is tested:

- Remove old language-specific Blade files.
- Remove obsolete route closures.
- Remove obsolete controller methods.
- Keep redirects for old URLs if public URLs change.

Only delete old duplicate code after confirming the old public URLs still render through the new shared route/controller/view flow.

## Testing Checklist

For every migrated page:

- `vi`, `en`, `ja`, `ko`, and `zh` routes return HTTP 200.
- Canonical URL points to the current locale URL.
- Hreflang URLs include all supported languages.
- Language switcher preserves the current page context.
- Legacy public URL remains unchanged.
- Existing indexed URL does not redirect to another language.
- Forms submit successfully in all locales.
- No hard-coded language text remains in the migrated view except domain data.
- No duplicated controller method remains for that migrated flow.

For room flow:

- Host room works.
- Guest room works.
- Random room works.
- Watch room works.
- Red side works.
- Black side works.
- Quick match works.
- Room password change works.
- Chat works.

## Rules for the Refactor

- Keep public URLs stable unless there is an explicit redirect plan.
- Refactor internals first; do not redesign URLs as part of cleanup.
- Keep existing SEO URLs like `/phong`, `/room`, `/rumu`, `/bang`, and `/fangjian`.
- Do not redirect language-specific pages to Vietnamese when they have translated content.
- Do not migrate all 76 duplicated views in one change.
- Do not mix AMP refactor with main web refactor.
- Do not put translated copy in route closures.
- Do not create new language-specific controller methods.
- Prefer translation keys and config mappings over conditionals like `if ($locale === 'ja')`.
- Each phase should leave the app runnable.

## Expected Outcome

After the refactor:

- Adding a new language requires adding config and translation files, not copying views/controllers/routes.
- Route definitions are compact and predictable.
- Controllers contain business logic only.
- Views are shared and use `__()` for copy.
- SEO alternate/canonical URLs are generated consistently.
- The codebase is easier to test and safer to change.
