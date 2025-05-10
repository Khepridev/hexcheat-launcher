<?php require_once '../includes/header.php'; ?>

<!-- Dil ve Menü Yönetimi -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Menü Yönetimi
            </h2>
            <p class="text-sm text-white/50 mt-1">Menü öğelerini düzenleyin</p>
        </div>
        <button onclick="openAddMenuModal()" 
                class="bg-green-500/10 hover:bg-green-500/20 text-green-400 px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i> Yeni Menü Öğesi
        </button>
    </div>

    <!-- Tüm Diller için Menü Listeleri -->
    <div class="space-y-6">
        <!-- Türkçe Menü -->
        <div class="">
            <h3 class="text-lg font-medium mb-4">Türkçe Menü</h3>
            <div class="space-y-3" id="menuListTR">
                <?php foreach ($manifest['translations']['tr']['menuItems'] as $item): ?>
                <div class="bg-black/30 p-4 rounded-lg flex items-center gap-4 group" data-id="<?php echo $item['id']; ?>">
                    <i class="fas fa-grip-vertical cursor-move opacity-50 group-hover:opacity-100"></i>
                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            <span class="font-medium text-white/90"><?php echo $item['title']; ?></span>
                            <span class="text-white/50 text-sm"><?php echo $item['url']; ?></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='editMenuItem(<?php echo json_encode($item); ?>, "tr")'
                                class="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteMenuItem(<?php echo $item['id']; ?>, 'tr')"
                                class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- İngilizce Menü -->
        <div class="">
            <h3 class="text-lg font-medium mb-4">English Menu</h3>
            <div class="space-y-3" id="menuListEN">
                <?php foreach ($manifest['translations']['en']['menuItems'] as $item): ?>
                <div class="bg-black/30 p-4 rounded-lg flex items-center gap-4 group" data-id="<?php echo $item['id']; ?>">
                    <i class="fas fa-grip-vertical cursor-move opacity-50 group-hover:opacity-100"></i>
                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            <span class="font-medium text-white/90"><?php echo $item['title']; ?></span>
                            <span class="text-white/50 text-sm"><?php echo $item['url']; ?></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='editMenuItem(<?php echo json_encode($item); ?>, "en")'
                                class="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteMenuItem(<?php echo $item['id']; ?>, 'en')"
                                class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Diğer Diller için Menüler -->
        <?php foreach ($manifest['languages'] as $langCode => $langName): ?>
        <div class="">
            <h3 class="text-lg font-medium mb-4"><?php echo $langName; ?> Menu</h3>
            <div class="space-y-3" id="menuList<?php echo strtoupper($langCode); ?>">
                <?php 
                // Eğer bu dil için menuItems dizisi varsa göster
                if (isset($manifest['translations'][$langCode]['menuItems']) && is_array($manifest['translations'][$langCode]['menuItems'])):
                    foreach ($manifest['translations'][$langCode]['menuItems'] as $item): 
                ?>
                <div class="bg-black/30 p-4 rounded-lg flex items-center gap-4 group" data-id="<?php echo $item['id']; ?>">
                    <i class="fas fa-grip-vertical cursor-move opacity-50 group-hover:opacity-100"></i>
                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            <span class="font-medium text-white/90"><?php echo $item['title']; ?></span>
                            <span class="text-white/50 text-sm"><?php echo $item['url']; ?></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='editMenuItem(<?php echo json_encode($item); ?>, "<?php echo $langCode; ?>")'
                                class="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteMenuItem(<?php echo $item['id']; ?>, '<?php echo $langCode; ?>')"
                                class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- Menü Ekleme Modal -->
<div id="addMenuModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Yeni Menü Öğesi Ekle</h3>
                <button onclick="closeAddMenuModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addMenuForm" onsubmit="handleAddMenuItem(event)" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Başlık (TR)</label>
                        <input type="text" name="title_tr" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Title (EN)</label>
                        <input type="text" name="title_en" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    
                    <!-- Diğer diller için input alanları -->
                    <?php foreach ($manifest['languages'] as $langCode => $langName): ?>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Title (<?php echo strtoupper($langCode); ?>)</label>
                        <input type="text" name="title_<?php echo $langCode; ?>" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="col-span-2">
                        <label class="block text-sm text-white/70 mb-2">URL</label>
                        <input type="url" name="url" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                            placeholder="https://example.com">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddMenuModal()"
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

