<?php

namespace Compli;

use Exception;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;

class Metrics
{
    /**
     * Initializes prometheus config
     */
    private static function init($remoteRedis = false)
    {
        if ($remoteRedis) {
            $host = env('REDIS_REMOTE_HOST', 'nodefault');
        } else {
            $host = env('REDIS_LOCAL_HOST', 'localhost');
        }
        Redis::setDefaultOptions(
            [
                'host' => $host,
                'port' => env('REDIS_PORT', '6379'),
                'password' => null,
                'timeout' => 0.1, // in seconds
                'read_timeout' => 10, // in seconds
                'persistent_connections' => false
            ]
        );
    }

    /**
     * @param $metricName string Name of the metric
     * @param $metricHelp string Help text for the metric
     * @param $labelNames array Label names
     * @param $labelValues array Label values
     * @param $value int Value to set gauge to
     * @param $remoteRedis boolean Whether the Redis store is local or remote
     */
    public static function counter($metricName, $metricHelp, $labelNames, $labelValues, $value, $remoteRedis = false)
    {
        try {
            self::init($remoteRedis);

            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter(env('PHP_METRICS_HELPER_NAMESPACE', 'nodefault'), $metricName, $metricHelp, $labelNames);
            $counter->incBy($value, $labelValues);

        } catch (\Prometheus\Exception $e) {
            self::logError($e->getMessage());
        }
    }

    /**
     * @param $metricName string Name of the metric
     * @param $metricHelp string Help text for the metric
     * @param $labelNames array Label names
     * @param $labelValues array Label values
     * @param $value int Value to set gauge to
     * @param $remoteRedis boolean Whether the Redis store is local or remote
     */
    public static function gauge($metricName, $metricHelp, $labelNames, $labelValues, $value, $remoteRedis = false)
    {
        try {
            self::init($remoteRedis);

            $registry = CollectorRegistry::getDefault();
            $gauge = $registry->getOrRegisterGauge(env('PHP_METRICS_HELPER_NAMESPACE', 'nodefault'), $metricName, $metricHelp, $labelNames);
            $gauge->set($value, $labelValues);

        } catch (\Prometheus\Exception $e) {
            self::logError($e->getMessage());
        }
    }

    /**
     * Report error
     * Laravel only
     *
     * @param $exception Exception
     */
    public static function reportError($exception, $remoteRedis = false)
    {
        try {
            self::counter('error', 'Errors',
                ['client_id', 'client_name', 'exception_type', 'exception_file'],
                [\Request::get('clientId'), \Request::get('clientName'), get_class($exception), $exception->getFile()],
                1,
                $remoteRedis
            );
        } catch (\Prometheus\Exception $e) {
            self::logError($e->getMessage());
        }
    }

    /**
     * Handle response
     * Laravel only, for use in middleware
     *
     * @param $response Response
     */
    public static function handleResponse($response) {
        $clientId = \Request::get('clientId');
        $clientName = \Request::get('clientName');
        $route = \Route::currentRouteAction();
        self::counter('http_requests_total', 'Total number of HTTP requests',
            ['client_id', 'client_name', 'status_code', 'route'],
            [$clientId, $clientName, $response->status(), $route],
            1
        );
        self::gauge('http_request_duration', 'Durations of HTTP requests',
            ['client_id', 'client_name', 'status_code', 'route'],
            [$clientId, $clientName, $response->status(), $route],
            microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]
        );
    }

    /**
     * Display the metrics file
     */
    public function publish()
    {
        try {
            self::init(false);

            $registry = CollectorRegistry::getDefault();

            $renderer = new RenderTextFormat();
            $result = $renderer->render($registry->getMetricFamilySamples());

            header('Content-type: ' . RenderTextFormat::MIME_TYPE);
            echo $result;
        } catch (\Prometheus\Exception $e) {
            self::logError($e->getMessage());
            return response('Error accessing metrics storage', 503);
        }
    }

    /**
     * Write error to stderr
     */
    private static function logError($error)
    {
        echo $error;
    }
}
