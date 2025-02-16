<?php
ini_set(option: 'display_errors', value: 1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';
header(header: 'Access-Control-Allow-Origin: ' . $_ENV['FRONT_END_URL']);
$request = json_decode(json: file_get_contents(filename: 'php://input'));
// Library
use App\Lib\Tools;

// Factories
use App\Factories\ResponseFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\SessionFactory;
use App\Factories\UserFactory;

// Repositories
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;

// Validators
use App\Validators\SessionValidationService;
use App\Validators\UserValidationService;

// Services
use App\Services\DBConnection;
use App\Services\SessionService;
use App\Services\UserService;

$tools = new Tools();
$db = new DBConnection();
// Factories
$responseFactory = new ResponseFactory();
$responseErrorFactory = new ResponseErrorFactory();
$sessionFactory = new SessionFactory();
$userFactory = new UserFactory();

// Repositories
$sessionRepository = new SessionRepository(
    db: $db,
    tools: $tools,
    sessionFactory: $sessionFactory
);
$userRepository = new UserRepository(
    db: $db,
    tools: $tools,
    userFactory: $userFactory
);

// Validators
$sessionValidationService = new SessionValidationService();
$userValidationService = new UserValidationService(
    userRepository: $userRepository
);

// Services
$sessionService = new SessionService(
    tools: $tools,
    sessionFactory: $sessionFactory,
    responseErrorFactory: $responseErrorFactory,
    sessionRepository: $sessionRepository,
    sessionValidationService: $sessionValidationService
);
$userService = new UserService(
    tools: $tools,
    userRepository: $userRepository,
    userValidationService: $userValidationService,
    userFactory: $userFactory,
    responseFactory: $responseFactory,
    responseErrorFactory: $responseErrorFactory
);

switch($request->action) {
    case 'connectEmailPass':
        try{
            if($result = $userService->ConnectEmailPass(email: $request->email, password: $request->password)){
                $response = $result;
            }else{
                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5020, 'message' => "Erreur lors de la connexion avec login et mot de passe"]);
            }
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
    case'checkSession':
        try{
            $sessionIsValide = $sessionService->tokenSessionIsValide(tokenSession: $request->session);
            if($sessionIsValide){
                $stringToken = $tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
                $session = $sessionFactory->createFromString(string: $stringToken);
                $session = $sessionService->getSession(key: 'id', value: $session->id);
                $user = $userService->getUser(key: 'id', value: $session->userId);
                $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Session Valide", 'data' => ['user' => $user]]);
            } else {
                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Ce token de device n'est pas valide! -> Utiliser login/pass et enregistrer + confirmer le device à nouveau"]);
            }
        } catch (\Throwable $th) {
            $tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
        break;
}
if (!isset($response)) {
    $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $th->getCode(), 'message' => $th->getMessage()]);
}
echo json_encode(value: $response);