<?php

ini_set(option: 'display_errors', value: 1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';
header(header: 'Access-Control-Allow-Origin: ' . $_ENV['FRONT_END_URL']);
$request = json_decode(json: file_get_contents(filename: 'php://input'));

// Library
use App\Lib\Tools;

// Factories
use App\Factories\EmailFactory;
use App\Factories\ResponseErrorFactory;
use App\Factories\ResponseFactory;
//Validators
use App\Validators\EmailValidationService;

// Services
use App\Services\EmailService;

//Responses
use App\Responses\ResponseError;

// Utils
$tools = new Tools();
//Factories
$emailFactory = new EmailFactory();
$responseErrorFactory = new ResponseErrorFactory();
$responseFactory = new ResponseFactory();
//Validator
$emailValidationService = new EmailValidationService();
//Services
$emailService = new EmailService(
    emailFactory: $emailFactory, 
    responseErrorFactory: $responseErrorFactory, 
    tools: $tools,
    emailValidationService: $emailValidationService
);

switch($request->action){
    case'sendEmail':
        try{
            $email = $emailService->sendMail(
                addressFrom:[
                    'address' => $_ENV['MAIL_DEFAULT_FROM_ADDRESSE'],
                    'name' => $_ENV['MAIL_DEFAULT_FROM_NAME']
                ],
                addressA: [
                        [
                            'address' => $_ENV['MAIL_DEFAULT_A_ADDRESSE'],
                            'name' => $_ENV['MAIL_DEFAULT_A_NAME']
                        ]
                    ],
                addressCc: null,
                addressCci: null,
                subject: $request->lastName . ' ' . $request->firstName . ' souhaite vous contacter.',
                contentsEmails:[
                    '{{UserName}}' => $request->lastName . ' ' . $request->firstName,
                    '{{Message}}' => $request->message,
                    '{{UserEmail}}' => $request->email
                ],
                urlTemplate: __DIR__ . '/../templates/emails/contactEmail.html'
            );
            if(!$email instanceof ResponseError){
                $response = $responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => 'Email envoyé']);
            }else{
                $error = $email;
                $response = $responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
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