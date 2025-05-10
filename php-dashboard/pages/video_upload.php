<?php
require_once '../includes/header.php';

// Video yükleme işlemi
$uploadMessage = '';
$uploadSuccess = false;
$videoLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {
    // Video klasörünü kontrol et
    $videoDir = __DIR__ . '/../video';
    if (!file_exists($videoDir)) {
        mkdir($videoDir, 0777, true);
    }
    
    // Hata kontrolü
    if ($_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Dosya boyutu PHP yapılandırmasında izin verilen maksimum boyutu aşıyor',
            UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu formda belirtilen maksimum boyutu aşıyor',
            UPLOAD_ERR_PARTIAL => 'Dosya yalnızca kısmen yüklendi',
            UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı',
            UPLOAD_ERR_CANT_WRITE => 'Dosya diske yazılamadı',
            UPLOAD_ERR_EXTENSION => 'Bir PHP uzantısı dosya yüklemeyi durdurdu'
        ];
        $uploadMessage = isset($errors[$_FILES['video_file']['error']]) 
                       ? $errors[$_FILES['video_file']['error']] 
                       : 'Bilinmeyen hata kodu: ' . $_FILES['video_file']['error'];
    } 
    // Dosya tipi kontrolü
    else if (!in_array($_FILES['video_file']['type'], ['video/mp4', 'application/octet-stream'])) {
        $uploadMessage = 'Sadece MP4 formatındaki videolar desteklenmektedir. Yüklenen dosya tipi: ' . $_FILES['video_file']['type'];
    }
    // Dosya boyutu kontrolü
    else if ($_FILES['video_file']['size'] > 1024 * 1024 * 1024) {
        $uploadMessage = 'Dosya boyutu 1GB\'ı geçemez';
    }
    else {
        try {
            // Benzersiz dosya adı oluştur
            $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);
            $fileName = 'video_' . $randomString . '.mp4';
            $filePath = $videoDir . '/' . $fileName;
            
            // Dosyayı taşı
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $filePath)) {
                chmod($filePath, 0644);
                
                // JSON dosyasına ekle
                $jsonFile = $videoDir . '/videos.json';
                $videosData = ['videos' => []];
                
                if (file_exists($jsonFile)) {
                    $jsonContent = file_get_contents($jsonFile);
                    if ($jsonContent) {
                        $data = json_decode($jsonContent, true);
                        if (is_array($data) && isset($data['videos'])) {
                            $videosData = $data;
                        }
                    }
                }
                
                // ID oluştur
                $maxId = 0;
                foreach ($videosData['videos'] as $video) {
                    if (isset($video['id']) && $video['id'] > $maxId) {
                        $maxId = (int)$video['id'];
                    }
                }
                
                // URL oluştur
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $baseUrl = $protocol . '://' . $host;
                $videoUrl = 'video/' . $fileName;
                $fullVideoUrl = $baseUrl . '/' . $videoUrl;
                
                // Yeni videoyu ekle
                $videosData['videos'][] = [
                    'id' => $maxId + 1,
                    'name' => $fileName,
                    'url' => $fullVideoUrl
                ];
                
                // JSON dosyasını kaydet
                if (file_put_contents($jsonFile, json_encode($videosData, JSON_PRETTY_PRINT))) {
                    $uploadSuccess = true;
                    $uploadMessage = 'Video başarıyla yüklendi!';
                    $videoLink = $fullVideoUrl;
                } else {
                    $uploadMessage = 'JSON dosyası kaydedilemedi';
                }
            } else {
                $uploadMessage = 'Dosya yüklenirken hata oluştu';
            }
        } catch (Exception $e) {
            $uploadMessage = 'Hata: ' . $e->getMessage();
        }
    }
}

// PHP ayarlarını kontrol et
$phpSettings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];
?>

<!-- Video Yükleme Sayfası -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Video Yükleme
            </h2>
            <p class="text-sm text-white/50 mt-1">Doğrudan video yükleme formu</p>
        </div>
        <a href="background.php" class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-colors">
            Geri Dön
        </a>
    </div>
    
    <?php if (!empty($uploadMessage)): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $uploadSuccess ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'; ?>">
            <p><?php echo $uploadMessage; ?></p>
            <?php if ($uploadSuccess && !empty($videoLink)): ?>
                <p class="mt-2">Video URL: <a href="<?php echo $videoLink; ?>" target="_blank" class="underline"><?php echo $videoLink; ?></a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm text-white/70 mb-2">Video Dosyası Seçin</label>
            <input type="file" name="video_file" accept="video/mp4" 
                   class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 text-white/70">
            <p class="text-sm text-gray-500 mt-1 mb-4">Sadece MP4 formatı desteklenir. Maksimum boyut: 1GB</p>
        </div>
        
        <div class="flex justify-end space-x-4">
            <a href="background.php" class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-colors">
                İptal
            </a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-colors flex items-center gap-2">
                <i class="fas fa-upload"></i>
                Video Yükle
            </button>
        </div>
    </form>
    
    <div class="mt-8 p-4 bg-black/20 border border-white/5 rounded-lg">
        <h3 class="text-md font-medium text-white mb-2">PHP Ayarları</h3>
        <table class="w-full text-sm">
            <tr class="border-b border-white/10">
                <td class="py-2 text-white/70">upload_max_filesize</td>
                <td class="py-2 text-white"><?php echo $phpSettings['upload_max_filesize']; ?></td>
            </tr>
            <tr class="border-b border-white/10">
                <td class="py-2 text-white/70">post_max_size</td>
                <td class="py-2 text-white"><?php echo $phpSettings['post_max_size']; ?></td>
            </tr>
            <tr class="border-b border-white/10">
                <td class="py-2 text-white/70">memory_limit</td>
                <td class="py-2 text-white"><?php echo $phpSettings['memory_limit']; ?></td>
            </tr>
            <tr>
                <td class="py-2 text-white/70">max_execution_time</td>
                <td class="py-2 text-white"><?php echo $phpSettings['max_execution_time']; ?> saniye</td>
            </tr>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 