phpcbf:
	php vendor/bin/phpcbf src tests

phpcs:
	php vendor/bin/phpcs src tests -n

php-cs-fixer:
	php bin/php-cs-fixer.phar fix -v

phpstan:
	php vendor/bin/phpstan analyse -c phpstan.neon src tests

phpunit:
	php vendor/bin/phpunit --colors=always --stop-on-failure --testdox --no-interaction
