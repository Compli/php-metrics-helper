#!/bin/bash

docker run -it --rm -v $(pwd):/code -w /code --entrypoint php ekreative/php-cs-fixer /usr/local/bin/php-cs-fixer "$@"

