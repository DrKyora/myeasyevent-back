<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

use App\Lib\Tools;
use App\Services\DBConnection;

use App\Repositories\AuthorizedDeviceRepository;

use App\Factories\AuthorizedDeviceFactory;

$db = new DBConnection();
$tools = new Tools();

$authorizedDeviceFactory = new AuthorizedDeviceFactory();

$authorizedDeviceRepository = new AuthorizedDeviceRepository(db: $db, tools: $tools,authorizedDeviceFactory: $authorizedDeviceFactory);

$string = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $_GET['token']);
$device = $authorizedDeviceFactory->createFromString(string: $string);
$device = $authorizedDeviceRepository->getAuthorizedDeviceById(deviceId: $device->id);
echo "event: validatedevice\n";
echo "data:" . $device->validateDate . "\n\n";
flush();
