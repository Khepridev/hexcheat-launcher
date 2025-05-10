<?php require_once '../includes/header.php'; ?>
<!-- Önemli Duyuru -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">
                Önemli Duyuru
            </h2>
            <p class="text-sm text-white/50 mt-1">Aktif duyuruyu yönetin</p>
        </div>
    </div>
    <form id="noticeForm" class="space-y-4">
        <input type="hidden" name="action" value="update_notice">
        <div class="space-y-4">
            <!-- İkon URL - Tek satır -->

            <!-- Duyuru Mesajı - Tek satır -->
            <div>
                <label class="block text-sm text-white/70 mb-2">Duyuru Mesajı</label>
                <textarea name="notice_message" rows="3"
                    class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5"><?php echo $manifest['importantNotice']['message']; ?></textarea>
            </div>

            <!-- Tarih ve Tip - Yan yana -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-white/70 mb-2">Tarih</label>
                    <input type="date" name="notice_date" 
                            value="<?php echo $manifest['importantNotice']['date']; ?>"
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm text-white/70 mb-2">Tip</label>
                    <select name="notice_type" 
                            class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5">
                        <option value="info" <?php echo $manifest['importantNotice']['type'] == 'info' ? 'selected' : ''; ?>>Bilgi</option>
                        <option value="warning" <?php echo $manifest['importantNotice']['type'] == 'warning' ? 'selected' : ''; ?>>Uyarı</option>
                        <option value="success" <?php echo $manifest['importantNotice']['type'] == 'success' ? 'selected' : ''; ?>>Başarılı</option>
                        <option value="danger" <?php echo $manifest['importantNotice']['type'] == 'danger' ? 'selected' : ''; ?>>Tehlike</option>
                        <option value="while" <?php echo $manifest['importantNotice']['type'] == 'while' ? 'selected' : ''; ?>>While</option>
                        <option value="black" <?php echo $manifest['importantNotice']['type'] == 'black' ? 'selected' : ''; ?>>Black</option>
                    </select>
                </div>
            </div>

            <!-- Duyuru Aktif - Tarih ve Tip'in altında -->
            <div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="notice_enabled" name="notice_enabled" 
                            <?php echo $manifest['importantNotice']['enabled'] == 1 ? 'checked' : ''; ?>
                            class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer 
                                peer-checked:after:translate-x-full peer-checked:after:border-white 
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all 
                                peer-checked:bg-green-500"></div>
                    <span class="ml-3 text-sm text-white/70">Duyuru Aktif</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" 
                    class="px-4 py-2 rounded-lg bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-400 transition-colors">
                Güncelle
            </button>
        </div>
    </form>
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
            title: 'text-white text-sm'
        }
    });
}

// Önemli duyuru formu
document.getElementById('noticeForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const noticeEnabled = document.getElementById('notice_enabled').checked;

    try {
        const response = await fetch('../api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'update_notice',
                message: formData.get('notice_message'),
                date: formData.get('notice_date'),
                type: formData.get('notice_type'),
                icon: formData.get('notice_icon'),
                enabled: noticeEnabled
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccessToast('Duyuru başarıyla güncellendi');
        } else {
            throw new Error(data.error || 'Güncelleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Duyuru güncelleme hatası:', error);
        Swal.fire({
            title: 'Hata!',
            text: error.message || 'Duyuru güncellenirken bir hata oluştu.',
            icon: 'error',
            background: '#111111',
            color: '#fff',
            customClass: {
                popup: 'bg-[#111111] border border-white/10',
                title: 'text-white',
                htmlContainer: 'text-white/70',
                confirmButton: 'bg-red-500/10 hover:bg-red-500/20 text-red-400 px-4 py-2 rounded-lg transition-all'
            }
        });
    }
});
</script>
<?php require_once '../includes/footer.php'; ?>