<?php
require_once '../includes/header.php';
?>

<style>
/* Custom Scrollbar Styles */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* Firefox için scrollbar stillemesi */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.1) rgba(0, 0, 0, 0.2);
}
</style>

<!-- Uygulamalar Yönetim Sayfası -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-white">Uygulamalar Yönetimi</h2>
        <button type="button" onclick="openAddAppModal()" class="px-4 py-2 bg-blue-500/10 hover:bg-blue-600/20 text-blue-400 rounded-lg transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Yeni Uygulama Ekle</span>
        </button>
    </div>
    
    <!-- Uygulamalar Listesi -->
    <div class="space-y-4">
        <?php if (isset($manifest['apps']) && count($manifest['apps']) > 0): ?>
            <?php foreach ($manifest['apps'] as $index => $app): ?>
                <div class="bg-black/30 hover:bg-black/40 border border-white/5 rounded-xl p-4 hover-card transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-lg bg-black/40 flex items-center justify-center overflow-hidden">
                            <img src="<?php echo htmlspecialchars($app['icon']); ?>" alt="<?php echo htmlspecialchars($app['name']); ?> icon" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h3 class="font-medium text-white/90"><?php echo htmlspecialchars($app['name']); ?></h3>
                            <p class="text-sm text-white/60 mb-1">Versiyon: <?php echo htmlspecialchars($app['version']); ?></p>
                            <p class="text-xs text-white/40 truncate">
                                <span title="<?php echo htmlspecialchars($app['mainPath']); ?>">
                                    <i class="fas fa-folder-open mr-1"></i> <?php echo htmlspecialchars($app['mainPath']); ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="app-files.php?id=<?php echo (int)$app['id']; ?>" class="px-3 py-2 rounded-lg bg-green-500/10 text-green-400 hover:bg-green-500/20 transition-colors">
                                <i class="fas fa-file-alt"></i>
                                <span class="ml-1">Dosyalar (<?php echo count($app['files']); ?>)</span>
                            </a>
                            <button type="button" onclick="editApp(<?php echo htmlspecialchars(json_encode($app)); ?>)" class="px-3 py-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" onclick="deleteApp(<?php echo (int)$app['id']; ?>, '<?php echo htmlspecialchars($app['name']); ?>')" class="px-3 py-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-black/20 border border-white/5 rounded-xl p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-700/50 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cube text-2xl text-white/30"></i>
                </div>
                <h3 class="text-white/70 mb-2">Henüz uygulama eklenmemiş</h3>
                <p class="text-white/50 text-sm mb-4">Yukarıdaki "Yeni Uygulama Ekle" butonunu kullanarak uygulama ekleyebilirsiniz.</p>
                <button type="button" onclick="openAddAppModal()" class="px-4 py-2 bg-blue-500/30 hover:bg-blue-600/50 text-white rounded-lg transition-all inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Uygulama Ekle</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Uygulama Ekleme Modal -->
