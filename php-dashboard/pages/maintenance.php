<?php require_once '../includes/header.php'; ?>

            <!-- Sağ İçerik Alanı - Kalan Genişlik -->
            <div class="flex-1 space-y-8">

                <!-- Bakım Modu Ayarları -->
                <div class="glass-effect rounded-2xl p-8 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-white">Bakım Modu Ayarları</h2>
                    </div>

                    <div class="space-y-4 mt-6">
                        <!-- Bakım Modu Durumu -->
                        <div class="flex items-center justify-between">
                            <span class="text-white/70">Bakım Modu</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="maintenanceEnabled" class="sr-only peer" 
                                       <?php echo isset($manifest['maintenance']['enabled']) && $manifest['maintenance']['enabled'] == 1 ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer 
                                            peer-checked:after:translate-x-full peer-checked:after:border-white 
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                            after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                            peer-checked:bg-blue-500/50"></div>
                            </label>
                        </div>

                        <!-- Bakım Mesajı (TR) -->
                        <div>
                            <label for="maintenanceMessageTr" class="block text-white/70 mb-1">Bakım Mesajı (TR)</label>
                            <textarea id="maintenanceMessageTr" rows="3" 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                                ><?php echo isset($manifest['maintenance']['message']['tr']) ? htmlspecialchars($manifest['maintenance']['message']['tr']) : ''; ?></textarea>
                        </div>

                        <!-- Bakım Mesajı (EN) -->
                        <div>
                            <label for="maintenanceMessageEn" class="block text-white/70 mb-1">Bakım Mesajı (EN)</label>
                            <textarea id="maintenanceMessageEn" rows="3" 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                                ><?php echo isset($manifest['maintenance']['message']['en']) ? htmlspecialchars($manifest['maintenance']['message']['en']) : ''; ?></textarea>
                        </div>

                        <!-- Bakım URL -->
                        <div>
                            <label for="maintenanceUrl" class="block text-white/70 mb-1">Bakım Sayfası URL</label>
                            <input type="text" id="maintenanceUrl" 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                                value="<?php echo isset($manifest['maintenance']['url']) ? htmlspecialchars($manifest['maintenance']['url']) : ''; ?>">
                        </div>

                        <!-- Kaydet Butonu -->
                        <div class="flex justify-end">
                            <button onclick="updateMaintenance()" 
                                class="px-4 py-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all">
                                Kaydet
                            </button>
                        </div>
                    </div>
                </div>
<script>
// SweetAlert2 için özel tema ayarları
const SwalCustom = Swal.mixin({
    customClass: {
        popup: 'bg-[#111111] border border-white/10',
        title: 'text-white',
        htmlContainer: 'text-white/70',
        confirmButton: 'bg-red-500/10 hover:bg-red-500/20 text-red-400 px-4 py-2 rounded-lg transition-all',
        cancelButton: 'bg-white/5 hover:bg-white/10 text-white/70 px-4 py-2 rounded-lg transition-all'
    },
    background: '#111111',
    color: '#fff',
    confirmButtonText: 'Tamam',
    cancelButtonText: 'İptal'
});

// Toast bildirimi için özel stil
function showSuccessToast(message) {
    Swal.fire({
        title: message,
        icon: 'success',
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 3000,
        background: '#111111',
        color: '#fff',
        customClass: {
            popup: 'bg-[#111111] border border-white/10',
            title: 'text-white text-sm'
        }
    });
}

                async function updateMaintenance() {
                    try {
                        const data = {
                            action: 'update_maintenance',
                            enabled: document.getElementById('maintenanceEnabled').checked ? 1 : 0,
                            message: {
                                tr: document.getElementById('maintenanceMessageTr').value,
                                en: document.getElementById('maintenanceMessageEn').value
                            },
                            url: document.getElementById('maintenanceUrl').value
                        };

                        const response = await fetch('../api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        // Check if response is OK
                        if (!response.ok) {
                            throw new Error(`Server response: ${response.status} ${response.statusText}`);
                        }

                        // Try to parse JSON
                        let result;
                        try {
                            result = await response.json();
                        } catch (e) {
                            throw new Error('Sunucudan geçersiz yanıt alındı. API endpoint\'i kontrol edin.');
                        }

                        if (result.success) {
                            showSuccessToast('Bakım modu ayarları başarıyla güncellendi');
                        } else {
                            throw new Error(result.error || 'Güncelleme başarısız');
                        }
                    } catch (error) {
                        console.error('Bakım modu güncelleme hatası:', error);
                        SwalCustom.fire({
                            title: 'Hata!',
                            text: error.message,
                            icon: 'error'
                        });
                    }
                }
                </script>

<?php
require_once '../includes/footer.php';
?> 