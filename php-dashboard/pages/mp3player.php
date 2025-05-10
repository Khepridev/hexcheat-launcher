<?php require_once '../includes/header.php'; ?>
 <!-- MP3 Player Control Section -->
 <div class="glass-effect rounded-2xl p-8 transition-all duration-300 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium text-white">MP3 Player Kontrolü</h3>
        </div>
    </div>

    <!-- MP3 Player Preview -->
    <div id="mp3PlayerPreview" class="bg-black/30 rounded-xl p-4">
        <div class="flex items-center gap-4">
            <img id="previewImage" src="<?php echo $manifest['mp3_player_control']['player'][0]['image'] ?? ''; ?>" 
                    alt="Album Art" class="w-16 h-16 rounded-lg object-cover">
            <div class="flex-1">
                <h4 id="previewTitle" class="text-white font-medium">
                    <?php echo $manifest['mp3_player_control']['player'][0]['title'] ?? 'Şarkı Adı'; ?>
                </h4>
                
                <!-- Progress Bar -->
                <div class="w-full mt-2">
                    <div class="flex items-center gap-2">
                        <span id="currentTime" class="text-xs text-white/50">0:00</span>
                        <div class="flex-1 relative h-1 bg-white/10 rounded-full overflow-hidden">
                            <div id="progressBar" class="absolute left-0 top-0 h-full bg-blue-500/50 rounded-full"></div>
                            <input type="range" id="seekBar" 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                    min="0" max="100" value="0">
                        </div>
                        <span id="duration" class="text-xs text-white/50">0:00</span>
                    </div>
                </div>

                <!-- Controls -->
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center gap-4">
                        <!-- Playback Speed -->
                        <div class="relative">
                            <button id="speedBtn" class="text-xs text-white/70 hover:text-white px-2 py-1 rounded-md bg-white/5">
                                1x
                            </button>
                            <div id="speedMenu" class="hidden absolute bottom-full left-0 mb-2 bg-black/90 rounded-lg shadow-lg w-40">
                                <div class="p-2">
                                    <div class="text-xs text-white/50 px-3 py-1">Oynatma Hızı</div>
                                        <button class="block w-full text-left px-3 py-1.5 text-sm text-white/70 hover:bg-white/10 rounded" data-speed="0.5">0.5x</button>
                                        <button class="block w-full text-left px-3 py-1.5 text-sm text-white/70 hover:bg-white/10 rounded" data-speed="1">1x</button>
                                        <button class="block w-full text-left px-3 py-1.5 text-sm text-white/70 hover:bg-white/10 rounded" data-speed="1.5">1.5x</button>
                                        <button class="block w-full text-left px-3 py-1.5 text-sm text-white/70 hover:bg-white/10 rounded" data-speed="2">2x</button>
                                        <div class="border-t border-white/10 my-1"></div>
                                        <button id="normalSpeedBtn" class="block w-full text-left px-3 py-2 text-sm text-blue-400 hover:bg-white/10 rounded">
                                            <i class="fas fa-rotate-left mr-2"></i>Normal Hız
                                        </button>
                                    </div>
                                </div>
                        </div>

                        <!-- Main Controls -->
                        <button id="prevBtn" class="text-white/70 hover:text-white">
                            <i class="fas fa-backward-step"></i>
                        </button>
                        <button id="rewindBtn" class="text-white/70 hover:text-white">
                            <i class="fas fa-backward"></i>
                        </button>
                        <button id="playPauseBtn" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white">
                            <i class="fas fa-play"></i>
                        </button>
                        <button id="forwardBtn" class="text-white/70 hover:text-white">
                            <i class="fas fa-forward"></i>
                        </button>
                        <button id="nextBtn" class="text-white/70 hover:text-white">
                            <i class="fas fa-forward-step"></i>
                        </button>
                    </div>

                    <!-- Volume Control -->
                    <div class="flex items-center gap-2">
                        <button id="muteBtn" class="text-white/70 hover:text-white">
                            <i class="fas fa-volume-up"></i>
                        </button>
                        <div class="w-20 relative">
                            <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                                <div id="volumeLevel" class="h-full bg-white/30 rounded-full"></div>
                                <input type="range" id="volumeBar" 
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        min="0" max="100" value="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MP3 Player Settings Form -->
        <div>
            <div class="flex items-center justify-between">
                <span class="text-white/70">MP3 çalar ayarlarını yönetin</span>
                
                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="mp3PlayerToggle" class="sr-only peer" 
                        <?php echo isset($manifest['mp3_player_control']['enabled']) && $manifest['mp3_player_control']['enabled'] == 1 ? 'checked' : ''; ?>>
                    <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer 
                                peer-checked:after:translate-x-full peer-checked:after:border-white 
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                peer-checked:bg-blue-500/50"></div>
                </label>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-white/70 mb-1">Şarkı Başlığı</label>
            <input type="text" id="mp3Title" value="<?php echo $manifest['mp3_player_control']['player'][0]['title'] ?? ''; ?>"
                    class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-2 text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-white/70 mb-1">Resim URL</label>
            <input type="text" id="mp3Image" value="<?php echo $manifest['mp3_player_control']['player'][0]['image'] ?? ''; ?>"
                    class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-2 text-white">
        </div>
        <div>
            <label class="block text-sm font-medium text-white/70 mb-1">MP3 URL</label>
            <input type="text" id="mp3Url" value="<?php echo $manifest['mp3_player_control']['player'][0]['url'] ?? ''; ?>"
                    class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-2 text-white">
        </div>
        <button onclick="updateMp3Player()" class="px-4 py-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all">
            Kaydet
        </button>
