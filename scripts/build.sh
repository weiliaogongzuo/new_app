#!/bin/bash
docker container rm docker-example -f
docker rmi chenxinying/docker-example –f
docker build -t chenxinying/docker-example .
docker run -d --name docker-example -p 80:80 -v ~/docker-project-example/src:/var/www/html --link mysql:mysql chenxinying/docker-example