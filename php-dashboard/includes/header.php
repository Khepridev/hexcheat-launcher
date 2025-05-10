<?php
// Check if config.php exists, if not redirect to installation
if (!file_exists(__DIR__ . '/config.php')) {
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/install.php");
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Oturum kontrolü
requireLogin();

// Try to get manifest, if error redirect to installation
try {
    $manifest = getManifest();
    if (!$manifest) {
        throw new Exception("Manifest file not found or invalid");
    }
    
    // Test database connection
    $conn = dbConnect();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    $conn->close();
} catch (Exception $e) {
    // Problem with configuration or database, redirect to install
    $base_path = str_replace('/includes', '', dirname($_SERVER['PHP_SELF']));
    header("Location: $base_path/install.php?error=" . urlencode($e->getMessage()));
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// URL ön eki için konuma göre ayarlama yapalım
$base_url = '';
if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
    $base_url = '../'; // pages/ klasöründeyiz, üst dizine çıkalım
} else {
    $base_url = ''; // Ana dizindeyiz
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Manifest Yönetimi</title>
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/fontawesome.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        @font-face {
            font-family: 'Devator';
            src: url('<?php echo $base_url; ?>fonts/Devator.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #0a0a0a, #111111);
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(23, 23, 23, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        .modal-backdrop {
            @apply fixed inset-0 bg-launcher-dark/90 backdrop-blur-sm flex items-center justify-center z-50;
        }

        .modal-content {
            @apply bg-launcher-light/95 backdrop-blur-md rounded-2xl p-8 w-[600px] max-w-[90%] border border-white/10 shadow-2xl;
        }

        .menu-item button {
            transition: all 0.3s ease;
        }

        .menu-item .submenu {
            transition: all 0.3s ease;
        }

        .menu-item .fa-chevron-down {
            transition: transform 0.3s ease;
        }

        .menu-item.active > button {
            background-color: rgba(225, 222, 245, 0.08);
        }

        .menu-item.active .fa-chevron-down {
            transform: rotate(180deg);
        }
        
        .active-link {
            @apply bg-blue-500/10 text-blue-400;
        }
        
        .logo-text {
            font-family: 'Devator', sans-serif;
            letter-spacing: 1px;
        }
    </style>
</head>
<body class="text-white min-h-screen pb-12">
    <!-- Header -->
    <div class="border-b border-white/10 bg-black/30 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-[1280px] mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="<?php echo Site_URL; ?>" class="no-underline">
                    <h1 class="text-2xl font-bold text-white logo-text">
                        Manifest
                    </h1>
                </a>
                <div class="flex items-center gap-4">
                    <a href="<?php echo $base_url; ?>profile.php" class="text-white/70 hover:text-white flex items-center gap-2">
                        <i class="fas fa-user"></i>
                        <span><?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Profil'; ?></span>
                    </a>
                    <a href="<?php echo $base_url; ?>logout.php" class="text-red-400 hover:text-red-300 px-3 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20">
                        <i class="fas fa-sign-out-alt mr-1"></i> Çıkış
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-[1280px] mx-auto px-4 py-8">
        <div class="flex gap-8">
            <!-- Sol Sidebar - 300px -->
            <div class="w-[300px] shrink-0">
                <div class="glass-effect rounded-2xl p-3 transition-all duration-300 sticky top-24">
                    <!-- Ana Menü -->
                    <div class="space-y-2">
                        <a href="<?php echo $base_url; ?>index.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'index.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/maintenance.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'maintenance.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-tools"></i>
                            <span>Bakım Modu</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/news.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'news.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-newspaper"></i>
                            <span>Haberler</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/notice.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'notice.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Önemli Duyuru</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/menu.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'menu.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-bars"></i>
                            <span>Menü Yönetimi</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/social.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'social.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-share-alt"></i>
                            <span>Sosyal Medya</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/apps.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'apps.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-cube"></i>
                            <span>Uygulamalar</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/files.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'files.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-file"></i>
                            <span>Dosya Yönetimi</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/intervals.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'intervals.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-clock"></i>
                            <span>Kontrol Aralıkları</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/background.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'background.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-image"></i>
                            <span>Arka Plan Ayarları</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/language.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'language.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-language"></i>
                            <span>Dil Yönetimi</span>
                        </a>                        
                        
                        <a href="<?php echo $base_url; ?>pages/logo.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'logo.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-image"></i>
                            <span>Logo Ayarları</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/mp3player.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'mp3player.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-music"></i>
                            <span>MP3 Player Kontrolü</span>
                        </a>
                        
                        <a href="<?php echo $base_url; ?>pages/users.php" class="w-full flex items-center gap-3 p-3 rounded-lg transition-all <?php echo $current_page === 'users.php' ? 'active-link' : 'text-white/70 hover:text-white hover:bg-white/5'; ?>">
                            <i class="fas fa-users"></i>
                            <span>Kullanıcı Yönetimi</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Sağ İçerik Alanı - Kalan Genişlik -->
            <div class="flex-1 space-y-8"><?php // İçerik buraya gelecek ?> 