<?php require_once '../includes/header.php'; ?>

<!-- Haberler Bölümü -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Haberler
            </h2>
            <p class="text-sm text-white/50 mt-1">Haber ve duyuruları yönetin</p>
        </div>
        <button onclick="openAddNewsModal()" 
                class="bg-green-500/10 hover:bg-green-500/20 text-green-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
            <i class="fas fa-plus"></i> Yeni Haber
        </button>
    </div>
    <!-- Haber Listesi -->
    <div class="space-y-4" id="newsList">
        <?php foreach ($manifest['news'] as $news): ?>
        <div class="bg-black/30 hover:bg-black/40 border border-white/5 rounded-xl p-4 flex items-center gap-4 hover-card transition-all duration-300"
                data-id="<?php echo $news['id']; ?>">
            <i class="fas fa-grip-vertical cursor-move opacity-50 group-hover:opacity-100 text-white/50"></i>
            <div class="flex-shrink-0">
                <img src="<?php echo $news['image']; ?>" 
                        class="w-16 h-16 rounded-lg object-cover ring-1 ring-white/10">
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-white/90 truncate"><?php echo $news['title']; ?></h3>
                <p class="text-sm text-white/60 line-clamp-2"><?php echo $news['description']; ?></p>
                <div class="flex items-center gap-4 mt-2 text-xs text-white/40">
                    <span class="flex items-center gap-1">
                        <i class="fas fa-link"></i>
                        <?php echo $news['url']; ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <i class="fas fa-calendar"></i>
                        <?php echo $news['date']; ?>
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick='editNews(<?php echo json_encode($news); ?>)'
                        class="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-colors">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteNews(<?php echo $news['id']; ?>)"
                        class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 transition-colors">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- Haber Ekleme Modal -->
<div id="addNewsModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Haber Ekle</h3>
                <button onclick="closeAddNewsModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addNewsForm" onsubmit="handleAddNews(event)" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Başlık</label>
                        <input type="text" name="title" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Resim URL</label>
                        <input type="url" name="image" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                            placeholder="https://example.com/image.png">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm text-white/70 mb-2">Açıklama</label>
                        <textarea name="description" required rows="3"
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Tarih</label>
                        <input type="date" name="date" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white focus:border-blue-500/50 focus:ring-2 
                                    focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">URL</label>
                        <input type="url" name="url" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                            placeholder="https://example.com/news">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddNewsModal()"
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

<!-- Haber Düzenleme Modal -->
<div id="editNewsModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Haber Düzenle</h3>
                <button onclick="closeEditModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editNewsForm" onsubmit="handleEditNews(event)" class="p-6 space-y-4">                                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Başlık</label>
                        <input type="text" name="title" id="edit_title" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Resim URL</label>
                        <input type="url" name="image" id="edit_image" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm text-white/70 mb-2">Açıklama</label>
                        <textarea name="description" id="edit_description" required rows="3"
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                        text-white placeholder-white/30 focus:border-blue-500/50 
                                        focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Tarih</label>
                        <input type="date" name="date" id="edit_date" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white focus:border-blue-500/50 focus:ring-2 
                                    focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">URL</label>
                        <input type="url" name="url" id="edit_url" required 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                    text-white placeholder-white/30 focus:border-blue-500/50 
                                    focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <input type="hidden" name="news_id" id="edit_news_id">
                    <button type="button" onclick="closeEditModal()"
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

// Haber silme fonksiyonu - düzeltilmiş
async function deleteNews(id) {
    const result = await SwalCustom.fire({
        title: 'Emin misiniz?',
        text: 'Bu haberi silmek istediğinize emin misiniz?',
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
                    action: 'delete_news', 
                    id: id 
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                const element = document.querySelector(`[data-id="${id}"]`);
                if (element) {
                    element.remove();
                    showSuccessToast('Haber başarıyla silindi');
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                throw new Error(data.error || 'Silme işlemi başarısız');
            }
        } catch (error) {
            console.error('Haber silme hatası:', error);
            SwalCustom.fire({
                title: 'Hata!',
                text: error.message || 'Haber silinirken bir hata oluştu.',
                icon: 'error'
            });
        }
    }
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

// Silme işlemi için onay
async function confirmDelete(title, text) {
    return await SwalCustom.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal'
    });
}

