#!/bin/bash
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php bin/console doctrine:database:drop --force --no-interaction
docker-compose exec app php bin/console doctrine:database:create --no-interaction
docker-compose exec app php bin/console doctrine:schema:create
