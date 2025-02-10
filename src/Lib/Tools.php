<?php

namespace App\Lib;

use App\Factories\ResponseErrorFactory;

use App\Responses\ResponseError;

class Tools
{
    private ResponseErrorFactory $responseErrorFactory;

    public function logDebug(string $message): void
    {
        $logFile = fopen(filename: __DIR__ . '/../../logs/debug_' . date(format: 'Y-m') . '.log', mode: 'a+');

        $log = "_______________________________________________" . date(format: 'd-m-Y H:i:s') . "_______________________________________________________\n";
        $log .= ">>> Message  : '" . $message . "'\n";
        $log .= "__________________________________________________________________________________________________________________________\n";
        fputs(stream: $logFile, data: $log);
    }

    public static function myErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        $messageTitle =  "<span style=\"color: red;\">Erreur PHP :</span> [$errno] $errstr<br>";
        $messageContent = " Erreur à la ligne { $errline } 
                        dans le fichier { $errfile }";
        self::logPHPerror(erreurPHP: $messageTitle, message: $messageContent);
    }

    private static function logPHPerror($erreurPHP, $message)
    {
        $logPath = __DIR__ . '/../../logs/';
        $logFile = fopen(filename: $logPath    . '#error_php_' . date(format: 'Y-m') . '.log', mode: 'a');
        $log = date(format: 'd-m-Y H:i:s') . " ERROR PHP : [" . $erreurPHP . "] >>> Message  : " . $message . "\n";
        // $log = "_______________________________________________".date('d-m-Y H:i:s')."_______________________________________________________\n";
        // $log .= $erreurPHP."\n";
        // $log .= ">>> Message  : ".$message."\n";
        // $log .= "__________________________________________________________________________________________________________________________\n";
        fputs(stream: $logFile, data: $log);
    }

    public function array_push_assoc($array, $key, $value): array
    {
        $array[$key] = $value;
        return $array;
    }
    
    public function encrypt_decrypt(string $action, string $stringToTreat): string|ResponseError
    {
        try {
            $output = false;
            $encrypt_method = "AES-256-CBC";

            $key = hash(algo: 'sha256', data: $_ENV['ENCRYPT_KEY']);
            $iv = substr(string: hash(algo: 'sha256', data: $_ENV['SECRET_IV']), offset: 0, length: 16);
            if ($action == 'encrypt') {
                $output = openssl_encrypt(data: $stringToTreat, cipher_algo: $encrypt_method, passphrase: $key, options: 0, iv: $iv);
                $output = base64_encode(string: $output);
            } else if ($action == 'decrypt') {
                $output = openssl_decrypt(data: base64_decode(string: $stringToTreat), cipher_algo: $encrypt_method, passphrase: $key, options: 0, iv: $iv);
            }
            return $output;
        } catch (\Exception $e) {
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }
}