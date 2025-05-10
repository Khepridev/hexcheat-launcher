<?php
require_once '../includes/header.php';

// Veritabanı bağlantısı
$conn = dbConnect();
$files = [];
$selectedType = isset($_GET['type']) ? $_GET['type'] : 'all';

// Dosya listesini al
$sql = "SELECT * FROM files";
if ($selectedType !== 'all') {
    $sql .= " WHERE file_type = ?";
}
$sql .= " ORDER BY file_id DESC";

if ($selectedType !== 'all') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedType);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
}

// Dosya türünü kontrol eden fonksiyon
function getFileTypeFromExtension($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $videoTypes = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv'];
    $audioTypes = ['mp3', 'wav', 'ogg', 'aac'];
    
    if (in_array($extension, $imageTypes)) {
        return 'img';
    } elseif (in_array($extension, $videoTypes)) {
        return 'video';
    } elseif (in_array($extension, $audioTypes)) {
        return 'audio';
    } else {
        return 'other';
    }
}

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_file') {
    $fileType = $_POST['file_type'];
    $uploadSuccess = false;
    $errorMessage = '';
    
    // Dosya yükleme kontrolü
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $fileName = $_FILES['file']['name']; // Orijinal dosya adı
        $fileSize = $_FILES['file']['size'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Dosya boyutu kontrolü (200MB)
        if ($fileSize > 200 * 1024 * 1024) {
            $_SESSION['upload_error'] = "Dosya boyutu çok büyük (maks. 200MB)";
            echo '<script>window.location.href = window.location.href;</script>';
            exit;
        } else {
            // Dosya tipine göre izin verilen uzantılar
            $allowedExtensions = [];
            if ($fileType === 'img') {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            } elseif ($fileType === 'video') {
                $allowedExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv'];
            } elseif ($fileType === 'audio') {
                $allowedExtensions = ['mp3', 'wav', 'ogg', 'aac'];
            }
            
            // Uzantı kontrolü
            if (!in_array($fileExtension, $allowedExtensions)) {
                $_SESSION['upload_error'] = "Bu dosya türü için geçersiz uzantı";
                echo '<script>window.location.href = window.location.href;</script>';
                exit;
            } else {
                // Dosya tipine göre klasörü belirle
                $uploadDir = '../files/' . $fileType . '/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Benzersiz dosya adı oluştur
                $newFileName = uniqid('file_') . '.' . $fileExtension;
                $uploadPath = $uploadDir . $newFileName;
                $fileUrl = 'files/' . $fileType . '/' . $newFileName;
                
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    // Veritabanına kaydet
                    $stmt = $conn->prepare("INSERT INTO files (file_name, file_type, file_url) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $fileName, $fileType, $fileUrl);
                    
                    if ($stmt->execute()) {
                        $_SESSION['upload_success'] = true;
                        echo '<script>window.location.href = window.location.href;</script>';
                        exit;
                    } else {
                        $errorMessage = "Veritabanı hatası: " . $conn->error;
                        $_SESSION['upload_error'] = $errorMessage;
                        echo '<script>window.location.href = window.location.href;</script>';
                        exit;
                    }
                    
                    $stmt->close();
                } else {
                    $errorMessage = "Dosya yükleme hatası";
                    $_SESSION['upload_error'] = $errorMessage;
                    echo '<script>window.location.href = window.location.href;</script>';
                    exit;
                }
            }
        }
    } else {
        $errorMessage = "Lütfen bir dosya seçin";
        $_SESSION['upload_error'] = $errorMessage;
        echo '<script>window.location.href = window.location.href;</script>';
        exit;
    }
}

// Add this near the top of the file, after session check
if (isset($_SESSION['upload_success'])) {
    echo '<script>
        Swal.fire({
            title: "Dosya başarıyla yüklendi",
            icon: "success",
            toast: true,
            position: "bottom-end",
            showConfirmButton: false,
            timer: 3000,
            background: "#111111",
            color: "#fff",
            customClass: {
                popup: "bg-[#111111] border border-white/10",
                title: "text-white text-sm"
            }
        });
    </script>';
    unset($_SESSION['upload_success']);
}

// Add error message handling near the top of the file
if (isset($_SESSION['upload_error'])) {
    echo '<script>
        Swal.fire({
            title: "' . htmlspecialchars($_SESSION['upload_error']) . '",
            icon: "error",
            toast: true,
            position: "bottom-end",
            showConfirmButton: false,
            timer: 3000,
            background: "#111111",
            color: "#fff",
            customClass: {
                popup: "bg-[#111111] border border-white/10",
                title: "text-white text-sm"
            }
        });
    </script>';
    unset($_SESSION['upload_error']);
}

