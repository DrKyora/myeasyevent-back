<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

/**
 * Libraries
 */
use App\Lib\Tools;
use App\Services\DBConnection;
/**
 * Factories
 */
use App\Factories\ResponseFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\UserFactory;
use App\Factories\EmailFactory;
/**
 * Repositories
 */
use App\Repositories\UserRepository;
/**
 * Validators
 */
use App\Validators\UserValidationService;
use App\Validators\EmailValidationService;
/**
 * Services
 */
use App\Services\UserService;
use App\Services\EmailService;

/**
 * Libraries
 */
$tools = new Tools;
$db = new DBConnection();
/**
 * Factories
 */
$responseFactory = new ResponseFactory();
$responseErrorFactory = new ResponseErrorFactory();
$userFactory = new UserFactory();
$emailFactory = new EmailFactory();
/**
 * Repositories
 */
$userRepository = new UserRepository(db: $db, tools: $tools,userFactory: $userFactory);
/**
 * Validators
 */
$userValidationService = new UserValidationService(userRepository: $userRepository);
$emailValidationService = new EmailValidationService();
/**
 * Services
 */
$emailService = new EmailService(
    tools: $tools,
    emailFactory: $emailFactory,
    responseErrorFactory: $responseErrorFactory,
    emailValidationService: $emailValidationService
);
$userService = new UserService(
    tools: $tools,
    userRepository: $userRepository,
    userValidationService: $userValidationService,
    userFactory: $userFactory,
    responseFactory: $responseFactory,
    responseErrorFactory: $responseErrorFactory,
    emailService: $emailService
);

if(isset($_GET['user'])){
    $string = $_GET['user'];
    $userId = $tools->encrypt_decrypt(action: 'decrypt',stringToTreat: $string);
    if($userService->confirmSubscriptionUser(userId: $userId) === true){
        $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateSubscriptionSuccess.html');
    } else {
        $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateSubscriptionError.html');
    }
} else {
    $message = file_get_contents(filename: __DIR__ . '/templates/pages/validateSubscriptionError.html');
}
$message = str_replace(search: '{{FrontEndAddress}}', replace: $_ENV['FRONT_END_URL'], subject: $message);
echo $message;