<?php

use App\Services\DBConnection;

use App\Factories\UserFactory;
use App\Repositories\UserRepository;

use App\Lib\Tools;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php'; 
require_once __DIR__ . '/../src/Lib/Tools.php';
require_once __DIR__ . '/../src/Factories/UserFactory.php';
require_once __DIR__ . '/../src/Repositories/UserRepository.php';
require_once __DIR__ . '/../src/Services/DBConnection.php';

$userFactory = new UserFactory();
$db = new DBConnection();
$tools = new Tools();
$userRepository = new UserRepository(db: $db, tools: $tools);

class tableLog
{
    public $name;
    public $event;
    public $state;
    public $columns;

    public function __construct(string $name = null, string $event = null, string $state = null, string $result = null, array $columns = null)
    {
        $this->event = $event;
    }
}

class columnLog
{
    public $name;
    public $event;
    public $state;

    public function __construct(string $name = null, string $event = null, string $state = null)
    {
        $this->name = $name;
        $this->event = $event;
        $this->state = $state;
    }
}


function createOrUpdateTable($tableName, $columns)
{
    global $tools;
    global $db;
    $tableLog = new tableLog($tableName, 'createTable', "success"); //Créé l'objet et l'initialise à success
    // Création de la table si elle n'existe pas
    $sql = "CREATE TABLE IF NOT EXISTS $tableName (id VARCHAR(16) NOT NULL, UNIQUE KEY `id` (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    // $req = $GLOBALS['dbPDO']->query($sql);
    $req = $db->getConnection()->prepare(query: $sql);
    $req->execute();
    if (!$req) {
        $tools->logSetup("Create table $tableName", $GLOBALS['dbPDO']->errorInfo(), $sql, 'error');
        return ["name" => $tableName, "success" => false, "cols" => []];
    } else {
        $tools->logSetup("Create table $tableName", 'Success', null, null);
    }
    $previousCol = '';
    $cols = [];

    // Gestion des colonnes
    foreach ($columns as $col) {
        $colLog = new columnLog($col['nameCol'], null, 'success');
        // Vérification de l'existence de la colonne et de ses caractéristiques
        $sql = "SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA='" . $_ENV['DB_NAME'] . "' AND TABLE_NAME='$tableName' AND COLUMN_NAME='" . $col['nameCol'] . "';";
        // $req = $GLOBALS['dbPDO']->query($sql);
        $req = $db->getConnection()->prepare(query: $sql);
        $req->execute();
        $existingCol = $req->fetch(PDO::FETCH_ASSOC);

        if (!$existingCol) {
            $colLog->event = "addColumn";
            // Colonne n'existe pas, on la crée
            if (!addColumn($tableName, $col, $previousCol)) {
                $tableLog->state = "error";
                $colLog->state = "error";
            } else {
                $colLog->state = "success";
            }
        } else {

            //Convertis le Boolean en booléen (YES/NO => true/false)
            $isNullableDb = $existingCol['IS_NULLABLE'] == 'YES' ? true : false;
            // Vérification du type de la colonne : BOOLEAN dans le tableau peut être TINYINT(1) en base
            $isBooleanEquivalent = strtolower($col['type']) === 'boolean' && $existingCol['COLUMN_TYPE'] === 'tinyint(1)';
            //Si c'est une colonne de type int on garde que les 3 premiers
            $col['type'] = strtoupper(substr($col['type'], 0, 3)) == 'INT' ? 'INT' : strtoupper($col['type']);
            // Colonne existe, on vérifie si elle a les mêmes caractéristiques
            if (
                ! $isBooleanEquivalent && strtoupper($existingCol['COLUMN_TYPE']) !== strtoupper($col['type']) ||
                $isNullableDb !== $col['nullable'] ||
                $existingCol['COLUMN_DEFAULT'] != $col['default']
            ) {
                if ($col['nameCol'] === 'DURATION') {
                    echo strtoupper($existingCol['COLUMN_TYPE']) . ' !== ' . strtoupper($col['type']) . '<br />';
                    echo $sql . '<br />';
                }
                // Les caractéristiques sont différentes, on renomme l'ancienne colonne
                if (!renameAndAddColumn($tableName, $col, $previousCol)) {
                    $tableLog->state = "error";
                    $colLog->state = "error";
                } else {
                    $colLog->event = "updateColumn";
                    $colLog->state = "success";
                }
            } else {
                $colLog->event = "identicalColumn";
                $colLog->state = "success";
                // La colonne existe avec les bonnes caractéristiques
                $tools->logSetup("Colonne {$col['nameCol']} déjà présente avec les bonnes caractéristiques", 'Success', null, null);
            }
        }
        $previousCol = $col['nameCol'];
        $cols[] = $colLog;
    }

    return ["name" => $tableName, "success" => true, "cols" => $cols];
}

function addColumn($tableName, $col, $previousCol)
{
    global $db;
    global $tools;
    // Construction de la requête SQL pour ajouter une colonne
    $sql = "ALTER TABLE $tableName ADD {$col['nameCol']} {$col['type']}";

    if (!$col['nullable']) {
        $sql .= " NOT NULL";
    }

    if ($col['default'] !== null) {
        if (strtoupper($col['type']) === 'BOOLEAN' || strtoupper($col['type']) === 'TINYINT(1)') {
            // Pour les colonnes BOOLEAN, ne pas entourer la valeur de guillemets
            $sql .= " DEFAULT " . ($col['default'] ? '1' : '0');
        } else {
            // Pour les autres types, traiter comme une chaîne de caractères
            $sql .= " DEFAULT '{$col['default']}'";
        }
    }

    if ($previousCol != '') {
        $sql .= " AFTER $previousCol";
    }
    // Exécution de la requête
    // $req = $GLOBALS['dbPDO']->query($sql);
    $req = $db->getConnection()->prepare(query: $sql);
    $req->execute();
    if (!$req) {
        $tools->logSetup("Add col {$col['nameCol']}", $GLOBALS['dbPDO']->errorInfo(), $sql, 'error');
        return false;
    } else {
        $tools->logSetup("Add col {$col['nameCol']}", 'Success', null, null);
        return true;
    }
}

