#!/usr/bin/env bash

if [[ -z "${DOCKER_REPO}" ]]; then
  DOCKER_REPO="mklocke/liga-manager-api"
fi

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi

trap 'rc=$?' ERR

# Build images
docker build -f docker/php/Dockerfile -t $DOCKER_REPO:$TAG .
docker build -f docker/php/Dockerfile -t $DOCKER_REPO:$TAG-xdebug --build-arg XDEBUG=1 .

# Define environment
MYSQL_ENV_ARGS="-e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_HOST=mariadb -e MYSQL_DATABASE=test -e MYSQL_USER=test -e MYSQL_PASSWORD=test"
EMAIL_ENV_ARGS="-e SMTP_HOST=maildev -e SMTP_PORT=25 -e MAILDEV_URI=http://maildev -e EMAIL_SENDER=admin@example.com;Admin"
APP_ENV_ARGS="$MYSQL_ENV_ARGS $EMAIL_ENV_ARGS -e LOG_LEVEL=warning -e REDIS_HOST=redis"

# Launch MariaDB and Redis containers
docker run -d --name mariadb ${MYSQL_ENV_ARGS} mariadb > /dev/null
docker run -d --name redis redis:4-alpine > /dev/null
docker run -d --name maildev djfarrelly/maildev > /dev/null

# Wait until MariaDB and Redis are ready
docker run --link redis --rm dadarek/wait-for-dependencies redis:6379
docker run --link mariadb --rm dadarek/wait-for-dependencies mariadb:3306

# Run tests
docker run --link mariadb --link redis --link maildev --rm ${APP_ENV_ARGS} \
    mklocke/liga-manager-api:${TAG} sh -c "bin/install.sh && phpunit"

if  [[ $1 = "-c" ]]; then
    # Run tests with coverage
    docker run --link mariadb --link redis --link maildev --rm ${APP_ENV_ARGS} -v $PWD/coverage:/coverage \
        mklocke/liga-manager-api:${TAG}-xdebug sh -c "bin/install.sh && phpunit --coverage-clover /coverage/clover.xml"

    # Upload coverage data
    docker run --rm -v $PWD:/var/www/api -e TRAVIS -e TRAVIS_JOB_ID \
        kielabokkie/coveralls-phpcov sh -c "cd /var/www/api && php-coveralls -v -x coverage/clover.xml -o coverage/coveralls.json"
fi

# Cleanup
docker rm -f mariadb redis maildev > /dev/null

exit ${rc}