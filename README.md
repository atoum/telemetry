# atoum telemetry [![Build Status](https://travis-ci.org/atoum/telemetry.svg?branch=master)](https://travis-ci.org/atoum/telemetry)

![atoum](http://downloads.atoum.org/images/logo.png)

## Running locally

To run a local telemtry instance and hack it around youwill have to use [Docker](https://www.docker.com/) and 
[Compose](https://docs.docker.com/compose/):

```sh
docker-compose up

# OR

docker-compose up -d # This will run the platform as a background daemon
```

Once started, you will be able to reach each service with the following URLs:

* InfluxDB admin: `http://localhost:8083`
* InfluxDB API: `http://localhost:8086`
* Telemtry API: `http://localhost:8087`
* Grafana: `http://localhost:8088`
* Redis:  `redis://localhost:8089`

_Redis does not come with any management console. You can use 
[redis-commander](https://www.npmjs.com/package/redis-commander) if you want to browse the database._

## Building the docker image

The telemetry platform is shipped and deployed as a docker image. To build it, run:

```sh
docker build -t atoum/telemetry .
```

## Configuring 

The telemtry platform is configured through environment variables:

| Variable                            | Description                            | Default     | API | Worker |
|-------------------------------------|----------------------------------------|-------------|:---:|:------:|
| `ATOUM_TELEMETRY_AUTH_TOKEN`        | Authentication token used for webhooks | `null`      | X   |        |
| `ATOUM_TELEMETRY_INFLUXDB_HOST`     | InfluxDB host name                     | `localhost` |     | X      |
| `ATOUM_TELEMETRY_INFLUXDB_PORT`     | InfluxDB API port                      | `8086`      |     | X      |
| `ATOUM_TELEMETRY_INFLUXDB_DATABASE` | InfluxDB database name                 | `atoum`     |     | X      |
| `ATOUM_TELEMETRY_REDIS_HOST`        | Redis host name                        | `localhost` | X   | X      |
| `ATOUM_TELEMETRY_REDIS_PORT`        | Redis port                             | `6379`      | X   | X      |
| `ATOUM_TELEMETRY_RESQUE_QUEUE`      | Resque queue name                      | `atoum`     | X   | X      |

## Running

To run the telemetry platform you will have to boot at least two containers: one for the HTTP API and another for the 
worker:

```sh
docker run --rm --name=atoum-telemetry-api -p 8087:80 -d atoum/telemetry
docker run --rm --name=atoum-telemetry-worker -d --entrypoint=php atoum/telemetry /app/bin/worker.php
```

**Do not forget to define the required environment variables for each container.**

## Telemetry API

The API exposes 2 useful routes:

* `POST /` is used by the [telemetry report]() to push data to the platform
* `POST /hook/{token}` is used by [Github's release webhook](https://developer.github.com/v3/activity/events/types/#releaseevent)

There are also two routes to access the API documentation:

* `GET /docs` to get the JSON definition of the API
* `GET /swagger` to reach the Swagger UI

## TODO

* Suites: Labels on legends
* Suites: Unit on "Number of tests"
* Means: Unit on "Assertions"
* Top 5: Remove time filter
* Top 5: Fix orderinf on "PHP versions"
* InfluxDB: Add env. vars to authenticate against database
