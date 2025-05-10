<?php
require_once '../includes/header.php';

// Add custom scrollbar styles
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

<?php
// Uygulama ID'sini kontrol et
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="bg-red-500/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6">
        <p><i class="fas fa-exclamation-circle mr-2"></i> Geçersiz uygulama ID\'si.</p>
    </div>';
    echo '<a href="apps.php" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all inline-flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        <span>Uygulamalar Sayfasına Dön</span>
    </a>';
    exit;
}

$appId = (int)$_GET['id'];
$appFound = false;
$currentApp = null;

// Uygulama bilgilerini al
if (isset($manifest['apps'])) {
    foreach ($manifest['apps'] as $app) {
        if ((int)$app['id'] === $appId) {
            $appFound = true;
            $currentApp = $app;
            break;
        }
    }
}

if (!$appFound) {
    echo '<div class="bg-red-500/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg mb-6">
        <p><i class="fas fa-exclamation-circle mr-2"></i> Uygulama bulunamadı.</p>
    </div>';
    echo '<a href="apps.php" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all inline-flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        <span>Uygulamalar Sayfasına Dön</span>
    </a>';
    exit;
}

// Uygulama dosyalarını manifest.json'dan al
$appFiles = [];
if (isset($currentApp['files'])) {
    $appFiles = $currentApp['files'];
}

// Mevcut dosya yollarını al (dosya yolu seçimi için)
$filePaths = [];
if (isset($currentApp['files']) && count($currentApp['files']) > 0) {
    foreach ($currentApp['files'] as $file) {
        $filePaths[] = $file['path'];
    }
}

// Uygulamanın ana dizini
$appMainPath = isset($currentApp['mainPath']) ? $currentApp['mainPath'] : '';

// Eğer mainPath bir dosya yolu içeriyorsa (içinde nokta varsa), sadece klasör kısmını al
if (!empty($appMainPath) && strpos(basename($appMainPath), '.') !== false) {
    $appMainPath = dirname($appMainPath) . '/';
} else if (!empty($appMainPath) && substr($appMainPath, -1) !== '/') {
    // Eğer zaten klasör yolu ise ve / ile bitmiyorsa ekleyelim
    $appMainPath .= '/';
}

