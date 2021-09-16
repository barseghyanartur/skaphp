.PHONY: help install lint test release clean clean-test clean-build all

help:
	@echo "test - Run tests"
	@echo "install - Install"
	@echo "lint - Check style with prettier"
	@echo "release - Publish package on npmjs"

clean-build:
	rm -rf ./.build
	rm -rf ./vendor
	rm -rf ./composer.lock
	rm -rf ./composer.phar

clean-test:
	rm -fr ./coverage
	rm -f ./coverage.xml
	find . -name '*.js,cover' -exec rm -f {} +

install:
	composer install && composer update

clean: clean-build clean-test

# lint: clean-test
# 	npx prettier --write .

test: clean-test install
	composer test; \
	status=$$?; \
	exit $$status

release: clean
	npm publish --access public

all: clean lint install test release
