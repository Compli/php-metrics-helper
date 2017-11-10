<?php

namespace App\Http\Controllers;

use Exception;
# TODO change logging?
use Illuminate\Support\Facades\Log;
use Prometheus\CollectorRegistry;
# TODO confirm exception handling
use Prometheus\Exception\StorageException;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;

class Metrics extends Controller
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

        # TODO change this to \Prometheus\Exception?
        # } catch (\Prometheus\Exception $e) {
        } catch (StorageException $e) {
            Log::error($e->getMessage());
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

        } catch (StorageException $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Report error
     *
     * @param $exception Exception
     */
    public static function reportError($exception)
    {
        try {
            self::counter('error',
                'Errors',
                ['client_id', 'client_name', 'exception_type', 'exception_file'],
                [\Request::get('clientId'), \Request::get('clientName'), get_class($exception), $exception->getFile()]);
        } catch (StorageException $e) {
            Log::error($e->getMessage());
        }
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
        } catch (StorageException $e) {
            Log::error($e->getMessage());
            return response('Error accessing metrics storage', 503);
        }
    }

}