// Dosya yükleme işlemi
$uploadMessage = '';
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_app_file') {
    if (isset($_FILES['app_file']) && $_FILES['app_file']['error'] === 0) {
        $fileName = $_FILES['app_file']['name'];
        $fileTmpName = $_FILES['app_file']['tmp_name'];
        $fileSize = $_FILES['app_file']['size'];
        
        // Ana dizin yolunu al
        $mainPath = isset($currentApp['mainPath']) ? $currentApp['mainPath'] : '';
        
        // Ana dizin yolunu düzgün formata getir
        if (!empty($mainPath) && strpos(basename($mainPath), '.') !== false) {
            $mainPath = dirname($mainPath) . '/';
        } else if (!empty($mainPath) && substr($mainPath, -1) !== '/') {
            $mainPath .= '/';
        }
        
        // Ek yolu kontrol et (boş olabilir)
        $subPath = isset($_POST['sub_path']) ? trim($_POST['sub_path']) : '';
        
        // Tam dosya yolu oluştur
        if (!empty($subPath)) {
            // Eğer subPath "/" ile başlıyorsa, baştaki "/" işaretini kaldır
            if (substr($subPath, 0, 1) === '/') {
                $subPath = substr($subPath, 1);
            }
            
            // Eğer subPath'te dosya adı yoksa, mevcut dosya adını ekle
            if (substr($subPath, -1) === '/' || !str_contains($subPath, '.')) {
                $appPath = rtrim($mainPath, '/') . '/' . rtrim($subPath, '/') . '/' . $fileName;
            } else {
                $appPath = rtrim($mainPath, '/') . '/' . $subPath;
            }
        } else {
            // Ek yol belirtilmemişse, dosyayı doğrudan ana dizine kendi adıyla yükle
            $appPath = rtrim($mainPath, '/') . '/' . $fileName;
        }
        
        // Dosyanın fiziksel olarak kaydedileceği tam yolu belirle
        $physicalPath = __DIR__ . '/../' . $appPath;
        
        if (empty($mainPath)) {
            $uploadError = "Uygulama ana dizini belirtilmemiş.";
        } else {
            // Dosyanın fiziksel olarak kaydedileceği klasör
            $uploadDir = dirname($physicalPath);
            
            // Hata ayıklama bilgisini yazdır
            error_log("Yükleme yolu (Manifest): " . $appPath);
            error_log("Yükleme yolu (Fiziksel): " . $physicalPath);
            error_log("Yükleme klasörü: " . $uploadDir);
            
            // Klasörü oluştur (yoksa)
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    $uploadError = "Klasör oluşturulamadı: " . $uploadDir;
                    error_log("Klasör oluşturma hatası: " . $uploadDir);
                }
            }
            
            // Hash hesapla
            $fileHash = hash_file('sha256', $fileTmpName);
            
            // Dosyayı taşı
            if (!$uploadError && move_uploaded_file($fileTmpName, $physicalPath)) {
                // Dosya izinlerini ayarla
                chmod($physicalPath, 0644);
                
                // Manifest.json'a dosyayı ekleyelim
                $fileUrl = Site_URL . $appPath;
                
                // Manifest'i tekrar alalım (yüklemeden sonra değişmiş olabilir)
                $manifest = getManifest();
                
                // İlgili uygulamayı bulup dosyayı ekleyelim
                foreach ($manifest['apps'] as &$app) {
                    if ((int)$app['id'] === $appId) {
                        $app['files'][] = [
                            'path' => $appPath,
                            'url' => $fileUrl,
                            'size' => $fileSize,
                            'hash' => $fileHash
                        ];
                        break;
                    }
                }
                
                // Manifest'i kaydedelim
                if (saveManifest($manifest)) {
                    $uploadMessage = "Dosya başarıyla yüklendi ve manifest.json'a eklendi.";
                    
                    // Sayfayı yeniden yüklemeden önce mevcut dosya listesini güncelleyelim
                    if (!isset($currentApp['files'])) {
                        $currentApp['files'] = [];
                    }
                    
                    $currentApp['files'][] = [
                        'path' => $appPath,
                        'url' => $fileUrl,
                        'size' => $fileSize,
                        'hash' => $fileHash
                    ];
                    
                    $appFiles = $currentApp['files'];
                } else {
                    $uploadError = "Dosya yüklendi fakat manifest.json güncellenemedi.";
                }
            } else {
                if (!$uploadError) {
                    $uploadError = "Dosya yükleme hatası: " . error_get_last()['message'];
                }
            }
        }
    } else {
        $uploadError = "Lütfen bir dosya seçin";
    }
}

