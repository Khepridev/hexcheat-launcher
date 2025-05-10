<?php
session_start();

// Check if config.php exists, otherwise redirect to installation
if (!file_exists('includes/config.php')) {
    header('Location: install.php');
    exit;
}

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Installation success message
$install_success = isset($_SESSION['install_success']) && $_SESSION['install_success'];
if ($install_success) {
    $success = 'Kurulum başarıyla tamamlandı! Oluşturduğunuz yönetici hesabı ile giriş yapabilirsiniz.';
    unset($_SESSION['install_success']);
}

// Eğer zaten giriş yapmışsa anasayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// URL'den gelen mesajları kontrol et
$success = isset($success) ? $success : (isset($_GET['success']) ? $_GET['success'] : '');
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Giriş işlemi kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            // Veritabanı bağlantısı
            $conn = dbConnect();
            
            // SQL injection koruması için prepared statement kullan
            $stmt = $conn->prepare("SELECT user_id, user_name, user_pass, user_status, user_rank FROM users WHERE user_name = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Kullanıcı yasaklı mı kontrol et
                if ($user['user_rank'] == 1) {
                    $error = 'Bu hesap yasaklanmıştır. Yönetici ile iletişime geçin.';
                }
                // Kullanıcı durumu aktif mi kontrol et
                else if ($user['user_status'] != 1) {
                    $error = 'Hesabınız aktif değil. Lütfen yönetici ile iletişime geçin.';
                } 
                // Şifre doğru mu kontrol et
                else if (password_verify($password, $user['user_pass'])) {
                    // Giriş başarılı, session'a bilgileri kaydet
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['user_name'];
                    $_SESSION['user_rank'] = $user['user_rank'];
                    
                    // Anasayfaya yönlendir
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Geçersiz kullanıcı adı veya şifre.';
                }
            } else {
                $error = 'Geçersiz kullanıcı adı veya şifre.';
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error = 'Bir hata oluştu: ' . $e->getMessage();
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #0a0a0a, #111111);
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(23, 23, 23, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .hover-card {
            transition: all 0.3s ease;
        }
        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="glass-effect rounded-2xl p-8 transition-all duration-300">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold">Giriş Yap</h1>
                <p class="text-white/60 mt-2">Yönetim paneline erişmek için giriş yapın</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <form method="post" action="" class="space-y-6">
                <div>
                    <label class="block text-white/80 mb-2">Kullanıcı Adı</label>
                    <input type="text" name="username" required 
                           class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20"
                           placeholder="Kullanıcı adınızı girin">
                </div>

                <div>
                    <label class="block text-white/80 mb-2">Şifre</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20"
                           placeholder="Şifrenizi girin">
                </div>

                <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Giriş Yap</span>
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-white/40 text-sm">
            &copy; <?php echo date('Y'); ?> Launcher Yönetim Paneli
        </div>
    </div>
</body>
</html> 