$conn->close();
?>

<!-- Dosya Yönetimi -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Dosya Yönetimi
            </h2>
            <p class="text-sm text-white/50 mt-1">Resim ve video dosyalarını yükleyin ve yönetin</p>
        </div>
        <button onclick="openUploadModal()" 
                class="bg-green-500/10 hover:bg-green-500/20 text-green-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
            <i class="fas fa-upload"></i> Dosya Yükle
        </button>
    </div>
    
    <?php if (isset($uploadSuccess) && $uploadSuccess): ?>
    <div class="mb-6 p-4 bg-green-500/10 text-green-400 rounded-lg">
        <p><i class="fas fa-check-circle mr-2"></i> Dosya başarıyla yüklendi.</p>
    </div>
    <?php endif; ?>
    
    <!-- Filtreler -->
    <div class="flex gap-3 mb-6">
        <a href="?type=all" class="px-4 py-2 rounded-lg <?php echo $selectedType === 'all' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80'; ?> transition-colors">
            Tümü
        </a>
        <a href="?type=img" class="px-4 py-2 rounded-lg <?php echo $selectedType === 'img' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80'; ?> transition-colors">
            <i class="fas fa-image mr-1"></i> Resimler
        </a>
        <a href="?type=video" class="px-4 py-2 rounded-lg <?php echo $selectedType === 'video' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80'; ?> transition-colors">
            <i class="fas fa-video mr-1"></i> Videolar
        </a>
        <a href="?type=audio" class="px-4 py-2 rounded-lg <?php echo $selectedType === 'audio' ? 'bg-blue-500/20 text-blue-400' : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white/80'; ?> transition-colors">
            <i class="fas fa-music mr-1"></i> MP3
        </a>
    </div>
    
    <!-- Dosya Listesi -->
    <div class="bg-black/30 border border-white/5 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider w-12"></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Dosya Adı</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Tür</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">URL</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($files) > 0): ?>
                    <?php foreach ($files as $file): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="px-4 py-3">
                                <div class="w-8 h-8 flex items-center justify-center overflow-hidden rounded">
                                    <?php if ($file['file_type'] === 'img'): ?>
                                        <img src="<?php echo Site_URL . htmlspecialchars($file['file_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($file['file_name']); ?>"
                                             class="w-full h-full object-cover cursor-pointer"
                                             onclick="openImageModal('<?php echo Site_URL . htmlspecialchars($file['file_url']); ?>', '<?php echo htmlspecialchars($file['file_name']); ?>')">
                                    <?php elseif ($file['file_type'] === 'video'): ?>
                                        <div class="w-full h-full bg-black/50 flex items-center justify-center rounded cursor-pointer"
                                             onclick="openVideoModal('<?php echo Site_URL . htmlspecialchars($file['file_url']); ?>', '<?php echo htmlspecialchars($file['file_name']); ?>')">
                                            <i class="fas fa-play text-white/70 text-xs"></i>
                                        </div>
                                    <?php elseif ($file['file_type'] === 'audio'): ?>
                                        <div class="w-full h-full bg-black/50 flex items-center justify-center rounded cursor-pointer"
                                             onclick="openAudioModal('<?php echo Site_URL . htmlspecialchars($file['file_url']); ?>', '<?php echo htmlspecialchars($file['file_name']); ?>')">
                                            <i class="fas fa-music text-white/70 text-xs"></i>
                                        </div>
                                    <?php else: ?>
                                        <i class="fas fa-file text-white/30"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-white">
                                <div class="truncate max-w-xs" title="<?php echo htmlspecialchars($file['file_name']); ?>">
                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-white/70">
                                <?php 
                                if ($file['file_type'] === 'img') {
                                    echo '<span class="text-green-400"><i class="fas fa-image mr-1"></i> Resim</span>';
                                } elseif ($file['file_type'] === 'video') {
                                    echo '<span class="text-blue-400"><i class="fas fa-video mr-1"></i> Video</span>';
                                } elseif ($file['file_type'] === 'audio') {
                                    echo '<span class="text-purple-400"><i class="fas fa-music mr-1"></i> MP3</span>';
                                } else {
                                    echo '<span class="text-gray-400"><i class="fas fa-file mr-1"></i> Diğer</span>';
                                }
                                ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-white/50">
                                <div class="truncate max-w-xs" title="<?php echo Site_URL . htmlspecialchars($file['file_url']); ?>">
                                    <?php echo Site_URL . htmlspecialchars($file['file_url']); ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="copyFileUrl('<?php echo Site_URL . htmlspecialchars($file['file_url']); ?>')"
                                            class="text-xs px-2 py-1 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded transition-colors">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" onclick="deleteFile(<?php echo $file['file_id']; ?>, '<?php echo htmlspecialchars($file['file_name']); ?>')"
                                            class="text-xs px-2 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-white/50">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder-open text-2xl mb-2 text-white/30"></i>
                                <p class="mb-3">Dosya bulunamadı</p>
                                <button type="button" onclick="openUploadModal()"
                                        class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all inline-flex items-center gap-1 text-xs">
                                    <i class="fas fa-upload"></i>
                                    <span>Dosya Yükle</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Dosya Yükleme Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-md rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Dosya Yükle</h3>
                <button onclick="closeUploadModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="post" enctype="multipart/form-data" class="p-6 space-y-4">
                <input type="hidden" name="action" value="upload_file">
                
                <div>
                    <label class="block text-sm text-white/70 mb-2">Dosya Türü</label>
                    <select name="file_type" id="file_type" required
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                  text-white focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                        <option value="img">Resim</option>
                        <option value="video">Video</option>
                        <option value="audio">MP3</option>
                    </select>
                </div>
                
                <div class="text-center p-4 border-2 border-dashed border-white/10 rounded-lg">
                    <div class="mb-4">
                        <i class="fas fa-cloud-upload-alt text-3xl text-white/50"></i>
                    </div>
                    <p class="text-white/70 mb-4">Dosya yüklemek için tıklayın veya sürükleyin</p>
                    <input type="file" name="file" id="file_input" required
                           class="block w-full text-white/70 bg-black/30 border border-white/10 rounded-lg cursor-pointer focus:outline-none">
                    <p class="mt-3 text-xs text-white/50">
                        Resim: JPG, JPEG, PNG, GIF, WEBP, SVG (Maks. 20MB)<br>
                        Video: MP4, WEBM, OGG, MOV, AVI, WMV (Maks. 20MB)<br>
                        MP3: MP3, WAV, OGG, AAC (Maks. 200MB)
                    </p>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-all">
                        İptal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-all">
                        Yükle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resim Modal -->
