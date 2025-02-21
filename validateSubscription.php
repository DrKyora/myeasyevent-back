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
/**
 * Repositories
 */
use App\Repositories\UserRepository;
/**
 * Validators
 */
use App\Validators\UserValidationService;

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
/**
 * Repositories
 */
$userRepository = new UserRepository(db: $db, tools: $tools,userFactory: $userFactory);
/**
 * Validators
 */
$userValidationService = new UserValidationService(userRepository: $userRepository);

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