<?php
// AJAX isteklerini işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // JSON verisini al
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if ($data && isset($data['action'])) {
        require_once '../includes/config.php';
        require_once '../includes/functions.php';
        
        header('Content-Type: application/json');
        $response = ['success' => false];
        
        try {
            $conn = dbConnect();
            
            if ($data['action'] === 'add_user') {
                $username = trim($data['username']);
                $email = trim($data['email']);
                $password = $data['password'];
                $status = (int)$data['status'];
                $rank = (int)$data['rank'];
                
                // E-posta ve kullanıcı adı kontrolü
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_name = ? OR user_mail = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $response['error'] = "Bu kullanıcı adı veya e-posta adresi zaten kullanımda.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (user_name, user_mail, user_pass, user_status, user_rank) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssii", $username, $email, $hashed_password, $status, $rank);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = "Kullanıcı başarıyla eklendi.";
                    } else {
                        $response['error'] = "Kullanıcı eklenirken bir hata oluştu.";
                    }
                }
            }
            else if ($data['action'] === 'edit_user') {
                $user_id = (int)$data['user_id'];
                $username = trim($data['username']);
                $email = trim($data['email']);
                $password = $data['password'];
                $status = (int)$data['status'];
                $rank = (int)$data['rank'];
                
                // Mevcut kullanıcıyı kontrol et
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE (user_name = ? OR user_mail = ?) AND user_id != ?");
                $stmt->bind_param("ssi", $username, $email, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $response['error'] = "Bu kullanıcı adı veya e-posta adresi başka bir kullanıcı tarafından kullanılıyor.";
                } else {
                    if (!empty($password)) {
                        // Şifre değişikliği varsa
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET user_name = ?, user_mail = ?, user_pass = ?, user_status = ?, user_rank = ? WHERE user_id = ?");
                        $stmt->bind_param("sssiii", $username, $email, $hashed_password, $status, $rank, $user_id);
                    } else {
                        // Şifre değişikliği yoksa
                        $stmt = $conn->prepare("UPDATE users SET user_name = ?, user_mail = ?, user_status = ?, user_rank = ? WHERE user_id = ?");
                        $stmt->bind_param("ssiii", $username, $email, $status, $rank, $user_id);
                    }
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = "Kullanıcı başarıyla güncellendi.";
                    } else {
                        $response['error'] = "Kullanıcı güncellenirken bir hata oluştu.";
                    }
                }
            }
            
            $conn->close();
        } catch (Exception $e) {
            $response['error'] = "İşlem sırasında bir hata oluştu: " . $e->getMessage();
        }
        
        echo json_encode($response);
        exit;
    }
}

// Normal sayfa yüklemesi için
require_once '../includes/header.php';

// Kullanıcıları listele
$conn = dbConnect();
$users = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
?>

<!-- Ana İçerik -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-xl font-semibold text-white">Kullanıcı Yönetimi</h2>
            <p class="text-sm text-white/50 mt-1">Sistem kullanıcılarını yönetin</p>
        </div>
        <button onclick="openAddUserModal()" class="bg-green-500/10 hover:bg-green-500/20 text-green-400 px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
            <i class="fas fa-plus"></i> Yeni Kullanıcı
        </button>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
    <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
    </div>
    <?php endif; ?>

    <!-- Kullanıcı Listesi -->
    <div class="bg-black/30 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="px-6 py-4 text-left text-white/60 font-medium">ID</th>
                    <th class="px-6 py-4 text-left text-white/60 font-medium">Kullanıcı Adı</th>
                    <th class="px-6 py-4 text-left text-white/60 font-medium">E-posta</th>
                    <th class="px-6 py-4 text-left text-white/60 font-medium">Durum</th>
                    <th class="px-6 py-4 text-left text-white/60 font-medium">Yetki</th>
                    <th class="px-6 py-4 text-left text-white/60 font-medium">Kayıt Tarihi</th>
                    <th class="px-6 py-4 text-left text-white/60 font-medium">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr class="border-b border-white/10 hover:bg-white/5">
                    <td class="px-6 py-4"><?php echo $user['user_id']; ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['user_name']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($user['user_mail']); ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs <?php echo $user['user_status'] ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'; ?>">
                            <?php echo $user['user_status'] ? 'Aktif' : 'Pasif'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                        $rank_badges = [
                            1 => '<span class="px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-400">Yasaklı</span>',
                            2 => '<span class="px-2 py-1 rounded-full text-xs bg-blue-500/10 text-blue-400">Moderatör</span>',
                            4 => '<span class="px-2 py-1 rounded-full text-xs bg-amber-500/10 text-amber-400">Admin</span>'
                        ];
                        echo $rank_badges[$user['user_rank']] ?? 'Bilinmiyor';
                        ?>
                    </td>
                    <td class="px-6 py-4 text-white/60"><?php echo date('d.m.Y H:i', strtotime($user['user_date'])); ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <button onclick='editUser(<?php echo json_encode($user); ?>)' 
                                    class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteUser(<?php echo $user['user_id']; ?>)"
                                    class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-all">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Kullanıcı Ekleme Modal -->
