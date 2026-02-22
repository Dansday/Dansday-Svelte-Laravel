.PHONY: up down install update

up:
	docker compose up --build -d

down:
	docker compose down

install:
	docker compose run --rm -v $(PWD)/main:/app main npm install
	docker compose run --rm -v $(PWD)/admin:/app admin composer install
	docker compose run --rm -v $(PWD)/admin:/app admin npm install

update:
	docker compose run --rm -v $(PWD)/main:/app main npm update
	docker compose run --rm -v $(PWD)/admin:/app admin composer update
	docker compose run --rm -v $(PWD)/admin:/app admin npm update
