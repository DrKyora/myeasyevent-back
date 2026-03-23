<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

$dependances = new \App\Services\DépendancesContainer();

if (isset($_GET['device'])) {
    $string = $_GET['device'];
    $deviceId = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $string);
    if ($dependances->authorizedDeviceService->confirmAuthorizedDevice(authorizedDeviceId: $deviceId) === true) {
        $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateDeviceSuccess.html');
    } else {
        $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateDeviceError.html');
    }
} else {
    $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateDeviceError.html');
}
$message = str_replace(search: '{{FrontEndAddress}}', replace: $_ENV['FRONT_END_URL'], subject: $message);
echo $message;