<!-- Menü Düzenleme Modal -->
<div id="editMenuModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Menü Öğesi Düzenle</h3>
                <button onclick="closeEditMenuModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editMenuForm" onsubmit="handleEditMenuItem(event)" class="p-6 space-y-4">                                
                <div>
                    <label class="block text-sm text-white/70 mb-2">Başlık</label>
                    <input type="text" name="title" id="edit_menu_title" required 
                        class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                text-white placeholder-white/30 focus:border-blue-500/50 
                                focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-sm text-white/70 mb-2">URL</label>
                    <input type="url" name="url" id="edit_menu_url" required 
                        class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                text-white placeholder-white/30 focus:border-blue-500/50 
                                focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <input type="hidden" name="menu_id" id="edit_menu_id">
                    <input type="hidden" name="menu_lang" id="edit_menu_lang">
                    <button type="button" onclick="closeEditMenuModal()"
                            class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-all">
                        İptal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-all">
                        Güncelle
                    </button>
                </div>
            </form>
            
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

// Menü Modal İşlevleri
function openAddMenuModal() {
    const modal = document.getElementById('addMenuModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddMenuModal() {
    const modal = document.getElementById('addMenuModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddMenuModal();
    }
});

function editMenuItem(item, lang) {
    document.getElementById('edit_menu_id').value = item.id;
    document.getElementById('edit_menu_lang').value = lang;
    document.getElementById('edit_menu_title').value = item.title;
    document.getElementById('edit_menu_url').value = item.url;

    const modal = document.getElementById('editMenuModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEditMenuModal() {
    const modal = document.getElementById('editMenuModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditMenuModal();
    }
});

// Menü Form İşlemleri
async function handleAddMenuItem(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    // Tüm diller için başlık değerlerini topla
    const menuData = {
        action: 'add_menu_item',
        title_tr: formData.get('title_tr'),
        title_en: formData.get('title_en'),
        url: formData.get('url')
    };
    
    // Diğer diller için başlık değerlerini ekle
    <?php foreach ($manifest['languages'] as $langCode => $langName): ?>
    menuData['title_<?php echo $langCode; ?>'] = formData.get('title_<?php echo $langCode; ?>');
    <?php endforeach; ?>

    try {
        const response = await fetch('../api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(menuData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessToast('Menü öğesi başarıyla eklendi');
            closeAddMenuModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.error || 'Ekleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Menü ekleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message || 'Menü öğesi eklenirken bir hata oluştu.',
            icon: 'error'
        });
    }
}

async function handleEditMenuItem(event) {
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
                action: 'edit_menu_item',
                menu_id: formData.get('menu_id'),
                menu_lang: formData.get('menu_lang'),
                title: formData.get('title'),
                url: formData.get('url')
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessToast('Menü öğesi başarıyla güncellendi');
            closeEditMenuModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.error || 'Güncelleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Menü düzenleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message || 'Menü öğesi güncellenirken bir hata oluştu.',
            icon: 'error'
        });
    }
}

// ESC tuşu ile modalları kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddMenuModal();
        closeEditMenuModal();
    }
});

