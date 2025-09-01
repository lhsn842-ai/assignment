<?php

namespace App\Listeners;

use Nuwave\Lighthouse\Events\EndRequest;
use Nuwave\Lighthouse\Events\StartRequest;
use Prometheus\CollectorRegistry;

class GraphQLMetricsListener
{
    private CollectorRegistry $registry;
    private array $startTimes = [];

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function handleStart(StartRequest $event)
    {
        $this->startTimes[spl_object_id($event)] = microtime(true);
    }

    public function handleEnd(EndRequest $event)
    {
        $id = spl_object_id($event);
        $duration = microtime(true) - ($this->startTimes[$id] ?? microtime(true));

        $type = $event->operationName ?? 'anonymous';

        // Count requests
        $counter = $this->registry->getOrRegisterCounter(
            'app',
            'graphql_requests_total',
            'Total GraphQL operations',
            ['operation']
        );
        $counter->inc([$type]);

        // Track duration
        $histogram = $this->registry->getOrRegisterHistogram(
            'app',
            'graphql_request_duration_seconds',
            'Duration of GraphQL operations',
            ['operation'],
            [0.05, 0.1, 0.3, 1, 3, 5]
        );
        $histogram->observe($duration, [$type]);

        unset($this->startTimes[$id]);
    }
}