<div id="addAppModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10 max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Uygulama Ekle</h3>
                <button onclick="closeAddAppModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto p-6 custom-scrollbar">
                <form id="addAppForm" onsubmit="saveApp(event)" class="space-y-4" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-white/70 mb-2">Uygulama Adı</label>
                            <input type="text" id="app_name" name="name" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-sm text-white/70 mb-2">İkon URL</label>
                            <input type="url" id="app_icon" name="icon" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                placeholder="https://example.com/icon.png">
                        </div>
                        <div>
                            <label class="block text-sm text-white/70 mb-2">Ana Dosya Yolu</label>
                            <input type="text" id="app_main_path" name="mainPath" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                placeholder="apps/UygulamaAdı/UygulamaAdı.exe">
                            <p class="text-xs text-white/50 mt-1">
                                Uygulama klasörü yolu (örn: "apps/UygulamaAdı/") veya ana exe dosyası yolu (örn: "apps/UygulamaAdı/UygulamaAdı.exe").
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm text-white/70 mb-2">Versiyon</label>
                            <input type="text" id="app_version" name="version" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                placeholder="1.0.0">
                        </div>
                    </div>
                    
                    <!-- EXE Dosyası Seçme Bölümü -->
                    <div class="mt-4">
                        <label class="block text-sm text-white/70 mb-2">EXE Dosyası (İsteğe Bağlı)</label>
                        <div class="border-2 border-dashed border-white/10 rounded-lg p-4 text-center">
                            <input type="file" id="app_exe_file" name="app_exe_file" accept=".exe"
                                   class="hidden">
                            <label for="app_exe_file" 
                                   class="cursor-pointer flex flex-col items-center justify-center">
                                <i class="fas fa-upload text-xl text-white/50 mb-2"></i>
                                <span class="text-sm text-white/70">Exe dosyasını yüklemek için tıklayın veya sürükleyin</span>
                                <span id="selected_exe_name" class="text-xs text-blue-400 mt-2 hidden"></span>
                            </label>
                        </div>
                        <p class="text-xs text-white/50 mt-1">
                            EXE dosyası seçilirse, otomatik olarak veritabanına kaydedilecek ve manifest.json ile ilişkilendirilecektir.
                        </p>
                    </div>
                    
                    <!-- Alt Dosya Yükleme Bölümü -->
                    <div class="mt-6 border border-white/10 rounded-lg p-4 bg-black/20">
                        <h4 class="text-white font-medium mb-3">Alt Dosyalar (İsteğe Bağlı)</h4>
                        
                        <div id="subfiles_container" class="max-h-[300px] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                            <!-- Buraya alt dosya girişleri eklenecek -->
                        </div>
                        
                        <button type="button" id="add_subfile_btn" 
                                class="mt-3 px-3 py-2 bg-green-500/10 text-green-400 hover:bg-green-500/20 rounded-lg transition-all flex items-center gap-2 text-sm">
                            <i class="fas fa-plus"></i>
                            <span>Dosya Ekle</span>
                        </button>
                        
                        <p class="text-xs text-white/50 mt-2">
                            Ana uygulamaya bağlı ek dosyalar ekleyebilirsiniz. Klasör yolu "apps/test" veya "apps/test1/test2" gibi belirtilebilir.
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeAddAppModal()"
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
</div>

<!-- Uygulama Düzenleme Modal -->
<div id="editAppModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10 max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Uygulamayı Düzenle</h3>
                <button onclick="closeEditAppModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto p-6 custom-scrollbar">
                <form id="editAppForm" onsubmit="updateApp(event)" class="space-y-4" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-white/70 mb-2">Uygulama Adı</label>
                            <input type="text" id="edit_app_name" name="name" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-sm text-white/70 mb-2">İkon URL</label>
                            <input type="url" id="edit_app_icon" name="icon" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                        </div>
                        <div>
                            <label class="block text-sm text-white/70 mb-2">Ana Dosya Yolu</label>
                            <input type="text" id="edit_app_main_path" name="mainPath" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                            <p class="text-xs text-white/50 mt-1">
                                Uygulama klasörü yolu (örn: "apps/UygulamaAdı/") veya ana exe dosyası yolu (örn: "apps/UygulamaAdı/UygulamaAdı.exe").
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm text-white/70 mb-2">Versiyon</label>
                            <input type="text" id="edit_app_version" name="version" required 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                        </div>
                    </div>
                    
                    <!-- EXE Dosyası Seçme Bölümü -->
                    <div class="mt-4">
                        <label class="block text-sm text-white/70 mb-2">EXE Dosyası Güncelle (İsteğe Bağlı)</label>
                        <div class="border-2 border-dashed border-white/10 rounded-lg p-4 text-center">
                            <input type="file" id="edit_app_exe_file" name="app_exe_file" accept=".exe"
                                   class="hidden">
                            <label for="edit_app_exe_file" 
                                   class="cursor-pointer flex flex-col items-center justify-center">
                                <i class="fas fa-upload text-xl text-white/50 mb-2"></i>
                                <span class="text-sm text-white/70">Exe dosyasını güncellemek için tıklayın veya sürükleyin</span>
                                <span id="edit_selected_exe_name" class="text-xs text-blue-400 mt-2 hidden"></span>
                            </label>
                        </div>
                        <p class="text-xs text-white/50 mt-1">
                            EXE dosyası seçilirse, otomatik olarak veritabanına kaydedilecek ve manifest.json ile ilişkilendirilecektir.
                        </p>
                    </div>
                    
                    <!-- Alt Dosya Yükleme Bölümü -->
                    <div class="mt-6 border border-white/10 rounded-lg p-4 bg-black/20">
                        <h4 class="text-white font-medium mb-3">Alt Dosyalar (İsteğe Bağlı)</h4>
                        
                        <div id="edit_subfiles_container" class="max-h-[300px] overflow-y-auto pr-2 space-y-3 custom-scrollbar">
                            <!-- Buraya alt dosya girişleri eklenecek -->
                        </div>
                        
                        <button type="button" id="edit_add_subfile_btn" 
                                class="mt-3 px-3 py-2 bg-green-500/10 text-green-400 hover:bg-green-500/20 rounded-lg transition-all flex items-center gap-2 text-sm">
                            <i class="fas fa-plus"></i>
                            <span>Dosya Ekle</span>
                        </button>
                        
                        <p class="text-xs text-white/50 mt-2">
                            Ana uygulamaya bağlı ek dosyalar ekleyebilirsiniz. Klasör yolu "apps/test" veya "apps/test1/test2" gibi belirtilebilir.
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <input type="hidden" name="app_id" id="edit_app_id">
                        <button type="button" onclick="closeEditAppModal()"
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

