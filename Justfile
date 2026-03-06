list:
    @just --list

install:
    composer install

test:
    composer test

test-coverage:
    composer test-coverage

analyse:
    composer analyse

format:
    composer format

format-check:
    composer format -- --test

verify: format-check analyse test
