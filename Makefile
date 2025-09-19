.PHONY: test codestyle phpstan devcontainer

test:
	vendor/bin/phpunit --color=always --no-coverage test/

codestyle:
	vendor/bin/php-cs-fixer check --config=.php-cs-fixer.php .

phpstan:
	vendor/bin/phpstan analyse --memory-limit=4G --configuration=phpstan.neon --no-progress --no-interaction src/ test/

devcontainer:
	docker build -t power-modules-devcontainer -f DockerfileDevContainer .
