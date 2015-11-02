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

echo "Klaar!"

echo "Veel plezier met Zermelo-Telegram-Bot!"
