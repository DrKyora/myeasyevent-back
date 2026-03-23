<?php

require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Responses\ResponseError;

$request = json_decode(json: file_get_contents(filename: 'php://input'));

$dependances = new \App\Services\DépendancesContainer();

if ($dependances->sessionService->tokenSessionIsValide(tokenSession: $request->session)) {
    $sessionString = $dependances->tools->encrypt_decrypt(action: 'decrypt', stringToTreat: $request->session);
    $session = $dependances->sessionFactory->createFromJson(json: $sessionString);
    switch($request->action){
        case 'getAllTemplates':
            try{
                $templates = $dependances->templateService->getAllTemplates();
                if(!$templates instanceof ResponseError){
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Tous les templates trouvés", 'data' => ['templates' => $templates]]);
                } else {
                    $error = $templates;
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
        case 'getTemplateById':
            try{
                $template = $dependances->templateService->getTemplateById(id: $request->id);
                if(!$template instanceof ResponseError){
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'success', 'code' => null, 'message' => "Template trouvé", 'data' => ['template' => $template]]);
                } else {
                    $error = $template;
                    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => $error->code, 'message' => $error->message]);
                }
            } catch (\Throwable $th) {
                $dependances->tools->myErrorHandler(errno: $th->getCode(), errstr: $th->getMessage(), errfile: $th->getFile(), errline: $th->getLine());
            }
            break;
    }
} else {
    $response = $dependances->responseFactory->createFromArray(data: ['status' => 'error', 'code' => 5009, 'message' => "Pas de session valable, l'utilisateur doit se reconnecter"]);
}
echo json_encode(value: $response);