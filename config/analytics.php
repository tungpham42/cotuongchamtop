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
    'credentials_path' =>  storage_path('app/google/ga4-service-account.json'),

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

];
