<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google My Maps - Otomatik Konum Oluşturucu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <div class="container mx-auto p-4 sm:p-8">
        <header class="mb-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-blue-600">Google My Maps için Otomatik Konum Oluşturucu</h1>
            <p class="text-md text-gray-600 mt-2">Belirttiğiniz merkez ve yarıçap içinde otomatik konumlar oluşturun.</p>
        </header>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Hata!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Başarılı!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($_GET['success']); ?></span>
            </div>
        <?php endif; ?>

        <form id="autoLocationForm" action="process_data.php" method="POST" class="bg-white p-6 rounded-lg shadow-md space-y-6">
            
            <div>
                <h3 class="text-xl font-semibold mb-3 text-gray-700">Daire Parametreleri</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="center_lat" class="block text-sm font-medium text-gray-700 mb-1">Merkez Enlem (Latitude):</label>
                        <input type="number" step="any" name="center_lat" id="center_lat" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Örn: 40.7128">
                    </div>
                    <div>
                        <label for="center_lon" class="block text-sm font-medium text-gray-700 mb-1">Merkez Boylam (Longitude):</label>
                        <input type="number" step="any" name="center_lon" id="center_lon" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Örn: -74.0060">
                    </div>
                    <div>
                        <label for="radius_km" class="block text-sm font-medium text-gray-700 mb-1">Yarıçap (km):</label>
                        <input type="number" step="0.1" min="0.1" name="radius_km" id="radius_km" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Örn: 5">
                    </div>
                    <div>
                        <label for="num_points" class="block text-sm font-medium text-gray-700 mb-1">Oluşturulacak Konum Sayısı:</label>
                        <input type="number" min="1" name="num_points" id="num_points" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Örn: 10">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold mb-3 text-gray-700">Konum Detayları</h3>
                <div>
                    <label for="keywords" class="block text-sm font-medium text-gray-700 mb-1">Anahtar Kelimeler (Her satıra bir tane):</label>
                    <textarea name="keywords" id="keywords" rows="5" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Şehir Merkezi\nPark Alanı\nAlışveriş Noktası"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Her konum başlığı için bu kelimeler sırayla kullanılır. Kelime sayısı konum sayısından azsa tekrar eder.</p>
                </div>
                <div class="mt-4">
                    <label for="website_url" class="block text-sm font-medium text-gray-700 mb-1">Web Sitesi Linki (Açıklamaya Eklenecek):</label>
                    <input type="url" name="website_url" id="website_url" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="https://www.example.com">
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                 <button type="submit" name="format" value="csv" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    CSV Oluştur
                </button>
                <button type="submit" name="format" value="kml" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    KML Oluştur
                </button>
            </div>
        </form>
    </div>

    </body>
</html>