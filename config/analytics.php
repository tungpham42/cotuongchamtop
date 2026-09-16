<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GA4 Property ID
    |--------------------------------------------------------------------------
    |
    | Found in GA4 Admin > Property Settings > Property ID (numeric only,
    | do NOT include the "properties/" prefix — the service adds it).
    |
    */
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials
    |--------------------------------------------------------------------------
    |
    | Absolute path to the service account JSON key downloaded from Google
    | Cloud Console. That service account's email must be added as a
    | "Viewer" under GA4 Admin > Property Access Management.
    |
    */
    'credentials_path' => env('GOOGLE_APPLICATION_CREDENTIALS', storage_path('app/google/ga4-service-account.json')),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (minutes)
    |--------------------------------------------------------------------------
    |
    | GA4's Data API has daily request quotas per property. We cache report
    | results so refreshing the dashboard doesn't burn quota.
    |
    */
    'cache_ttl' => env('GOOGLE_ANALYTICS_CACHE_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Marketing Funnel (TOFU / MOFU / BOFU)
    |--------------------------------------------------------------------------
    |
    | GA4 has no built-in "funnel stage" concept, so we map each stage onto
    | a real GA4 metric:
    |
    |   TOFU (awareness)   -> sessions          anyone who landed on the site
    |   MOFU (engagement)  -> engagedSessions    GA4's own "stuck around" metric
    |   BOFU (conversion)  -> one of three modes, set via bofu_type below:
    |
    |     'count'    -> the generic "conversions" metric (default)
    |     'event'    -> a specific key event you name in bofu_event_name
    |                   (e.g. "sign_up", "room_created")
    |     'revenue'  -> AdSense/ad revenue via GA4's "totalAdRevenue" metric.
    |                   Requires linking AdSense to this GA4 property under
    |                   AdSense > Account > Access and authorization >
    |                   Google Analytics integration. Reports $0 (not an
    |                   error) until that link is made and data has flowed.
    |
    */
    'funnel' => [
        'tofu_metric' => env('GA_FUNNEL_TOFU_METRIC', 'sessions'),
        'mofu_metric' => env('GA_FUNNEL_MOFU_METRIC', 'engagedSessions'),
        'bofu_type' => env('GA_FUNNEL_BOFU_TYPE', 'count'), // count | event | revenue
        'bofu_metric' => env('GA_FUNNEL_BOFU_METRIC', 'conversions'),
        'bofu_event_name' => env('GA_FUNNEL_BOFU_EVENT'), // e.g. 'sign_up'
        'bofu_currency' => env('GA_FUNNEL_BOFU_CURRENCY', 'USD'), // display only
    ],

];
