<?php

/**
 * Belirtilen bir merkez nokta, yarıçap ve sayıda dairenin ÇEVRESİNDE düzenli aralıklarla konumlar üretir.
 *
 * @param float $centerLat Merkez enlem.
 * @param float $centerLon Merkez boylam.
 * @param float $radiusKm Kilometre cinsinden yarıçap.
 * @param int $numPoints Oluşturulacak nokta sayısı.
 * @param array $keywords Konum adları için kullanılacak anahtar kelimeler.
 * @param string $websiteUrl Konum açıklamaları için web sitesi URL'si.
 * @return array Oluşturulan konumların dizisi.
 */
function generatePointsOnCircumference(float $centerLat, float $centerLon, float $radiusKm, int $numPoints, array $keywords, string $websiteUrl): array {
    $generatedLocations = [];
    $earthRadiusKm = 6371.0; // Dünya'nın ortalama yarıçapı (km)
    $numKeywords = count($keywords);

    if ($numKeywords === 0) {
        $keywords = ['Çevresel Nokta'];
        $numKeywords = 1;
    }

    if ($numPoints <= 0) { // Hiç nokta istenmiyorsa boş dizi döndür
        return [];
    }

    $angleStep = 360.0 / $numPoints; // Noktalar arasındaki açı farkı

    for ($i = 0; $i < $numPoints; $i++) {
        // 1. Açıyı (bearing) hesapla: Her nokta için eşit aralıklarla
        $bearingDegrees = $i * $angleStep;
        $bearingRad = deg2rad($bearingDegrees);

        // 2. Mesafe her zaman yarıçap kadar olacak (dairenin çevresinde)
        $distanceKm = $radiusKm;

        // Merkez enlem ve boylamı radyana çevir
        $lat1Rad = deg2rad($centerLat);
        $lon1Rad = deg2rad($centerLon);

        // Açısal mesafeyi hesapla (Dünya yarıçapına göre)
        $angularDistance = $distanceKm / $earthRadiusKm;

        // Yeni noktanın enlemini hesapla
        $lat2Rad = asin(sin($lat1Rad) * cos($angularDistance) +
                        cos($lat1Rad) * sin($angularDistance) * cos($bearingRad));

        // Yeni noktanın boylamını hesapla
        $lon2Rad = $lon1Rad + atan2(sin($bearingRad) * sin($angularDistance) * cos($lat1Rad),
                                    cos($angularDistance) - sin($lat1Rad) * sin($lat2Rad));

        // Radyandan dereceye çevir
        $newLat = rad2deg($lat2Rad);
        $newLon = rad2deg($lon2Rad);

        // Anahtar kelimeyi ve açıklamayı belirle
        $keyword = $keywords[$i % $numKeywords]; // Anahtar kelimeleri döngüsel kullan
        $pointName = $keyword; // SADECE anahtar kelimeyi kullan
        $description = "Detaylar için ziyaret edin: " . htmlspecialchars($websiteUrl) . "\n " . htmlspecialchars($keyword);

        $generatedLocations[] = [
            'name' => $pointName,
            'address' => '',
            'latitude' => round($newLat, 6),
            'longitude' => round($newLon, 6),
            'description' => $description
        ];
    }

    return $generatedLocations;
}


/**
 * Verilen konum dizisinden CSV içeriği oluşturur.
 * (Bu fonksiyon öncekiyle 거의 aynı, sadece $location['address'] boş olabilir)
 * @param array $locations Konum bilgilerini içeren dizi.
 * @return string CSV formatında metin.
 */
function generateCsvContent(array $locations): string {
    if (empty($locations)) {
        return '';
    }
    $csvOutput = "Name,Address,Latitude,Longitude,Description\n";

    foreach ($locations as $location) {
        $name = str_replace('"', '""', $location['name'] ?? '');
        $address = str_replace('"', '""', $location['address'] ?? ''); // Artık boş olabilir
        $latitude = $location['latitude'] ?? '';
        $longitude = $location['longitude'] ?? '';
        $description = str_replace('"', '""', $location['description'] ?? '');

        $csvOutput .= sprintf(
            '"%s","%s","%s","%s","%s"' . "\n",
            $name,
            $address,
            $latitude,
            $longitude,
            $description
        );
    }
    return $csvOutput;
}

/**
 * Verilen konum dizisinden ve daire parametrelerinden KML içeriği oluşturur.
 *
 * @param array $locations Konum bilgilerini içeren dizi (rastgele noktalar).
 * @param string $mapName KML dosyasındaki haritanın adı.
 * @param float|null $centerLat Dairenin merkez enlemi (daire çizimi için).
 * @param float|null $centerLon Dairenin merkez boylamı (daire çizimi için).
 * @param float|null $radiusKm Dairenin yarıçapı (daire çizimi için).
 * @return string KML formatında XML metni.
 */
