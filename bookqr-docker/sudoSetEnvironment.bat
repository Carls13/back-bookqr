#!/bin/bash
sudo docker-compose up -d
sudo docker-compose exec app composer install
sudo docker-compose exec app php bin/console doctrine:database:drop --force --no-interaction
sudo docker-compose exec app php bin/console doctrine:database:create --no-interaction
sudo docker-compose exec app php bin/console doctrine:schema:create
