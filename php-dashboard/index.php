<?php
// Check if configuration file exists and database is set up
if (!file_exists('includes/config.php')) {
    // Redirect to installation page
    header("Location: install.php");
    exit;
}

// If config exists, include it and continue
require_once 'includes/header.php';
?>

<!-- Dashboard Ana Sayfa -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl font-semibold text-white">Dashboard</h2>
    </div>
    
    <!-- Dashboard cards with better spacing -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
        <!-- İlk Satır - 3 Kart -->
        <!-- Toplam Haber Sayısı -->
        <div class="bg-black/20 rounded-xl p-6 hover-card transition-all h-full">
            <div class="flex flex-col h-full">
                <p class="text-white/50 text-sm mb-2">Toplam Haber</p>
                <div class="flex items-center mt-auto">
                    <h3 class="text-3xl font-bold"><?php echo count($manifest['news']); ?></h3>
                    <div class="ml-auto w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                        <i class="fas fa-newspaper text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Menü Öğesi Sayısı -->
        <div class="bg-black/20 rounded-xl p-6 hover-card transition-all h-full">
            <div class="flex flex-col h-full">
                <p class="text-white/50 text-sm mb-2">Menü Öğeleri</p>
                <div class="flex items-center mt-auto">
                    <h3 class="text-3xl font-bold">
                        <?php echo count($manifest['translations']['tr']['menuItems']) + count($manifest['translations']['en']['menuItems']); ?>
                    </h3>
                    <div class="ml-auto w-12 h-12 rounded-full bg-green-500/10 flex items-center justify-center text-green-400">
                        <i class="fas fa-bars text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Toplam Uygulama Sayısı -->
        <div class="bg-black/20 rounded-xl p-6 hover-card transition-all h-full">
            <div class="flex flex-col h-full">
                <p class="text-white/50 text-sm mb-2">Uygulamalar</p>
                <div class="flex items-center mt-auto">
                    <h3 class="text-3xl font-bold">
                        <?php echo isset($manifest['apps']) ? count($manifest['apps']) : 0; ?>
                    </h3>
                    <div class="ml-auto w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-400">
                        <i class="fas fa-cube text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- İkinci Satır - 2 Kart -->
        <!-- Toplam Dosya Sayısı -->
        <?php
        // Tüm uygulamaların dosyalarını say
        $totalFiles = 0;
        if (isset($manifest['apps'])) {
            foreach ($manifest['apps'] as $app) {
                if (isset($app['files'])) {
                    $totalFiles += count($app['files']);
                }
            }
        }
        
        // Initialize file counts
        $fileCount = $totalFiles;
        $mediaCount = 0;
        
        // If database is being used, get counts from there
        if (function_exists('dbConnect')) {
            try {
                $conn = dbConnect();
                
                // Check if the 'apps' table exists
                $result = $conn->query("SHOW TABLES LIKE 'apps'");
                if ($result && $result->num_rows > 0) {
                    // Uygulama dosyalarını say
                    $result = $conn->query("SELECT COUNT(*) as total FROM apps WHERE app_size > 0");
                    if ($result && $row = $result->fetch_assoc()) {
                        $fileCount += $row['total'];
                    }
                }
                
                // Check if the 'files' table exists
                $result = $conn->query("SHOW TABLES LIKE 'files'");
                if ($result && $result->num_rows > 0) {
                    // Medya dosyalarını say
                    $result = $conn->query("SELECT COUNT(*) as total FROM files");
                    if ($result && $row = $result->fetch_assoc()) {
                        $mediaCount = $row['total'];
                        $fileCount += $mediaCount;
                    }
                }
                
                $conn->close();
            } catch (Exception $e) {
                // If there's an error with the database, just use manifest counts
            }
        }
        ?>
        <div class="bg-black/20 rounded-xl p-6 hover-card transition-all h-full md:col-span-2">
            <div class="flex flex-col h-full">
                <p class="text-white/50 text-sm mb-2">Toplam Dosya</p>
                <div class="flex items-center mt-auto">
                    <div>
                        <h3 class="text-3xl font-bold">
                            <?php echo $fileCount; ?>
                        </h3>
                        <div class="flex flex-wrap text-white/30 text-xs mt-1 gap-2">
                            <span class="inline-flex items-center">
                                <i class="fas fa-cubes mr-1"></i> <?php echo $fileCount - $mediaCount; ?>
                            </span>
                            <span class="inline-flex items-center">
                                <i class="fas fa-images mr-1"></i> <?php echo $mediaCount; ?>
                            </span>
                        </div>
                    </div>
                    <div class="ml-auto w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-400">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bakım Modu Durumu -->
        <div class="bg-black/20 rounded-xl p-6 hover-card transition-all h-full">
            <div class="flex flex-col h-full">
                <p class="text-white/50 text-sm mb-2">Bakım Modu</p>
                <div class="flex items-center mt-auto">
                    <h3 class="text-3xl font-bold">
                        <?php echo isset($manifest['maintenance']['enabled']) && $manifest['maintenance']['enabled'] == 1 ? 'Aktif' : 'Kapalı'; ?>
                    </h3>
                    <div class="ml-auto w-12 h-12 rounded-full <?php echo isset($manifest['maintenance']['enabled']) && $manifest['maintenance']['enabled'] == 1 ? 'bg-red-500/10 text-red-400' : 'bg-gray-500/10 text-gray-400'; ?> flex items-center justify-center">
                        <i class="fas fa-tools text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hızlı Erişim Kısayolları -->
    <h3 class="text-lg font-medium mb-5">Hızlı Erişim</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- İlk Satır -->
        <a href="pages/maintenance.php" class="bg-black/20 rounded-xl p-6 hover:bg-black/30 transition-all flex flex-col items-center justify-center h-full">
            <div class="w-14 h-14 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 mb-4">
                <i class="fas fa-tools text-xl"></i>
            </div>
            <p class="text-white/80">Bakım Modu</p>
        </a>
        
        <a href="pages/news.php" class="bg-black/20 rounded-xl p-6 hover:bg-black/30 transition-all flex flex-col items-center justify-center h-full">
            <div class="w-14 h-14 rounded-full bg-green-500/10 flex items-center justify-center text-green-400 mb-4">
                <i class="fas fa-newspaper text-xl"></i>
            </div>
            <p class="text-white/80">Haberler</p>
        </a>
        
        <a href="pages/notice.php" class="bg-black/20 rounded-xl p-6 hover:bg-black/30 transition-all flex flex-col items-center justify-center h-full">
            <div class="w-14 h-14 rounded-full bg-yellow-500/10 flex items-center justify-center text-yellow-400 mb-4">
                <i class="fas fa-exclamation-circle text-xl"></i>
            </div>
            <p class="text-white/80">Önemli Duyuru</p>
        </a>
        
        <!-- İkinci Satır -->
        <a href="pages/menu.php" class="bg-black/20 rounded-xl p-6 hover:bg-black/30 transition-all flex flex-col items-center justify-center h-full">
            <div class="w-14 h-14 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-400 mb-4">
                <i class="fas fa-bars text-xl"></i>
            </div>
            <p class="text-white/80">Menü Yönetimi</p>
        </a>
        
        <a href="pages/apps.php" class="bg-black/20 rounded-xl p-6 hover:bg-black/30 transition-all flex flex-col items-center justify-center h-full">
            <div class="w-14 h-14 rounded-full bg-indigo-500/10 flex items-center justify-center text-indigo-400 mb-4">
                <i class="fas fa-cube text-xl"></i>
            </div>
            <p class="text-white/80">Uygulamalar</p>
        </a>
        
        <a href="pages/files.php" class="bg-black/20 rounded-xl p-6 hover:bg-black/30 transition-all flex flex-col items-center justify-center h-full">
            <div class="w-14 h-14 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-400 mb-4">
                <i class="fas fa-file-alt text-xl"></i>
            </div>
            <p class="text-white/80">Dosya Yönetimi</p>
        </a>
    </div>
