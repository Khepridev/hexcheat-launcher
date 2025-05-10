<?php
require_once '../includes/header.php';
?>
<!-- Arka Plan Ayarları -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Arka Plan Ayarları
            </h2>
            <p class="text-sm text-white/50 mt-1">Arka plan görünümünü özelleştirin</p>
        </div>
    </div>
    <form id="backgroundForm" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm text-white/70 mb-2">Arka Plan Modu</label>
                <select name="background_mode" 
                        class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
                    <option value="1" <?php echo $manifest['background']['mode'] == 1 ? 'selected' : ''; ?>>Resim</option>
                    <option value="2" <?php echo $manifest['background']['mode'] == 2 ? 'selected' : ''; ?>>Video</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-white/70 mb-2">Resim URL</label>
                <input type="url" name="background_image_url" 
                        value="<?php echo $manifest['background']['imageUrl']; ?>"
                        class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            </div>
            <div>
                <label class="block text-sm text-white/70 mb-2">Video URL</label>
                <input type="url" name="background_video_url" 
                        value="<?php echo $manifest['background']['videoUrl']; ?>"
                        class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" 
                    class="px-4 py-2 rounded-lg bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-400 transition-colors">
                Güncelle
            </button>
        </div>
    </form>
    <!-- Video URL input'undan sonra -->       

    <!-- Video Listesi -->
    <div class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-white">Video Listesi</h2>
            <button onclick="openAddVideoModal()" 
                    class="bg-green-500/10 hover:bg-green-500/20 text-green-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
                <i class="fas fa-plus"></i> Video Ekle
            </button>
        </div>
        <div class="grid grid-cols-3 gap-4" id="videoList">
            <?php
            $videos_json = file_get_contents('../video/videos.json');
            $videos = json_decode($videos_json, true)['videos'];
            foreach ($videos as $video): ?>
                <div class="glass-effect rounded-lg overflow-hidden group cursor-pointer hover:scale-[1.02] transition-all duration-300"
                    onclick="openVideoModal('<?php echo $video['url']; ?>', '<?php echo $video['name']; ?>')">
                    <div class="relative aspect-video">
                        <!-- Video Thumbnail -->
                        <video class="w-full h-full object-cover" preload="metadata">
                            <source src="<?php echo $video['url']; ?>#t=0.5" type="video/mp4">
                        </video>
                        <!-- Play Button Overlay -->
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-play text-white/90 text-2xl"></i>
                        </div>
                    </div>
                    <!-- Video kartının alt kısmı -->
                    <div class="p-3 flex items-center justify-between">
                        <span class="text-sm text-white/70"><?php echo $video['name']; ?></span>
                        <div class="flex items-center gap-2">
                            <button onclick="selectVideo('<?php echo $video['url']; ?>', event)" 
                                    class="text-white/50 hover:text-white transition-colors" title="Videoyu Seç">
                                    <i class="fa-solid fa-up-right-from-square"></i>
                            </button>
                            <button onclick="copyVideoUrl('<?php echo $video['url']; ?>', event)" 
                                    class="text-white/50 hover:text-white transition-colors" title="URL Kopyala">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button onclick="deleteVideo(<?php echo $video['id']; ?>, event)" 
                                    class="text-red-400/50 hover:text-red-400 transition-colors" title="Videoyu Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>                
<!-- Video Modal -->
<div id="videoModal" class="hidden">
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>        
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-2xl border border-white/10">
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="videoModalTitle"></h3>
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

<!-- Video Ekleme Modal -->
<div id="addVideoModal" class="hidden">
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50"></div>
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Video Ekle</h3>
                <button onclick="closeAddVideoModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addVideoForm" onsubmit="handleAddVideo(event)" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm text-white/70 mb-2">Video URL</label>
                    <input type="url" name="video_url" required 
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                  text-white placeholder-white/30 focus:border-blue-500/50 
                                  focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                           placeholder="http://localhost/launcher/video/video.mp4">
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddVideoModal()"
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

    // Arka plan ayarları formu
    document.getElementById('backgroundForm').addEventListener('submit', async function(event) {
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
                action: 'update_background',
                background_mode: formData.get('background_mode'),
                background_image_url: formData.get('background_image_url'),
                background_video_url: formData.get('background_video_url')
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessToast('Arka plan ayarları başarıyla güncellendi');
        } else {
            throw new Error(data.error || 'Güncelleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Arka plan güncelleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message || 'Arka plan ayarları güncellenirken bir hata oluştu.',
            icon: 'error'
        });
    }
});
</script>
<script>
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
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});

// ESC tuşu ile modalı kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});

// URL kopyalama fonksiyonu
function copyVideoUrl(url, event) {
    event.stopPropagation();
    navigator.clipboard.writeText(url);
    
    // Kopyalama bildirimi
    showSuccessToast('URL Kopyalandı!');
}

function copyVideoUrlFromModal() {
    const url = document.getElementById('videoUrl').textContent;
    navigator.clipboard.writeText(url);
    
    // Kopyalama bildirimi
    showSuccessToast('URL Kopyalandı!');
}

</script>
<script>
// Video seçme fonksiyonu
function selectVideo(url, event) {
    event.stopPropagation(); // Modal açılmasını engelle
    const input = document.querySelector('input[name="background_video_url"]');
    input.value = url; // Input değerini güncelle
    
    // Başarılı bildirimini göster
    showSuccessToast('Video Seçildi!');
}

// Video önizleme fonksiyonu
function previewVideo(url, name, event) {
    event.stopPropagation();
    openVideoModal(url, name);
}

// URL kopyalama fonksiyonu
function copyVideoUrl(url, event) {
    event.stopPropagation();
    navigator.clipboard.writeText(url);
    
    // Kopyalama bildirimi
    showSuccessToast('URL Kopyalandı!');
}
</script>

<script>
// Video ekleme modal işlemleri
function openAddVideoModal() {
    const modal = document.getElementById('addVideoModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddVideoModal() {
    const modal = document.getElementById('addVideoModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Video ekleme işlemi
async function handleAddVideo(event) {
    event.preventDefault();
    const form = event.target;
    const videoUrl = form.video_url.value;

    try {
        const response = await fetch('../api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_video',
                url: videoUrl
            })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        
        if (data.success) {
            showSuccessToast(data.message);
            closeAddVideoModal();
            // Sayfayı yenile
            location.reload();
        } else {
            throw new Error(data.error || 'Bir hata oluştu');
        }
    } catch (error) {
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message,
            icon: 'error'
        });
    }
}

// Video silme işlemi
async function deleteVideo(id, event) {
    event.stopPropagation();
    
    try {
        const result = await SwalCustom.fire({
            title: 'Emin misiniz?',
            text: "Bu video kalıcı olarak silinecek!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        });

        if (result.isConfirmed) {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_video',
                    id: id
                })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.success) {
                showSuccessToast(data.message);
                // Sayfayı yenile
                location.reload();
            } else {
                throw new Error(data.error || 'Bir hata oluştu');
            }
        }
    } catch (error) {
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