<div id="imageModal" class="hidden fixed inset-0 z-50">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-3xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="imageModalTitle">Resim Önizleme</h3>
                <button onclick="closeImageModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <div class="rounded-lg overflow-hidden bg-black/20 flex items-center justify-center">
                    <img id="imageModalContent" src="" alt="Image Preview" class="max-w-full max-h-[70vh]">
                </div>
                
                <!-- Modal Footer -->
                <div class="mt-4 flex justify-between items-center">
                    <span id="imageUrl" class="text-white/50 text-sm truncate"></span>
                    <button onclick="copyImageUrl()" 
                            class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/70 rounded-lg transition-all flex items-center gap-2">
                        <i class="fas fa-copy"></i>
                        <span>URL Kopyala</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div id="videoModal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>        
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-2xl border border-white/10">
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="videoModalTitle">Video Önizleme</h3>
                <button onclick="closeVideoModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <!-- Video Player -->
                <div class="relative aspect-video bg-black">
                    <video id="videoPlayer" class="w-full h-full" controls>
                        <source src="" type="video/mp4">
                    </video>
                </div>
                
                <!-- Modal Footer -->
                <div class="p-4 flex justify-between items-center">
                    <span id="videoUrl" class="text-white/50 text-sm truncate"></span>
                    <button onclick="copyVideoUrlFromModal()" 
                            class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/70 rounded-lg transition-all flex items-center gap-2">
                        <i class="fas fa-copy"></i>
                        <span>URL Kopyala</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audio Modal -->
