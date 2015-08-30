#!/bin/sh
BASE_PATH=$(pwd)

# fetch kvuli pripadnym novym vetvim
git fetch origin

echo "Dostupne vetve v repozitari:"
git branch

echo "Nazev vetve, ktera se ma nasadit (prazdne znamena master):"
read BRANCH

if [ -z "$BRANCH" ]
then
    BRANCH="master"
fi

# pull aktualni verze kodu
git pull origin $BRANCH

# smazat cache konfigurace aplikace
rm -rf temp/cache/*

# grunt 
grunt