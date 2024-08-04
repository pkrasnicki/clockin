rwildcard=$(foreach d,$(wildcard $(1:=/*)),$(call rwildcard,$d,$2) $(filter $(subst *,%,$2),$d))

SRC_FILES = $(call rwildcard,src,*.php)
TEST_FILES = $(call rwildcard,tests,*.php)

bin/clockin.phar: box.json composer.json $(SRC_FILES) $(TEST_FILES)
	composer install
	./vendor/bin/phpunit ./tests
	box compile

