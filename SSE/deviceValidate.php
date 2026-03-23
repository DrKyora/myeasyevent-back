<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

$dependances = new \App\Services\DépendancesContainer();

$string = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $_GET['token']);
$device = $dependances->authorizedDeviceFactory->createFromString(string: $string);
$device = $dependances->authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $device->id);
echo "event: validatedevice\n";
echo "data:" . $device->validateDate . "\n\n";
flush();