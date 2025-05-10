<?php
require_once '../includes/header.php';
?>
<!-- Kontrol Aralıkları -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-4">
        <label class="block text-sm text-white/70">Kontrol Aralıkları</label>
        <button onclick="openTimeConverterModal()" 
                class="text-white/50 hover:text-white transition-colors flex items-center gap-2 text-sm bg-white/5 hover:bg-white/10 px-3 py-1.5 rounded-lg">
            <i class="fas fa-clock"></i>
            <span>Dakika → Salise</span>
        </button>
    </div>
    <form id="intervalsForm" onsubmit="handleIntervalsSubmit(event)" class="grid grid-cols-3 gap-6">
        <input type="hidden" name="action" value="update_intervals">
        
        <!-- Mevcut kontroller -->
        <div class="bg-black/20 p-6 rounded-lg">
            <label class="block text-sm text-white/70 mb-2">
                <i class="fas fa-newspaper text-blue-400 mr-2"></i>
                Haber Kontrol Süresi
            </label>
            <input type="number" name="news_interval" 
                    value="<?php echo $manifest['settings']['checkIntervals']['news']; ?>" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            <span class="text-xs text-white/50 mt-1 block">Milisaniye cinsinden süre</span>
        </div>

        <div class="bg-black/20 p-6 rounded-lg">
            <label class="block text-sm text-white/70 mb-2">
                <i class="fas fa-bell text-yellow-400 mr-2"></i>
                Duyuru Kontrol Süresi
            </label>
            <input type="number" name="notice_interval" 
                    value="<?php echo $manifest['settings']['checkIntervals']['notice']; ?>" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            <span class="text-xs text-white/50 mt-1 block">Milisaniye cinsinden süre</span>
        </div>

        <div class="bg-black/20 p-6 rounded-lg">
            <label class="block text-sm text-white/70 mb-2">
                <i class="fas fa-bars text-green-400 mr-2"></i>
                Menü Kontrol Süresi
            </label>
            <input type="number" name="menu_interval" 
                    value="<?php echo $manifest['settings']['checkIntervals']['menuItems']; ?>" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            <span class="text-xs text-white/50 mt-1 block">Milisaniye cinsinden süre</span>
        </div>

        <!-- Yeni eklenen kontroller -->
        <div class="bg-black/20 p-6 rounded-lg">
            <label class="block text-sm text-white/70 mb-2">
                <i class="fas fa-image text-purple-400 mr-2"></i>
                Arkaplan Kontrol Süresi
            </label>
            <input type="number" name="background_interval" 
                    value="<?php echo $manifest['settings']['checkIntervals']['background']; ?>" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            <span class="text-xs text-white/50 mt-1 block">Milisaniye cinsinden süre</span>
        </div>

        <div class="bg-black/20 p-6 rounded-lg">
            <label class="block text-sm text-white/70 mb-2">
                <i class="fas fa-share-alt text-pink-400 mr-2"></i>
                Sosyal Medya Kontrol Süresi
            </label>
            <input type="number" name="social_media_interval" 
                    value="<?php echo $manifest['settings']['checkIntervals']['socialMedia']; ?>" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            <span class="text-xs text-white/50 mt-1 block">Milisaniye cinsinden süre</span>
        </div>

        <div class="bg-black/20 p-6 rounded-lg">
            <label class="block text-sm text-white/70 mb-2">
                <i class="fas fa-cube text-orange-400 mr-2"></i>
                Uygulama Kontrol Süresi
            </label>
            <input type="number" name="files_interval" 
                    value="<?php echo $manifest['settings']['checkIntervals']['files']; ?>" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            <span class="text-xs text-white/50 mt-1 block">Milisaniye cinsinden süre</span>
        </div>

        <div class="col-span-3 flex justify-end">
            <button type="submit" 
                    class="px-4 py-2 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 transition-colors">
                <i class="fas fa-save mr-2"></i>
                Süreleri Güncelle
            </button>
        </div>
    </form>
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

    // Kontrol Aralıkları form submit işlemi
    async function handleIntervalsSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update_intervals',
                    news_interval: parseInt(formData.get('news_interval')),
                    notice_interval: parseInt(formData.get('notice_interval')),
                    menu_interval: parseInt(formData.get('menu_interval')),
                    background_interval: parseInt(formData.get('background_interval')),
                    social_media_interval: parseInt(formData.get('social_media_interval')),
                    files_interval: parseInt(formData.get('files_interval'))
                })
            });

            const data = await response.json();

            if (data.success) {
                // Başarılı bildirim göster
                showSuccessToast('Kontrol süreleri başarıyla güncellendi!');
            } else {
                throw new Error(data.error || 'Güncelleme işlemi başarısız');
            }
        } catch (error) {
            console.error('Kontrol süreleri güncelleme hatası:', error);
            SwalCustom.fire({
                title: 'Hata!',
                text: error.message || 'Kontrol süreleri güncellenirken bir hata oluştu.',
                icon: 'error'
            });
        }
    }