<div id="audioModal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>        
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-2xl border border-white/10 rounded-lg">
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="audioModalTitle">MP3 Oynatıcı</h3>
                <button onclick="closeAudioModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <!-- Audio Player -->
                <div class="bg-black/30 p-4 rounded-lg">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-lg bg-purple-500/10 flex items-center justify-center overflow-hidden">
                            <i class="fas fa-music text-3xl text-purple-400"></i>
                        </div>
                        <div class="flex-1">
                            <h4 id="audioFileName" class="text-white font-medium truncate"></h4>
                            
                            <!-- Progress Bar -->
                            <div class="w-full mt-2">
                                <div class="flex items-center gap-2">
                                    <span id="audioCurrentTime" class="text-xs text-white/50">0:00</span>
                                    <div class="flex-1 relative h-1 bg-white/10 rounded-full overflow-hidden">
                                        <div id="audioProgressBar" class="absolute left-0 top-0 h-full bg-purple-500/50 rounded-full"></div>
                                        <input type="range" id="audioSeekBar" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                                min="0" max="100" value="0">
                                    </div>
                                    <span id="audioDuration" class="text-xs text-white/50">0:00</span>
                                </div>
                            </div>

                            <!-- Controls -->
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center gap-3">
                                    <button id="audioRewindBtn" class="text-white/70 hover:text-white">
                                        <i class="fas fa-backward"></i>
                                    </button>
                                    <button id="audioPlayPauseBtn" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button id="audioForwardBtn" class="text-white/70 hover:text-white">
                                        <i class="fas fa-forward"></i>
                                    </button>
                                </div>

                                <!-- Volume Control -->
                                <div class="flex items-center gap-2">
                                    <button id="audioMuteBtn" class="text-white/70 hover:text-white">
                                        <i class="fas fa-volume-up"></i>
                                    </button>
                                    <div class="w-20 relative">
                                        <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                                            <div id="audioVolumeLevel" class="h-full bg-white/30 rounded-full"></div>
                                            <input type="range" id="audioVolumeBar" 
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                    min="0" max="100" value="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="mt-4 flex justify-between items-center">
                    <span id="audioUrl" class="text-white/50 text-sm truncate"></span>
                    <button onclick="copyAudioUrl()" 
                            class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white/70 rounded-lg transition-all flex items-center gap-2">
                        <i class="fas fa-copy"></i>
                        <span>URL Kopyala</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// SweetAlert2 için özel tema
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

