TAG_VERSION=`git describe --tag | cut -d- -f1`
FILE_VERSION=`cat style.css | grep Version | sed 's/Version: //'`
FILES=coalescence/*.php \
      coalescence/*.css \
      coalescence/*.js \
      theme/index.html \
      *.php \
      *.css \
      screenshot.png \
      rules.xml \
      README.txt \
      LICENCE.txt \
      documentation/*

.PHONY: test
test: unittest

.PHONY: unittest
unittest:
	@php tests/phpunit-3.7.22.phar

.PHONY: documentation
documentation: docs/index.rst
	@rm -rf documentation/*
	@echo Generating documentation
	@rst2html docs/index.rst > documentation/index.html
	@rst2html docs/getting_started.rst > documentation/getting_started.html
	@cp -R docs/images documentation/images

.PHONY: sonar
sonar:
	@sonar-runner -Dsonar.projectVersion=$(FILE_VERSION)

.PHONY: release
release: test documentation
	@echo Creating coalescence-$(TAG_VERSION).zip
	@zip ../coalescence-$(TAG_VERSION).zip $(FILES)
