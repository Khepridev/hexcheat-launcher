<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Hata ayıklamasını kapatıp, JSON çıktı vermeden önce herhangi bir çıktı olmamasını sağlayalım
error_reporting(0);
ini_set('display_errors', 0);

// Doğru header'ları ayarlayalım
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Çıktı tamponlamasını başlatalım, herhangi bir hata mesajı çıkışını engeller
ob_start();

$manifest = getManifest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form işlemleri için kontrol
    if (isset($_POST['action']) && $_POST['action'] === 'upload_app_exe') {
        // Uygulama exe yükleme işlemi
        if (isset($_POST['app_id']) && isset($_FILES['app_exe_file']) && $_FILES['app_exe_file']['error'] === 0) {
            $appId = (int)$_POST['app_id'];
            $fileName = $_FILES['app_exe_file']['name'];
            $fileTmpName = $_FILES['app_exe_file']['tmp_name'];
            $fileSize = $_FILES['app_exe_file']['size'];
            
            // Uygulama bilgilerini manifest.json'dan al
            $appFound = false;
            $appMainPath = '';
            
            foreach ($manifest['apps'] as $app) {
                if ((int)$app['id'] === $appId) {
                    $appFound = true;
                    $appMainPath = $app['mainPath'];
                    break;
                }
            }
            
            if (!$appFound) {
                echo json_encode(['error' => 'Uygulama bulunamadı']);
                exit;
            }
            
            // Ana dizin yolunu işle
            if (strpos(basename($appMainPath), '.') !== false) {
                $appMainPath = dirname($appMainPath) . '/';
            } else if (substr($appMainPath, -1) !== '/') {
                $appMainPath .= '/';
            }
            
            // Dosya yolu ve klasörü oluştur - doğrudan ana klasöre kaydedilecek
            $relativePath = rtrim($appMainPath, '/') . '/' . $fileName;
            $uploadDir = dirname($relativePath);
            
            // Eğer klasör yoksa oluştur
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $relativePath;
            
            // Hash hesapla
            $fileHash = hash_file('sha256', $fileTmpName);
            
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Manifest.json'a dosyayı ekleyelim
                $fileUrl = Site_URL . $relativePath;
                
                // İlgili uygulamayı bulup dosyayı ekleyelim
                foreach ($manifest['apps'] as &$app) {
                    if ((int)$app['id'] === $appId) {
                        $app['files'][] = [
                            'path' => $relativePath,
                            'url' => $fileUrl,
                            'size' => $fileSize,
                            'hash' => $fileHash
                        ];
                        break;
                    }
                }
                
                // Manifest'i kaydedelim
                if (saveManifest($manifest)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Manifest.json güncellenemedi']);
                }
            } else {
                echo json_encode(['error' => 'Dosya yükleme hatası']);
            }
        } else {
            echo json_encode(['error' => 'Geçersiz dosya veya eksik parametreler']);
        }
        exit;
    }
    
    // Alt dosya yükleme işlemi
    if (isset($_POST['action']) && $_POST['action'] === 'upload_app_subfile') {
        if (isset($_POST['app_id']) && isset($_FILES['app_subfile']) && $_FILES['app_subfile']['error'] === 0) {
            $appId = (int)$_POST['app_id'];
            $fileName = $_FILES['app_subfile']['name'];
            $fileTmpName = $_FILES['app_subfile']['tmp_name'];
            $fileSize = $_FILES['app_subfile']['size'];
            $subPath = isset($_POST['sub_path']) ? trim($_POST['sub_path']) : '';
            
            // Uygulama bilgilerini manifest.json'dan al
            $appFound = false;
            $appMainPath = '';
            
            foreach ($manifest['apps'] as $app) {
                if ((int)$app['id'] === $appId) {
                    $appFound = true;
                    $appMainPath = $app['mainPath'];
                    break;
                }
            }
            
            if (!$appFound) {
                echo json_encode(['error' => 'Uygulama bulunamadı']);
                exit;
            }
            
            // Ana dizin yolunu işle
            if (strpos(basename($appMainPath), '.') !== false) {
                $appMainPath = dirname($appMainPath) . '/';
            } else if (substr($appMainPath, -1) !== '/') {
                $appMainPath .= '/';
            }
            
            // Tam dosya yolu oluştur
            if (!empty($subPath)) {
                // Eğer subPath "/" ile başlıyorsa, baştaki "/" işaretini kaldır
                if (substr($subPath, 0, 1) === '/') {
                    $subPath = substr($subPath, 1);
                }
                
                // Eğer subPath'te dosya adı yoksa, mevcut dosya adını ekle
                if (substr($subPath, -1) === '/' || !strpos($subPath, '.')) {
                    $relativePath = rtrim($appMainPath, '/') . '/' . rtrim($subPath, '/') . '/' . $fileName;
                } else {
                    $relativePath = rtrim($appMainPath, '/') . '/' . $subPath;
                }
            } else {
                // Ek yol belirtilmemişse, dosyayı doğrudan ana dizine kendi adıyla yükle
                $relativePath = rtrim($appMainPath, '/') . '/' . $fileName;
            }
            
            // Dosya yolu ve klasörü hazırla
            $physicalPath = __DIR__ . '/' . $relativePath;
            $uploadDir = dirname($physicalPath);
            
            // Eğer klasör yoksa oluştur
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Hash hesapla
            $fileHash = hash_file('sha256', $fileTmpName);
            
            if (move_uploaded_file($fileTmpName, $physicalPath)) {
                // Manifest.json'a dosyayı ekleyelim
                $fileUrl = Site_URL . $relativePath;
                
                // İlgili uygulamayı bulup dosyayı ekleyelim
                foreach ($manifest['apps'] as &$app) {
                    if ((int)$app['id'] === $appId) {
                        $app['files'][] = [
                            'path' => $relativePath,
                            'url' => $fileUrl,
                            'size' => $fileSize,
                            'hash' => $fileHash
                        ];
                        break;
                    }
                }
                
                // Manifest'i kaydedelim
                if (saveManifest($manifest)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Manifest.json güncellenemedi']);
                }
            } else {
                echo json_encode(['error' => 'Alt dosya yükleme hatası']);
            }
        } else {
            echo json_encode(['error' => 'Geçersiz dosya veya eksik parametreler']);
        }
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'delete_news':
                if (isset($data['id'])) {
                    // Haberi filtrele
                    $manifest['news'] = array_filter($manifest['news'], function($news) use ($data) {
                        return $news['id'] !== $data['id'];
                    });
                    
                    // Array'i yeniden indexle
                    $manifest['news'] = array_values($manifest['news']);
                    
                    // Kaydet ve yanıt döndür
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'ID gerekli']);
                }
                break;
            case 'add_menu_item':
                if (isset($data['title_tr']) && isset($data['title_en']) && isset($data['url'])) {
                    $newId = generateNewId('menuItems', $manifest);
                    
                    // TR menü öğesi
                    $newItemTr = [
                        'id' => $newId,
                        'title' => $data['title_tr'],
                        'url' => $data['url']
                    ];
                    
                    // EN menü öğesi
                    $newItemEn = [
                        'id' => $newId,
                        'title' => $data['title_en'],
                        'url' => $data['url']
                    ];
                    
                    // Menü öğelerini ekle
                    $manifest['translations']['tr']['menuItems'][] = $newItemTr;
                    $manifest['translations']['en']['menuItems'][] = $newItemEn;
                    
                    // Diğer diller için menü öğelerini ekle
                    if (isset($manifest['languages']) && is_array($manifest['languages'])) {
                        foreach ($manifest['languages'] as $langCode => $langName) {
                            // Eğer bu dil için başlık verilmişse onu kullan, yoksa İngilizce başlığı kullan
                            $title = isset($data['title_' . $langCode]) ? $data['title_' . $langCode] : $data['title_en'];
                            
                            // Dil için menuItems dizisi yoksa oluştur
                            if (!isset($manifest['translations'][$langCode]['menuItems']) || !is_array($manifest['translations'][$langCode]['menuItems'])) {
                                $manifest['translations'][$langCode]['menuItems'] = [];
                            }
                            
                            // Menü öğesini ekle
                            $manifest['translations'][$langCode]['menuItems'][] = [
                                'id' => $newId,
                                'title' => $title,
                                'url' => $data['url']
                            ];
                        }
                    }
                    
                    // Kaydet ve yanıt döndür
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'edit_news':
                if (isset($data['news_id'])) {
                    foreach ($manifest['news'] as &$news) {
                        if ($news['id'] == $data['news_id']) {
                            $news['title'] = $data['title'];
                            $news['description'] = $data['description'];
                            $news['image'] = $data['image'];
                            $news['url'] = $data['url'];
                            $news['date'] = $data['date'];
                            break;
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz ID']);
                }
                break;
            case 'edit_menu_item':
                if (isset($data['menu_id']) && isset($data['menu_lang'])) {
                    $menuId = (int)$data['menu_id'];
                    $menuLang = $data['menu_lang'];
                    $updated = false;
                    
                    // Debug için
                    error_log("Düzenlenecek Menü ID: " . $menuId);
                    error_log("Menü Dil: " . $menuLang);
                    
                    // Tüm menü öğelerini yazdıralım
                    error_log("TR Menü Öğeleri: " . json_encode($manifest['translations']['tr']['menuItems']));
                    error_log("EN Menü Öğeleri: " . json_encode($manifest['translations']['en']['menuItems']));

                    // Seçilen dildeki menüyü güncelle
                    foreach ($manifest['translations'][$menuLang]['menuItems'] as $key => &$item) {
                        if ((int)$item['id'] === $menuId) {
                            error_log("Menü bulundu: " . json_encode($item));
                            $manifest['translations'][$menuLang]['menuItems'][$key] = [
                                'id' => $menuId,
                                'title' => $data['title'],
                                'url' => $data['url']
                            ];
                            $updated = true;
                            break;
                        }
                    }

                    // Diğer tüm dillerdeki URL'yi güncelle
                    foreach ($manifest['translations'] as $lang => &$translation) {
                        if ($lang !== $menuLang && isset($translation['menuItems']) && is_array($translation['menuItems'])) {
                            foreach ($translation['menuItems'] as $key => &$item) {
                                if ((int)$item['id'] === $menuId) {
                                    $translation['menuItems'][$key]['url'] = $data['url'];
                                    break;
                                }
                            }
                        }
                    }

                    if ($updated) {
                        if (saveManifest($manifest)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Manifest kaydedilemedi']);
                        }
                    } else {
                        echo json_encode(['error' => 'Menü öğesi bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz parametreler']);
                }
                break;
            case 'delete_menu_item':
                if (isset($data['id']) && isset($data['lang'])) {
                    $menuId = (int)$data['id'];
                    
                    // TR menüsünden sil
                    $manifest['translations']['tr']['menuItems'] = array_values(array_filter(
                        $manifest['translations']['tr']['menuItems'],
                        function($item) use ($menuId) {
                            return (int)$item['id'] !== $menuId;
                        }
                    ));
                    
                    // EN menüsünden sil
                    $manifest['translations']['en']['menuItems'] = array_values(array_filter(
                        $manifest['translations']['en']['menuItems'],
                        function($item) use ($menuId) {
                            return (int)$item['id'] !== $menuId;
                        }
                    ));
                    
                    // Diğer dillerin menülerinden de sil
                    if (isset($manifest['languages']) && is_array($manifest['languages'])) {
                        foreach ($manifest['languages'] as $langCode => $langName) {
                            if (isset($manifest['translations'][$langCode]['menuItems']) && is_array($manifest['translations'][$langCode]['menuItems'])) {
                                $manifest['translations'][$langCode]['menuItems'] = array_values(array_filter(
                                    $manifest['translations'][$langCode]['menuItems'],
                                    function($item) use ($menuId) {
                                        return (int)$item['id'] !== $menuId;
                                    }
                                ));
                            }
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz parametreler']);
                }
                break;
            case 'delete_file':
                if (isset($data['file_id'])) {
                    $conn = dbConnect();
                    
                    // Önce dosya bilgilerini al
                    $stmt = $conn->prepare("SELECT file_url, file_type FROM files WHERE file_id = ?");
                    $stmt->bind_param("i", $data['file_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $file = $result->fetch_assoc();
                        $filePath = __DIR__ . '/' . $file['file_url'];
                        
                        // Fiziksel dosyayı sil
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        
                        // Veritabanından sil
                        $stmt = $conn->prepare("DELETE FROM files WHERE file_id = ?");
                        $stmt->bind_param("i", $data['file_id']);
                        
                        if ($stmt->execute()) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Dosya silinemedi']);
                        }
                    } else {
                        echo json_encode(['error' => 'Dosya bulunamadı']);
                    }
                    
                    $stmt->close();
                    $conn->close();
                } else {
                    echo json_encode(['error' => 'Geçersiz dosya ID']);
                }
                break;
            case 'update_menu_order':
                if (isset($data['lang']) && isset($data['items']) && is_array($data['items'])) {
                    $lang = $data['lang'];
                    $items = $data['items'];
                    
                    if (!isset($manifest['translations'][$lang])) {
                        echo json_encode(['error' => 'Geçersiz dil parametresi']);
                        exit;
                    }
                    
                    // Yeni sıralamaya göre menüyü düzenle
                    $orderedItems = [];
                    foreach ($items as $id) {
                        foreach ($manifest['translations'][$lang]['menuItems'] as $item) {
                            if ((int)$item['id'] === (int)$id) {
                                $orderedItems[] = $item;
                                break;
                            }
                        }
                    }
                    
                    // Yeni sıralamayı eksiksiz olduğundan emin ol
                    if (count($orderedItems) === count($manifest['translations'][$lang]['menuItems'])) {
                        $manifest['translations'][$lang]['menuItems'] = $orderedItems;
                        
                        if (saveManifest($manifest)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Kayıt başarısız']);
                        }
                    } else {
                        echo json_encode(['error' => 'Eksik menü öğeleri - veya yeni sıralama mevcut menü öğeleriyle eşleşmiyor']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz parametreler']);
                }
                break;
            case 'add_news':
                if (isset($data['title']) && isset($data['description']) && isset($data['image']) && isset($data['url']) && isset($data['date'])) {
                    $newNews = [
                        'id' => generateNewId('news', $manifest),
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'image' => $data['image'],
                        'date' => $data['date'],
                        'url' => $data['url']
                    ];
                    
                    array_push($manifest['news'], $newNews);
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'update_notice':
                if (isset($data['message']) && isset($data['date']) && isset($data['type'])) {
                    $manifest['importantNotice'] = [
                        'message' => $data['message'],
                        'date' => $data['date'],
                        'type' => $data['type'],
                        'enabled' => isset($data['enabled']) && $data['enabled'] === true ? 1 : 0
                    ];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'update_background':
                if (isset($data['background_mode']) && isset($data['background_image_url']) && isset($data['background_video_url'])) {
                    $manifest['background'] = [
                        'mode' => (int)$data['background_mode'],
                        'imageUrl' => $data['background_image_url'],
                        'videoUrl' => $data['background_video_url']
                    ];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'add_social_media':
                if (isset($data['title']) && isset($data['url']) && isset($data['icon'])) {
                    $newId = generateNewId('socialMedia', $manifest);
                    
                    $newSocialMedia = [
                        'id' => $newId,
                        'title' => $data['title'],
                        'url' => $data['url'],
                        'icon' => $data['icon']
                    ];
                    
                    if (!isset($manifest['socialMedia'])) {
                        $manifest['socialMedia'] = [];
                    }
                    
                    array_push($manifest['socialMedia'], $newSocialMedia);
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'edit_social_media':
                if (isset($data['id']) && isset($data['title']) && isset($data['url']) && isset($data['icon'])) {
                    $found = false;
                    foreach ($manifest['socialMedia'] as &$social) {
                        if ($social['id'] == $data['id']) {
                            $social['title'] = $data['title'];
                            $social['url'] = $data['url'];
                            $social['icon'] = $data['icon'];
                            $found = true;
                            break;
                        }
                    }
                    
                    if ($found && saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız veya ID bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'delete_social_media':
                if (isset($data['id'])) {
                    if (!isset($manifest['socialMedia'])) {
                        echo json_encode(['error' => 'Sosyal medya bulunamadı']);
                        break;
                    }
                    
                    $manifest['socialMedia'] = array_values(array_filter(
                        $manifest['socialMedia'],
                        function($item) use ($data) {
                            return $item['id'] != $data['id'];
                        }
                    ));
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'ID gerekli']);
                }
                break;
            case 'update_intervals':
                if (isset($data['news_interval']) && isset($data['notice_interval']) && 
                    isset($data['menu_interval']) && isset($data['background_interval']) && 
                    isset($data['social_media_interval']) && isset($data['files_interval'])) {
                    
                    // Mevcut ayarları koru ve yeni ayarları güncelle
                    $manifest['settings']['checkIntervals']['news'] = (int)$data['news_interval'];
                    $manifest['settings']['checkIntervals']['notice'] = (int)$data['notice_interval'];
                    $manifest['settings']['checkIntervals']['menuItems'] = (int)$data['menu_interval'];
                    $manifest['settings']['checkIntervals']['background'] = (int)$data['background_interval'];
                    $manifest['settings']['checkIntervals']['socialMedia'] = (int)$data['social_media_interval'];
                    $manifest['settings']['checkIntervals']['files'] = (int)$data['files_interval'];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'reorder_social_media':
                if (isset($data['order']) && is_array($data['order'])) {
                    $newOrder = [];
                    foreach ($data['order'] as $id) {
                        foreach ($manifest['socialMedia'] as $social) {
                            if ($social['id'] == $id) {
                                $newOrder[] = $social;
                                break;
                            }
                        }
                    }
                    
                    $manifest['socialMedia'] = $newOrder;
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz sıralama']);
                }
                break;
            case 'update_translation':
                if (isset($data['lang']) && isset($data['key']) && isset($data['value'])) {
                    $manifest['translations'][$data['lang']][$data['key']] = $data['value'];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'update_translation_array':
                if (isset($data['lang']) && isset($data['key']) && isset($data['index']) && 
                    isset($data['itemKey']) && isset($data['value'])) {
                    
                    $manifest['translations'][$data['lang']][$data['key']][$data['index']][$data['itemKey']] = $data['value'];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            
            case 'reorder_news':
                if (isset($data['order'])) {
                    $reorderedNews = [];
                    foreach ($data['order'] as $id) {
                        foreach ($manifest['news'] as $news) {
                            if ($news['id'] == $id) {
                                $reorderedNews[] = $news;
                                break;
                            }
                        }
                    }
                    
                    $manifest['news'] = $reorderedNews;
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Sıralama kaydedilemedi']);
                    }
                } else {
                    echo json_encode(['error' => 'Sıralama verisi gerekli']);
                }
                break;
            case 'update_maintenance':
                if (isset($data['enabled']) && isset($data['message']) && isset($data['url'])) {
                    $manifest['maintenance'] = [
                        'enabled' => (int)$data['enabled'],
                        'message' => [
                            'tr' => $data['message']['tr'],
                            'en' => $data['message']['en']
                        ],
                        'url' => $data['url']
                    ];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'update_logo':
                if (isset($data['url']) && isset($data['width']) && isset($data['height']) && isset($data['text'])) {
                    $manifest['logo'] = [
                        'url' => $data['url'],
                        'width' => (int)$data['width'],
                        'height' => (int)$data['height'],
                        'link' => $data['link'],
                        'text' => [
                            'enabled' => (int)$data['text']['enabled'],
                            'content' => $data['text']['content'],
                            'style' => [
                                'cssLink' => [
                                    'value' => $data['text']['style']['cssLink']['value'],
                                    'customCSS' => $data['text']['style']['cssLink']['customCSS']
                                ]
                            ]
                        ]
                    ];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
            case 'update_mp3':              
                if (isset($data['mp3Title']) && isset($data['mp3Image']) && isset($data['mp3Url'])) {
                    $manifest['mp3_player_control'] = [
                        'enabled' => isset($data['enabled']) ? (int)$data['enabled'] : 0,
                        'player' => [
                            [
                                'title' => $data['mp3Title'],
                                'image' => $data['mp3Image'], 
                                'url' => $data['mp3Url']
                            ]
                        ]
                    ];
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            // Dil yönetimi için endpoint'ler
            case 'add_translation':
                if (isset($data['key']) && isset($data['value_en']) && isset($data['value_tr'])) {
                    $key = trim($data['key']);
                    
                    // Anahtar kontrolü
                    if (isset($manifest['translations']['en'][$key]) || isset($manifest['translations']['tr'][$key])) {
                        echo json_encode(['error' => 'Bu anahtar zaten mevcut']);
                        exit;
                    }
                    
                    // İngilizce ve Türkçe çevirileri ekle
                    $manifest['translations']['en'][$key] = $data['value_en'];
                    $manifest['translations']['tr'][$key] = $data['value_tr'];
                    
                    // Diğer dillere de boş değer ekle
                    foreach ($manifest['translations'] as $lang => &$translations) {
                        if ($lang !== 'en' && $lang !== 'tr') {
                            $translations[$key] = $data['value_en']; // Diğer diller için İngilizce değeri varsayılan olarak kullan
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            case 'delete_translation':
                if (isset($data['key'])) {
                    $key = trim($data['key']);
                    
                    // Her dilden anahtarı kaldır
                    foreach ($manifest['translations'] as $lang => &$translations) {
                        if (isset($translations[$key])) {
                            unset($translations[$key]);
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Silme başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Anahtar belirtilmedi']);
                }
                break;
                
            case 'delete_translation_section':
                if (isset($data['key'])) {
                    $key = trim($data['key']);
                    
                    // Her dilden bölümü kaldır
                    foreach ($manifest['translations'] as $lang => &$translations) {
                        if (isset($translations[$key]) && is_array($translations[$key])) {
                            unset($translations[$key]);
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Silme başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Bölüm belirtilmedi']);
                }
                break;
                
            case 'delete_translation_item':
                if (isset($data['key']) && isset($data['index'])) {
                    $key = trim($data['key']);
                    $index = (int)$data['index'];
                    
                    // Her dilden öğeyi kaldır
                    foreach ($manifest['translations'] as $lang => &$translations) {
                        if (isset($translations[$key]) && is_array($translations[$key]) && isset($translations[$key][$index])) {
                            array_splice($translations[$key], $index, 1);
                            // Dizinin indekslerini yeniden düzenle
                            $translations[$key] = array_values($translations[$key]);
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Silme başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            case 'save_all_translations':
                if (isset($data['translations']) && is_array($data['translations'])) {
                    $newTranslations = $data['translations'];
                    
                    // Tüm dillerin çevirilerini güncelle
                    foreach ($newTranslations as $lang => $translations) {
                        if (isset($manifest['translations'][$lang]) && is_array($translations)) {
                            foreach ($translations as $key => $value) {
                                $manifest['translations'][$lang][$key] = $value;
                            }
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Güncelleme başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz çeviri verisi']);
                }
                break;
                
            case 'add_translation_all':
                if (isset($data['key']) && isset($data['translations']) && is_array($data['translations'])) {
                    $key = trim($data['key']);
                    
                    // Anahtar kontrolü
                    if (isset($manifest['translations']['en'][$key]) || isset($manifest['translations']['tr'][$key])) {
                        echo json_encode(['error' => 'Bu anahtar zaten mevcut']);
                        exit;
                    }
                    
                    // Tüm dillere çevirileri ekle
                    foreach ($data['translations'] as $lang => $value) {
                        if (isset($manifest['translations'][$lang])) {
                            $manifest['translations'][$lang][$key] = $value;
                        }
                    }
                    
                    // Eksik dillere çeviri ekle (varsayılan olarak İngilizce değerini kullan)
                    $defaultValue = $data['translations']['en'] ?? array_values($data['translations'])[0];
                    foreach ($manifest['translations'] as $lang => &$translations) {
                        if (!isset($data['translations'][$lang])) {
                            $translations[$key] = $defaultValue;
                        }
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            case 'add_language':
                if (isset($data['lang_code']) && isset($data['lang_name'])) {
                    $langCode = trim($data['lang_code']);
                    $langName = trim($data['lang_name']);
                    
                    // Dil kodu kontrolü
                    if (isset($manifest['languages'][$langCode]) || isset($manifest['translations'][$langCode])) {
                        echo json_encode(['error' => 'Bu dil kodu zaten mevcut']);
                        exit;
                    }
                    
                    // Languages dizisine ekle
                    $manifest['languages'][$langCode] = $langName;
                    
                    // Translations dizisine yeni dil ekle ve İngilizce çevirileri kopyala
                    if (!isset($manifest['translations'][$langCode])) {
                        $manifest['translations'][$langCode] = $manifest['translations']['en'];
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            case 'delete_language':
                if (isset($data['lang_code'])) {
                    $langCode = trim($data['lang_code']);
                    
                    // Ana diller (en, tr) silinemez
                    if ($langCode === 'en' || $langCode === 'tr') {
                        echo json_encode(['error' => 'Ana diller silinemez (en, tr)']);
                        exit;
                    }
                    
                    // Languages dizisinden kaldır
                    if (isset($manifest['languages'][$langCode])) {
                        unset($manifest['languages'][$langCode]);
                    }
                    
                    // Translations dizisinden kaldır
                    if (isset($manifest['translations'][$langCode])) {
                        unset($manifest['translations'][$langCode]);
                    }
                    
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Silme başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Dil kodu belirtilmedi']);
                }
                break;
            case 'update_registration_status':
                // Check for admin privileges
                if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz yok.']);
                    exit;
                }
                
                // Get the data from the request
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['status'])) {
                    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
                    exit;
                }
                
                $status = $data['status'];
                
                // Update the registration status in the settings table
                $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE name = 'registration_status'");
                $stmt->bind_param('s', $status);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Kayıt durumu başarıyla güncellendi.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Kayıt durumu güncellenirken bir hata oluştu.']);
                }
                break;
            case 'add_app':
                if (isset($data['name']) && isset($data['icon']) && isset($data['mainPath']) && isset($data['version'])) {
                    // Yeni ID oluştur
                    $maxId = 0;
                    if (isset($manifest['apps'])) {
                        foreach ($manifest['apps'] as $app) {
                            if ((int)$app['id'] > $maxId) {
                                $maxId = (int)$app['id'];
                            }
                        }
                    } else {
                        $manifest['apps'] = [];
                    }
                    
                    $newId = $maxId + 1;
                    
                    // Yeni uygulama oluştur
                    $newApp = [
                        'id' => $newId,
                        'name' => $data['name'],
                        'icon' => $data['icon'],
                        'mainPath' => $data['mainPath'],
                        'version' => $data['version'],
                        'files' => []
                    ];
                    
                    // Uygulamayı manifest.json'a ekle
                    $manifest['apps'][] = $newApp;
                    
                    // Kaydet ve yanıt döndür
                    if (saveManifest($manifest)) {
                        echo json_encode(['success' => true, 'id' => $newId]);
                    } else {
                        echo json_encode(['error' => 'Kayıt başarısız']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            case 'edit_app':
                if (isset($data['app_id']) && isset($data['name']) && isset($data['icon']) && isset($data['mainPath']) && isset($data['version'])) {
                    $appId = (int)$data['app_id'];
                    $appFound = false;
                    
                    // Uygulamayı bul ve güncelle
                    foreach ($manifest['apps'] as &$app) {
                        if ((int)$app['id'] === $appId) {
                            $app['name'] = $data['name'];
                            $app['icon'] = $data['icon'];
                            $app['mainPath'] = $data['mainPath'];
                            $app['version'] = $data['version'];
                            $appFound = true;
                            break;
                        }
                    }
                    
                    if ($appFound) {
                        // Kaydet ve yanıt döndür
                        if (saveManifest($manifest)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Kayıt başarısız']);
                        }
                    } else {
                        echo json_encode(['error' => 'Uygulama bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            case 'delete_app':
                if (isset($data['id'])) {
                    $appId = (int)$data['id'];
                    $appIndex = -1;
                    $appFound = false;
                    
                    // Uygulamayı bul
                    foreach ($manifest['apps'] as $index => $app) {
                        if ((int)$app['id'] === $appId) {
                            $appIndex = $index;
                            $appFound = true;
                            break;
                        }
                    }
                    
                    if ($appFound) {
                        // İlgili dosyaları diskten sil
                        if (isset($manifest['apps'][$appIndex]['files'])) {
                            foreach ($manifest['apps'][$appIndex]['files'] as $file) {
                                $filePath = __DIR__ . '/' . $file['path'];
                                if (file_exists($filePath)) {
                                    @unlink($filePath);
                                }
                            }
                        }
                        
                        // Uygulamayı manifest.json'dan kaldır
                        array_splice($manifest['apps'], $appIndex, 1);
                        
                        // Kaydet ve yanıt döndür
                        if (saveManifest($manifest)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Kayıt başarısız']);
                        }
                    } else {
                        echo json_encode(['error' => 'Uygulama bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'ID gerekli']);
                }
                break;
                
            case 'add_app_file':
                if (isset($data['app_id']) && isset($data['path']) && isset($data['url']) && isset($data['size']) && isset($data['hash'])) {
                    $appId = (int)$data['app_id'];
                    $updated = false;
                    
                    foreach ($manifest['apps'] as &$app) {
                        if ((int)$app['id'] === $appId) {
                            $app['files'][] = [
                                'path' => $data['path'],
                                'url' => $data['url'],
                                'size' => (int)$data['size'],
                                'hash' => $data['hash']
                            ];
                            $updated = true;
                            break;
                        }
                    }
                    
                    if ($updated) {
                        if (saveManifest($manifest)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Manifest kaydedilemedi']);
                        }
                    } else {
                        echo json_encode(['error' => 'Uygulama bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'Eksik parametreler']);
                }
                break;
                
            case 'delete_app_file':
                if (isset($data['app_id']) && isset($data['file_index'])) {
                    $appId = (int)$data['app_id'];
                    $fileIndex = (int)$data['file_index'];
                    $updated = false;
                    
                    foreach ($manifest['apps'] as &$app) {
                        if ((int)$app['id'] === $appId && isset($app['files'][$fileIndex])) {
                            // Dosyayı kaldır
                            array_splice($app['files'], $fileIndex, 1);
                            $updated = true;
                            break;
                        }
                    }
                    
                    if ($updated) {
                        if (saveManifest($manifest)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Manifest kaydedilemedi']);
                        }
                    } else {
                        echo json_encode(['error' => 'Uygulama veya dosya bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz parametreler']);
                }
                break;
                
            case 'edit_app_file':
                if (isset($data['app_id']) && isset($data['file_index']) && isset($data['path']) && isset($data['url']) && isset($data['size']) && isset($data['hash'])) {
                    $appId = (int)$data['app_id'];
                    $fileIndex = (int)$data['file_index'];
                    $updated = false;
                    
                    foreach ($manifest['apps'] as &$app) {
                        if ((int)$app['id'] === $appId && isset($app['files'][$fileIndex])) {
                            // Dosyayı güncelle
                            $app['files'][$fileIndex] = [
                                'path' => $data['path'],
                                'url' => $data['url'],
                                'size' => (int)$data['size'],
                                'hash' => $data['hash']
                            ];
                            $updated = true;
                            break;
                        }
                    }
                    
                    if ($updated) {
                        if (saveManifest($manifest)) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['error' => 'Manifest kaydedilemedi']);
                        }
                    } else {
                        echo json_encode(['error' => 'Uygulama veya dosya bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'Geçersiz parametreler']);
                }
                break;
            case 'add_video':
                if (isset($data['url'])) {
                    $videoUrl = $data['url'];
                    
                    // Video adını URL'den al
                    $videoName = basename($videoUrl);
                    
                    // videos.json dosyasını oku
                    $videosJson = file_get_contents('video/videos.json');
                    $videos = json_decode($videosJson, true);
                    
                    // Yeni video için ID belirle
                    $maxId = 0;
                    foreach ($videos['videos'] as $video) {
                        if ($video['id'] > $maxId) {
                            $maxId = $video['id'];
                        }
                    }
                    $newId = $maxId + 1;
                    
                    // Yeni videoyu ekle
                    $videos['videos'][] = [
                        'id' => $newId,
                        'name' => $videoName,
                        'url' => $videoUrl
                    ];
                    
                    // JSON dosyasını güncelle
                    if (file_put_contents('video/videos.json', json_encode($videos, JSON_PRETTY_PRINT))) {
                        echo json_encode(['success' => true, 'message' => 'Video başarıyla eklendi']);
                    } else {
                        echo json_encode(['error' => 'Video eklenirken bir hata oluştu']);
                    }
                } else {
                    echo json_encode(['error' => 'Video URL\'si gerekli']);
                }
                break;
            case 'delete_video':
                if (isset($data['id'])) {
                    $videoId = (int)$data['id'];
                    
                    // videos.json dosyasını oku
                    $videosJson = file_get_contents('video/videos.json');
                    $videos = json_decode($videosJson, true);
                    
                    // Videoyu bul ve sil
                    $found = false;
                    foreach ($videos['videos'] as $key => $video) {
                        if ($video['id'] === $videoId) {
                            unset($videos['videos'][$key]);
                            $found = true;
                            break;
                        }
                    }
                    
                    // Array'i yeniden indexle
                    $videos['videos'] = array_values($videos['videos']);
                    
                    if ($found) {
                        // JSON dosyasını güncelle
                        if (file_put_contents('video/videos.json', json_encode($videos, JSON_PRETTY_PRINT))) {
                            echo json_encode(['success' => true, 'message' => 'Video başarıyla silindi']);
                        } else {
                            echo json_encode(['error' => 'Video silinirken bir hata oluştu']);
                        }
                    } else {
                        echo json_encode(['error' => 'Video bulunamadı']);
                    }
                } else {
                    echo json_encode(['error' => 'Video ID\'si gerekli']);
                }
                break;
        }
    }
} 