// Toast bildirimi
function showToast(message, icon = 'success') {
    Swal.fire({
        title: message,
        icon: icon,
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

// Modal işlevleri
function openUploadModal() {
    document.getElementById('uploadModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC tuşu ile modalları kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
        closeImageModal();
        closeAudioModal();
        closeUploadModal();
    }
});

// Dosya URL'sini kopyalama
function copyFileUrl(url) {
    navigator.clipboard.writeText(url).then(function() {
        showToast('URL panoya kopyalandı');
    }, function() {
        showToast('Kopyalama başarısız oldu', 'error');
    });
}

// Dosya silme
function deleteFile(fileId, fileName) {
    SwalCustom.fire({
        title: 'Emin misiniz?',
        text: `"${fileName}" dosyasını silmek istediğinize emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sil'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete_file',
                    file_id: fileId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Dosya başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.error || 'Bir hata oluştu', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Bir hata oluştu', 'error');
            });
        }
    });
}

// Dosya tipi değiştiğinde kabul edilen dosya uzantılarını güncelle
document.getElementById('file_type').addEventListener('change', function() {
    const fileInput = document.getElementById('file_input');
    if (this.value === 'img') {
        fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml';
    } else if (this.value === 'video') {
        fileInput.accept = 'video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo,video/x-ms-wmv';
    } else if (this.value === 'audio') {
        fileInput.accept = 'audio/mpeg,audio/x-wav,audio/ogg,audio/aac';
    }
});

// Sayfa yüklendiğinde resim için dosya tiplerini ayarla
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file_input');
    fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml';
});

// Video modal işlemleri
function openVideoModal(url, name) {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    const title = document.getElementById('videoModalTitle');
    const urlSpan = document.getElementById('videoUrl');
    
    // Direkt video URL'sini ayarla
    player.innerHTML = ''; // Önceki kaynakları temizle
    const sourceElement = document.createElement('source');
    sourceElement.setAttribute('src', url);
    sourceElement.setAttribute('type', 'video/mp4');
    player.appendChild(sourceElement);
    
    title.textContent = name;
    urlSpan.textContent = url;
    urlSpan.setAttribute('data-full-url', url);
    
    modal.style.position = 'fixed';
    modal.style.zIndex = '999999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Video yüklendikten sonra başlat
    player.load();
    
    // Player hazır olduğunda oynat
    player.onloadedmetadata = function() {
        player.play().catch(e => console.log('Video oynatma hatası:', e));
    };
}

function closeVideoModal() {
    const modal = document.getElementById('videoModal');
    const player = document.getElementById('videoPlayer');
    
    player.pause();
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function copyVideoUrlFromModal() {
    const url = document.getElementById('videoUrl').textContent;
    navigator.clipboard.writeText(url);
    
    // Kopyalama bildirimi
    showToast('URL panoya kopyalandı');
}

// Resim modal işlemleri
function openImageModal(url, name) {
    const modal = document.getElementById('imageModal');
    const image = document.getElementById('imageModalContent');
    const title = document.getElementById('imageModalTitle');
    const urlSpan = document.getElementById('imageUrl');
    
    image.src = url;
    title.textContent = name;
    urlSpan.textContent = url;
    
    modal.style.position = 'fixed';
    modal.style.zIndex = '999999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function copyImageUrl() {
    const url = document.getElementById('imageUrl').textContent;
    navigator.clipboard.writeText(url);
    
    // Kopyalama bildirimi
    showToast('URL panoya kopyalandı');
}

// Audio modal işlemleri için yeni kod
let audioPlayer = null;
let audioIsPlaying = false;

function openAudioModal(url, name) {
    const modal = document.getElementById('audioModal');
    const fileName = document.getElementById('audioFileName');
    const urlSpan = document.getElementById('audioUrl');
    
    // Yeni bir audio oluştur ve eski varsa temizle
    if (audioPlayer) {
        audioPlayer.pause();
        audioPlayer.src = '';
    }
    
    audioPlayer = new Audio(url);
    audioIsPlaying = false;
    
    // Audio kontrolları için event listener'lar
    setupAudioListeners();
    
    fileName.textContent = name;
    urlSpan.textContent = url;
    urlSpan.setAttribute('data-full-url', url);
    
    modal.style.position = 'fixed';
    modal.style.zIndex = '999999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Audio yüklendikten sonra süresi göster
    audioPlayer.addEventListener('loadedmetadata', () => {
        document.getElementById('audioDuration').textContent = formatTime(audioPlayer.duration);
    });
}

function setupAudioListeners() {
    // Oynatma/duraklatma butonu
    const playPauseBtn = document.getElementById('audioPlayPauseBtn');
    playPauseBtn.onclick = () => {
        if (audioIsPlaying) {
            audioPlayer.pause();
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
        } else {
            audioPlayer.play();
            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
        }
        audioIsPlaying = !audioIsPlaying;
    };
    
    // İleri/geri sarma butonları
    document.getElementById('audioRewindBtn').onclick = () => {
        audioPlayer.currentTime = Math.max(0, audioPlayer.currentTime - 10);
    };
    
    document.getElementById('audioForwardBtn').onclick = () => {
        audioPlayer.currentTime = Math.min(audioPlayer.duration, audioPlayer.currentTime + 10);
    };
    
    // Seek bar
    const seekBar = document.getElementById('audioSeekBar');
    seekBar.oninput = () => {
        const time = (seekBar.value / 100) * audioPlayer.duration;
        audioPlayer.currentTime = time;
    };
    
    // Progress bar güncelleme
    audioPlayer.addEventListener('timeupdate', () => {
        const progress = (audioPlayer.currentTime / audioPlayer.duration) * 100;
        document.getElementById('audioProgressBar').style.width = `${progress}%`;
        document.getElementById('audioCurrentTime').textContent = formatTime(audioPlayer.currentTime);
        document.getElementById('audioSeekBar').value = progress;
    });
    
    // Ses kontrolu
    const volumeBar = document.getElementById('audioVolumeBar');
    volumeBar.oninput = () => {
        const volume = volumeBar.value / 100;
        audioPlayer.volume = volume;
        document.getElementById('audioVolumeLevel').style.width = `${volume * 100}%`;
        updateAudioVolumeIcon(volume);
    };
    
    const muteBtn = document.getElementById('audioMuteBtn');
    muteBtn.onclick = () => {
        audioPlayer.muted = !audioPlayer.muted;
        updateAudioVolumeIcon(audioPlayer.muted ? 0 : audioPlayer.volume);
    };
    
    // Ek özellikler
    audioPlayer.controlsList = "nodownload"; // İndirmeyi engelle
    audioPlayer.addEventListener('contextmenu', (e) => e.preventDefault()); // Sağ tıklamayı engelle
}

function updateAudioVolumeIcon(volume) {
    const icon = document.getElementById('audioMuteBtn').querySelector('i');
    if (volume === 0 || audioPlayer.muted) {
        icon.className = 'fas fa-volume-mute';
    } else if (volume < 0.5) {
        icon.className = 'fas fa-volume-down';
    } else {
        icon.className = 'fas fa-volume-up';
    }
}

function formatTime(seconds) {
    if (isNaN(seconds)) return "0:00";
    const minutes = Math.floor(seconds / 60);
    seconds = Math.floor(seconds % 60);
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
}

function closeAudioModal() {
    const modal = document.getElementById('audioModal');
    
    if (audioPlayer) {
        audioPlayer.pause();
        audioIsPlaying = false;
    }
    
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function copyAudioUrl() {
    const url = document.getElementById('audioUrl').textContent;
    navigator.clipboard.writeText(url);
    
    // Kopyalama bildirimi
    showToast('URL panoya kopyalandı');
}
</script>

<?php
require_once '../includes/footer.php';
?> 