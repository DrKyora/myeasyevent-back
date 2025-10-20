<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

use App\Lib\Tools;

use App\Factories\ResponseFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\AuthorizedDeviceFactory;
use App\Factories\LogsBadFactory;
use App\Factories\EmailFactory;
use App\Factories\UserFactory;

use App\Repositories\UserRepository;
use App\Repositories\AuthorizedDeviceRepository;
use App\Repositories\LogsBadRepository;


use App\Validators\AuthorizedDeviceValidationService;
use App\Validators\UserValidationService;
use App\Validators\EmailValidationService;

use App\Services\DBConnection;
use App\Services\AuthorizedDeviceService;
use App\Services\EmailService;


$tools = new Tools();
$db = new DBConnection();
$responseFactory = new ResponseFactory();
$responseErrorFactory = new ResponseErrorFactory();
$authorizedDeviceFactory = new AuthorizedDeviceFactory();
$logsBadFactory = new LogsBadFactory();
$emailFactory = new EmailFactory();
$userFactory = new UserFactory();
$userRepository = new UserRepository(db: $db, tools: $tools,userFactory: $userFactory);
$authorizedDeviceRepository = new AuthorizedDeviceRepository(db: $db, tools: $tools,authorizedDeviceFactory: $authorizedDeviceFactory);
$logsBadRepository = new LogsBadRepository(db: $db, tools: $tools, logsBadFactory: $logsBadFactory);
$authorizedDeviceValidationService = new AuthorizedDeviceValidationService(tools: $tools,authorizedDeviceRepository: $authorizedDeviceRepository);
$userValidationService = new UserValidationService(userRepository: $userRepository);
$emailValidationService = new EmailValidationService();


$emailService = new EmailService(responseErrorFactory: $responseErrorFactory, emailValidationService: $emailValidationService, emailFactory: $emailFactory, tools: $tools);
$authorizedDeviceService = new AuthorizedDeviceService(
    tools: $tools,
    authorizedDeviceFactory: $authorizedDeviceFactory,
    responseErrorFactory: $responseErrorFactory,
    responseFactory: $responseFactory,
    logsBadFactory: $logsBadFactory,
    authorizedDeviceRepository: $authorizedDeviceRepository,
    userRepository: $userRepository,
    logsBadRepository: $logsBadRepository,
    authorizedDeviceValidationService: $authorizedDeviceValidationService,
    userValidationService: $userValidationService,
    emailService: $emailService
);


if (isset($_GET['device'])) {
    $string = $_GET['device'];
    $deviceId = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $string);
    if ($authorizedDeviceService->confirmAuthorizedDevice(authorizedDeviceId: $deviceId) === true) {
        $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateDeviceSuccess.html');
    } else {
        $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateDeviceError.html');
    }
} else {
    $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateDeviceError.html');
}
$message = str_replace(search: '{{FrontEndAddress}}', replace: $_ENV['FRONT_END_URL'], subject: $message);
echo $message;
