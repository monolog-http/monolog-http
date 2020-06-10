phpcbf:
	php vendor/bin/phpcbf ./src ./tests

phpcs:
	php vendor/bin/phpcs ./src ./tests -n

php-cs-fixer:
	php bin/php-cs-fixer.phar fix -v

phpstan:
	php vendor/bin/phpstan analyse -c phpstan.neon src tests

phpunit:
	php vendor/bin/phpunit tests/ --colors=always --stop-on-failure --testdox --no-interaction

security-check:
	php bin/security-checker.phar security:check composer.lock
