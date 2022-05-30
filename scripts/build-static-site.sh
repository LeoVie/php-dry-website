#!/bin/bash

cp -r builds/$(docker-compose exec -T -u www-data app ./bin/console app:build-static-site)/* builds/latest

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