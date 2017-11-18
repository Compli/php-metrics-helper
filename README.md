# php-metrics-helper

Wrap the semi official PHP client library for Prometheus

## Features

* Allow the usage of a persistent redis store for non-persistent jobs, such as in a Kubernetes environment

## Set the following env vars

* `REDIS_REMOTE_HOST=my-app-redis.svc.cluster.local`
* `REDIS_LOCAL_HOST=localhost`
* `REDIS_PORT=6379`
* `PHP_METRICS_HELPER_NAMESPACE=my_app`

## Examples

_Also see apps such as case-tracking for examples_

#### Local metric store (normal)

```php
Metrics::gauge('notification_duration', 'Duration of the Notification cronjob in seconds', [], [], $timeTook);
Metrics::counter('notification_cases_checked_total', 'How many cases have been checked', [], [], count($casesChecked)
try {
    # something
} catch (MyException $e) {
    Metrics::reportError($e);
    return;
}
```

#### Remote metric store (for jobs)

```php
Metrics::gauge('notification_duration', 'Duration of the Notification cronjob in seconds', [], [], $timeTook, true);
Metrics::counter('notification_cases_checked_total', 'How many cases have been checked', [], [], count($casesChecked), true)
try {
    # something
} catch (MyException $e) {
    Metrics::reportError($e, true);
    return;
}
```

