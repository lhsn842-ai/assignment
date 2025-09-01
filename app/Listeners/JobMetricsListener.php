<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Prometheus\CollectorRegistry;

class JobMetricsListener
{
    private CollectorRegistry $registry;
    private array $startTimes = [];

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function handleProcessing(JobProcessing $event)
    {
        $this->startTimes[$event->job->getJobId()] = microtime(true);
    }

    public function handleProcessed(JobProcessed $event)
    {
        $id = $event->job->getJobId();
        $duration = microtime(true) - ($this->startTimes[$id] ?? microtime(true));
        $name = $event->job->resolveName();

        $counter = $this->registry->getOrRegisterCounter(
            'app',
            'jobs_processed_total',
            'Total jobs processed',
            ['job']
        );
        $counter->inc([$name]);

        $histogram = $this->registry->getOrRegisterHistogram(
            'app',
            'job_duration_seconds',
            'Duration of jobs',
            ['job'],
            [0.1, 0.5, 1, 3, 10, 30]
        );
        $histogram->observe($duration, [$name]);

        unset($this->startTimes[$id]);
    }

    public function handleFailed(JobFailed $event)
    {
        $name = $event->job->resolveName();

        $counter = $this->registry->getOrRegisterCounter(
            'app',
            'jobs_failed_total',
            'Total jobs failed',
            ['job']
        );
        $counter->inc([$name]);
    }
}
