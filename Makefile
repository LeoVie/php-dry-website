php_version=8.0.18

.PHONY: setup_dev_environment
setup_dev_environment:
	make setup_env env=dev
	make setup_ci_images php_version=$(php_version)
	make install env=dev

.PHONY: setup_test_environment
setup_test_environment:
ifndef php_version
	$(error php_version is not set)
endif
	make setup_env env=test
	make setup_ci_images php_version=$(php_version)
	make install env=test

.PHONY: setup_prod_environment
setup_prod_environment:
ifndef php_version
	$(error php_version is not set)
endif
	make setup_env env=prod
	make setup_ci_images php_version=$(php_version)
	make install env=prod

.PHONY: setup_ci_images
setup_ci_images:
ifndef php_version
	$(error php_version is not set)
endif
	make build_composer_image php_version=$(php_version)
	make build_php-cs-fixer_image php_version=$(php_version)
	make build_phpstan_image
	make build_phpunit_image php_version=$(php_version)
	make build_psalm_image php_version=$(php_version)
	make build_infection_image php_version=$(php_version)

.PHONY: build_composer_image
build_composer_image:
ifndef php_version
	$(error php_version is not set)
endif
	cd docker && docker build . -f composer.Dockerfile -t php-dry-website/composer:latest --build-arg PHP_VERSION=$(php_version) && cd -

.PHONY: composer
composer:
ifndef command
	$(error command is not set)
endif
	docker run -v $(shell pwd):/app php-dry-website/composer:latest $(command)

.PHONY: install
install:
ifndef env
	$(error env is not set)
endif
	@if [ $(env) = "prod" ]; then \
		make composer command="install --no-dev"; \
  		npm install; \
		npm run build; \
	else \
		make composer command="install"; \
		npm install; \
		npm run dev; \
	fi

.PHONY: setup_env
setup_env:
ifndef env
	$(error env is not set)
endif
	cp .env.$(env) .env

.PHONY: test
test: phpstan psalm phpunit infection

.PHONY: build_php-cs-fixer_image
build_php-cs-fixer_image:
ifndef php_version
	$(error php_version is not set)
endif
	cd docker && docker build . -f php-cs-fixer.Dockerfile -t php-dry-website/php-cs-fixer:latest --build-arg PHP_VERSION=$(php_version) && cd -

.PHONY: php-cs-fixer
php-cs-fixer:
	docker run -v ${PWD}:/app --rm php-dry-website/php-cs-fixer:latest fix --config /app/build/config/.php-cs-fixer.php

.PHONY: build_phpstan_image
build_phpstan_image:
	cd docker && docker build . -f phpstan.Dockerfile -t php-dry-website/phpstan:latest && cd -

.PHONY: phpstan
phpstan:
	docker run -v ${PWD}:/app --rm php-dry-website/phpstan:latest analyse -c /app/build/config/phpstan.neon

.PHONY: build_phpunit_image
build_phpunit_image:
ifndef php_version
	$(error php_version is not set)
endif
	cd docker && docker build . -f phpunit.Dockerfile -t php-dry-website/phpunit:latest --build-arg PHP_VERSION=$(php_version) && cd -

.PHONY: phpunit
phpunit:
	docker run -v ${PWD}:/app --rm php-dry-website/phpunit:latest

.PHONY: phpunit-group
ifndef group
	$(error group is not set)
endif
phpunit-group:
	docker run -v ${PWD}:/app --rm php-dry-website/phpunit:latest -- --group $(group)

.PHONY: build_psalm_image
build_psalm_image:
ifndef php_version
	$(error php_version is not set)
endif
	cd docker && docker build . -f psalm.Dockerfile -t php-dry-website/psalm:latest --build-arg PHP_VERSION=$(php_version) && cd -

.PHONY: psalm
psalm:
	docker run -v ${PWD}:/app --rm php-dry-website/psalm:latest

.PHONY: build_infection_image
build_infection_image:
ifndef php_version
	$(error php_version is not set)
endif
	cd docker && docker build . -f infection.Dockerfile -t php-dry-website/infection:latest --build-arg PHP_VERSION=$(php_version) && cd -

.PHONY: infection
infection:
	docker run -v ${PWD}:/app --rm php-dry-website/infection:latest

.PHONY: cypress
cypress:
	npm run cypress:run

.PHONY: start
start:
	UID="$(shell id -u)" GID="$(shell id -g)" docker-compose up -d $(extra_args)

.PHONY: stop
stop:
	UID="$(shell id -u)" GID="$(shell id -g)" docker-compose down --remove-orphans

.PHONY: restart
restart: stop start

.PHONY: build_static_site
build_static_site:
	make setup_prod_environment
	chmod +x ./scripts/build-static-site.sh
	./scripts/build-static-site.sh

.PHONY: build_static_site_dev
build_static_site_dev:
	chmod +x ./scripts/build-static-site.sh
	./scripts/build-static-site.sh

.PHONY: convert_blog_articles
convert_blog_articles:
	cd blog_articles && for f in *.md; do \
		docker run --rm -v "$(shell pwd)/blog_articles:/data" -v$(shell pwd)/pandoc:/custom_pandoc --user $(shell id -u):$(shell id -g) pandoc/latex:2.18 "$$f" -o "rendered/$${f%.txt}_metadata.yaml" --data-dir=/custom_pandoc --template=metadata_template.yaml --to=plain; \
		docker run --rm -v "$(shell pwd)/blog_articles:/data" --user $(shell id -u):$(shell id -g) pandoc/latex:2.18 "$$f" -o "rendered/$${f%.txt}.html"; \
	done && cd -

.PHONY: build_docs
build_docs:
	docker-compose exec -T mkdocs bash -c "cd /mkdocs && mkdocs build && chmod -R 777 /builds/latest/documentation"