// Modal Kontrolleri
function openAddAppModal() {
    const modal = document.getElementById('addAppModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('addAppForm').reset();
}

function closeAddAppModal() {
    const modal = document.getElementById('addAppModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function editApp(app) {
    document.getElementById('edit_app_id').value = app.id;
    document.getElementById('edit_app_name').value = app.name;
    document.getElementById('edit_app_icon').value = app.icon;
    document.getElementById('edit_app_main_path').value = app.mainPath;
    document.getElementById('edit_app_version').value = app.version;
    
    const modal = document.getElementById('editAppModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEditAppModal() {
    const modal = document.getElementById('editAppModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddAppModal();
        closeEditAppModal();
    }
});

// Seçilen dosya adını gösterme
document.getElementById('app_exe_file').addEventListener('change', function() {
    const fileNameElement = document.getElementById('selected_exe_name');
    if (this.files.length > 0) {
        fileNameElement.textContent = this.files[0].name;
        fileNameElement.classList.remove('hidden');
    } else {
        fileNameElement.classList.add('hidden');
    }
});

// Seçilen dosya adını gösterme - Edit modal
document.getElementById('edit_app_exe_file').addEventListener('change', function() {
    const fileNameElement = document.getElementById('edit_selected_exe_name');
    if (this.files.length > 0) {
        fileNameElement.textContent = this.files[0].name;
        fileNameElement.classList.remove('hidden');
    } else {
        fileNameElement.classList.add('hidden');
    }
});

// Uygulama Ekleme
function saveApp(event) {
    event.preventDefault();
    
    const name = document.getElementById('app_name').value;
    const icon = document.getElementById('app_icon').value;
    const mainPath = document.getElementById('app_main_path').value;
    const version = document.getElementById('app_version').value;
    const exeFile = document.getElementById('app_exe_file').files[0];
    
    // Alt dosyaları topla
    const subfiles = [];
    for (let i = 0; i < subFileCount; i++) {
        const pathInput = document.querySelector(`[name="subfile_path_${i}"]`);
        const fileInput = document.querySelector(`[name="subfile_${i}"]`);
        
        if (pathInput && fileInput && fileInput.files.length > 0) {
            subfiles.push({
                path: pathInput.value,
                file: fileInput.files[0]
            });
        }
    }
    
    // Manifest.json'a uygulama ekle
    fetch('../api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add_app',
            name: name,
            icon: icon,
            mainPath: mainPath,
            version: version
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const appId = data.id; // API'den dönen uygulamanın ID'si
            
            // Eğer exe dosyası seçildiyse yükleyelim
            let uploadPromises = [];
            
            if (exeFile) {
                uploadPromises.push(uploadExeFile(exeFile, appId));
            }
            
            // Alt dosyaları yükle
            if (subfiles.length > 0) {
                subfiles.forEach(subfile => {
                    uploadPromises.push(uploadSubFile(subfile.file, appId, subfile.path));
                });
            }
            
            // Tüm yüklemeleri bekleyelim
            if (uploadPromises.length > 0) {
                Promise.all(uploadPromises)
                    .then(() => {
                        showSuccessToast('Uygulama ve tüm dosyalar başarıyla eklendi');
                        closeAddAppModal();
                        setTimeout(() => location.reload(), 1000);
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        showSuccessToast('Uygulama eklendi ancak bazı dosyalar yüklenemedi');
                        closeAddAppModal();
                        setTimeout(() => location.reload(), 1000);
                    });
            } else {
                showSuccessToast('Uygulama başarıyla eklendi');
                closeAddAppModal();
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            SwalCustom.fire({
                title: 'Hata!',
                text: data.error || 'Bir hata oluştu.',
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: 'Bir hata oluştu.',
            icon: 'error'
        });
    });
}

// Alt Dosya Yükleme Fonksiyonu
function uploadSubFile(file, appId, subPath) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('action', 'upload_app_subfile');
        formData.append('app_id', appId);
        formData.append('sub_path', subPath || '');
        formData.append('app_subfile', file);
        
        fetch('../api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data);
            } else {
                reject(data.error || 'Alt dosya yükleme hatası');
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

// EXE Dosyasını Yükleme
function uploadExeFile(file, appId) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('action', 'upload_app_exe');
        formData.append('app_id', appId);
        formData.append('app_exe_file', file);
        
        fetch('../api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data);
            } else {
                reject(data.error || 'EXE dosyası yükleme hatası');
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

// Uygulama Düzenleme
function updateApp(event) {
    event.preventDefault();
    
    const appId = document.getElementById('edit_app_id').value;
    const name = document.getElementById('edit_app_name').value;
    const icon = document.getElementById('edit_app_icon').value;
    const mainPath = document.getElementById('edit_app_main_path').value;
    const version = document.getElementById('edit_app_version').value;
    const exeFile = document.getElementById('edit_app_exe_file').files[0];
    
    // Alt dosyaları topla
    const subfiles = [];
    for (let i = 0; i < editSubFileCount; i++) {
        const pathInput = document.querySelector(`[name="edit_subfile_path_${i}"]`);
        const fileInput = document.querySelector(`[name="edit_subfile_${i}"]`);
        
        if (pathInput && fileInput && fileInput.files.length > 0) {
            subfiles.push({
                path: pathInput.value,
                file: fileInput.files[0]
            });
        }
    }
    
    // Manifest.json'da uygulamayı güncelle
    fetch('../api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'edit_app',
            app_id: appId,
            name: name,
            icon: icon,
            mainPath: mainPath,
            version: version
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Dosya yüklemeleri için promiseler
            let uploadPromises = [];
            
            // Eğer exe dosyası seçildiyse yükleyelim
            if (exeFile) {
                uploadPromises.push(uploadExeFile(exeFile, appId));
            }
            
            // Alt dosyaları yükle
            if (subfiles.length > 0) {
                subfiles.forEach(subfile => {
                    uploadPromises.push(uploadSubFile(subfile.file, appId, subfile.path));
                });
            }
            
            // Tüm yüklemeleri bekleyelim
            if (uploadPromises.length > 0) {
                Promise.all(uploadPromises)
                    .then(() => {
                        showSuccessToast('Uygulama ve tüm dosyalar başarıyla güncellendi');
                        closeEditAppModal();
                        setTimeout(() => location.reload(), 1000);
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        showSuccessToast('Uygulama güncellendi ancak bazı dosyalar yüklenemedi');
                        closeEditAppModal();
                        setTimeout(() => location.reload(), 1000);
                    });
            } else {
                showSuccessToast('Uygulama başarıyla güncellendi');
                closeEditAppModal();
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            SwalCustom.fire({
                title: 'Hata!',
                text: data.error || 'Bir hata oluştu.',
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: 'Bir hata oluştu.',
            icon: 'error'
        });
    });
}

// Uygulama Silme
function deleteApp(id, name) {
    SwalCustom.fire({
        title: 'Emin misiniz?',
        text: `"${name}" uygulamasını silmek istediğinize emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_app',
                    id: id
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessToast('Uygulama başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    SwalCustom.fire({
                        title: 'Hata!',
                        text: data.error || 'Bir hata oluştu.',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                SwalCustom.fire({
                    title: 'Hata!',
                    text: 'Bir hata oluştu.',
                    icon: 'error'
                });
            });
        }
    });
}

// Alt dosya eklemek için gerekli fonksiyonlar
let subFileCount = 0;
let editSubFileCount = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Alt dosya ekleme butonları
    document.getElementById('add_subfile_btn').addEventListener('click', addSubFile);
    document.getElementById('edit_add_subfile_btn').addEventListener('click', addEditSubFile);
});

function addSubFile() {
    const container = document.getElementById('subfiles_container');
    const subfileId = subFileCount++;
    
    const subfileHtml = `
        <div id="subfile_${subfileId}" class="mb-4 p-3 bg-black/30 rounded-lg border border-white/5">
            <div class="flex items-center justify-between mb-2">
                <h5 class="text-white/80 text-sm font-medium">Alt Dosya ${subfileId + 1}</h5>
                <button type="button" onclick="removeSubFile(${subfileId})" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-xs text-white/70 mb-1">Klasör Yolu</label>
                    <input type="text" name="subfile_path_${subfileId}" placeholder="apps/test veya apps/test1/test2" 
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-sm 
                                  text-white placeholder-white/30 focus:border-blue-500/50">
                </div>
                <div>
                    <label class="block text-xs text-white/70 mb-1">Dosya</label>
                    <input type="file" name="subfile_${subfileId}" 
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-sm 
                                  text-white file:mr-4 file:py-1 file:px-3 file:border-0 
                                  file:text-xs file:bg-gray-700/50 file:text-white/70 
                                  hover:file:bg-gray-700">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', subfileHtml);
}

function removeSubFile(id) {
    const element = document.getElementById(`subfile_${id}`);
    if (element) {
        element.remove();
    }
}

function addEditSubFile() {
    const container = document.getElementById('edit_subfiles_container');
    const subfileId = editSubFileCount++;
    
    const subfileHtml = `
        <div id="edit_subfile_${subfileId}" class="mb-4 p-3 bg-black/30 rounded-lg border border-white/5">
            <div class="flex items-center justify-between mb-2">
                <h5 class="text-white/80 text-sm font-medium">Alt Dosya ${subfileId + 1}</h5>
                <button type="button" onclick="removeEditSubFile(${subfileId})" class="text-red-400 hover:text-red-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-xs text-white/70 mb-1">Klasör Yolu</label>
                    <input type="text" name="edit_subfile_path_${subfileId}" placeholder="apps/test veya apps/test1/test2" 
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-sm 
                                  text-white placeholder-white/30 focus:border-blue-500/50">
                </div>
                <div>
                    <label class="block text-xs text-white/70 mb-1">Dosya</label>
                    <input type="file" name="edit_subfile_${subfileId}" 
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-3 py-2 text-sm 
                                  text-white file:mr-4 file:py-1 file:px-3 file:border-0 
                                  file:text-xs file:bg-gray-700/50 file:text-white/70 
                                  hover:file:bg-gray-700">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', subfileHtml);
}

function removeEditSubFile(id) {
    const element = document.getElementById(`edit_subfile_${id}`);
    if (element) {
        element.remove();
    }
}
</script>

<?php
require_once '../includes/footer.php';
?> 