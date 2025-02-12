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

    public function myErrorHandler($errno, $errstr, $errfile, $errline): void
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

    function logSetup($action, $message, $sql, $type)
    {
        $logPath = __DIR__ . '/../logs/';
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }
        $logFile = fopen($logPath . 'setup_' . date('Y-m') . '.log', 'a');
        if ($type == 'error') {
            $log = "***ERROR*** [" . date('d-m-Y H:i:s') . "] " . strtoupper($action) . "\n";
            if (is_array($message)) {
                $log .= "SQL error : " . implode(" ", $message) . "\n";
            } else {
                $log .= "SQL error : " . $message . "\n";
            }
            $log .= "SQL : " . $sql . "\n";
        } else {
            $log = "[" . date('d-m-Y H:i:s') . "] " . $action . " => " . $message . " \n";
        }

        if (fputs($logFile, $log)) {
            return true;
        }
    }

    function logPHP($origine, $message)
    {
        if (LOG_ALL_PHP) {
            $logPath = __DIR__ . '/../logs/';
            $log = "[" . date('d-m-Y H:i:s') . "] -INFO : [" . $origine . "] >>> " . $message . "\n";
            // $log = "_______________________________________________".date('d-m-Y H:i:s')."_______________________________________________________\n";
            // $log .= $origine."\n";
            // $log .= ">>> Message  : ".$message."\n";
            // $log .= "__________________________________________________________________________________________________________________________\n";
            $logFile = fopen($logPath . 'php_' . date('Y-m') . '.log', 'a');
            fputs($logFile, $log);
            fclose($logFile);
        }
    }
}