</div>

<!-- JavaScript for MP3 Player -->
<script>
let audio = new Audio();
let isPlaying = false;
audio.src = document.getElementById('mp3Url').value;

// İndirmeyi engelle
audio.addEventListener('loadeddata', () => {
    audio.controlsList = "nodownload";
});

// Progress bar güncelleme
audio.addEventListener('timeupdate', () => {
    const progress = (audio.currentTime / audio.duration) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('currentTime').textContent = formatTime(audio.currentTime);
    document.getElementById('seekBar').value = progress;
});

audio.addEventListener('loadedmetadata', () => {
    document.getElementById('duration').textContent = formatTime(audio.duration);
});

// Oynatma/Duraklatma
playPauseBtn.onclick = () => {
    if (isPlaying) {
        audio.pause();
        playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
    } else {
        audio.play();
        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
    }
    isPlaying = !isPlaying;
};

// İleri/Geri Sarma
rewindBtn.onclick = () => {
    audio.currentTime = Math.max(0, audio.currentTime - 10);
};

forwardBtn.onclick = () => {
    audio.currentTime = Math.min(audio.duration, audio.currentTime + 10);
};

// Hız Kontrolü
const speedBtn = document.getElementById('speedBtn');
const speedMenu = document.getElementById('speedMenu');

speedBtn.onclick = () => {
    speedMenu.classList.toggle('hidden');
};

document.querySelectorAll('#speedMenu button').forEach(btn => {
    btn.onclick = () => {
        const speed = parseFloat(btn.dataset.speed);
        audio.playbackRate = speed;
        speedBtn.textContent = btn.textContent;
        speedMenu.classList.add('hidden');
    };
});

// Ses Kontrolü
volumeBar.oninput = () => {
    const volume = volumeBar.value / 100;
    audio.volume = volume;
    document.getElementById('volumeLevel').style.width = `${volume * 100}%`;
    updateVolumeIcon(volume);
};

muteBtn.onclick = () => {
    audio.muted = !audio.muted;
    updateVolumeIcon(audio.muted ? 0 : audio.volume);
};

function updateVolumeIcon(volume) {
    const icon = muteBtn.querySelector('i');
    if (volume === 0 || audio.muted) {
        icon.className = 'fas fa-volume-mute';
    } else if (volume < 0.5) {
        icon.className = 'fas fa-volume-down';
    } else {
        icon.className = 'fas fa-volume-up';
    }
}

// Zaman Formatı
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    seconds = Math.floor(seconds % 60);
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
}

// Seek Bar
seekBar.oninput = () => {
    const time = (seekBar.value / 100) * audio.duration;
    audio.currentTime = time;
};

// Sayfa dışı tıklamada hız menüsünü kapat
document.addEventListener('click', (e) => {
    if (!speedBtn.contains(e.target) && !speedMenu.contains(e.target)) {
        speedMenu.classList.add('hidden');
    }
});

// Context menu'yü engelle (sağ tık)
audio.addEventListener('contextmenu', (e) => {
    e.preventDefault();
});

document.getElementById('mp3PlayerPreview').addEventListener('contextmenu', (e) => {
    e.preventDefault();
});
</script>

<!-- JavaScript kısmına eklenecek -->
<script>
// Normal hıza dönme butonu
document.getElementById('normalSpeedBtn').onclick = () => {
    audio.playbackRate = 1;
    speedBtn.textContent = '1x';
    speedMenu.classList.add('hidden');
};
</script>

<script>
// Mp3 ayarlarını güncelleme
async function updateMp3Player() {
    try {
        const data = {
            action: 'update_mp3',
            mp3Title: document.getElementById('mp3Title').value,
            mp3Image: document.getElementById('mp3Image').value,
            mp3Url: document.getElementById('mp3Url').value,
            enabled: document.getElementById('mp3PlayerToggle').checked // logoTextEnabled yerine mp3PlayerToggle kullanıyoruz
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
            showSuccessToast('MP3 Player ayarları başarıyla güncellendi');
            
            // Önizleme güncelleme
            document.getElementById('previewTitle').textContent = data.mp3Title;
            document.getElementById('previewImage').src = data.mp3Image;
            audio.src = data.mp3Url;
            
        } else {
            throw new Error(result.error || 'Güncelleme başarısız');
        }
    } catch (error) {
        console.error('MP3 güncelleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message,
            icon: 'error'
        });
    }
}
</script>

<!-- SweetAlert ve Toast ayarları -->
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
</script>
<?php require_once '../includes/footer.php'; ?>