#!/bin/bash
docker container rm docker-project-example -f
docker rmi docker-project-example –f
docker build -t docker-project-example .
docker run -d --name docker-project-example -p 20000:80 -v ~/docker-project-example/src:/var/www/html --link mysql:mysql docker-project-example