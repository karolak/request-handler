docker-build:
	docker-compose build
docker-up:
	docker-compose up -d
docker-down:
	docker-compose down
composer-install:
	docker-compose exec php composer install
composer-update:
	docker-compose exec php composer update
composer-phpstan:
	docker-compose exec php composer phpstan
composer-psalm:
	docker-compose exec php composer psalm
composer-test:
	docker-compose exec php composer test
composer-coverage:
	docker-compose exec php composer coverage
test: composer-phpstan composer-psalm composer-test