// Dosya silme işlemi
if (isset($_GET['delete_file']) && !empty($_GET['delete_file'])) {
    $filePathToDelete = urldecode($_GET['delete_file']);
    $fileFound = false;
    $fileIndex = -1;
    
    // Manifest.json'dan dosyayı bulalım
    $manifest = getManifest();
    
    // İlgili uygulamayı bulup dosyayı silelim
    foreach ($manifest['apps'] as &$app) {
        if ((int)$app['id'] === $appId) {
            foreach ($app['files'] as $index => $file) {
                if ($file['path'] === $filePathToDelete) {
                    // Fiziksel dosya yolu
                    $physicalPath = __DIR__ . '/../' . $file['path'];
                    
                    // Dosyayı diskten sil
                    if (file_exists($physicalPath)) {
                        if (unlink($physicalPath)) {
                            // Dosyayı files dizisinden çıkar
                            array_splice($app['files'], $index, 1);
                            $fileFound = true;
                            $fileIndex = $index;
                            break;
                        } else {
                            $uploadError = "Dosya manifest.json'dan silindi ancak fiziksel olarak silinemedi: " . $physicalPath;
                            error_log("Dosya silme hatası: " . $physicalPath);
                        }
                    } else {
                        // Fiziksel dosya yoksa sadece listeden çıkaralım
                        array_splice($app['files'], $index, 1);
                        $fileFound = true;
                        $fileIndex = $index;
                        $uploadMessage = "Dosya manifest.json'dan silindi. (Fiziksel dosya bulunamadı)";
                        error_log("Silinecek dosya bulunamadı: " . $physicalPath);
                        break;
                    }
                }
            }
            break;
        }
    }
    
    // Manifest güncellemesini kaydet
    if ($fileFound) {
        if (saveManifest($manifest)) {
            $uploadMessage = "Dosya başarıyla silindi ve manifest.json güncellendi.";
            
            // Mevcut sayfadaki dosya listesini de güncelleyelim
            if (isset($currentApp['files']) && $fileIndex >= 0) {
                array_splice($currentApp['files'], $fileIndex, 1);
                $appFiles = $currentApp['files'];
            }
        } else {
            $uploadError = "Dosya silinememedi: manifest.json güncellenemedi.";
        }
    } else {
        $uploadError = "Dosya bulunamadı veya bu uygulamaya ait değil.";
    }
}
?>

<!-- Uygulama Dosyaları Yönetim Sayfası -->
<div class="glass-effect rounded-2xl p-4 transition-all duration-300">
    <div class="flex items-center justify-between ">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-black/40 flex items-center justify-center overflow-hidden">
                <img src="<?php echo htmlspecialchars($currentApp['icon']); ?>" alt="<?php echo htmlspecialchars($currentApp['name']); ?> icon" class="w-full h-full object-cover">
            </div>
            <div>
                <h2 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($currentApp['name']); ?></h2>
                <p class="text-sm text-white/60">Versiyon: <?php echo htmlspecialchars($currentApp['version']); ?></p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="apps.php" class="px-4 py-2 bg-gray-500/10 hover:bg-gray-500/20 text-white/80 hover:text-white rounded-lg transition-all flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Geri</span>
            </a>
        </div>
    </div>
</div>

<!-- Fiziksel Dosya Yükleme Bölümü -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300 mt-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                <?php echo htmlspecialchars($currentApp['name']); ?> - Dosya Yükleme
            </h2>
            <p class="text-sm text-white/50 mt-1">Uygulama için fiziksel dosya yükleyin</p>
        </div>
        <button 
            onclick="showUploadFileModal()" 
            class="flex items-center bg-green-800/20 hover:bg-green-800/30 px-3 py-2 rounded-lg border border-green-800/30 text-green-300 transition-all">
            <i class="fas fa-cloud-upload-alt mr-2"></i>
            Dosya Yükle
        </button>
    </div>
    
    <?php if (!empty($uploadMessage)): ?>
    <div class="mb-6 p-4 bg-green-500/10 text-green-400 rounded-lg">
        <p><i class="fas fa-check-circle mr-2"></i> <?php echo $uploadMessage; ?></p>
    </div>
    <?php elseif (!empty($uploadError)): ?>
    <div class="mb-6 p-4 bg-red-500/10 text-red-400 rounded-lg">
        <p><i class="fas fa-exclamation-circle mr-2"></i> <?php echo $uploadError; ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Yüklenen Dosyalar Listesi -->
    <div class="space-y-4">
        <?php if (isset($appFiles) && count($appFiles) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-black/20 text-white/70 text-sm">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Dosya Adı</th>
                            <th class="px-4 py-3">Dosya Yolu</th>
                            <th class="px-4 py-3">Boyut</th>
                            <th class="px-4 py-3">Hash</th>
                            <th class="px-4 py-3 rounded-r-lg">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="text-white/80">
                        <?php foreach ($appFiles as $file): ?>
                            <tr class="border-b border-white/5 hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-file-alt text-green-400"></i>
                                        <span class="truncate" title="<?php echo htmlspecialchars(basename($file['path'])); ?>"><?php echo htmlspecialchars(basename($file['path'])); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="truncate block max-w-[200px]" title="<?php echo htmlspecialchars($file['path']); ?>"><?php echo htmlspecialchars($file['path']); ?></span>
                                </td>
                                <td class="px-4 py-3"><?php echo formatBytes($file['size']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="truncate block max-w-[100px]" title="<?php echo htmlspecialchars($file['hash']); ?>"><?php echo substr(htmlspecialchars($file['hash']), 0, 10); ?>...</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button onclick="showFileDetailsModal(<?php echo htmlspecialchars(json_encode([
                                            'app_name' => basename($file['path']),
                                            'app_path' => $file['path'],
                                            'app_size' => formatBytes($file['size']),
                                            'app_hash' => $file['hash'],
                                            'app_date' => date('d.m.Y H:i')
                                        ])); ?>)" class="px-2 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 transition-colors">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="<?php echo '../' . $file['path']; ?>" download class="px-2 py-1 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="?id=<?php echo $appId; ?>&delete_file=<?php echo urlencode($file['path']); ?>" onclick="return confirm('Bu dosyayı silmek istediğinize emin misiniz?')" class="px-2 py-1 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-black/20 border border-white/5 rounded-xl p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-700/50 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cloud-upload-alt text-2xl text-white/30"></i>
                </div>
                <h3 class="text-white/70 mb-2">Henüz dosya yüklenmemiş</h3>
                <p class="text-white/50 text-sm mb-4">Yukarıdaki "Dosya Yükle" butonunu kullanarak uygulama dosyalarını yükleyebilirsiniz.</p>
                <button type="button" onclick="showUploadFileModal()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-all inline-flex items-center gap-2">
                    <i class="fas fa-upload"></i>
                    <span>Dosya Yükle</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Dosya Detay Modal -->
