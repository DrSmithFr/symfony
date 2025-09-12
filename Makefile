.PHONY: hooks nvm assets build install reload dependencies nvm start kill database fixtures migration test mock jwt

build: reload
install: env hooks dependencies assets nvm start build start database
reload: kill start

env:
	sudo apt install php8.4-cli php8.4-fpm php8.4-common php8.4-curl php8.4-pgsql php8.4-xml php8.4-mbstring php8.4-intl php8.4-redis php-xdebug
	sudo apt install nginx

dependencies:
	symfony composer self-update --2
	symfony composer install

assets:
	symfony console assets:install --symlink

start:
	docker compose -f docker-compose.yml -f docker-compose.override.yml up -d

kill:
	docker compose kill
	docker compose rm -f

database:
	-symfony console doctrine:database:drop --force
	symfony console doctrine:database:create
	symfony console doctrine:migration:migrate -n

fixtures:
	symfony console doctrine:fixtures:load -n

migration:
	symfony console doctrine:migration:diff

test:
	symfony php bin/phpunit --stop-on-failure

hooks:
	chmod +x hooks/pre-commit.sh
	chmod +x hooks/pre-push.sh
	rm -f .git/hooks/pre-commit
	rm -f .git/hooks/pre-push
	ln -s -f ../../hooks/pre-commit.sh .git/hooks/pre-commit
	ln -s -f ../../hooks/pre-push.sh .git/hooks/pre-push
