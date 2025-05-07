document.addEventListener('DOMContentLoaded', function () {
    const addLocationBtn = document.getElementById('addLocationBtn');
    const locationsContainer = document.getElementById('locationsContainer');
    let locationCount = 1; // Başlangıçta 1 konum alanı var

    addLocationBtn.addEventListener('click', function () {
        locationCount++;
        const newLocationEntry = document.createElement('div');
        newLocationEntry.classList.add('location-entry', 'mb-6', 'p-4', 'border', 'border-gray-200', 'rounded-md', 'mt-4');
        newLocationEntry.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xl font-semibold text-gray-700">Konum #${locationCount}</h3>
                <button type="button" class="remove-location-btn text-red-500 hover:text-red-700 font-semibold" title="Bu konumu kaldır">Kaldır</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name_${locationCount}" class="block text-sm font-medium text-gray-700 mb-1">Konum Adı:</label>
                    <input type="text" name="locations[${locationCount - 1}][name]" id="name_${locationCount}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="address_${locationCount}" class="block text-sm font-medium text-gray-700 mb-1">Adres (Opsiyonel):</label>
                    <input type="text" name="locations[${locationCount - 1}][address]" id="address_${locationCount}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="latitude_${locationCount}" class="block text-sm font-medium text-gray-700 mb-1">Enlem (Latitude):</label>
                    <input type="number" step="any" name="locations[${locationCount - 1}][latitude]" id="latitude_${locationCount}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Örn: 40.7128">
                </div>
                <div>
                    <label for="longitude_${locationCount}" class="block text-sm font-medium text-gray-700 mb-1">Boylam (Longitude):</label>
                    <input type="number" step="any" name="locations[${locationCount - 1}][longitude]" id="longitude_${locationCount}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Örn: -74.0060">
                </div>
            </div>
            <div class="mt-4">
                <label for="description_${locationCount}" class="block text-sm font-medium text-gray-700 mb-1">Açıklama (Opsiyonel):</label>
                <textarea name="locations[${locationCount - 1}][description]" id="description_${locationCount}" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
            </div>
        `;
        locationsContainer.appendChild(newLocationEntry);

        // "Kaldır" butonlarına event listener ekle
        attachRemoveListeners();
    });

    function attachRemoveListeners() {
        const removeButtons = document.querySelectorAll('.remove-location-btn');
        removeButtons.forEach(button => {
            // Eski listener'ları kaldırıp yenisini eklemek, birden fazla listener birikmesini önler
            button.replaceWith(button.cloneNode(true));
        });
        // Klonlanmış butonlara yeniden listener ekle
        document.querySelectorAll('.remove-location-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.target.closest('.location-entry').remove();
                // Konum sayılarını ve ID'lerini güncellemek daha karmaşık, şimdilik sadece kaldırıyoruz.
                // Gerekirse bu kısım daha sonra geliştirilebilir.
            });
        });
    }
    // Sayfa yüklendiğinde varsa ilk "Kaldır" butonu için de listener ekle
    attachRemoveListeners();
});