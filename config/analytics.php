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
    | a real GA4 metric or a custom event you fire from the app. Every stage
    | (not just BOFU) can now be either:
    |
    |   'metric' -> a built-in GA4 metric name (e.g. "sessions",
    |               "engagedSessions", "conversions")
    |   'event'  -> a specific event you name in *_event, matched exactly
    |               against GA4's eventName dimension (e.g. "game_started",
    |               "sign_up", "tournament_paid")
    |
    | BOFU has a third option:
    |   'revenue' -> AdSense/ad revenue via GA4's "totalAdRevenue" metric.
    |                Requires linking AdSense to this GA4 property under
    |                AdSense > Account > Access and authorization >
    |                Google Analytics integration. Reports $0 (not an
    |                error) until that link is made and data has flowed.
    |
    | Generic GA4 defaults ("engagedSessions", "conversions") rarely describe
    | what actually matters for a specific product. For a game/community site
    | like this one, engagement is better measured by an actual gameplay
    | event than by GA4's 10-second "engaged session" heuristic, and the
    | real conversion is whatever your business monetizes on (sign-ups,
    | paid tournament entries via PayOS, or ad revenue). Set the stages
    | below to match that, then mark the events as Key Events in GA4 Admin.
    |
    */
    'funnel' => [
        'tofu' => [
            'type' => env('GA_FUNNEL_TOFU_TYPE', 'metric'), // metric | event
            'metric' => env('GA_FUNNEL_TOFU_METRIC', 'sessions'),
            'event' => env('GA_FUNNEL_TOFU_EVENT'),
            'label' => env('GA_FUNNEL_TOFU_LABEL'),
        ],
        'mofu' => [
            'type' => env('GA_FUNNEL_MOFU_TYPE', 'metric'), // metric | event
            'metric' => env('GA_FUNNEL_MOFU_METRIC', 'engagedSessions'),
            'event' => env('GA_FUNNEL_MOFU_EVENT', 'game_started'), // e.g. AI game started, room joined/created
            'label' => env('GA_FUNNEL_MOFU_LABEL'),
        ],
        'bofu' => [
            'type' => env('GA_FUNNEL_BOFU_TYPE', 'revenue'), // metric | event | revenue
            'metric' => env('GA_FUNNEL_BOFU_METRIC', 'conversions'),
            'event' => env('GA_FUNNEL_BOFU_EVENT', 'sign_up'), // e.g. 'sign_up', 'tournament_paid'
            'currency' => env('GA_FUNNEL_BOFU_CURRENCY', 'VND'), // display only, revenue mode
            'label' => env('GA_FUNNEL_BOFU_LABEL'),
        ],
    ],

];
