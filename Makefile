.PHONY: up down install update

up:
	docker compose up --build -d

down:
	docker compose down

install:
	cd main && npm install
	cd admin && composer install && npm install
	@test -f admin/.env || cp admin/.env.example admin/.env
	@test -f main/.env || (test -f main/.env.example && cp main/.env.example main/.env || true)

update:
	cd main && npx --yes npm-check-updates -u && npm install
	cd admin && composer update --with-all-dependencies --no-interaction && npx --yes npm-check-updates -u && npm install
