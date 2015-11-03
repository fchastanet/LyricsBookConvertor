# LyricsBookConvertor
Permet de convertir mon fichier de chanson au format word vers le format json(script insert couchdb) 

# Installation
curl -sS https://getcomposer.org/installer | php
php composer.phar install

# Convertir un fichier
php index.php "chemin du fichier doc à convertir" > fichier.json

# Alternative
utiliser nodejs et https://www.npmjs.com/package/mammoth => supporte la conversion markdown
