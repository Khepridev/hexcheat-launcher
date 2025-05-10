<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if config.php exists and has valid database connection
$configExists = file_exists('includes/config.php');
$installCompleted = false;

if ($configExists) {
    // Check if we can connect to the database
    require_once 'includes/config.php';
    try {
        $conn = @dbConnect();
        if ($conn) {
            $installCompleted = true;
            // Redirect to index.php if installation is already completed
            header("Location: index.php");
            exit;
        }
    } catch (Exception $e) {
        // Config exists but can't connect to database
    }
}

// Initialize step to 1 if not set
if (!isset($_SESSION['install_step'])) {
    $_SESSION['install_step'] = 1;
}

// Process form submissions based on the current step
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step1_submit']) && $_SESSION['install_step'] == 1) {
        if (isset($_POST['agree_terms']) && $_POST['agree_terms'] == 1) {
            $_SESSION['install_step'] = 2;
        } else {
            $step1_error = 'Kuruluma devam etmek için şartları kabul etmelisiniz.';
        }
    } 
    else if (isset($_POST['step2_submit']) && $_SESSION['install_step'] == 2) {
        // Validate database connection
        $db_host = trim($_POST['db_host']);
        $db_user = trim($_POST['db_user']);
        $db_pass = $_POST['db_pass']; // Password can be empty
        $db_name = trim($_POST['db_name']);
        $site_url = trim($_POST['site_url']);
        
        if (empty($db_host)) {
            $step2_error = 'Veritabanı sunucusu boş olamaz.';
        } else if (empty($db_user)) {
            $step2_error = 'Veritabanı kullanıcı adı boş olamaz.';
        } else if (empty($db_name)) {
            $step2_error = 'Veritabanı adı boş olamaz.';
        } else if (empty($site_url)) {
            $step2_error = 'Site URL boş olamaz.';
        } else if (substr($site_url, -1) !== '/') {
            $step2_error = 'Site URL "/" ile bitmelidir.';
        } else {
            // Test the connection
            try {
                $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
                
                if ($conn->connect_error) {
                    $step2_error = 'Veritabanı bağlantı hatası: ' . $conn->connect_error;
                } else {
                    // Connection successful - save details to session
                    $_SESSION['db_config'] = [
                        'host' => $db_host,
                        'user' => $db_user,
                        'pass' => $db_pass,
                        'name' => $db_name,
                        'site_url' => $site_url
                    ];
                    
                    // Import database schema
                    $sql = file_get_contents('manifest.sql');
                    
                    // Execute multi query
                    if ($conn->multi_query($sql)) {
                        do {
                            // Get next result
                            if ($result = $conn->store_result()) {
                                $result->free();
                            }
                        } while ($conn->more_results() && $conn->next_result());
                        
                        if ($conn->error) {
                            $step2_error = 'Veritabanı şeması yüklenirken hata oluştu: ' . $conn->error;
                        } else {
                            $_SESSION['install_step'] = 3;
                        }
                    } else {
                        $step2_error = 'Veritabanı şeması yüklenemedi: ' . $conn->error;
                    }
                    
                    $conn->close();
                }
            } catch (Exception $e) {
                $step2_error = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
            }
        }
    }
    else if (isset($_POST['step3_submit']) && $_SESSION['install_step'] == 3) {
        // Create admin user
        $admin_username = trim($_POST['admin_username']);
        $admin_email = trim($_POST['admin_email']);
        $admin_password = $_POST['admin_password'];
        $admin_password_confirm = $_POST['admin_password_confirm'];
        
        if (empty($admin_username)) {
            $step3_error = 'Kullanıcı adı boş olamaz.';
        } else if (empty($admin_email)) {
            $step3_error = 'E-posta adresi boş olamaz.';
        } else if (empty($admin_password)) {
            $step3_error = 'Şifre boş olamaz.';
        } else if ($admin_password !== $admin_password_confirm) {
            $step3_error = 'Şifreler eşleşmiyor.';
        } else {
            // Connect to database with saved config
            $db_config = $_SESSION['db_config'];
            try {
                $conn = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['name']);
                
                if ($conn->connect_error) {
                    $step3_error = 'Veritabanı bağlantı hatası: ' . $conn->connect_error;
                } else {
                    // Hash the password
                    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                    
                    // Create config.php file
                    $config_content = "<?php
// Oturum zaten başlatılmamışsa başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('MANIFEST_FILE', __DIR__ . '/../manifest.json');

// Manuel olarak ayarlanabilir manifest URL'si
// Bu URL'yi launcher'lar için kullanın
define('Site_URL', '{$db_config['site_url']}');

// Veritabanı bağlantı fonksiyonu
function dbConnect() {
    \$host = '{$db_config['host']}';
    \$user = '{$db_config['user']}';
    \$pass = '{$db_config['pass']}';
    \$db = '{$db_config['name']}';
    
    \$conn = new mysqli(\$host, \$user, \$pass, \$db);
    
    if (\$conn->connect_error) {
        die(\"Veritabanı bağlantı hatası: \" . \$conn->connect_error);
    }
    
    \$conn->set_charset(\"utf8mb4\");
    return \$conn;
}

// Manifest dosyasını okuma fonksiyonu
function getManifest() {
    if (file_exists(MANIFEST_FILE)) {
        return json_decode(file_get_contents(MANIFEST_FILE), true);
    }
    return null;
}

// Manifest dosyasını kaydetme fonksiyonu
function saveManifest(\$data) {
    try {
        \$result = file_put_contents(MANIFEST_FILE, json_encode(\$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if (\$result === false) {
            error_log(\"Manifest dosyası kaydedilemedi\");
            return false;
        }
        return true;
    } catch (Exception \$e) {
        error_log(\"Manifest kaydetme hatası: \" . \$e->getMessage());
        return false;
    }
}

// Oturum kontrolü fonksiyonu
function requireLogin() {
    if (!isset(\$_SESSION['user_id'])) {
        // Determine the correct path to login.php based on current script path
        \$path = '';
        if (strpos(\$_SERVER['SCRIPT_NAME'], '/pages/') !== false) {
            \$path = '../login.php';
        } else {
            \$path = 'login.php';
        }
        
        header(\"Location: \$path\");
        exit;
    }
}
?>";

                    // Create includes directory if it doesn't exist
                    if (!is_dir('includes')) {
                        mkdir('includes', 0755, true);
                    }
                    
                    // Save config.php
                    if (file_put_contents('includes/config.php', $config_content)) {
                        // Create initial admin user
                        $stmt = $conn->prepare("INSERT INTO users (user_name, user_mail, user_pass, user_status, user_rank) VALUES (?, ?, ?, 1, 4)");
                        $stmt->bind_param("sss", $admin_username, $admin_email, $hashed_password);
                        
                        if ($stmt->execute()) {
                            $_SESSION['install_success'] = true;
                            session_destroy(); // Clear installation session
                            header("Location: login.php");
                            exit;
                        } else {
                            $step3_error = 'Yönetici kullanıcısı oluşturulamadı: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $step3_error = 'Config dosyası oluşturulamadı. Lütfen "includes" klasörünün yazma iznini kontrol edin.';
                    }
                    $conn->close();
                }
            } catch (Exception $e) {
                $step3_error = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
            }
        }
    }
}

// Get current step
$current_step = $_SESSION['install_step'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Launcher Kurulum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Devator';
            src: url('fonts/Devator.ttf') format('truetype');
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
        .hover-card {
            transition: all 0.3s ease;
        }
        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
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
                <h1 class="text-2xl font-bold text-white logo-text">
                    Manifest
                </h1>
            </div>
        </div>
    </div>

    <div class="max-w-[1280px] mx-auto px-4 py-8">
        <!-- Ana İçerik -->
        <div class="glass-effect rounded-2xl p-8 transition-all duration-300">
            <!-- Adımlar -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <!-- Adım 1 -->
                <div class="bg-black/30 rounded-xl p-6 hover-card transition-all h-full <?php echo $current_step >= 1 ? 'border border-blue-500/20' : ''; ?>">
                    <div class="flex flex-col h-full">
                        <p class="text-white/50 text-sm mb-2">Adım 1</p>
                        <div class="flex items-center mt-auto">
                            <h3 class="text-lg font-semibold">Proje Bilgileri</h3>
                            <div class="ml-auto w-12 h-12 rounded-full <?php echo $current_step >= 1 ? 'bg-blue-500/10 text-blue-400' : 'bg-white/10 text-white/40'; ?> flex items-center justify-center">
                                <?php if ($current_step > 1): ?>
                                    <i class="fas fa-check text-lg"></i>
                                <?php else: ?>
                                    <span class="font-semibold">1</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Adım 2 -->
                <div class="bg-black/30 rounded-xl p-6 hover-card transition-all h-full <?php echo $current_step >= 2 ? 'border border-blue-500/20' : ''; ?>">
                    <div class="flex flex-col h-full">
                        <p class="text-white/50 text-sm mb-2">Adım 2</p>
                        <div class="flex items-center mt-auto">
                            <h3 class="text-lg font-semibold">Veritabanı</h3>
                            <div class="ml-auto w-12 h-12 rounded-full <?php echo $current_step >= 2 ? 'bg-blue-500/10 text-blue-400' : 'bg-white/10 text-white/40'; ?> flex items-center justify-center">
                                <?php if ($current_step > 2): ?>
                                    <i class="fas fa-check text-lg"></i>
                                <?php else: ?>
                                    <span class="font-semibold">2</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Adım 3 -->
                <div class="bg-black/30 rounded-xl p-6 hover-card transition-all h-full <?php echo $current_step >= 3 ? 'border border-blue-500/20' : ''; ?>">
                    <div class="flex flex-col h-full">
                        <p class="text-white/50 text-sm mb-2">Adım 3</p>
                        <div class="flex items-center mt-auto">
                            <h3 class="text-lg font-semibold">Yönetici</h3>
                            <div class="ml-auto w-12 h-12 rounded-full <?php echo $current_step >= 3 ? 'bg-blue-500/10 text-blue-400' : 'bg-white/10 text-white/40'; ?> flex items-center justify-center">
                                <span class="font-semibold">3</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($current_step == 1): ?>
                <?php if (isset($step1_error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $step1_error; ?>
                </div>
                <?php endif; ?>

                <form method="post" action="">
                    <!-- Proje Hakkında -->
                    <div class="bg-black/30 rounded-xl p-6 hover-card transition-all mb-6">
                        <h3 class="text-lg font-semibold mb-4">Launcher Yönetim Paneli</h3>
                        <p class="text-white/60 mb-6">
                            Bu uygulama, oyun launcherları için merkezi bir yönetim paneli sunarak haber, güncelleme ve uygulama yönetimini kolaylaştırır.
                            Açık kaynak kodlu ve ücretsiz olan bu panel, json tabanlı bir yapı kullanarak launcher uygulamalarınızı kolay ve esnek bir şekilde yönetmenize olanak tanır.
                        </p>

                        <div class="flex gap-4">
                            <a href="https://github.com/Khepridev" target="_blank" 
                               class="flex items-center gap-2 px-4 py-2 bg-black/30 hover:bg-black/40 rounded-lg transition-all text-white/70 hover:text-white">
                                <i class="fab fa-github"></i>
                                <span>GitHub</span>
                            </a>
                            <a href="https://khepridev.xyz/" target="_blank"
                               class="flex items-center gap-2 px-4 py-2 bg-black/30 hover:bg-black/40 rounded-lg transition-all text-white/70 hover:text-white">
                                <i class="fas fa-globe"></i>
                                <span>Web Sitesi</span>
                            </a>
                        </div>
                    </div>

                    <!-- Kullanım Kuralları -->
                    <div class="bg-black/30 rounded-xl p-6 hover-card transition-all">
                        <h4 class="text-lg font-semibold mb-4 flex items-center gap-2">
                            <i class="fas fa-shield-alt text-blue-400"></i>
                            Kullanım Kuralları
                        </h4>

                        <div class="space-y-4">
                            <div class="bg-black/30 rounded-lg p-4 hover-card transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-blue-500/10 flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-check text-xs text-blue-400"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-white/70">Bu projenin tamamen ücretsiz olduğunu ve herhangi bir şekilde ücretli olarak satılamayacağını kabul ediyorum.</p>
                                        <a href="https://github.com/Khepridev/hex-cheat-launcher/LICENSE" target="_blank" class="text-blue-400 hover:text-blue-300 text-sm flex items-center gap-1">
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                            <span>Lisans detaylarını görüntüle</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-black/30 rounded-lg p-4 hover-card transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="w-6 h-6 rounded-full bg-blue-500/10 flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-check text-xs text-blue-400"></i>
                                    </div>
                                    <p class="text-white/70">Projenin geliştirilebilir olduğunu ve topluluğa katkıda bulunabileceğimi kabul ediyorum.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Onay Checkbox -->
                        <div class="mt-6 flex items-center gap-3">
                            <input type="checkbox" id="agree_terms" name="agree_terms" value="1" 
                                   class="w-5 h-5 bg-black/50 border-2 border-blue-500/50 rounded checked:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <label for="agree_terms" class="text-white/80 cursor-pointer select-none">
                                Yukarıdaki kullanım kurallarını kabul ediyorum
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end mt-6">
                        <button type="submit" name="step1_submit" id="nextButton" disabled
                                class="flex items-center gap-2 px-6 py-3 bg-blue-500 hover:bg-blue-600 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span>Devam Et</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($current_step == 2): ?>
                <?php if (isset($step2_error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $step2_error; ?>
                </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="bg-black/30 rounded-xl p-6 hover-card transition-all">
                        <h3 class="text-lg font-semibold mb-4">Veritabanı Yapılandırması</h3>
                        <p class="text-white/60 mb-6">Launcher paneli için MySQL veritabanı ayarlarınızı girin.</p>

                        <div class="space-y-6">
                            <div class="form-group">
                                <label class="block text-white/80 mb-2">Veritabanı Sunucusu</label>
                                <input type="text" name="db_host" value="localhost" required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <div class="form-group">
                                <label class="block text-white/80 mb-2">Veritabanı Kullanıcı Adı</label>
                                <input type="text" name="db_user" value="root" required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <div class="form-group">
                                <label class="block text-white/80 mb-2">Veritabanı Şifresi</label>
                                <input type="password" name="db_pass"
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <div class="form-group">
                                <label class="block text-white/80 mb-2">Veritabanı Adı</label>
                                <input type="text" name="db_name" required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <div class="form-group mt-6">
                                <label class="block text-white/80 mb-2">Site URL</label>
                                <input type="url" name="site_url" 
                                       value="<?php echo 'http://'.$_SERVER['HTTP_HOST'].str_replace('/install.php','',$_SERVER['PHP_SELF']).'/'; ?>"
                                       required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                                <p class="mt-2 text-yellow-400/70 text-sm">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    URL'nin sonunda "/" olmalıdır. Örnek: http://siteadi.com/
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between mt-6">
                        <button type="button" onclick="window.location.href='install.php'"
                                class="flex items-center gap-2 px-6 py-3 bg-black/30 hover:bg-black/40 rounded-lg transition-all text-white/70 hover:text-white">
                            <i class="fas fa-arrow-left"></i>
                            <span>Geri</span>
                        </button>

                        <button type="submit" name="step2_submit"
                                class="flex items-center gap-2 px-6 py-3 bg-blue-500 hover:bg-blue-600 rounded-lg transition-all">
                            <span>Devam Et</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($current_step == 3): ?>
                <?php if (isset($step3_error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $step3_error; ?>
                </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="bg-black/30 rounded-xl p-6 hover-card transition-all">
                        <h3 class="text-lg font-semibold mb-4">Yönetici Hesabı Oluşturma</h3>
                        <p class="text-white/60 mb-6">Yönetici olarak giriş yapabilmeniz için bir hesap oluşturun.</p>

                        <div class="space-y-6">
                            <div class="form-group">
                                <label class="block text-white/80 mb-2">Kullanıcı Adı</label>
                                <input type="text" name="admin_username" required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <div class="form-group">
                                <label class="block text-white/80 mb-2">E-posta Adresi</label>
                                <input type="email" name="admin_email" required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <div class="form-group">
                                <label class="block text-white/80 mb-2">Şifre</label>
                                <input type="password" name="admin_password" required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>

                            <div class="form-group">
                                <label class="block text-white/80 mb-2">Şifre (Tekrar)</label>
                                <input type="password" name="admin_password_confirm" required
                                       class="w-full px-4 py-3 bg-black/30 border border-white/10 rounded-lg text-white/90 focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20">
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between mt-6">
                        <button type="button" onclick="history.back()"
                                class="flex items-center gap-2 px-6 py-3 bg-black/30 hover:bg-black/40 rounded-lg transition-all text-white/70 hover:text-white">
                            <i class="fas fa-arrow-left"></i>
                            <span>Geri</span>
                        </button>

                        <button type="submit" name="step3_submit"
                                class="flex items-center gap-2 px-6 py-3 bg-blue-500 hover:bg-blue-600 rounded-lg transition-all">
                            <span>Kurulumu Tamamla</span>
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-white/10 text-center text-white/40 text-sm">
                &copy; <?php echo date('Y'); ?> Launcher Yönetim Paneli - Tüm hakları saklıdır.
            </div>
        </div>
    </div>

    <script>
        document.getElementById('agree_terms')?.addEventListener('change', function() {
        
            document.getElementById('nextButton').disabled = !this.checked;
        });
    </script>
</body>
</html> 