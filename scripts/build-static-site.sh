#!/bin/bash

build_name=$(docker-compose exec -T -u www-data app ./bin/console app:build-static-site)
mkdir -p builds/latest
cp -r builds/$build_name/* builds/latest

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