.PHONY: install
install:
	composer install

.PHONY: unit-tests
unit-tests:
	php ./vendor/bin/atoum --directories tests/Unit --no-code-coverage

.PHONY: performance-tests
performance-tests:
	php ./tests/Performance/console performance:array:launch
