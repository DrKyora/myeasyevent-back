<?php

ini_set(option: 'display_errors', value: 1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';
header(header: 'Access-Control-Allow-Origin: ' . $_ENV['FRONT_END_URL']);

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();

switch($request->action){
    case'sendEmail':
        try{
            $email = $dependances->emailService->sendMail(
                addressFrom: [
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
                contentsEmails: [
                    '{{UserName}}' => $request->lastName . ' ' . $request->firstName,
                    '{{Message}}' => $request->message,
                    '{{UserEmail}}' => $request->email
                ],
                urlTemplate: __DIR__ . '/../templates/emails/contactEmail.html'
            );
            if(!$email instanceof \App\Responses\ResponseError){
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => 'Email envoyé']);
            }else{
                $error = $email;
                $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
            }
        } catch (\Throwable $th) {
            $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
        }
    break;
}

if (!isset($response)) {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 500, 'message' => 'Une erreur est survenue']);
}
echo json_encode(value: $response);