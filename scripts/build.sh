#!/bin/bash
docker rmi chenxinying/docker-example –f
docker build -t chenxinying/docker-example .
docker run -d --name docker-example -p 80:80 --link mysql:mysql chenxinying/docker-example