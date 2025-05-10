<?php require_once '../includes/header.php'; ?>
<!-- Dil Yönetimi Bölümü -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Dil Yönetimi
            </h2>
            <p class="text-sm text-white/50 mt-1">Çeviri metinlerini düzenleyin</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openAddLanguageItemModal()" 
                    class="bg-orange-500/10 hover:bg-orange-500/20 text-orange-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
                <i class="fas fa-language"></i> Yeni Dil Ekle
            </button>
            <button onclick="openAddLanguageModal()" 
                    class="bg-green-500/10 hover:bg-green-500/20 text-green-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
                <i class="fas fa-plus"></i> Yeni Çeviri Ekle
            </button>
        </div>
    </div>

    <!-- Aktif Diller -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-white/90 mb-4">Aktif Diller</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <?php
            // Varsayılan diller
            $defaultLanguages = [
                'en' => 'English',
                'tr' => 'Türkçe'
            ];
            
            // Manifest'teki ek diller
            $additionalLanguages = isset($manifest['languages']) ? $manifest['languages'] : [];
            
            // Tüm dilleri birleştir
            $allLanguages = array_merge($defaultLanguages, $additionalLanguages);
            
            // translations içindeki tüm dilleri kontrol et
            $translationLanguages = array_keys($manifest['translations']);
            
            // Tüm dilleri göster
            foreach ($allLanguages as $langCode => $langName):
            ?>
                <div class="bg-black/30 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <span class="text-white font-semibold"><?php echo $langName; ?></span>
                        <span class="text-xs text-white/60 block mt-1"><?php echo strtoupper($langCode); ?></span>
                    </div>
                    <?php if ($langCode !== 'en' && $langCode !== 'tr'): ?>
                    <button type="button" onclick="deleteLanguage('<?php echo $langCode; ?>')" 
                            class="px-2 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Çeviri Tablosu -->
    <form id="languageForm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="text-left p-3 border-b border-white/10 text-white/70 w-1/6">Anahtar</th>
                        <?php foreach ($translationLanguages as $langCode): ?>
                        <th class="text-left p-3 border-b border-white/10 text-white/70">
                            <?php echo isset($allLanguages[$langCode]) ? $allLanguages[$langCode] : ucfirst($langCode); ?>
                        </th>
                        <?php endforeach; ?>
                        <th class="text-center p-3 border-b border-white/10 text-white/70 w-[60px]">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $excludeKeys = ['menuItems' => '', 'notices' => ''];
                    $translationKeys = array_diff_key($manifest['translations']['en'], $excludeKeys);
                    
                    // Basit çevirileri göster
                    foreach ($translationKeys as $key => $value):
                        if (is_string($value)):
                    ?>
                    <tr class="hover:bg-black/20">
                        <td class="p-3 border-b border-white/10 text-white/90">
                            <code class="bg-black/30 px-2 py-1 rounded text-xs font-mono"><?php echo $key; ?></code>
                        </td>
                        <?php foreach ($translationLanguages as $langCode): ?>
                        <td class="p-3 border-b border-white/10">
                            <input type="text" 
                                name="trans_<?php echo $langCode; ?>_<?php echo $key; ?>"
                                value="<?php echo htmlspecialchars($manifest['translations'][$langCode][$key] ?? ''); ?>"
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-white text-sm">
                        </td>
                        <?php endforeach; ?>
                        <td class="p-3 border-b border-white/10 text-center">
                            <button type="button" onclick="deleteTranslation('<?php echo $key; ?>')" 
                                    class="px-2 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endif; endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Dizi Çevirileri Bölümü -->
        <?php foreach ($translationKeys as $key => $value):
            if (is_array($value) && !in_array($key, array_keys($excludeKeys))):
        ?>
        <div class="mt-8 bg-black/20 p-4 rounded-lg">
            <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
                <h3 class="text-lg font-medium text-white/90"><?php echo ucfirst($key); ?></h3>
                <button type="button" onclick="deleteTranslationSection('<?php echo $key; ?>')" 
                        class="px-3 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg">
                    <i class="fas fa-trash"></i> Tüm Grubu Sil
                </button>
            </div>
            
            <?php foreach ($value as $index => $item): ?>
            <div class="mb-6 border border-white/10 rounded-lg p-4 bg-black/30">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?php echo count($translationLanguages); ?> gap-4">
                    <?php foreach ($translationLanguages as $langCode): 
                        $langData = $manifest['translations'][$langCode][$key][$index] ?? $item;
                    ?>
                    <div>
                        <h4 class="text-white/80 text-sm font-medium mb-3"><?php echo isset($allLanguages[$langCode]) ? $allLanguages[$langCode] : ucfirst($langCode); ?></h4>
                        <?php foreach ($item as $itemKey => $itemValue): ?>
                        <div class="mb-3">
                            <label class="block text-xs text-white/50 mb-1"><?php echo ucfirst($itemKey); ?></label>
                            <input type="text" 
                                    name="trans_array_<?php echo $langCode; ?>_<?php echo $key; ?>_<?php echo $index; ?>_<?php echo $itemKey; ?>"
                                    value="<?php echo htmlspecialchars($langData[$itemKey] ?? ''); ?>"
                                    class="w-full bg-black/40 border border-white/10 rounded-lg px-3 py-2 text-white text-sm">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 border-t border-white/10 pt-3 flex justify-end">
                    <button type="button" onclick="deleteTranslationItem('<?php echo $key; ?>', <?php echo $index; ?>)" 
                            class="px-3 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg text-sm">
                        <i class="fas fa-trash"></i> Öğeyi Sil
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; endforeach; ?>

        <!-- Kaydet Butonu -->
        <div class="flex justify-end mt-8">
            <button type="button" onclick="saveAllTranslations()" 
                    class="px-5 py-2.5 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-all flex items-center gap-2">
                <i class="fas fa-save"></i> Tüm Değişiklikleri Kaydet
            </button>
        </div>
    </form>