</script>

<!-- Time Converter Modal -->
<div id="timeConverterModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="w-full max-w-md bg-[#111111] rounded-2xl border border-white/10">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-white/10">
                <h3 class="text-lg font-medium text-white">Dakika/Saniye → Salise Dönüştürücü</h3>
                <button onclick="closeTimeConverterModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Dakika</label>
                        <input type="text" id="minutesInput"
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 text-white"
                                placeholder="Örn: 1 veya <30"
                                oninput="convertToMilliseconds(this.value)">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Salise</label>
                        <input type="text" id="millisecondsResult" 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 text-white"
                                readonly>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end p-4 border-t border-white/10">
                <button onclick="copyMilliseconds()" 
                        class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/70 rounded-lg transition-all flex items-center gap-2">
                    <i class="fas fa-copy"></i>
                    <span>Kopyala</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>

    function openTimeConverterModal() {
        const modal = document.getElementById('timeConverterModal');
        modal.style.position = 'fixed';
        modal.style.zIndex = '99999';
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeTimeConverterModal() {
        const modal = document.getElementById('timeConverterModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // ESC tuşu ile kapatma
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTimeConverterModal();
        }
    });
    // Dakika/saniye to salise dönüşümü fonksiyonunu güncelleyelim
    function convertToMilliseconds(value) {
        // Input'u temizle ve boş kontrolü yap
        if (!value || value.trim() === '') {
            document.getElementById('millisecondsResult').value = '';
            return;
        }

        let milliseconds = 0;
        value = value.trim();

        // Saniye kontrolü (<) işareti varsa
        if (value.includes('<')) {
            // < işaretini kaldır ve sayıyı al
            const seconds = parseInt(value.replace('<', ''));
            if (!isNaN(seconds) && seconds >= 1) {
                milliseconds = seconds * 1000; // Saniyeyi milisaniyeye çevir
            } else {
                document.getElementById('millisecondsResult').value = '';
                return;
            }
        } else {
            // Normal dakika hesaplaması
            const minutes = parseInt(value);
            if (!isNaN(minutes) && minutes >= 1) {
                milliseconds = minutes * 60 * 1000; // Dakikayı milisaniyeye çevir
            } else {
                document.getElementById('millisecondsResult').value = '';
                return;
            }
        }

        document.getElementById('millisecondsResult').value = milliseconds;
    }

    // Salise değerini kopyalama fonksiyonu
    function copyMilliseconds() {
        const result = document.getElementById('millisecondsResult').value;
        if (result && result !== 'Geçersiz değer') {
            navigator.clipboard.writeText(result);
            
            // Kopyalama bildirimi
            const button = document.querySelector('button[onclick="copyMilliseconds()"]');
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check text-green-400"></i><span>Kopyalandı!</span>';
            setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 1000);
        }
    }

    // Input temizleme ve ESC ile kapatma
    document.getElementById('minutesInput').addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTimeConverterModal();
        }
    });


    // Input kontrolü için event listener ekleyelim
    document.getElementById('minutesInput').addEventListener('input', function(e) {
        let value = e.target.value;
        
        // Sadece sayılar ve < işaretine izin ver
        value = value.replace(/[^0-9<]/g, '');
        
        // < işareti sadece başta olabilir
        if (value.includes('<')) {
            value = '<' + value.replace(/</g, '');
        }
        
        // Input değerini güncelle
        e.target.value = value;
        
        // Dönüşümü yap
        convertToMilliseconds(value);
    });
</script>
<?php
require_once '../includes/footer.php';
?> 