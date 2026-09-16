<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleAnalyticsService
{
    protected BetaAnalyticsDataClient $client;
    protected string $property;
    protected int $cacheTtl;

    public function __construct()
    {
        $propertyId = config('analytics.property_id');
        $credentialsPath = config('analytics.credentials_path');

        if (empty($propertyId)) {
            throw new RuntimeException('GOOGLE_ANALYTICS_PROPERTY_ID is not set.');
        }

        if (empty($credentialsPath) || ! file_exists($credentialsPath)) {
            throw new RuntimeException("GA4 service account credentials not found at [{$credentialsPath}].");
        }

        $this->property = 'properties/' . $propertyId;
        $this->cacheTtl = (int) config('analytics.cache_ttl', 30);

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentialsPath,
        ]);
    }

    /**
     * Top-line totals for the period: sessions, users, engagement, pageviews, etc.
     */
    public function overview(string $startDate, string $endDate): array
    {
        return $this->remember('overview', $startDate, $endDate, function () use ($startDate, $endDate) {
            $response = $this->client->runReport([
                'property' => $this->property,
                'dateRanges' => [new DateRange(['start_date' => $startDate, 'end_date' => $endDate])],
                'metrics' => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'newUsers']),
                    new Metric(['name' => 'engagementRate']),
                    new Metric(['name' => 'averageSessionDuration']),
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'conversions']),
                ],
            ]);

            $row = $response->getRows()[0] ?? null;

            if (! $row) {
                return $this->emptyOverview();
            }

            $values = array_map(fn ($v) => $v->getValue(), iterator_to_array($row->getMetricValues()));

            return [
                'sessions' => (int) $values[0],
                'active_users' => (int) $values[1],
                'new_users' => (int) $values[2],
                'engagement_rate' => round(((float) $values[3]) * 100, 1),
                'avg_session_duration' => round((float) $values[4]),
                'page_views' => (int) $values[5],
                'conversions' => (int) $values[6],
            ];
        });
    }

    /**
     * Daily sessions/users, for a line chart.
     */
    public function timeseries(string $startDate, string $endDate): array
    {
        return $this->remember('timeseries', $startDate, $endDate, function () use ($startDate, $endDate) {
            $response = $this->client->runReport([
                'property' => $this->property,
                'dateRanges' => [new DateRange(['start_date' => $startDate, 'end_date' => $endDate])],
                'dimensions' => [new Dimension(['name' => 'date'])],
                'metrics' => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'activeUsers']),
                ],
                'orderBys' => [
                    new OrderBy([
                        'dimension' => new DimensionOrderBy(['dimension_name' => 'date']),
                    ]),
                ],
            ]);

            $labels = [];
            $sessions = [];
            $users = [];

            foreach ($response->getRows() as $row) {
                $rawDate = $row->getDimensionValues()[0]->getValue(); // YYYYMMDD
                $labels[] = \Carbon\Carbon::createFromFormat('Ymd', $rawDate)->format('M j');
                $metricValues = iterator_to_array($row->getMetricValues());
                $sessions[] = (int) $metricValues[0]->getValue();
                $users[] = (int) $metricValues[1]->getValue();
            }

            return compact('labels', 'sessions', 'users');
        });
    }

    /**
     * Best-performing pages by pageviews.
     */
    public function topPages(string $startDate, string $endDate, int $limit = 10): array
    {
        return $this->remember("top_pages_{$limit}", $startDate, $endDate, function () use ($startDate, $endDate, $limit) {
            $response = $this->client->runReport([
                'property' => $this->property,
                'dateRanges' => [new DateRange(['start_date' => $startDate, 'end_date' => $endDate])],
                'dimensions' => [new Dimension(['name' => 'pagePath'])],
                'metrics' => [
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'averageSessionDuration']),
                ],
                'orderBys' => [
                    new OrderBy([
                        'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews']),
                        'desc' => true,
                    ]),
                ],
                'limit' => $limit,
            ]);

            return $this->mapRows($response, ['path'], ['views', 'sessions', 'avg_duration']);
        });
    }

    /**
     * Traffic split by channel (Organic Search, Direct, Referral, Paid, Social...).
     */
    public function trafficSources(string $startDate, string $endDate): array
    {
        return $this->remember('traffic_sources', $startDate, $endDate, function () use ($startDate, $endDate) {
            $response = $this->client->runReport([
                'property' => $this->property,
                'dateRanges' => [new DateRange(['start_date' => $startDate, 'end_date' => $endDate])],
                'dimensions' => [new Dimension(['name' => 'sessionDefaultChannelGroup'])],
                'metrics' => [new Metric(['name' => 'sessions'])],
                'orderBys' => [
                    new OrderBy([
                        'metric' => new MetricOrderBy(['metric_name' => 'sessions']),
                        'desc' => true,
                    ]),
                ],
            ]);

            return $this->mapRows($response, ['channel'], ['sessions']);
        });
    }

    /**
     * Device category breakdown (desktop / mobile / tablet).
     */
    public function deviceBreakdown(string $startDate, string $endDate): array
    {
        return $this->remember('devices', $startDate, $endDate, function () use ($startDate, $endDate) {
            $response = $this->client->runReport([
                'property' => $this->property,
                'dateRanges' => [new DateRange(['start_date' => $startDate, 'end_date' => $endDate])],
                'dimensions' => [new Dimension(['name' => 'deviceCategory'])],
                'metrics' => [new Metric(['name' => 'activeUsers'])],
                'orderBys' => [
                    new OrderBy([
                        'metric' => new MetricOrderBy(['metric_name' => 'activeUsers']),
                        'desc' => true,
                    ]),
                ],
            ]);

            return $this->mapRows($response, ['device'], ['users']);
        });
    }

    /**
     * Top countries by active users.
     */
    public function topCountries(string $startDate, string $endDate, int $limit = 10): array
    {
        return $this->remember("countries_{$limit}", $startDate, $endDate, function () use ($startDate, $endDate, $limit) {
            $response = $this->client->runReport([
                'property' => $this->property,
                'dateRanges' => [new DateRange(['start_date' => $startDate, 'end_date' => $endDate])],
                'dimensions' => [new Dimension(['name' => 'country'])],
                'metrics' => [new Metric(['name' => 'activeUsers'])],
                'orderBys' => [
                    new OrderBy([
                        'metric' => new MetricOrderBy(['metric_name' => 'activeUsers']),
                        'desc' => true,
                    ]),
                ],
                'limit' => $limit,
            ]);

            return $this->mapRows($response, ['country'], ['users']);
        });
    }

    /**
     * Fetch every dataset the marketing dashboard needs in one call.
     */
    public function fullReport(string $startDate, string $endDate): array
    {
        try {
            return [
                'overview' => $this->overview($startDate, $endDate),
                'timeseries' => $this->timeseries($startDate, $endDate),
                'top_pages' => $this->topPages($startDate, $endDate),
                'traffic_sources' => $this->trafficSources($startDate, $endDate),
                'devices' => $this->deviceBreakdown($startDate, $endDate),
                'countries' => $this->topCountries($startDate, $endDate),
                'error' => null,
            ];
        } catch (Throwable $e) {
            Log::error('GA4 report fetch failed: ' . $e->getMessage());

            return [
                'overview' => $this->emptyOverview(),
                'timeseries' => ['labels' => [], 'sessions' => [], 'users' => []],
                'top_pages' => [],
                'traffic_sources' => [],
                'devices' => [],
                'countries' => [],
                'error' => 'Could not load Google Analytics data: ' . $e->getMessage(),
            ];
        }
    }

    protected function remember(string $key, string $startDate, string $endDate, \Closure $callback)
    {
        $cacheKey = "ga4:{$key}:{$this->property}:{$startDate}:{$endDate}";

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheTtl), $callback);
    }

    /**
     * Turn a runReport response into an array of associative rows.
     *
     * @param  string[]  $dimensionKeys
     * @param  string[]  $metricKeys
     */
    protected function mapRows($response, array $dimensionKeys, array $metricKeys): array
    {
        $rows = [];

        foreach ($response->getRows() as $row) {
            $entry = [];
            $dimensionValues = iterator_to_array($row->getDimensionValues());
            $metricValues = iterator_to_array($row->getMetricValues());

            foreach ($dimensionKeys as $i => $key) {
                $entry[$key] = $dimensionValues[$i]->getValue();
            }

            foreach ($metricKeys as $i => $key) {
                $entry[$key] = is_numeric($metricValues[$i]->getValue())
                    ? (float) $metricValues[$i]->getValue()
                    : $metricValues[$i]->getValue();
            }

            $rows[] = $entry;
        }

        return $rows;
    }

    protected function emptyOverview(): array
    {
        return [
            'sessions' => 0,
            'active_users' => 0,
            'new_users' => 0,
            'engagement_rate' => 0,
            'avg_session_duration' => 0,
            'page_views' => 0,
            'conversions' => 0,
        ];
    }
}
