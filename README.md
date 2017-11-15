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

#### Local metric store (normal)

```php
```

#### Remote metric store (for jobs)

```php
```