// Mevcut DOMContentLoaded event listener'ına ekleyin
document.addEventListener('DOMContentLoaded', function() {
    // TR Menü sıralaması
    new Sortable(document.getElementById('menuListTR'), {
        handle: '.fa-grip-vertical',
        animation: 150,
        ghostClass: 'bg-gray-700/50',
        onEnd: async function(evt) {
            const items = [...evt.to.children].map(el => el.dataset.id);
            try {
                const response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_menu_order',
                        lang: 'tr',
                        items: items
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showSuccessToast('Menü sıralaması güncellendi');
                    // Diğer dildeki sıralamayı da aynı şekilde güncelle
                    updateAllMenuOrder(items);
                } else {
                    throw new Error(data.error || 'Sıralama güncellenemedi');
                }
            } catch (error) {
                console.error('Menü sıralama hatası:', error);
                SwalCustom.fire({
                    title: 'Hata!',
                    text: error.message || 'Menü sıralaması güncellenirken bir hata oluştu.',
                    icon: 'error'
                });
                setTimeout(() => location.reload(), 1000);
            }
        }
    });

    // EN Menü sıralaması
    new Sortable(document.getElementById('menuListEN'), {
        handle: '.fa-grip-vertical',
        animation: 150,
        ghostClass: 'bg-gray-700/50',
        onEnd: async function(evt) {
            const items = [...evt.to.children].map(el => el.dataset.id);
            try {
                const response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_menu_order',
                        lang: 'en',
                        items: items
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showSuccessToast('Menü sıralaması güncellendi');
                    // Diğer dildeki sıralamayı da aynı şekilde güncelle
                    updateAllMenuOrder(items);
                } else {
                    throw new Error(data.error || 'Sıralama güncellenemedi');
                }
            } catch (error) {
                console.error('Menü sıralama hatası:', error);
                SwalCustom.fire({
                    title: 'Hata!',
                    text: error.message || 'Menü sıralaması güncellenirken bir hata oluştu.',
                    icon: 'error'
                });
                setTimeout(() => location.reload(), 1000);
            }
        }
    });
    
    // Diğer diller için menü sıralaması
    <?php foreach ($manifest['languages'] as $langCode => $langName): ?>
    const menuList<?php echo strtoupper($langCode); ?> = document.getElementById('menuList<?php echo strtoupper($langCode); ?>');
    if (menuList<?php echo strtoupper($langCode); ?>) {
        new Sortable(menuList<?php echo strtoupper($langCode); ?>, {
            handle: '.fa-grip-vertical',
            animation: 150,
            ghostClass: 'bg-gray-700/50',
            onEnd: async function(evt) {
                const items = [...evt.to.children].map(el => el.dataset.id);
                try {
                    const response = await fetch('../api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'update_menu_order',
                            lang: '<?php echo $langCode; ?>',
                            items: items
                        })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        showSuccessToast('Menü sıralaması güncellendi');
                        // Diğer dildeki sıralamayı da aynı şekilde güncelle
                        updateAllMenuOrder(items);
                    } else {
                        throw new Error(data.error || 'Sıralama güncellenemedi');
                    }
                } catch (error) {
                    console.error('Menü sıralama hatası:', error);
                    SwalCustom.fire({
                        title: 'Hata!',
                        text: error.message || 'Menü sıralaması güncellenirken bir hata oluştu.',
                        icon: 'error'
                    });
                    setTimeout(() => location.reload(), 1000);
                }
            }
        });
    }
    <?php endforeach; ?>
    
    // Tüm dillerdeki menü sıralamasını güncelleme fonksiyonu
    async function updateAllMenuOrder(items) {
        try {
            // EN menüyü güncelle
            await updateMenuOrder('en', items);
            
            // Diğer dilleri güncelle
            <?php foreach ($manifest['languages'] as $langCode => $langName): ?>
            await updateMenuOrder('<?php echo $langCode; ?>', items);
            <?php endforeach; ?>
            
            // TR menüyü güncelle (eğer başlangıç dili değilse)
            await updateMenuOrder('tr', items);
        } catch (error) {
            console.error('Tüm menüleri güncellerken hata:', error);
        }
    }
    
    // Belirli bir dildeki menüyü güncelleme fonksiyonu
    async function updateMenuOrder(lang, items) {
        try {
            // API üzerinden menüyü güncelle
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_menu_order',
                    lang: lang,
                    items: items
                })
            });
            
            if (!response.ok) {
                throw new Error(lang + ' menü güncellenirken API hatası oluştu');
            }
            
            // UI'da görsel güncelleme
            const menuList = document.getElementById('menuList' + lang.toUpperCase());
            if (menuList) {
                items.forEach(id => {
                    const item = document.querySelector(`#menuList${lang.toUpperCase()} [data-id="${id}"]`);
                    if (item) menuList.appendChild(item);
                });
            }
        } catch (error) {
            console.error(lang + ' menü güncellenirken hata:', error);
        }
    }
});

// Menü öğesi silme fonksiyonu
async function deleteMenuItem(id, lang) {
    const result = await SwalCustom.fire({
        title: 'Emin misiniz?',
        text: 'Bu menü öğesini silmek istediğinize emin misiniz?',
        icon: 'warning',
        showCancelButton: true
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    action: 'delete_menu_item', 
                    id: id,
                    lang: lang
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Tüm dillerdeki menü öğelerini UI'dan kaldır
                document.querySelectorAll(`[data-id="${id}"]`).forEach(el => el.remove());
                
                showSuccessToast('Menü öğesi başarıyla silindi');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.error || 'Silme işlemi başarısız');
            }
        } catch (error) {
            console.error('Menü silme hatası:', error);
            SwalCustom.fire({
                title: 'Hata!',
                text: error.message || 'Menü öğesi silinirken bir hata oluştu.',
                icon: 'error'
            });
        }
    }
}
</script>

<?php
require_once '../includes/footer.php';
?> 