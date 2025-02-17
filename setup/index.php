<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <title>Setup myeasyevent-back</title>
    <style>
        html {
            font-family: "Roboto Mono", monospace;
            background-color: black;
            color: chartreuse;
        }

        ::-webkit-scrollbar {
            width: 5px;
            background-color: forestgreen;
        }

        ::-webkit-scrollbar-thumb {
            background-color: chartreuse;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-track {
            background-color: forestgreen;
        }

        .header-success {
            margin: 0;
            background-color: forestgreen;
        }

        .header-error {
            margin: 0;
            background-color: firebrick;
        }

        #title {
            font-size: 40px;
        }

        .listeTables {
            display: flex;
            flex-wrap: wrap;
        }

        .listeTables div {
            padding: 10px;
        }

        ul.success {
            margin: 0;
            padding-left: 0;
            list-style-type: none;
            border-top: 1px solid chartreuse;
        }

        ul.error {
            margin: 0;
            padding-bottom: 10px;
            list-style-type: none;
            border-top: 1px solid chartreuse;
        }

        li.success {
            padding: 10px;
            color: chartreuse;
            background-color: forestgreen;
        }

        li.error {
            margin: 10px 0;
            padding: 0 10px;
            color: red;
            background-color: firebrick;
        }

        li.neutral {
            padding: 10px;
            color: white;
            background-color: black;
        }

        p.error {
            padding: 7px;
            background-color: darkred;
            color: red;
        }

        #btnContinue {
            display: block;
            font-family: "Roboto Mono", monospace;
            margin: auto;
            padding: 20px;
            font-size: 20px;
            background-color: black;
            border: 1px solid chartreuse;
            color: chartreuse;
        }

        #btnContinue:hover {
            color: darkgreen;
            border-color: darkgreen;
        }

        .divError {
            margin: auto;
            width: 60%;
            padding: 30px;
            border: 3px solid red;
            color: red;
            text-align: center;
            background-color: darkred;
        }
    </style>
</head>

<body>
    <p id="title">> Récap. du setup...</p>
    <?php
    if (phpversion() < 8) {
        echo '<div class="divError">
        <h1>ATTENTION VERSION DE PHP INSUFISANTE!</h1>
        <p>
            L\'applicaiton est installer sur une version ' . phpversion() . ' elle nécessite au minimu une version 8.0
        </p>
    </div>';
    }
    require_once("./controleurSetup.php");
    // Récupérer tous les fichiers JSON du répertoire
    $srcPath = "./src/structures/";
    $jsonFiles = glob($srcPath . '*.json');
    echo '<div class="listeTables">';
    foreach ($jsonFiles as $jsonFile) {
        // Charger le contenu du fichier JSON
        $jsonData = file_get_contents($jsonFile);

        // Convertir le JSON en tableau PHP
        $data = json_decode($jsonData, true);

        // Vérifier si le JSON est valide
        if ($data === null) {
            echo '<p class="error"> Erreur de lecture du fichier JSON : ' . $jsonFile . ' </p>';
            continue;
        }

        // Extraire les variables depuis le tableau
        if (isset($data['tableName']) && isset($data['columns'])) {
            $tableName = $data['tableName'];
            $columns = $data['columns'];

            // Appeler la fonction createOrUpdateTable
            $result = createOrUpdateTable($tableName, $columns);
            displayResult($result);
            //Si c'est la table user on ajout un flag si ça c'est bien passé
            if ($result['name'] == 'users' && $result['success'] == true) {
                addDefaultAdminUsers();
            }
        } else {
            echo '<p class="error"> Le fichier ' . $jsonFile . ' ne contient pas les clés [tableName] et [columns] nécessaires.';
        }
    }
    //Ajoute les données
    echo '<p><button id="btnContinue" onclick="window.location.replace(\'../index.php\')">Cliquer ici pour continuer</button></p></div>';


    ?>

</body>

</html>