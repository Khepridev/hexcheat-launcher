<?php
require_once 'includes/header.php';

// Kullanıcı bilgilerini al
$conn = dbConnect();
$userData = null;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT user_id, user_name, user_mail, user_date FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    }
    
    $stmt->close();
}

// Kullanıcı profil bilgilerini güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $username = $_POST['username'] ?? '';
    
    // Boş alan kontrolü
    if (empty($username)) {
        $error = "Kullanıcı adı boş bırakılamaz!";
    }
    else {
        // Kullanıcı adı benzersiz mi kontrol et
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_name = ? AND user_id != ?");
        $stmt->bind_param("si", $username, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Bu kullanıcı adı zaten kullanılmaktadır!";
        } else {
            // Profil bilgilerini güncelle
            $updateStmt = $conn->prepare("UPDATE users SET user_name = ? WHERE user_id = ?");
            $updateStmt->bind_param("si", $username, $userId);
            
            if ($updateStmt->execute()) {
                $success = "Kullanıcı adınız başarıyla güncellendi.";
                // Kullanıcı bilgilerini yeniden yükle
                $userData['user_name'] = $username;
            } else {
                $error = "Profil güncellenirken bir hata oluştu: " . $conn->error;
            }
            
            $updateStmt->close();
        }
        
        $stmt->close();
    }
}

// Şifre değiştirme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Boş alan kontrolü
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "Tüm alanları doldurunuz!";
    }
    // Şifre eşleşme kontrolü
    elseif ($newPassword !== $confirmPassword) {
        $error = "Yeni şifreler eşleşmiyor!";
    }
    // Şifre uzunluğu kontrolü
    elseif (strlen($newPassword) < 6) {
        $error = "Şifre en az 6 karakter olmalıdır!";
    }
    else {
        // Mevcut şifreyi kontrol et
        $stmt = $conn->prepare("SELECT user_pass FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($currentPassword, $user['user_pass'])) {
                // Yeni şifreyi hash'le
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Şifreyi güncelle
                $updateStmt = $conn->prepare("UPDATE users SET user_pass = ? WHERE user_id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $userId);
                
                if ($updateStmt->execute()) {
                    $success = "Şifreniz başarıyla güncellendi.";
                } else {
                    $error = "Şifre güncellenirken bir hata oluştu: " . $conn->error;
                }
                
                $updateStmt->close();
            } else {
                $error = "Mevcut şifreniz doğru değil!";
            }
        } else {
            $error = "Kullanıcı bulunamadı!";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!-- Kullanıcı Profili -->
<div class="glass-effect rounded-2xl p-8 transition-all duration-300">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-white">Kullanıcı Profili</h2>
            <p class="text-sm text-white/50 mt-1">Hesap bilgilerinizi görüntüleyin ve güncelleyin</p>
        </div>
    </div>
    
    <?php if (isset($success)): ?>
    <div class="mb-6 p-4 bg-green-500/10 text-green-400 rounded-lg">
        <?php echo $success; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="mb-6 p-4 bg-red-500/10 text-red-400 rounded-lg">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($userData): ?>
    
    <!-- Profil Özeti -->
    <div class="bg-black/20 rounded-lg p-6 mb-8">
        <div class="flex-1">
            <h3 class="text-2xl font-semibold"><?php echo htmlspecialchars($userData['user_name']); ?></h3>
            <p class="text-white/60"><?php echo htmlspecialchars($userData['user_mail']); ?></p>
            <p class="text-white/40 text-sm mt-1">
                <i class="far fa-calendar-alt mr-1"></i> 
                <?php echo date('d.m.Y H:i', strtotime($userData['user_date'])); ?> tarihinde katıldı
            </p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Profil Bilgileri Güncelleme -->
        <div class="bg-black/20 rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">Kullanıcı Adı Değiştir</h3>
            <form method="post" class="space-y-4">
                <input type="hidden" name="action" value="update_profile">
                
                <div>
                    <label for="username" class="block text-white/70 mb-2">Kullanıcı Adı</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo htmlspecialchars($userData['user_name']); ?>"
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                  text-white focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-2.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all">
                        <i class="fas fa-save mr-2"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    
        <!-- Şifre Değiştir -->
        <div class="bg-black/20 rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4">Şifre Değiştir</h3>
            <form method="post" class="space-y-4">
                <input type="hidden" name="action" value="change_password">
                
                <div>
                    <label for="current_password" class="block text-white/70 mb-2">Mevcut Şifre</label>
                    <input type="password" id="current_password" name="current_password" required
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                  text-white focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                </div>
                
                <div>
                    <label for="new_password" class="block text-white/70 mb-2">Yeni Şifre</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6"
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                  text-white focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-white/70 mb-2">Yeni Şifre Tekrar</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                           class="w-full bg-black/30 border border-white/10 rounded-lg px-4 py-2.5 
                                  text-white focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-2.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-all">
                        <i class="fas fa-lock mr-2"></i> Şifreyi Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php else: ?>
        <div class="p-4 bg-yellow-500/10 text-yellow-400 rounded-lg">
            Kullanıcı bilgileri yüklenemedi.
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?> 