#!/bin/bash

echo "Docker - rebuild images"
cd docker/development
docker-compose -p fitchartnet up -d --build --force-recreate