<div id="addUserModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Yeni Kullanıcı Ekle</h3>
                <button onclick="closeAddUserModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addUserForm" onsubmit="handleAddUser(event)" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Kullanıcı Adı</label>
                        <input type="text" name="username" required
                               class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                      text-white placeholder-white/30 focus:border-blue-500/50 
                                      focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">E-posta Adresi</label>
                        <input type="email" name="email" required
                               class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                      text-white placeholder-white/30 focus:border-blue-500/50 
                                      focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Şifre</label>
                        <input type="password" name="password" required
                               class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                      text-white placeholder-white/30 focus:border-blue-500/50 
                                      focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Durum</label>
                        <select name="status" required
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                       text-white focus:border-blue-500/50 focus:ring-2 
                                       focus:ring-blue-500/20 transition-all outline-none">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm text-white/70 mb-2">Yetki</label>
                        <select name="rank" required
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                       text-white focus:border-blue-500/50 focus:ring-2 
                                       focus:ring-blue-500/20 transition-all outline-none">
                            <option value="1">Yasaklı</option>
                            <option value="2">Moderatör</option>
                            <option value="4">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddUserModal()"
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

<!-- Kullanıcı Düzenleme Modal -->
<div id="editUserModal" class="hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50"></div>
    
    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="relative bg-[#111111] w-full max-w-xl rounded-lg border border-white/10">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Kullanıcı Düzenle</h3>
                <button onclick="closeEditModal()" class="text-white/50 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editUserForm" onsubmit="handleEditUser(event)" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Kullanıcı Adı</label>
                        <input type="text" name="username" id="edit_username" required
                               class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                      text-white placeholder-white/30 focus:border-blue-500/50 
                                      focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">E-posta Adresi</label>
                        <input type="email" name="email" id="edit_email" required
                               class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                      text-white placeholder-white/30 focus:border-blue-500/50 
                                      focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Şifre (Boş bırakılırsa değişmez)</label>
                        <input type="password" name="password"
                               class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                      text-white placeholder-white/30 focus:border-blue-500/50 
                                      focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-white/70 mb-2">Durum</label>
                        <select name="status" id="edit_status" required
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                       text-white focus:border-blue-500/50 focus:ring-2 
                                       focus:ring-blue-500/20 transition-all outline-none">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm text-white/70 mb-2">Yetki</label>
                        <select name="rank" id="edit_rank" required
                                class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                       text-white focus:border-blue-500/50 focus:ring-2 
                                       focus:ring-blue-500/20 transition-all outline-none">
                            <option value="1">Yasaklı</option>
                            <option value="2">Moderatör</option>
                            <option value="4">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <input type="hidden" name="user_id" id="edit_user_id">
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

function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openEditModal() {
    const modal = document.getElementById('editUserModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    const modal = document.getElementById('editUserModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

async function handleAddUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {
        action: 'add_user',
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password'),
        status: formData.get('status'),
        rank: formData.get('rank')
    };

    try {
        const response = await fetch('users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessToast(result.message);
            closeAddUserModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.error || 'Ekleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Kullanıcı ekleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message || 'Kullanıcı eklenirken bir hata oluştu.',
            icon: 'error'
        });
    }
}

async function handleEditUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = {
        action: 'edit_user',
        user_id: formData.get('user_id'),
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password'),
        status: formData.get('status'),
        rank: formData.get('rank')
    };

    try {
        const response = await fetch('users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessToast(result.message);
            closeEditModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(result.error || 'Güncelleme işlemi başarısız');
        }
    } catch (error) {
        console.error('Kullanıcı düzenleme hatası:', error);
        SwalCustom.fire({
            title: 'Hata!',
            text: error.message || 'Kullanıcı güncellenirken bir hata oluştu.',
            icon: 'error'
        });
    }
}

function editUser(user) {
    // Form alanlarını doldur
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_username').value = user.user_name;
    document.getElementById('edit_email').value = user.user_mail;
    document.getElementById('edit_status').value = user.user_status;
    document.getElementById('edit_rank').value = user.user_rank;
    
    // Modalı aç
    openEditModal();
}

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

function deleteUser(userId) {
    SwalCustom.fire({
        title: 'Emin misiniz?',
        text: "Bu kullanıcıyı silmek istediğinize emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, Sil',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // AJAX ile silme işlemi yapılabilir
            alert('Silme özelliği yakında eklenecek.');
        }
    });
}

// Modal dışına tıklandığında kapatma
document.getElementById('addUserModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddUserModal();
    }
});

document.getElementById('editUserModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddUserModal();
        closeEditModal();
    }
});
</script>

<?php
require_once '../includes/footer.php';
?> 