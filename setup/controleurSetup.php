<?php

use App\Services\DBConnection;

use App\Factories\UserFactory;
use App\Repositories\UserRepository;

use App\Lib\Tools;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Lib/Tools.php';
require_once __DIR__ . '/../src/Factories/UserFactory.php';
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/DBConnection.php';

$userFactory = new UserFactory();
$db = new DBConnection();
$tools = new Tools();
$userRepository = new UserRepository(db: $db, tools: $tools);