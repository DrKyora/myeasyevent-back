<?php

namespace App\Services;
// Library
use App\Lib\Tools;
use \SendGrid\Mail\Mail;

// Responses
use App\Responses\ResponseError;

// Factories
use App\Factories\EmailFactory;
use App\Factories\ResponseErrorFactory;

// ServiceModels
use App\ServiceModels\Email;

//Validators
use App\Validators\EmailValidationService;

class EmailService
{
    private Tools $tools;
    private EmailFactory $emailFactory;
    private ResponseErrorFactory $responseErrorFactory;
    private EmailValidationService $emailValidationService;

    public function __construct(
        Tools $tools,
        EmailFactory $emailFactory,
        ResponseErrorFactory $responseErrorFactory,
        EmailValidationService $emailValidationService
    ){
        $this->tools = $tools;
        $this->emailFactory = $emailFactory;
        $this->responseErrorFactory = $responseErrorFactory;
        $this->emailValidationService = $emailValidationService;
    }

    public function sendMail(array $addressFrom, array $addressA, ?array $addressCc, ?array $addressCci, string $subject, array $contentsEmails, string $urlTemplate): bool|ResponseError
    {
        try{
            $email = $this->emailFactory->createEmpty();
            $sendGridMail = new Mail();
            $email = $this->constructHeaderEmail(
                email: $email,
                addressFrom: $addressFrom,
                addressA: $addressA,
                addressCc: $addressCc,
                addressCci: $addressCci,
                subject: $subject
            );
            $email = $this->constructBodyEmail(
                email: $email,
                contentsEmails: $contentsEmails,
                urlTemplate: $urlTemplate
            );

            $this->emailValidationService->validate(sendMail: $email);
            $sendGridMail->setFrom(email: $email->addressFrom[0]['address'],name: $email->addressFrom[0]['name']);
            $recipients = [];
            $addressTo = $email->addressA;
            foreach($addressTo as $addressA){
                $recipients = $this->tools->array_push_assoc(array: $recipients, key: $addressA['address'], value: $addressA['name']);
            }
            $sendGridMail->addTos(toEmails: $recipients);
            if(!empty($email->addressCc)){
                $recipients = [];
                $addressTo = $email->addressCc;
                foreach($addressTo as $addressCc){
                    $recipients = $this->tools->array_push_assoc(array: $recipients, key: $addressCc['address'], value: $addressCc['name']);
                }
                $sendGridMail->addCcs(ccEmails: $recipients);
            }
            if(!empty($email->addressCci)){
                $recipients = [];
                $addressTo = $email->addressCci;
                foreach($addressTo as $addressCci){
                    $recipients = $this->tools->array_push_assoc(array: $recipients, key: $addressCci['address'], value: $addressCci['name']);
                }
                $sendGridMail->addBccs( bccEmails: $recipients);
            }
            $sendGridMail->setSubject(subject: $subject);
            $sendGridMail->addContent(type: "text/html", value: $email->content);
            $sendGrid = new \SendGrid(apiKey: $_ENV['SENDGRID_KEY']);
            $response = $sendGrid->send($sendGridMail);
            if($response->statusCode() <= 202){
                return true;
            }else{
                $message = 'Error ' . $response->statusCode() . ' - header : ' . json_encode(value: $response->headers()) . ' - body : ' . $response->body();
                return $this->responseErrorFactory->createFromArray(data: ['code' => 1100, 'message' => $message]);
            }
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function constructHeaderEmail(Email $email, array $addressFrom, array $addressA, ?array $addressCc, ?array $addressCci, $subject): Email|ResponseError
    {
        try{
            $email->addressFrom[] = ['address' => $addressFrom['address'], 'name' => $addressFrom['name']];
            foreach($addressA as $value){
                $email->addressA[] = ['address' => $value['address'], 'name' => $value['name']];
            }
            if(!empty($addressCc)){
                foreach($addressCc as $value){
                    $email->addressCc[] = ['address' => $value['address'], 'name' => $value['name']];
                }
            }
            if(!empty($addressCci)){
                foreach($addressCc as $value){
                    $email->addressCc[] = ['address' => $value['address'], 'name' => $value['name']];
                }
            }
            $email->subject = $subject;
            return $email;
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    public function constructBodyEmail(Email $email, array $contentsEmails, string $urlTemplate): Email|ResponseError
    {
        try{
            $contentMail = file_get_contents(filename: $urlTemplate);
            foreach($contentsEmails as $key => $value){
                $contentMail = str_replace(search: "$key", replace: $value, subject: $contentMail);
            }
            $contentMail = str_replace(search: "{{FrontEndAddress}}", replace: $_ENV['FRONTEND_ADDRESS'], subject: $contentMail);
            $email->content = $contentMail;
            return $email;
        }catch(\Exception $e){
            return $this->responseErrorFactory->createFromArray(data: ['code' => $e->getCode(), 'messsage' => $e->getMessage()]);
        }
    }
}