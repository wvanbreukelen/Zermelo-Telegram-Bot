#!/bin/sh

echo "Starten met het installeren van Zermelo-Telegram-Bot..."

echo "Schoonmaken..."

# Eerst even gaan schoonmaken
sudo rm -rf ZermeloRoosterPHP
sudo rm -rf gebruikers

# Eventuele backup maken van config.php naar home directory
sudo cp config.php ~/config.php

echo "Klaar!"

echo "Downloaden benodigde bestanden..."

git clone https://github.com/wvanbreukelen/ZermeloRoosterPHP.git

echo "Bestanden met succes gedownload!"

echo "Mappenstructuur aanmaken..."

mkdir gebruikers
touch gebruikers/geregistreerd.txt

echo "Sample configuratie bestand overzetten..."

cp config_sample.php config.php

echo "Cache.json bestand aanmaken..."

touch cache.json

#echo "<?php
#
#$token = '<token>';
#$website = 'https://api.telegram.org/bot'.$token.'/';
#
#" > config.php

echo "Klaar!"

echo "Veel plezier met Zermelo-Telegram-Bot!"
