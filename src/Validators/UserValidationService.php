<?php

namespace App\Validators;

use App\Lib\Tools;

use App\Models\User;

use App\Repositories\UserRepository;

class UserValidationService
{
    private $userRepository;
    public function __construct(
        UserRepository $userRepository
    ){
        $this->userRepository = $userRepository;
    }
    public function validateCreate(User $user): bool
    {
        $this->userContainMandatoryProperties(user: $user);
        $this->emailIsValid(email: $user->email);
        $this->isUniqueEmailForAdd(user: $user);
        $this->passwordIsValid(password: $user->password);
        return true;
    }

    public function validateUpdate(User $user): bool
    {
        $this->userContainMandatoryProperties(user: $user);
        $this->emailIsValid(email: $user->email);
        $this->isUniqueEmailForUpdate(user: $user);
        return true;
    }

    public function validateUpdateWithPassword(User $user): bool
    {
        $this->userContainMandatoryProperties(user: $user);
        $this->emailIsValid(email: $user->email);
        $this->isUniqueEmailForUpdate(user: $user);
        $this->passwordIsValid(password: $user->password);
        return true;
    }


    public function userContainMandatoryProperties(User $user): bool
    {
        if (empty($user->firstName) && empty($user->lastName)) {
            throw new \Exception(message: "Veuillez renseigner le prénom ou le nom de l'utilisateur", code: 5005);
        }
        return true;
    }

    public function emailIsValid(string $email): bool
    {
        if (empty($email)) {
            throw new \Exception(message: "Veuillez renseigner l'email de l'utilisateur", code: 5000);
        }
        if (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
            throw new \Exception(message: "Veuillez renseigner un email valide", code: 5001);
        }
        return true;
    }

    public function passwordIsValid(string $password): bool
    {
        if (strlen(string: $password) < 8) {
            throw new \Exception(message: "Le mot de passe de l'utilisateur doit comporter au moins 8 caractères", code: 5002);
        }
        if (!preg_match(pattern: '/[A-Z]/', subject: $password)) {
            throw new \Exception(message: "Le mot de passe de l'utilisateur doit comporter au moins une majuscule", code: 5003);
        }
        if (!preg_match(pattern: '/[#$!€µ@%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/', subject: $password)) {
            throw new \Exception(message: "Le mot de passe de l'utilisateur doit comporter au moins un caractère special", code: 5004);
        }
        return true;
    }

    public function isUniqueEmailForAdd(User $user): bool
    {
        if ($this->userRepository->emailUserExist(emailToVerif: $user->email)) {
            throw new \Exception(message: "Cet email est déjà utilisé", code: 5007);
        }
        return true;
    }
    public function isUniqueEmailForUpdate(User $user): bool
    {
        if ($this->userRepository->emailUserExist(emailToVerif: $user->email, excludedId: $user->id)) {
            throw new \Exception(message: "Cet email est déjà utilisé", code: 5007);
        }
        return true;
    }

    public function userExists($userId): bool
    {
        if(!$this->userRepository->getUserById( id: $userId)) {
            throw new \Exception(message: "Cet utilisateur n'existe pas", code: 5016);
        }
        return true;
    }
}