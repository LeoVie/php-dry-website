#!/bin/bash

docker-compose exec -T -u root app /bin/sh -c "chmod -R 777 /var/www/var"
docker-compose exec -T -u www-data app ./bin/console app:build-static-site

RETURN_CODE=$?

if [ $RETURN_CODE -ne 0 ]
then
  echo "Failure" >&2
  echo "app logs:"
  docker-compose logs app
  echo "web logs:"
  docker-compose logs web
  exit $RETURN_CODE
fi