<div id="fileDetailsModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10 max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Dosya Detayları</h3>
                <button onclick="closeFileDetailsModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto p-6 custom-scrollbar">
                <div class="flex flex-col space-y-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Dosya Adı</label>
                        <div class="flex items-center gap-2">
                            <div id="file_detail_name" class="flex-grow p-3 bg-black/30 border border-white/10 rounded-lg text-white"></div>
                            <button onclick="copyToClipboard('file_detail_name')" class="px-3 py-3 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Dosya Yolu</label>
                        <div class="flex items-center gap-2">
                            <div id="file_detail_path" class="flex-grow p-3 bg-black/30 border border-white/10 rounded-lg text-white break-all"></div>
                            <button onclick="copyToClipboard('file_detail_path')" class="px-3 py-3 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Dosya Boyutu</label>
                        <div class="flex items-center gap-2">
                            <div id="file_detail_size" class="flex-grow p-3 bg-black/30 border border-white/10 rounded-lg text-white"></div>
                            <button onclick="copyToClipboard('file_detail_size')" class="px-3 py-3 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Hash (SHA-256)</label>
                        <div class="flex items-center gap-2">
                            <div id="file_detail_hash" class="flex-grow p-3 bg-black/30 border border-white/10 rounded-lg text-white break-all"></div>
                            <button onclick="copyToClipboard('file_detail_hash')" class="px-3 py-3 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Yükleme Tarihi</label>
                        <div class="flex items-center gap-2">
                            <div id="file_detail_date" class="flex-grow p-3 bg-black/30 border border-white/10 rounded-lg text-white"></div>
                            <button onclick="copyToClipboard('file_detail_date')" class="px-3 py-3 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeFileDetailsModal()" class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-all">
                        Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fiziksel Dosya Yükleme Modal -->
