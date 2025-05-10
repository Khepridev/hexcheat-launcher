<?php
require_once '../includes/header.php'; 
?>
<!-- Sosyal Medya Yönetimi Bölümü -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Sosyal Medya
            </h2>
            <p class="text-sm text-white/50 mt-1">Sosyal medya bağlantılarını yönetin</p>
        </div>
        <button onclick="openAddSocialMediaModal()" 
                class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-colors">
            <i class="fas fa-plus mr-2"></i>Yeni Ekle
        </button>
    </div>

    <!-- Sosyal Medya listesi için sıralama özelliğini ekliyoruz -->
    <div id="socialMediaList" class="space-y-4">
        <?php foreach ($manifest['socialMedia'] as $social): ?>
        <div class="bg-black/30 hover:bg-black/40 border border-white/5 rounded-xl p-4 flex items-center gap-4 hover-card transition-all duration-300"
                data-id="<?php echo $social['id']; ?>">
            <i class="fas fa-grip-vertical cursor-move text-white/50"></i>
            <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center">
                <i class="<?php echo htmlspecialchars($social['icon']); ?> text-2xl text-white/70"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-white/90 truncate"><?php echo $social['title']; ?></h3>
                <p class="text-sm text-white/60 truncate"><?php echo $social['url']; ?></p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick='editSocialMedia(<?php echo json_encode($social); ?>)'
                        class="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-colors">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteSocialMedia(<?php echo $social['id']; ?>)"
                        class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 transition-colors">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Sosyal Medya Modal -->
<div id="socialMediaModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 id="socialMediaModalTitle" class="text-lg font-medium text-white">Sosyal Medya Ekle</h3>
                <button onclick="closeSocialMediaModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="socialMediaForm" class="p-6 space-y-6">                              
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        Başlık
                    </label>
                    <input type="text" id="socialMediaTitle" required
                        class="w-full px-4 py-2.5 rounded-lg bg-white/5 border border-white/10 
                                text-white placeholder-white/30 focus:border-blue-500/50 
                                focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                        placeholder="örn: Discord">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        URL
                    </label>
                    <input type="url" id="socialMediaUrl" required
                        class="w-full px-4 py-2.5 rounded-lg bg-white/5 border border-white/10 
                                text-white placeholder-white/30 focus:border-blue-500/50 
                                focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                        placeholder="örn: https://discord.gg/hexlob">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-2">
                        İkon (Font Awesome Class)
                    </label>
                    <input type="text" id="socialMediaIcon" required
                        class="w-full px-4 py-2.5 rounded-lg bg-white/5 border border-white/10 
                                text-white placeholder-white/30 focus:border-blue-500/50 
                                focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                        placeholder="örn: fab fa-discord">
                    <p class="mt-2 text-sm text-white/50">
                        Font Awesome ikonları için: 
                        <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-400 hover:underline">
                            fontawesome.com/icons
                        </a>
                    </p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <input type="hidden" id="socialMediaId">
                    <button type="button" onclick="closeSocialMediaModal()"
                            class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white/70 transition-all">
                        İptal
                    </button>
                    <button id="socialMediaModalBtn" type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-all">
                        Kaydet
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

    // Sosyal Medya Modal İşlemleri
    function openAddSocialMediaModal() {
        document.getElementById('socialMediaModalTitle').textContent = 'Sosyal Medya Ekle';
        document.getElementById('socialMediaModalBtn').textContent = 'Ekle';

        const modal = document.getElementById('socialMediaModal');
        modal.style.position = 'fixed';
        modal.style.zIndex = '99999';
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function editSocialMedia(social) {
        const modal = document.getElementById('socialMediaModal');
        document.getElementById('socialMediaModalTitle').textContent = 'Sosyal Medya Düzenle';
        document.getElementById('socialMediaModalBtn').textContent = 'Düzenle';
        document.getElementById('socialMediaId').value = social.id;
        document.getElementById('socialMediaTitle').value = social.title;
        document.getElementById('socialMediaUrl').value = social.url;
        document.getElementById('socialMediaIcon').value = social.icon;
        modal.style.position = 'fixed';
        modal.style.zIndex = '99999';
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeSocialMediaModal() {
        const modal = document.getElementById('socialMediaModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // ESC tuşu ile kapatma
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSocialMediaModal();
        }
    });

    // Form submit işlemi
    document.getElementById('socialMediaForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('socialMediaId').value;
        const formData = {
            title: document.getElementById('socialMediaTitle').value,
            url: document.getElementById('socialMediaUrl').value,
            icon: document.getElementById('socialMediaIcon').value
        };

        try {
            const response = await fetch('../api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: id ? 'edit_social_media' : 'add_social_media',
                    id: id || undefined,
                    ...formData
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccessToast(`Sosyal medya başarıyla ${id ? 'güncellendi' : 'eklendi'}`);
                closeSocialMediaModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(data.error || 'İşlem başarısız');
            }
        } catch (error) {
            console.error('Sosyal medya işlem hatası:', error);
            SwalCustom.fire({
                title: 'Hata!',
                text: error.message || 'İşlem sırasında bir hata oluştu.',
                icon: 'error'
            });
        }
    });

    // Silme işlemi
    async function deleteSocialMedia(id) {
        try {
            const result = await SwalCustom.fire({
                title: 'Emin misiniz?',
                text: 'Bu sosyal medya bağlantısı kalıcı olarak silinecek!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet, Sil!',
                cancelButtonText: 'İptal'
            });

            if (result.isConfirmed) {
                const response = await fetch('../api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_social_media',
                        id: id
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showSuccessToast('Sosyal medya başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(data.error || 'Silme işlemi başarısız');
                }
            }
        } catch (error) {
            console.error('Sosyal medya silme hatası:', error);
            SwalCustom.fire({
                title: 'Hata!',
                text: error.message || 'Silme işlemi sırasında bir hata oluştu.',
                icon: 'error'
            });
        }
    }

    // Sosyal Medya sıralama işlemi
    document.addEventListener('DOMContentLoaded', function() {
        new Sortable(document.getElementById('socialMediaList'), {
            handle: '.fa-grip-vertical',
            animation: 150,
            ghostClass: 'bg-gray-700/50',
            onEnd: async function(evt) {
                const items = [...evt.to.children].map(el => el.dataset.id);
                try {
                    const response = await fetch('../api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'reorder_social_media',
                            order: items
                        })
                    });

                    const data = await response.json();
                    if (!data.success) {
                        throw new Error(data.error || 'Sıralama güncellenemedi');
                    }
                } catch (error) {
                    console.error('Sıralama hatası:', error);
                    Swal.fire({
                        title: 'Hata!',
                        text: error.message || 'Sıralama güncellenirken bir hata oluştu.',
                        icon: 'error',
                        background: '#1F2937',
                        color: '#fff'
                    });
                    location.reload();
                }
            }
        });
    });
</script>
<?php
require_once '../includes/footer.php';
?> 