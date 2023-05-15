docker-compose build
docker-compose up -d
docker-compose exec php bash -c "cd /home/wwwroot/app && composer install"