// Modal işlevleri
function openAddNewsModal() {
    const modal = document.getElementById('addNewsModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddNewsModal() {
    const modal = document.getElementById('addNewsModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddNewsModal();
        closeEditModal();
    }
});

function editNews(news) {
    document.getElementById('edit_news_id').value = news.id;
    document.getElementById('edit_title').value = news.title;
    document.getElementById('edit_description').value = news.description;
    document.getElementById('edit_image').value = news.image;
    document.getElementById('edit_url').value = news.url;
    document.getElementById('edit_date').value = news.date;
    

    const modal = document.getElementById('editNewsModal');
    modal.style.position = 'fixed';
    modal.style.zIndex = '99999';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    const modal = document.getElementById('editNewsModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Form işlemleri
async function handleAddNews(event) {
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
                action: 'add_news',
                title: formData.get('title'),
                description: formData.get('description'),
                image: formData.get('image'),
                url: formData.get('url'),
                date: formData.get('date')
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessToast('Haber başarıyla eklendi');
            closeAddNewsModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.error || 'Ekleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Haber ekleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message || 'Haber eklenirken bir hata oluştu.',
            icon: 'error'
        });
    }
}

async function handleEditNews(event) {
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
                action: 'edit_news',
                news_id: formData.get('news_id'),
                title: formData.get('title'),
                description: formData.get('description'),
                image: formData.get('image'),
                url: formData.get('url'),
                date: formData.get('date')
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessToast('Haber başarıyla güncellendi');
            closeEditModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.error || 'Güncelleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Haber düzenleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message || 'Haber güncellenirken bir hata oluştu.',
            icon: 'error'
        });
    }
}

// Liste güncelleme fonksiyonları
function addNewsToList(news) {
    const newsHtml = `
        <div class="bg-black/30 hover:bg-black/40 border border-white/5 rounded-xl p-4 flex items-center gap-4 hover-card transition-all duration-300"
                data-id="${news.id}">
            <i class="fas fa-grip-vertical cursor-move opacity-50 group-hover:opacity-100 text-white/50"></i>
            <div class="flex-shrink-0">
                <img src="${news.image}" class="w-16 h-16 rounded-lg object-cover ring-1 ring-white/10">
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-white/90 truncate">${news.title}</h3>
                <p class="text-sm text-white/60 line-clamp-2">${news.description}</p>
                <div class="flex items-center gap-4 mt-2 text-xs text-white/40">
                    <span class="flex items-center gap-1">
                        <i class="fas fa-link"></i>
                        ${news.url}
                    </span>
                    <span class="flex items-center gap-1">
                        <i class="fas fa-calendar"></i>
                        ${news.date}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick='editNews(${JSON.stringify(news)})'
                        class="p-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 transition-colors">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteNews(${news.id})"
                        class="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 transition-colors">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('newsList').insertAdjacentHTML('afterbegin', newsHtml);
}

function updateNewsInList(news) {
    const newsElement = document.querySelector(`[data-id="${news.news_id}"]`);
    if (newsElement) {
        newsElement.querySelector('img').src = news.image;
        newsElement.querySelector('h3').textContent = news.title;
        newsElement.querySelector('p').textContent = news.description;
        const spans = newsElement.querySelectorAll('.text-white/40 span');
        spans[0].querySelector('span').textContent = news.url;
        spans[1].querySelector('span').textContent = news.date;
    }
}

// Sürükle-bırak işlevselliği
document.addEventListener('DOMContentLoaded', function() {
    // Haber listesi sıralaması
    new Sortable(document.getElementById('newsList'), {
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
                        action: 'reorder_news',
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