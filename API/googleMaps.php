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

switch ($request['action'] ?? null) {
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

echo json_encode(value: $response);