<?php
$STDERR = fopen('php://stderr', 'w+');
function print2stderr($msg) {
	global $STDERR;
	fwrite($STDERR, $msg."\n");
}

if (count($argv) !== 2) {
	print2stderr('Vous devez spécifier le fichier à traiter');
	exit(-1);
}

$fileName = $argv[1];
if (!file_exists($fileName)) {
	print2stderr("Le fichier '$fileName' n'existe pas");	
	exit(-1);
}

require_once 'vendor/autoload.php';

date_default_timezone_set('UTC');
error_reporting(E_ALL);

use LyricsBookConvertor\Reader;

$reader = new Reader();
$reader->parse($fileName);
