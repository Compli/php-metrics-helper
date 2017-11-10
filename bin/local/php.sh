#!/bin/bash

# Use this script to use the proper version of PHP in Docker

echo "Running the command 'docker run -it --rm -v $(pwd):/code:rw --user $(id -u) --workdir /code --entrypoint php 933480341162.dkr.ecr.us-west-2.amazonaws.com/php7-nginx:latest $@'"
echo

docker run -it --rm -v $(pwd):/code:rw --user $(id -u) --workdir /code --entrypoint php 933480341162.dkr.ecr.us-west-2.amazonaws.com/php7-nginx:latest "$@"