function generateKmlContent(array $locations, string $mapName = 'Oluşturulan Haritam', ?float $centerLat = null, ?float $centerLon = null, ?float $radiusKm = null): string {
    // Fonksiyon tanımında $centerLat, $centerLon, $radiusKm parametrelerini ekledik.
    // Nullable (?) yaptık ki eski çağrılarda hata vermesin ama idealde process_data.php'den bu değerleri göndereceğiz.

    $kml = [];
    $kml[] = '<?xml version="1.0" encoding="UTF-8"?>';
    $kml[] = '<kml xmlns="http://www.opengis.net/kml/2.2">';
    $kml[] = '  <Document>';
    $kml[] = '    <name>' . htmlspecialchars($mapName) . '</name>';

    // Daire Çizgisi için Stil Tanımı (Örnek KML'den esinlenerek)
    $kml[] = '    <Style id="circleLineStyle">'; // Stil için bir ID tanımlıyoruz
    $kml[] = '      <LineStyle>';
    $kml[] = '        <color>ff0000ff</color>'; // KML renk formatı: aabbggrr (alfa, mavi, yeşil, kırmızı) -> ff0000ff = Kırmızı, opak
    $kml[] = '        <width>2</width>';
    $kml[] = '      </LineStyle>';
    $kml[] = '      <PolyStyle>'; // Poligonlar için (LineString bir poligon olmasa da bazen PolyStyle etkileyebilir)
    $kml[] = '        <fill>0</fill>'; // İçini doldurma (0 = hayır)
    $kml[] = '      </PolyStyle>';
    $kml[] = '    </Style>';

    // Daire Çizgisini (Circle) Ekleme
    if ($centerLat !== null && $centerLon !== null && $radiusKm !== null && $radiusKm > 0) {
        $circleCoords = generateCircleCoordinates($centerLat, $centerLon, $radiusKm);
        $kml[] = '    <Placemark>';
        $kml[] = '      <name>Çalışma Alanı Sınırı</name>';
        $kml[] = '      <description><![CDATA[Yarıçap: ' . htmlspecialchars($radiusKm) . ' km]]></description>';
        $kml[] = '      <styleUrl>#circleLineStyle</styleUrl>'; // Yukarıda tanımladığımız stili kullan
        $kml[] = '      <LineString>';
        $kml[] = '        <tessellate>1</tessellate>'; // Yeryüzü şekillerine uyması için
        $kml[] = '        <coordinates>' . $circleCoords . '</coordinates>';
        $kml[] = '      </LineString>';
        $kml[] = '    </Placemark>';
    }

    // Rastgele Oluşturulan Noktaları Ekleme
    if (!empty($locations)) {
        foreach ($locations as $location) {
            $name = htmlspecialchars($location['name'] ?? 'İsimsiz Konum');
            $latitude = htmlspecialchars($location['latitude'] ?? '0');
            $longitude = htmlspecialchars($location['longitude'] ?? '0');
            $descriptionText = $location['description'] ?? '';

            $kml[] = '    <Placemark>';
            $kml[] = '      <name>' . $name . '</name>';
            if (!empty($descriptionText)) {
                $kml[] = '      <description><![CDATA[' . $descriptionText . ']]></description>';
            }
            
            if (!empty($location['latitude']) && !empty($location['longitude'])) {
                $kml[] = '      <Point>';
                $kml[] = '        <coordinates>' . $longitude . ',' . $latitude . ',0</coordinates>';
                $kml[] = '      </Point>';
            }
            $kml[] = '    </Placemark>';
        }
    } else if ($centerLat === null) { // Eğer hiç nokta yoksa ve daire de çizilmiyorsa boş bir KML olmasın diye uyarı
        $kml[] = '    <Placemark>';
        $kml[] = '      <name>Veri Yok</name>';
        $kml[] = '      <description>Oluşturulacak konum bulunamadı.</description>';
        $kml[] = '    </Placemark>';
    }


    $kml[] = '  </Document>';
    $kml[] = '</kml>';

    return implode("\n", $kml);
}

/**
 * Belirtilen merkez nokta ve yarıçap için KML LineString koordinatlarını oluşturur.
 * Bu, dairenin çevresini çizmek için kullanılır.
 *
 * @param float $centerLat Merkez enlem.
 * @param float $centerLon Merkez boylam.
 * @param float $radiusKm Kilometre cinsinden yarıçap.
 * @param int $segments Daireyi oluşturmak için kullanılacak segment sayısı (nokta sayısı).
 * @return string KML <coordinates> formatında koordinat listesi.
 */
function generateCircleCoordinates(float $centerLat, float $centerLon, float $radiusKm, int $segments = 72): string {
    $coordinates = [];
    $earthRadiusKm = 6371.0;

    $lat1Rad = deg2rad($centerLat);
    $lon1Rad = deg2rad($centerLon);
    $angularDistance = $radiusKm / $earthRadiusKm;

    for ($i = 0; $i <= $segments; $i++) { // <= $segments olması, başlangıç noktasına geri dönerek kapatmasını sağlar
        $bearing = deg2rad(($i / $segments) * 360); // 0'dan 360 dereceye

        $lat2Rad = asin(sin($lat1Rad) * cos($angularDistance) +
                        cos($lat1Rad) * sin($angularDistance) * cos($bearing));
        $lon2Rad = $lon1Rad + atan2(sin($bearing) * sin($angularDistance) * cos($lat1Rad),
                                    cos($angularDistance) - sin($lat1Rad) * sin($lat2Rad));

        $newLat = rad2deg($lat2Rad);
        $newLon = rad2deg($lon2Rad);

        $coordinates[] = round($newLon, 6) . ',' . round($newLat, 6) . ',0'; // KML: lon,lat,altitude
    }

    return implode(' ', $coordinates);
}


?>