<div id="uploadFileModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10 max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Dosya Yükle</h3>
                <button onclick="closeUploadFileModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto p-6 custom-scrollbar">
                <form method="post" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="upload_app_file">
                    
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Ana Dizin (Otomatik)</label>
                        <div class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-3 text-white/70">
                            <?php echo htmlspecialchars($appMainPath); ?>
                        </div>
                        <p class="text-xs text-white/50 mt-1">
                            Bu ana dizin otomatik olarak uygulamanın ana yolundan alınmıştır ve değiştirilemez. 
                            Dosyalar bu klasör altına yüklenecektir.
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Ek Klasör/Dosya Yolu (İsteğe Bağlı)</label>
                        <div class="relative">
                            <input type="text" name="sub_path" id="sub_path" 
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                                placeholder="klasör/dosya.txt veya sadece dosya.txt">
                        </div>
                        <p class="text-xs text-white/50 mt-1">
                            Ana dizin altında ek klasörler oluşturmak için "klasör/alt_klasör/dosya.txt" formatını kullanın. 
                            Sadece dosya adı girerseniz (örn: "test.txt"), dosya direkt ana dizine yüklenir.
                            Boş bırakırsanız, dosya kendi adıyla ana dizine yüklenir.
                        </p>
                    </div>
                    
                    <div class="text-center p-4 border-2 border-dashed border-white/10 rounded-lg">
                        <div class="mb-4">
                            <i class="fas fa-cloud-upload-alt text-3xl text-white/50"></i>
                        </div>
                        <p class="text-white/70 mb-4">Dosya yüklemek için tıklayın veya sürükleyin</p>
                        <input type="file" name="app_file" id="app_file" required
                            class="block w-full text-white/70 bg-black/30 border border-white/10 rounded-lg cursor-pointer focus:outline-none">
                        <p class="mt-3 text-xs text-white/50">
                            Dosyalarınız "<?php echo htmlspecialchars($appMainPath); ?>" klasörü altına yüklenecektir.<br>
                            Dosya boyutu limiti: 200MB
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeUploadFileModal()"
                                class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-all">
                            İptal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-green-500/10 hover:bg-green-500/20 text-green-400 transition-all">
                            Yükle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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
            title: 'text-white text-sm',
            container: 'swal-toast-container'
        },
        target: document.body,
        didOpen: (toast) => {
            toast.style.zIndex = '9999999'; // Çok daha yüksek z-index değeri
            document.querySelector('.swal-toast-container').style.zIndex = '9999999';
        }
    });
}

// Dosya Detay Modal Kontrolleri
function showFileDetailsModal(file) {
    document.getElementById('file_detail_name').textContent = file.app_name;
    document.getElementById('file_detail_path').textContent = file.app_path;
    document.getElementById('file_detail_size').textContent = file.app_size;
    document.getElementById('file_detail_hash').textContent = file.app_hash;
    document.getElementById('file_detail_date').textContent = file.app_date;
    
    const modal = document.getElementById('fileDetailsModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeFileDetailsModal() {
    const modal = document.getElementById('fileDetailsModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Panoya kopyalama işlemi
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        // Kopyalama başarılı olduğunda bildirim göster
        showSuccessToast('Panoya kopyalandı');
    }).catch(err => {
        console.error('Kopyalama hatası:', err);
    });
}

// Bytes formatı
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Tarih formatı
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFileDetailsModal();
        closeUploadFileModal();
    }
});

// Fiziksel Dosya Yükleme Modal Kontrolleri
function showUploadFileModal() {
    const modal = document.getElementById('uploadFileModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeUploadFileModal() {
    const modal = document.getElementById('uploadFileModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    // Formu sıfırla
    document.getElementById('app_file').value = '';
    document.getElementById('sub_path').value = '';
}

// Upload mesajlarını göster
<?php if (!empty($uploadMessage)) : ?>
    Swal.fire({
        icon: 'success',
        title: 'Başarılı!',
        text: '<?php echo $uploadMessage; ?>',
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#10B981'
    }).then(() => {
        window.location.reload();
    });
<?php endif; ?>

<?php if (!empty($uploadError)) : ?>
    Swal.fire({
        icon: 'error',
        title: 'Hata!',
        text: '<?php echo $uploadError; ?>',
        confirmButtonText: 'Tamam',
        confirmButtonColor: '#EF4444'
    });
<?php endif; ?>
</script>

<?php
require_once '../includes/footer.php';
?> 