function renameAndAddColumn($tableName, $col, $previousCol)
{
    global $db;
    global $tools;
    // Renommer l'ancienne colonne
    $renameSql = "ALTER TABLE $tableName CHANGE {$col['nameCol']} tmp_{$col['nameCol']} " . getColType($col['type']) . ";";
    // $req = $GLOBALS['dbPDO']->query($renameSql);
    $req = $db->getConnection()->prepare(query: $renameSql);
    $req->execute();
    if (!$req) {
        $tools->logSetup("Rename col {$col['nameCol']}", $GLOBALS['dbPDO']->errorInfo(), $renameSql, 'error');
        return;
    } else {
        $tools->logSetup("Renamed col {$col['nameCol']} to tmp_{$col['nameCol']}", 'Success', null, null);
    }

    // Créer la nouvelle colonne avec les bonnes caractéristiques
    addColumn($tableName, $col, $previousCol);

    // Copier les données de l'ancienne colonne vers la nouvelle
    $copySql = "UPDATE $tableName SET {$col['nameCol']} = tmp_{$col['nameCol']};";
    // $req = $GLOBALS['dbPDO']->query($copySql);
    $req = $db->getConnection()->prepare(query: $copySql);
    $req->execute();
    if (!$req) {
        $tools->logSetup("Copy data from tmp_{$col['nameCol']} to {$col['nameCol']}", $GLOBALS['dbPDO']->errorInfo(), $copySql, 'error');
    } else {
        $tools->logSetup("Copied data from tmp_{$col['nameCol']} to {$col['nameCol']}", 'Success', null, null);
    }

    // Supprimer l'ancienne colonne
    $dropSql = "ALTER TABLE $tableName DROP COLUMN tmp_{$col['nameCol']};";
    // $req = $GLOBALS['dbPDO']->query($dropSql);
    $req = $db->getConnection()->prepare(query: $dropSql);
    $req->execute();
    if (!$req) {
        $tools->logSetup("Drop col tmp_{$col['nameCol']}", $GLOBALS['dbPDO']->errorInfo(), $dropSql, 'error');
        return false;
    } else {
        $tools->logSetup("Dropped col tmp_{$col['nameCol']}", 'Success', null, null);
        return true;
    }
}

function getColType($type)
{
    // Peut être utilisé pour générer les types SQL, ici on retourne simplement le type.
    return $type;
}
//Affichage des résultats
function displayResult($result)
{
    // var_dump($result);
    $tableBgClass = $result['success'] ? 'success' : 'error';

    $containsError = array_filter($result['cols'], function ($col) {
        return $col->state == 'error';
    });
    $tableBgBody = !empty($containsError) ? 'error' : 'success';

    $containsError = array_filter($result['cols'], function ($col) {
        return $col->state == 'error';
    });
    $tableBgBody = !empty($containsError) ? 'error' : 'success';
    // Commencer la div contenant toutes les tables
    echo '<div class="tableCard">
            <div class="header-' . $tableBgClass . '">
                <p class="' . $result['success'] . ' tableHeader">Table : ' . $result['name'] . '</p>
            </div>
            <ul class="' . $tableBgBody . '">';
    // Parcourir chaque colonne de la table et vérifier le statut de succès
    foreach ($result['cols'] as $col) {
        $bgTableLine = "success";
        if ($col->state == 'error') {
            $bgTableLine = "error";
        }
        if ($col->event == 'identicalColumn') {
            $bgTableLine = "neutral";
        }
        // Afficher chaque colonne avec la classe CSS appropriée
        echo '<li class="' . $bgTableLine . '">Column : ' . $col->name . ' <br /> ' . $col->event . '</li>';
    }
    echo '  </ul>
        </div>';
}
function addDefaultAdminUsers()
{
    global $tools;
    global $userFactory;
    global $userRepository;
    $tools->logPHP(origine: __METHOD__, message: "Ajout des utilisateurs par défaut");
    $jsonData = file_get_contents(filename: 'src/datas/users.json');
    $data = json_decode(json: $jsonData, associative: true);
    if ($data === null) {
        echo '<p class="error"> Erreur de lecture du fichier JSON : src/data/users.json </p>';
    }
    $users = $data['users'];
    $createUser = false;
    foreach ($users as $newUser) {
        if (!$userRepository->emailUserExist(emailToVerif: $newUser['email'])) {
            $newUser = $userFactory::createFromArray(data: $newUser);
            var_dump(value: $newUser);
            $response = $userRepository->addUser(user: $newUser);
            var_dump(value: $response);
            if ($response) {
                if (!$createUser) {
                    echo '<div class="tableCard">
                            <div class="header-success">
                                <p class="success tableHeader">Datas : users</p>
                            </div>
                            <ul class="success">';
                    $createUser = true;
                }
                echo '<li class="success">Utilisateur ajoute : login : ' . $newUser->email . ' | password : ' . $newUser->password . '</li>';
            }
        };
    }
    if ($createUser) {
        echo '      </ul>
                </div>';
    }
}
