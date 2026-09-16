<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
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
            $request = (new RunReportRequest())
                ->setProperty($this->property)
                ->setDateRanges([$this->dateRange($startDate, $endDate)])
                ->setMetrics([
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'newUsers']),
                    new Metric(['name' => 'engagementRate']),
                    new Metric(['name' => 'averageSessionDuration']),
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'conversions']),
                ]);

            $response = $this->client->runReport($request);

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
            $request = (new RunReportRequest())
                ->setProperty($this->property)
                ->setDateRanges([$this->dateRange($startDate, $endDate)])
                ->setDimensions([new Dimension(['name' => 'date'])])
                ->setMetrics([
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'activeUsers']),
                ])
                ->setOrderBys([
                    (new OrderBy())->setDimension(
                        (new DimensionOrderBy())->setDimensionName('date')
                    ),
                ]);

            $response = $this->client->runReport($request);

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
            $request = (new RunReportRequest())
                ->setProperty($this->property)
                ->setDateRanges([$this->dateRange($startDate, $endDate)])
                ->setDimensions([new Dimension(['name' => 'pagePath'])])
                ->setMetrics([
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'averageSessionDuration']),
                ])
                ->setOrderBys([
                    (new OrderBy())
                        ->setMetric((new MetricOrderBy())->setMetricName('screenPageViews'))
                        ->setDesc(true),
                ])
                ->setLimit($limit);

            $response = $this->client->runReport($request);

            return $this->mapRows($response, ['path'], ['views', 'sessions', 'avg_duration']);
        });
    }

    /**
     * Traffic split by channel (Organic Search, Direct, Referral, Paid, Social...).
     */
    public function trafficSources(string $startDate, string $endDate): array
    {
        return $this->remember('traffic_sources', $startDate, $endDate, function () use ($startDate, $endDate) {
            $request = (new RunReportRequest())
                ->setProperty($this->property)
                ->setDateRanges([$this->dateRange($startDate, $endDate)])
                ->setDimensions([new Dimension(['name' => 'sessionDefaultChannelGroup'])])
                ->setMetrics([new Metric(['name' => 'sessions'])])
                ->setOrderBys([
                    (new OrderBy())
                        ->setMetric((new MetricOrderBy())->setMetricName('sessions'))
                        ->setDesc(true),
                ]);

            $response = $this->client->runReport($request);

            return $this->mapRows($response, ['channel'], ['sessions']);
        });
    }

    /**
     * Device category breakdown (desktop / mobile / tablet).
     */
    public function deviceBreakdown(string $startDate, string $endDate): array
    {
        return $this->remember('devices', $startDate, $endDate, function () use ($startDate, $endDate) {
            $request = (new RunReportRequest())
                ->setProperty($this->property)
                ->setDateRanges([$this->dateRange($startDate, $endDate)])
                ->setDimensions([new Dimension(['name' => 'deviceCategory'])])
                ->setMetrics([new Metric(['name' => 'activeUsers'])])
                ->setOrderBys([
                    (new OrderBy())
                        ->setMetric((new MetricOrderBy())->setMetricName('activeUsers'))
                        ->setDesc(true),
                ]);

            $response = $this->client->runReport($request);

            return $this->mapRows($response, ['device'], ['users']);
        });
    }

    /**
     * Top countries by active users.
     */
    public function topCountries(string $startDate, string $endDate, int $limit = 10): array
    {
        return $this->remember("countries_{$limit}", $startDate, $endDate, function () use ($startDate, $endDate, $limit) {
            $request = (new RunReportRequest())
                ->setProperty($this->property)
                ->setDateRanges([$this->dateRange($startDate, $endDate)])
                ->setDimensions([new Dimension(['name' => 'country'])])
                ->setMetrics([new Metric(['name' => 'activeUsers'])])
                ->setOrderBys([
                    (new OrderBy())
                        ->setMetric((new MetricOrderBy())->setMetricName('activeUsers'))
                        ->setDesc(true),
                ])
                ->setLimit($limit);

            $response = $this->client->runReport($request);

            return $this->mapRows($response, ['country'], ['users']);
        });
    }

    /**
     * TOFU / MOFU / BOFU funnel, built from GA4 metrics (see config/analytics.php
     * to point BOFU at a specific tracked event instead of generic conversions).
     */
    public function funnel(string $startDate, string $endDate): array
    {
        return $this->remember('funnel', $startDate, $endDate, function () use ($startDate, $endDate) {
            $tofu = $this->resolveStage($startDate, $endDate, config('analytics.funnel.tofu', []), 'Sessions');
            $mofu = $this->resolveStage($startDate, $endDate, config('analytics.funnel.mofu', []), 'Engaged Sessions');
            $bofu = $this->resolveStage($startDate, $endDate, config('analytics.funnel.bofu', []), 'Conversions');

            $isRevenue = $bofu['type'] === 'revenue';

            $result = [
                'tofu' => $tofu['value'],
                'mofu' => $mofu['value'],
                'bofu' => $bofu['value'],
                'is_revenue' => $isRevenue,
                'currency' => $isRevenue ? config('analytics.funnel.bofu.currency', 'VND') : null,
                'mofu_rate' => $tofu['value'] > 0 ? round($mofu['value'] / $tofu['value'] * 100, 1) : 0.0,
                'labels' => [
                    'tofu' => $tofu['label'],
                    'mofu' => $mofu['label'],
                    'bofu' => $bofu['label'],
                ],
            ];

            if ($isRevenue) {
                // Revenue isn't a headcount, so "% of MOFU" doesn't apply.
                // Show the exact revenue total (already in $bofu['value']) plus
                // revenue per engaged session as supporting context.
                $result['revenue_per_engaged_session'] = $mofu['value'] > 0 ? round($bofu['value'] / $mofu['value'], 4) : 0.0;
                $result['bofu_rate'] = null;
                $result['overall_rate'] = null;
            } else {
                $result['bofu_rate'] = $mofu['value'] > 0 ? round($bofu['value'] / $mofu['value'] * 100, 1) : 0.0;
                $result['overall_rate'] = $tofu['value'] > 0 ? round($bofu['value'] / $tofu['value'] * 100, 1) : 0.0;
            }

            return $result;
        });
    }

    /**
     * Resolve one funnel stage (TOFU/MOFU/BOFU) from its config: a stage can
     * be a plain GA4 metric, a specific named event, or (BOFU only) ad
     * revenue. This is what lets MOFU/TOFU point at a real product action
     * (e.g. "game_started") instead of being stuck on generic GA4 metrics.
     *
     * @return array{value: int|float, label: string, type: string}
     */
    protected function resolveStage(string $startDate, string $endDate, array $stage, string $defaultLabel): array
    {
        $type = $stage['type'] ?? 'metric';

        if ($type === 'revenue') {
            return [
                'value' => round($this->metricValue($startDate, $endDate, 'totalAdRevenue'), 2),
                'label' => $stage['label'] ?: 'Ad Revenue',
                'type' => 'revenue',
            ];
        }

        if ($type === 'event' && ! empty($stage['event'])) {
            return [
                'value' => (int) round($this->eventCount($startDate, $endDate, $stage['event'])),
                'label' => $stage['label'] ?: ('Event: ' . $stage['event']),
                'type' => 'event',
            ];
        }

        $metric = $stage['metric'] ?? 'sessions';

        return [
            'value' => (int) round($this->metricValue($startDate, $endDate, $metric)),
            'label' => $stage['label'] ?: $this->metricLabel($metric),
            'type' => 'metric',
        ];
    }

    /**
     * Fetch a single aggregate metric value (no dimensions) for the period.
     */
    protected function metricValue(string $startDate, string $endDate, string $metricName): float
    {
        $request = (new RunReportRequest())
            ->setProperty($this->property)
            ->setDateRanges([$this->dateRange($startDate, $endDate)])
            ->setMetrics([new Metric(['name' => $metricName])]);

        $response = $this->client->runReport($request);
        $row = $response->getRows()[0] ?? null;

        return $row ? (float) $row->getMetricValues()[0]->getValue() : 0.0;
    }

    /**
     * Fetch a total event count filtered to a single named event (e.g. "sign_up").
     */
    protected function eventCount(string $startDate, string $endDate, string $eventName): float
    {
        $request = (new RunReportRequest())
            ->setProperty($this->property)
            ->setDateRanges([$this->dateRange($startDate, $endDate)])
            ->setMetrics([new Metric(['name' => 'eventCount'])])
            ->setDimensionFilter(
                (new FilterExpression())->setFilter(
                    (new Filter())
                        ->setFieldName('eventName')
                        ->setStringFilter(
                            (new StringFilter())
                                ->setValue($eventName)
                                ->setMatchType(MatchType::EXACT)
                        )
                )
            );

        $response = $this->client->runReport($request);
        $row = $response->getRows()[0] ?? null;

        return $row ? (float) $row->getMetricValues()[0]->getValue() : 0.0;
    }

    protected function metricLabel(string $metric): string
    {
        return match ($metric) {
            'sessions' => 'Sessions',
            'newUsers' => 'New Users',
            'activeUsers' => 'Active Users',
            'engagedSessions' => 'Engaged Sessions',
            'conversions' => 'Conversions',
            'screenPageViews' => 'Page Views',
            default => $metric,
        };
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
                'funnel' => $this->funnel($startDate, $endDate),
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
                'funnel' => $this->emptyFunnel(),
                'error' => 'Could not load Google Analytics data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build a DateRange via its fluent setters (start_date/end_date are
     * already snake_case, so the array constructor works fine for this one).
     */
    protected function dateRange(string $startDate, string $endDate): DateRange
    {
        return (new DateRange())
            ->setStartDate($startDate)
            ->setEndDate($endDate);
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

    protected function emptyFunnel(): array
    {
        $bofuStage = config('analytics.funnel.bofu', []);
        $isRevenue = ($bofuStage['type'] ?? null) === 'revenue';

        return [
            'tofu' => 0,
            'mofu' => 0,
            'bofu' => 0,
            'is_revenue' => $isRevenue,
            'currency' => $bofuStage['currency'] ?? 'VND',
            'mofu_rate' => 0,
            'bofu_rate' => 0,
            'overall_rate' => 0,
            'revenue_per_engaged_session' => 0,
            'labels' => [
                'tofu' => config('analytics.funnel.tofu.label') ?: 'Sessions',
                'mofu' => config('analytics.funnel.mofu.label') ?: 'Engaged Sessions',
                'bofu' => $bofuStage['label'] ?: ($isRevenue ? 'Ad Revenue' : 'Conversions'),
            ],
        ];
    }
}
