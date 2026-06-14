<?php
$response = null;
require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';

$request = json_decode(
    json: file_get_contents(filename: 'php://input'),
    associative: true
);

$dependances = new \App\Services\DépendancesContainer();

$googleAddressValidationApiKey = $_ENV['GOOGLE_ADDRESS_VALIDATION_API_KEY'] ?? null;
$googleGeocodingApiKey = $_ENV['GOOGLE_GEOCODING_API_KEY'] ?? $googleAddressValidationApiKey;
$googleMapsJsApiKey = $_ENV['GOOGLE_MAPS_JS_API_KEY'] ?? null;
$googleMapsMapId = $_ENV['GOOGLE_MAPS_MAP_ID'] ?? null;

if (!$request || !isset($request['session'])) {
    $response = $dependances->responseFactory->createFromArray(data: [
        'status' => 'error',
        'code' => 400,
        'message' => 'Requête invalide ou session manquante'
    ]);
    echo json_encode(value: $response);
    exit;
}

if ($dependances->sessionService->tokenSessionIsValide(tokenSession: $request['session'])) {
    $sessionString = $dependances->tools->encrypt_decrypt(
        action: 'decrypt',
        stringToTreat: $request['session']
    );
    $session = $dependances->sessionFactory->createFromJson(json: $sessionString);

    switch ($request['action'] ?? null) {
        case 'validateAddress':
            try {
                if (!$googleAddressValidationApiKey) {
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'error',
                        'code' => 500,
                        'message' => 'Configuration GOOGLE_ADDRESS_VALIDATION_API_KEY manquante'
                    ]);
                    break;
                }

                $fullAddress = trim($request['fullAddress'] ?? '');
                if ($fullAddress === '') {
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Adresse complète manquante'
                    ]);
                    break;
                }

                $addressData = [
                    'address' => [
                        'regionCode' => $request['regionCode'] ?? 'FR',
                        'addressLines' => [$fullAddress]
                    ]
                ];

                $ch = curl_init("https://addressvalidation.googleapis.com/v1:validateAddress?key={$googleAddressValidationApiKey}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($addressData));

                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($httpCode === 200) {
                    $googleResponse = json_decode($result, associative: true);
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'success',
                        'code' => null,
                        'message' => 'Validation effectuée avec succès',
                        'data' => $googleResponse
                    ]);
                } else {
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'error',
                        'code' => $httpCode,
                        'message' => 'Erreur lors de la validation de l\'adresse',
                        'error' => $curlError
                    ]);
                }
            } catch (\Throwable $th) {
                $dependances->tools->myErrorHandler(
                    errno: $th->getCode(),
                    errstr: $th->getMessage(),
                    errfile: $th->getFile(),
                    errline: $th->getLine()
                );
            }
            break;

        case 'geocodeAddress':
    try {
        if (!$googleGeocodingApiKey) {
            $response = $dependances->responseFactory->createFromArray(data: [
                'status' => 'error',
                'code' => 500,
                'message' => 'Configuration GOOGLE_GEOCODING_API_KEY manquante'
            ]);
            break;
        }

        $streetNumber = trim((string)($request['streetNumber'] ?? ''));
        $street = trim((string)($request['street'] ?? ''));
        $zipCode = trim((string)($request['zipCode'] ?? ''));
        $city = trim((string)($request['city'] ?? ''));
        $country = trim((string)($request['country'] ?? ''));
        $regionCode = strtolower(trim((string)($request['regionCode'] ?? 'FR')));

        // fallback si front ne fournit pas les champs structurés
        $address = trim((string)($request['address'] ?? ''));

        $structuredParts = [];
        if ($streetNumber !== '' || $street !== '') {
            $structuredParts[] = trim($streetNumber . ' ' . $street);
        }
        if ($zipCode !== '' || $city !== '') {
            $structuredParts[] = trim($zipCode . ' ' . $city);
        }
        if ($country !== '') {
            $structuredParts[] = $country;
        }

        $fullAddress = !empty($structuredParts) ? implode(', ', $structuredParts) : $address;

        if ($fullAddress === '') {
            $response = $dependances->responseFactory->createFromArray(data: [
                'status' => 'error',
                'code' => 400,
                'message' => 'Adresse manquante'
            ]);
            break;
        }

        $components = [];
        if ($country !== '') {
            $countryCode = str_contains(strtolower($country), 'belg') ? 'BE' : (str_contains(strtolower($country), 'fr') ? 'FR' : strtoupper($country));
            $components[] = 'country:' . $countryCode;
        }
        if ($zipCode !== '') {
            $components[] = 'postal_code:' . $zipCode;
        }
        if ($city !== '') {
            $components[] = 'locality:' . $city;
        }

        // 1) Requête stricte (meilleure précision)
        $strictUrl = 'https://maps.googleapis.com/maps/api/geocode/json?'
            . 'address=' . urlencode($fullAddress)
            . '&region=' . urlencode($regionCode)
            . '&language=fr'
            . '&result_type=street_address'
            . (!empty($components) ? '&components=' . urlencode(implode('|', $components)) : '')
            . '&key=' . urlencode($googleGeocodingApiKey);

        $ch = curl_init($strictUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $strictResult = curl_exec($ch);
        $strictHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $googleResponse = null;

        if ($strictHttpCode === 200) {
            $googleResponse = json_decode($strictResult, associative: true);
        }

        // 2) Fallback souple si pas de résultat exploitable
        $hasStrictResult = $googleResponse
            && ($googleResponse['status'] ?? null) === 'OK'
            && !empty($googleResponse['results']);

        if (!$hasStrictResult) {
            $fallbackUrl = 'https://maps.googleapis.com/maps/api/geocode/json?'
                . 'address=' . urlencode($fullAddress)
                . '&region=' . urlencode($regionCode)
                . '&language=fr'
                . '&key=' . urlencode($googleGeocodingApiKey);

            $ch = curl_init($fallbackUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $fallbackResult = curl_exec($ch);
            $fallbackHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($fallbackHttpCode === 200) {
                $googleResponse = json_decode($fallbackResult, associative: true);
            } else {
                $response = $dependances->responseFactory->createFromArray(data: [
                    'status' => 'error',
                    'code' => $fallbackHttpCode,
                    'message' => 'Erreur lors du géocodage',
                    'error' => $curlError
                ]);
                break;
            }
        }

        if (($googleResponse['status'] ?? null) === 'OK' && !empty($googleResponse['results'])) {
            $result0 = $googleResponse['results'][0];
            $location = $result0['geometry']['location'];

            $response = $dependances->responseFactory->createFromArray(data: [
                'status' => 'success',
                'code' => null,
                'message' => 'Géocodage effectué avec succès',
                'data' => [
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                    'locationType' => $result0['geometry']['location_type'] ?? null,
                    'partialMatch' => (bool)($result0['partial_match'] ?? false),
                    'formattedAddress' => $result0['formatted_address'] ?? null,
                    'placeId' => $result0['place_id'] ?? null
                ]
            ]);
        } else {
            $response = $dependances->responseFactory->createFromArray(data: [
                'status' => 'error',
                'code' => 404,
                'message' => 'Adresse non trouvée',
                'googleStatus' => $googleResponse['status'] ?? 'UNKNOWN'
            ]);
        }
    } catch (\Throwable $th) {
        $dependances->tools->myErrorHandler(
            errno: $th->getCode(),
            errstr: $th->getMessage(),
            errfile: $th->getFile(),
            errline: $th->getLine()
        );
    }
    break;

        case 'getGoogleMapsApiKey':
            try {
                if (!$googleMapsJsApiKey) {
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'error',
                        'code' => 500,
                        'message' => 'Configuration GOOGLE_MAPS_JS_API_KEY manquante'
                    ]);
                    break;
                }

                if (!$googleMapsMapId) {
                    $response = $dependances->responseFactory->createFromArray(data: [
                        'status' => 'error',
                        'code' => 500,
                        'message' => 'Configuration GOOGLE_MAPS_MAP_ID manquante'
                    ]);
                    break;
                }

                $response = $dependances->responseFactory->createFromArray(data: [
                    'status' => 'success',
                    'code' => null,
                    'message' => 'Configuration Google Maps JS récupérée',
                    'data' => [
                        'apiKey' => $googleMapsJsApiKey,
                        'mapId' => $googleMapsMapId
                    ]
                ]);
            } catch (\Throwable $th) {
                $dependances->tools->myErrorHandler(
                    errno: $th->getCode(),
                    errstr: $th->getMessage(),
                    errfile: $th->getFile(),
                    errline: $th->getLine()
                );
            }
            break;

        default:
            $response = $dependances->responseFactory->createFromArray(data: [
                'status' => 'error',
                'code' => 400,
                'message' => 'Action inconnue'
            ]);
            break;
    }
} else {
    $response = $dependances->responseFactory->createFromArray(data: [
        'status' => 'error',
        'code' => 5009,
        'message' => "Pas de session valable, l'utilisateur doit se reconnecter"
    ]);
}

echo json_encode(value: $response);