</div>

<!-- Son Haberler -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300 mt-8">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl font-semibold text-white">Son Haberler</h2>
        <a href="pages/news.php" class="text-blue-400 hover:text-blue-300 transition-colors flex items-center">
            Tümünü Gör <i class="fas fa-chevron-right ml-2"></i>
        </a>
    </div>
    
    <div class="space-y-6">
        <?php 
        // Son 3 haberi göster
        $latest_news = array_slice($manifest['news'], 0, 3);
        foreach ($latest_news as $news): 
        ?>
        <div class="bg-black/30 hover:bg-black/40 border border-white/5 rounded-xl p-5 flex items-center gap-6 hover-card transition-all duration-300">
            <div class="flex-shrink-0">
                <img src="<?php echo $news['image']; ?>" 
                     class="w-20 h-20 rounded-lg object-cover ring-1 ring-white/10">
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-white/90 text-lg truncate"><?php echo $news['title']; ?></h3>
                <p class="text-sm text-white/60 line-clamp-2 mt-1"><?php echo $news['description']; ?></p>
                <div class="flex items-center gap-4 mt-3 text-xs text-white/40">
                    <span class="flex items-center gap-1">
                        <i class="fas fa-calendar"></i>
                        <?php echo $news['date']; ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Manifest URL Bölümü -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300 mt-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-white">Manifest URL</h2>
        <div class="text-white/50 text-sm">Launcher yapılandırması için kullanın</div>
    </div>
    
    <div class="bg-black/30 border border-white/5 rounded-xl p-6 hover-card transition-all duration-300">
        <div class="flex-1 min-w-0">
            <?php
            // Use the manually configured URL from config.php
            $site_url = Site_URL;
            ?>
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 flex-shrink-0">
                    <i class="fas fa-link text-xl"></i>
                </div>
                <input type="text" value="<?php echo $site_url; ?>manifest.json" id="manifestUrl" readonly
                       class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-3 text-white/90">
                <button onclick="copyManifestUrl()" class="px-6 py-3 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all flex items-center gap-2 flex-shrink-0">
                    <i class="fas fa-copy" id="copyIcon"></i>
                    <span>Kopyala</span>
                </button>
            </div>
            <p class="text-white/50 text-sm mt-4 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                Bu URL'yi launcher yapılandırmanızda kullanabilirsiniz.
            </p>
        </div>
    </div>
</div>

<script>
// URL kopyalama fonksiyonu
function copyManifestUrl() {
    const manifestUrlInput = document.getElementById('manifestUrl');
    const copyIcon = document.getElementById('copyIcon');
    
    // Input değerini seç
    manifestUrlInput.select();
    manifestUrlInput.setSelectionRange(0, 99999); // Mobil cihazlar için
    
    // Kopyala
    navigator.clipboard.writeText(manifestUrlInput.value).then(() => {
        // Kopyalandı göstergesi
        copyIcon.className = 'fas fa-check';
        
        // Toast bildirim
        showSuccessToast('Manifest URL kopyalandı');
        
        // 2 saniye sonra ikonu geri çevir
        setTimeout(() => {
            copyIcon.className = 'fas fa-copy';
        }, 2000);
    });
}

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
</script>

<?php
require_once 'includes/footer.php';
?> 