</div>

<!-- Dil Ekleme Modalı -->
<div id="addLanguageModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Yeni Çeviri Anahtarı Ekle</h3>
                <button onclick="closeAddLanguageModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addLanguageForm" onsubmit="handleAddLanguage(event)" class="p-6 space-y-4">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Anahtar (Teknik İsim)</label>
                        <input type="text" name="key" required 
                            placeholder="ornek_anahtar" 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 text-white">
                        <p class="text-xs text-white/50 mt-1">Sadece harf, rakam ve alt çizgi (_) kullanın, boşluk ve özel karakter kullanmayın.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4" id="translationInputs">
                        <?php foreach ($translationLanguages as $langCode): ?>
                        <div>
                            <label class="block text-sm text-white/70 mb-2"><?php echo isset($allLanguages[$langCode]) ? $allLanguages[$langCode] : $langCode; ?> Değer</label>
                            <input type="text" name="value_<?php echo $langCode; ?>" required 
                                placeholder="<?php echo $langCode === 'en' ? 'English Translation' : ($langCode === 'tr' ? 'Türkçe Çeviri' : 'Translation'); ?>" 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 text-white">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddLanguageModal()"
                            class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-all">
                        İptal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-all">
                        Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Yeni Dil Ekleme Modalı -->
<div id="addLanguageItemModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="relative bg-[#111111] w-full max-w-md rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Yeni Dil Ekle</h3>
                <button onclick="closeAddLanguageItemModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addLanguageItemForm" onsubmit="handleAddLanguageItem(event)" class="p-6 space-y-4">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Dil Kodu</label>
                        <input type="text" name="lang_code" required 
                            placeholder="fr" 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 text-white">
                        <p class="text-xs text-white/50 mt-1">ISO 639-1 standart dil kodu kullanın (örn: fr, es, de)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Dil Adı</label>
                        <input type="text" name="lang_name" required 
                            placeholder="Français" 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 text-white">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddLanguageItemModal()"
                            class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-all">
                        İptal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-all">
                        Ekle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript kısmına eklenecek fonksiyonlar -->
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
                popup: 'bg-[#111111] border border-white/10 z-[200]',
                title: 'text-white text-sm',
                container: 'z-[200]'
            }
        });
    }

    // Hata toast bildirimi
    function showErrorToast(message) {
        Swal.fire({
            title: message,
            icon: 'error',
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 4000,
            background: '#111111',
            color: '#fff',
            customClass: {
                popup: 'bg-[#111111] border border-white/10 z-[200]',
                title: 'text-white text-sm',
                container: 'z-[200]'
            }
        });
    }

    // Hata alert'i
    function showErrorAlert(message) {
        SwalCustom.fire({
            title: 'Hata!',
            text: message,
            icon: 'error'
        });
    }

    // Dil ekleme modalı için fonksiyonlar
    function openAddLanguageModal() {
        const modal = document.getElementById('addLanguageModal');
        modal.style.position = 'fixed';
        modal.style.zIndex = '999999';
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeAddLanguageModal() {
        const modal = document.getElementById('addLanguageModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Yeni dil ekleme modalı için fonksiyonlar
    function openAddLanguageItemModal() {
        const modal = document.getElementById('addLanguageItemModal');
        modal.style.position = 'fixed';
        modal.style.zIndex = '999999';
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeAddLanguageItemModal() {
        const modal = document.getElementById('addLanguageItemModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Yeni dil eklemek için fonksiyon
    async function handleAddLanguageItem(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        const langCode = formData.get('lang_code').toLowerCase();
        const langName = formData.get('lang_name');

        // Dil kodu formatı kontrolü
        if (!/^[a-z]{2,3}$/.test(langCode)) {
            showErrorToast('Dil kodu 2-3 harfli bir kod olmalıdır (ISO 639-1)');
            return;
        }

        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add_language',
                    lang_code: langCode,
                    lang_name: langName
                })
            });

            // Yanıt durumu kontrolü
            if (!response.ok) {
                throw new Error(`Sunucu hatası: ${response.status}`);
            }

            // Yanıt içeriği kontrolü
            const text = await response.text();
            if (!text) {
                throw new Error('API boş yanıt döndürdü');
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON ayrıştırma hatası:', parseError, 'Yanıt:', text);
                throw new Error('API geçersiz bir yanıt döndürdü');
            }
            
            if (data.success) {
                showSuccessToast('Yeni dil başarıyla eklendi');
                closeAddLanguageItemModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.error || 'Ekleme işlemi başarısız');
            }
        } catch (error) {
            console.error('Dil ekleme hatası:', error);
            showErrorToast(error.message || 'Dil eklenirken bir hata oluştu.');
        }
    }

    // Dil silme işlemi
    async function deleteLanguage(langCode) {
        const result = await SwalCustom.fire({
            title: 'Emin misiniz?',
            text: `"${langCode}" dilini silmek istediğinize emin misiniz? Bu işlem, bu dilde yapılmış tüm çevirileri de silecektir.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_language',
                        lang_code: langCode
                    })
                });

                // Yanıt durumu kontrolü
                if (!response.ok) {
                    throw new Error(`Sunucu hatası: ${response.status}`);
                }

                // Yanıt içeriği kontrolü
                const text = await response.text();
                if (!text) {
                    throw new Error('API boş yanıt döndürdü');
                }

                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    console.error('JSON ayrıştırma hatası:', parseError, 'Yanıt:', text);
                    throw new Error('API geçersiz bir yanıt döndürdü');
                }
                
                if (data.success) {
                    showSuccessToast('Dil başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.error || 'Silme işlemi başarısız');
                }
            } catch (error) {
                console.error('Dil silme hatası:', error);
                showErrorToast(error.message || 'Dil silinirken bir hata oluştu.');
            }
        }
    }

    // Yeni çeviri eklemek için fonksiyon
    async function handleAddLanguage(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        const key = formData.get('key');
        
        // Tüm dil değerlerini topla
        const translations = {};
        for (const [name, value] of formData.entries()) {
            if (name.startsWith('value_')) {
                const langCode = name.replace('value_', '');
                translations[langCode] = value;
            }
        }

        // Anahtar formatı kontrolü
        if (!/^[a-zA-Z0-9_]+$/.test(key)) {
            showErrorToast('Anahtar sadece harf, rakam ve alt çizgi (_) içerebilir!');
            return;
        }

        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add_translation_all',
                    key: key,
                    translations: translations
                })
            });

            // Yanıt durumu kontrolü
            if (!response.ok) {
                throw new Error(`Sunucu hatası: ${response.status}`);
            }

            // Yanıt içeriği kontrolü
            const text = await response.text();
            if (!text) {
                throw new Error('API boş yanıt döndürdü');
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON ayrıştırma hatası:', parseError, 'Yanıt:', text);
                throw new Error('API geçersiz bir yanıt döndürdü');
            }
            
            if (data.success) {
                showSuccessToast('Yeni çeviri anahtarı başarıyla eklendi');
                closeAddLanguageModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.error || 'Ekleme işlemi başarısız');
            }
        } catch (error) {
            console.error('Çeviri ekleme hatası:', error);
            showErrorToast(error.message || 'Çeviri eklenirken bir hata oluştu.');
        }
    }

    // Bir çeviri anahtarını silme
    async function deleteTranslation(key) {
        const result = await SwalCustom.fire({
            title: 'Emin misiniz?',
            text: `"${key}" anahtarını ve tüm çevirilerini silmek istediğinize emin misiniz?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_translation',
                        key: key
                    })
                });

                // Yanıt durumu kontrolü
                if (!response.ok) {
                    throw new Error(`Sunucu hatası: ${response.status}`);
                }

                // Yanıt içeriği kontrolü
                const text = await response.text();
                if (!text) {
                    throw new Error('API boş yanıt döndürdü');
                }

                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    console.error('JSON ayrıştırma hatası:', parseError, 'Yanıt:', text);
                    throw new Error('API geçersiz bir yanıt döndürdü');
                }
                
                if (data.success) {
                    showSuccessToast('Çeviri başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.error || 'Silme işlemi başarısız');
                }
            } catch (error) {
                console.error('Çeviri silme hatası:', error);
                showErrorToast(error.message || 'Çeviri silinirken bir hata oluştu.');
            }
        }
    }

    // Bir çeviri grubu silme
    async function deleteTranslationSection(key) {
        const result = await SwalCustom.fire({
            title: 'Emin misiniz?',
            text: `"${key}" grubunu ve tüm içeriğini silmek istediğinize emin misiniz?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_translation_section',
                        key: key
                    })
                });

                // Yanıt durumu kontrolü
                if (!response.ok) {
                    throw new Error(`Sunucu hatası: ${response.status}`);
                }

                // Yanıt içeriği kontrolü
                const text = await response.text();
                if (!text) {
                    throw new Error('API boş yanıt döndürdü');
                }

                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    console.error('JSON ayrıştırma hatası:', parseError, 'Yanıt:', text);
                    throw new Error('API geçersiz bir yanıt döndürdü');
                }
                
                if (data.success) {
                    showSuccessToast('Çeviri grubu başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.error || 'Silme işlemi başarısız');
                }
            } catch (error) {
                console.error('Çeviri grubu silme hatası:', error);
                showErrorToast(error.message || 'Çeviri grubu silinirken bir hata oluştu.');
            }
        }
    }

    // Dizi içindeki bir öğeyi silme
    async function deleteTranslationItem(key, index) {
        const result = await SwalCustom.fire({
            title: 'Emin misiniz?',
            text: `Bu çeviri öğesini silmek istediğinize emin misiniz?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_translation_item',
                        key: key,
                        index: index
                    })
                });

                // Yanıt durumu kontrolü
                if (!response.ok) {
                    throw new Error(`Sunucu hatası: ${response.status}`);
                }

                // Yanıt içeriği kontrolü
                const text = await response.text();
                if (!text) {
                    throw new Error('API boş yanıt döndürdü');
                }

                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    console.error('JSON ayrıştırma hatası:', parseError, 'Yanıt:', text);
                    throw new Error('API geçersiz bir yanıt döndürdü');
                }
                
                if (data.success) {
                    showSuccessToast('Çeviri öğesi başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.error || 'Silme işlemi başarısız');
                }
            } catch (error) {
                console.error('Çeviri öğesi silme hatası:', error);
                showErrorToast(error.message || 'Çeviri öğesi silinirken bir hata oluştu.');
            }
        }
    }

    // Tüm çevirileri kaydet
    async function saveAllTranslations() {
        const form = document.getElementById('languageForm');
        const formData = new FormData(form);
        
        // Çevirileri topla
        const translations = {};
        
        // Mevcut dilleri tanımla
        <?php foreach($translationLanguages as $lang): ?>
        translations['<?php echo $lang; ?>'] = {};
        <?php endforeach; ?>
        
        // Form elemanlarını döngüye al ve değerleri çıkar
        for (const [name, value] of formData.entries()) {
            // Basit çeviriler için
            if (name.startsWith('trans_')) {
                const parts = name.split('_');
                const lang = parts[1];
                const key = parts.slice(2).join('_');
                
                if (!translations[lang]) {
                    translations[lang] = {};
                }
                
                translations[lang][key] = value;
            }
            // Dizi çevirileri için
            else if (name.startsWith('trans_array_')) {
                const parts = name.split('_');
                const lang = parts[2];
                const key = parts[3];
                const index = parseInt(parts[4]);
                const itemKey = parts.slice(5).join('_');
                
                if (!translations[lang]) {
                    translations[lang] = {};
                }
                
                if (!translations[lang][key]) {
                    translations[lang][key] = [];
                }
                
                if (!translations[lang][key][index]) {
                    translations[lang][key][index] = {};
                }
                
                translations[lang][key][index][itemKey] = value;
            }
        }

        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'save_all_translations',
                    translations: translations
                })
            });

            // Yanıt durumu kontrolü
            if (!response.ok) {
                throw new Error(`Sunucu hatası: ${response.status}`);
            }

            // Yanıt içeriği kontrolü
            const text = await response.text();
            if (!text) {
                throw new Error('API boş yanıt döndürdü');
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON ayrıştırma hatası:', parseError, 'Yanıt:', text);
                throw new Error('API geçersiz bir yanıt döndürdü');
            }
            
            if (data.success) {
                showSuccessToast('Tüm çeviriler başarıyla kaydedildi');
            } else {
                throw new Error(data.error || 'Kaydetme işlemi başarısız');
            }
        } catch (error) {
            console.error('Çevirileri kaydetme hatası:', error);
            showErrorToast(error.message || 'Çeviriler kaydedilirken bir hata oluştu.');
        }
    }

    // ESC tuşu ile modalı kapatma
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddLanguageModal();
            closeAddLanguageItemModal();
        }
    });
</script>
<?php require_once '../includes/footer.php'; ?>