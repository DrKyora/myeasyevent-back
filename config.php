<?php

define(constant_name: 'LOG_ALL_PHP', value: true);

use App\Lib\Tools;

require_once __DIR__ . '/src/Lib/Tools.php';
 $tools = new Tools();

set_error_handler(callback: [$tools, "myErrorHandler"]);


function loadEnv($path)
{
    if (!file_exists(filename: $path)) {
        throw new Exception(message: "Le fichier de configuration n'existe pas : " . $path);
    }

    $lines = file(filename: $path, flags: FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(haystack: trim(string: $line), needle: '#') === 0) {
            continue; // Ignorer les commentaires
        }

        list($key, $value) = explode(separator: '=', string: $line, limit: 2);
        $_ENV[trim(string: $key)] = trim(string: $value);
    }
}


// Appel de la fonction pour charger les variables d'environnement
switch ($_SERVER['SERVER_NAME']) {
    case 'localhost':
        loadEnv(path: __DIR__ . '/localhost.env');
        break;
}