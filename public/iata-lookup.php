<?php
header('Content-Type: application/json');

$q = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$csvFile = __DIR__ . '/../airports.csv';
$cacheDir = __DIR__ . '/../cache/iata/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}
$cacheFile = $cacheDir . 'csv_' . md5($q) . '.json';
if (file_exists($cacheFile) && filemtime($cacheFile) > (time() - 86400)) {
    echo file_get_contents($cacheFile);
    exit;
}

$grouped = [];
$unique = [];
if (($handle = fopen($csvFile, 'r')) !== false) {
    $header = fgetcsv($handle);
    while (($row = fgetcsv($handle)) !== false) {
        $data = array_combine($header, $row);
        // Match on city, name, or code
        if (
            strpos(strtolower($data['city']), $q) !== false ||
            strpos(strtolower($data['name']), $q) !== false ||
            strpos(strtolower($data['code']), $q) !== false
        ) {
            $city = $data['city'];
            $key = $city . '|' . $data['code'];
            if (isset($unique[$key])) continue;
            $unique[$key] = true;
            $grouped[$city][] = [
                'code' => $data['code'],
                'city' => $city,
                'name' => $data['name'],
                'country' => $data['country'],
                'state' => $data['state'],
                'icao' => $data['icao'],
            ];
        }
    }
    fclose($handle);
}
file_put_contents($cacheFile, json_encode($grouped));
echo json_encode($grouped); 