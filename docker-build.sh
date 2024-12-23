#!/bin/bash

echo "Docker - build images"
cd docker/development
docker-compose -p fitchartnet up -d --build