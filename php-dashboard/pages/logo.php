<?php require_once '../includes/header.php'; ?>
<!-- Logo Ayarları -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold text-white">Logo Ayarları</h2>
    </div>

    <div class="space-y-4 mt-6">
        <!-- Logo URL -->
        <div>
            <label for="logoUrl" class="block text-white/70 mb-1">Logo URL</label>
            <input type="text" id="logoUrl" 
                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                value="<?php echo isset($manifest['logo']['url']) ? htmlspecialchars($manifest['logo']['url']) : ''; ?>">
        </div>

        <div>
            <label for="linkUrl" class="block text-white/70 mb-1">Logo Link</label>
            <input type="text" id="linkUrl" 
                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                value="<?php echo isset($manifest['logo']['link']) ? htmlspecialchars($manifest['logo']['link']) : ''; ?>">
        </div>

        <!-- Logo Boyutları -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="logoWidth" class="block text-white/70 mb-1">Genişlik (px)</label>
                <input type="number" id="logoWidth" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                    value="<?php echo isset($manifest['logo']['width']) ? $manifest['logo']['width'] : '24'; ?>">
            </div>
            <div>
                <label for="logoHeight" class="block text-white/70 mb-1">Yükseklik (px)</label>
                <input type="number" id="logoHeight" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                    value="<?php echo isset($manifest['logo']['height']) ? $manifest['logo']['height'] : '24'; ?>">
            </div>
        </div>

        <!-- Logo Text Ayarları -->
        <div class="space-y-4">

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-white/70">Logo Yazısı</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="logoTextEnabled" class="sr-only peer" 
                            <?php echo isset($manifest['logo']['text']['enabled']) && $manifest['logo']['text']['enabled'] == 1 ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer 
                                    peer-checked:after:translate-x-full peer-checked:after:border-white 
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                    after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                    peer-checked:bg-blue-500/50"></div>
                    </label>
                </div>
                
                <input type="text" id="logoText" 
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                    value="<?php echo isset($manifest['logo']['text']['content']) ? htmlspecialchars($manifest['logo']['text']['content']) : ''; ?>">
            </div>
            
            <!-- CSS Link -->
            <div class="space-y-2">
                <div class="space-y-2">
                    <span class="text-white/70">Özel CSS</span>
                    
                    <!-- CSS URL Input -->
                    <input type="text" id="cssLink" 
                        class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"
                        value="<?php echo isset($manifest['logo']['text']['style']['cssLink']['value']) ? htmlspecialchars($manifest['logo']['text']['style']['cssLink']['value']) : ''; ?>"
                        placeholder="CSS URL">
                </div>


                <!-- CSS Editor -->
                <div class="bg-[#1E1E1E] rounded-lg border border-white/10 mt-2 ">
                    <!-- Editor başlığı ve macOS kontrollerini güncelleyelim -->
                    <div class="flex items-center justify-between px-4 py-2 border-b border-white/10 bg-black/20">
                        <div class="flex items-center space-x-4">
                            <!-- macOS window controls -->
                            <div class="flex items-center space-x-1.5">
                                <div class="w-3 h-3 rounded-full bg-[#FF5F57]"></div>
                                <div class="w-3 h-3 rounded-full bg-[#FFBD2E]"></div>
                                <div class="w-3 h-3 rounded-full bg-[#28C840]"></div>
                            </div>
                            <span class="text-white/70 text-sm">css</span>
                        </div>
                        
                        <!-- Toolbar buttons -->
                        <div class="flex items-center space-x-2">
                            <button onclick="formatCSS()" class="text-white/70 hover:text-white transition-colors px-2 py-1 rounded hover:bg-white/5" title="Format CSS">
                                <i class="fas fa-code text-sm"></i>
                            </button>
                            <button onclick="copyCSS()" class="text-white/70 hover:text-white transition-colors px-2 py-1 rounded hover:bg-white/5" title="Copy CSS">
                                <i class="fas fa-copy text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mevcut editör içeriği -->
                    <div class="p-4">
                        <textarea id="customCSS" 
                            class="w-full bg-transparent text-[#9CDCFE] font-mono text-sm outline-none min-h-[300px] resize-none"
                            placeholder="/* Logo için özel CSS kodlarınızı buraya yazın */"
                            spellcheck="false"><?php echo isset($manifest['logo']['text']['style']['cssLink']['customCSS']) ? htmlspecialchars($manifest['logo']['text']['style']['cssLink']['customCSS']) : ''; ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kaydet Butonu -->
        <div class="flex justify-end">
            <button onclick="updateLogo()" 
                class="px-4 py-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all">
                Kaydet
            </button>
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

// Logo önizleme güncelleme
document.getElementById('logoUrl').addEventListener('input', function() {
    document.getElementById('logoPreview').src = this.value;
});

// Logo ayarlarını güncelleme
async function updateLogo() {
    try {
        const data = {
            action: 'update_logo',
            url: document.getElementById('logoUrl').value,
            width: parseInt(document.getElementById('logoWidth').value),
            height: parseInt(document.getElementById('logoHeight').value),
            link: document.getElementById('linkUrl').value,
            text: {
                enabled: document.getElementById('logoTextEnabled').checked ? 1 : 0,
                content: document.getElementById('logoText').value,
                style: {
                    cssLink: {                                        
                        value: document.getElementById('cssLink').value,
                        customCSS: document.getElementById('customCSS').value
                    }
                }
            }
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
            showSuccessToast('Logo ayarları başarıyla güncellendi');
        } else {
            throw new Error(result.error || 'Güncelleme başarısız');
        }
    } catch (error) {
        console.error('Logo güncelleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message,
            icon: 'error'
        });
    }
}
</script>
<script>
    // CSS Formatlama fonksiyonu
    function formatCSS() {
        const textarea = document.getElementById('customCSS');
        let css = textarea.value;
        css = css.replace(/\s*{\s*/g, ' {\n    ')
                    .replace(/;\s*/g, ';\n    ')
                    .replace(/\s*}\s*/g, '\n}\n\n')
                    .replace(/\n\s*\n/g, '\n')
                    .trim();
        textarea.value = css;
    }

    // CSS Kopyalama fonksiyonu
    function copyCSS() {
        const textarea = document.getElementById('customCSS');
        textarea.select();
        document.execCommand('copy');
        
        // Kopyalama bildirimi
        const button = document.querySelector('button[onclick="copyCSS()"]');
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check text-sm text-green-400"></i>';
        setTimeout(() => {
            button.innerHTML = originalIcon;
        }, 1000);
    }
</script>
<?php require_once '../includes/footer.php'; ?>