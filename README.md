# php-metrics-helper

Wraps the semi official PHP client library for Prometheus

Allows the usage of a persistent redis store for non-persistent jobs, via `REDIS_REMOTE_HOST`

## Set the following env vars

* `REDIS_REMOTE_HOST=my-app-redis.src.cluster.local`
* `REDIS_LOCAL_HOST=localhost`
* `REDIS_PORT=6379`
* `PHP_METRICS_HELPER_NAMESPACE=my_app`
