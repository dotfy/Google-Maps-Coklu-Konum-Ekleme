<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=" . urlencode("Geçersiz istek yöntemi."));
    exit;
}

// Formdan gelen verileri al ve doğrula
$centerLat = filter_input(INPUT_POST, 'center_lat', FILTER_VALIDATE_FLOAT);
$centerLon = filter_input(INPUT_POST, 'center_lon', FILTER_VALIDATE_FLOAT);
$radiusKm = filter_input(INPUT_POST, 'radius_km', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0.1]]);
$numPoints = filter_input(INPUT_POST, 'num_points', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$keywordsInput = isset($_POST['keywords']) ? trim($_POST['keywords']) : '';
$websiteUrl = filter_input(INPUT_POST, 'website_url', FILTER_VALIDATE_URL);
$format = isset($_POST['format']) ? trim($_POST['format']) : null;

// Temel doğrulama
if ($centerLat === false || $centerLon === false || $radiusKm === false || $numPoints === false || empty($keywordsInput) || $websiteUrl === false) {
    $errorMessage = "Lütfen tüm alanları doğru bir şekilde doldurun. ";
    if ($centerLat === false) $errorMessage .= "Geçerli bir merkez enlem girin. ";
    if ($centerLon === false) $errorMessage .= "Geçerli bir merkez boylam girin. ";
    if ($radiusKm === false) $errorMessage .= "Geçerli bir yarıçap (en az 0.1 km) girin. ";
    if ($numPoints === false) $errorMessage .= "Geçerli bir konum sayısı (en az 1) girin. ";
    if (empty($keywordsInput)) $errorMessage .= "Anahtar kelimeleri girin. ";
    if ($websiteUrl === false) $errorMessage .= "Geçerli bir web sitesi URL'si girin. ";
    
    header("Location: index.php?error=" . urlencode(trim($errorMessage)));
    exit;
}

if (!$format || !in_array($format, ['csv', 'kml'])) {
    header("Location: index.php?error=" . urlencode("Geçersiz dosya formatı seçimi."));
    exit;
}

// Anahtar kelimeleri diziye çevir (her satır bir eleman)
$keywords = array_filter(array_map('trim', explode("\n", $keywordsInput)));
if (empty($keywords)) {
    header("Location: index.php?error=" . urlencode("Lütfen en az bir geçerli anahtar kelime girin."));
    exit;
}

// Rastgele konumları üret
$generatedLocations = generatePointsOnCircumference($centerLat, $centerLon, $radiusKm, $numPoints, $keywords, $websiteUrl);

if (empty($generatedLocations)) {
    header("Location: index.php?error=" . urlencode("Konumlar üretilemedi. Lütfen girdilerinizi kontrol edin."));
    exit;
}

$filename_base = "otomatik_konumlar_" . date("Ymd_His");

if ($format === 'csv') {
    $content = generateCsvContent($generatedLocations);
    if (empty($content)) {
        header("Location: index.php?error=" . urlencode("CSV içeriği oluşturulamadı."));
        exit;
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename_base . '.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo $content;
    exit;

} elseif ($format === 'kml') {
    $mapName = "Otomatik Oluşturulan Harita (" . date("d-m-Y") . ")";
    // generateKmlContent fonksiyonuna $centerLat, $centerLon, $radiusKm değerlerini iletiyoruz
    $content = generateKmlContent($generatedLocations, $mapName, $centerLat, $centerLon, $radiusKm);
    if (empty($content)) {
        header("Location: index.php?error=" . urlencode("KML içeriği oluşturulamadı."));
        exit;
    }
    header('Content-Type: application/vnd.google-earth.kml+xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename_base . '.kml"');
    echo $content;
    exit;

} else {
    header("Location: index.php?error=" . urlencode("Bilinmeyen bir hata oluştu."));
    exit;
}

?>