const { app, BrowserWindow, dialog, ipcMain, Notification } = require('electron')
const path = require('path')
const Store = require('electron-store')
const axios = require('axios')
const fs = require('fs')
const crypto = require('crypto')
const { execFile } = require('child_process')
const notifier = require('node-notifier')

// Sanal makine tespiti
function isVirtualMachine() {
  try {
    const { execSync } = require('child_process');
    // Windows için GPU bilgisini kontrol et
    if (process.platform === 'win32') {
      let isVM = false;
      
      // 1. GPU Kontrolü
      const gpuInfo = execSync('wmic path win32_VideoController get name', { encoding: 'utf-8' }).toLowerCase();
      
      // Sanal makine GPU'larını tespit etmek için anahtar kelimeler
      const vmGpuKeywords = [
        "virtualbox", "vmware", "virtual", "remote", "basic", "microsoft remote", 
        "parallels", "hyper-v", "vm", "standard vga", "qemu", "citrix"
      ];
      
      for (const keyword of vmGpuKeywords) {
        if (gpuInfo.includes(keyword)) {
          console.log(`Sanal makine tespiti (GPU): TRUE (${keyword} bulundu)`);
          isVM = true;
          break;
        }
      }
      
      // 2. Sistem Modeli Kontrolü - BIOS üzerinden
      if (!isVM) {
        try {
          const systemInfo = execSync('wmic computersystem get model,manufacturer', { encoding: 'utf-8' }).toLowerCase();
          const vmSystemKeywords = ["virtualbox", "vmware", "virtual", "kvm", "qemu", "xen"];
          
          for (const keyword of vmSystemKeywords) {
            if (systemInfo.includes(keyword)) {
              console.log(`Sanal makine tespiti (System): TRUE (${keyword} bulundu)`);
              isVM = true;
              break;
            }
          }
        } catch (error) {
          console.error('Sistem modeli kontrolü hatası:', error);
        }
      }
      
      return isVM;
    }
    
    return false;
  } catch (error) {
    console.error('Sanal makine tespiti hatası:', error);
    return false;
  }
}

// Store'u başlat
Store.initRenderer()
const store = new Store()

// Sanal makine tespiti yap
const isVM = isVirtualMachine();
store.set('isVirtualMachine', isVM);

// Platform'a göre GPU ayarlarını yap
if (isVM) {
  // Sanal makinede CPU kullan
  console.log('Sanal makine tespit edildi. GPU devre dışı bırakıldı.');
  app.disableHardwareAcceleration();
  app.commandLine.appendSwitch('disable-gpu');
  app.commandLine.appendSwitch('disable-gpu-vsync');
  app.commandLine.appendSwitch('disable-software-rasterizer');
} else {
  // Normal bilgisayarda GPU kullan
  app.commandLine.appendSwitch('ignore-gpu-blacklist');
  app.commandLine.appendSwitch('enable-gpu-rasterization');
  app.commandLine.appendSwitch('enable-zero-copy');
  // Cache hatalarını önlemek için ek ayarlar
  app.commandLine.appendSwitch('disable-gpu-shader-disk-cache');
  app.commandLine.appendSwitch('disable-gpu-program-cache');
}

// Cache dizinini ayarla
const userDataPath = app.getPath('userData');
const cachePath = path.join(userDataPath, 'Cache');

// Cache dizinini oluştur
if (!fs.existsSync(cachePath)) {
  try {
    fs.mkdirSync(cachePath, { recursive: true });
  } catch (error) {
    console.error('Cache dizini oluşturma hatası:', error);
  }
}

// Cache izinlerini ayarla
try {
  fs.chmodSync(cachePath, 0o755);
} catch (error) {
  console.error('Cache izinleri ayarlama hatası:', error);
}

// App başlatılmadan önce
if (process.platform === 'win32') {
  app.setAsDefaultProtocolClient('hexcheat')
}

// Yönetici izinlerini kontrol et
if (process.platform === 'win32' && !process.env.ELECTRON_IS_DEV) {
  app.commandLine.appendSwitch('run-as-administrator')
}

// Manifest URL'yi tanımla
const MANIFEST_URL = 'http://localhost/site-lanc/manifest.json'
let mainWindow
let manifestCheckInterval = null

// Varsayılan yol ayarı
async function setupDefaultPath() {
  try {
    // Önce mevcut bir ayar var mı kontrol et
    const currentPath = store.get('installPath')
    if (currentPath) {
      return currentPath
    }
    
    // Manifest'i al
    const manifest = await fetchManifest()
    if (manifest && manifest.default_file_path) {
      // Manifest'ten varsayılan yolu kullan
      const defaultPath = manifest.default_file_path.replace(/\\\\|\\\//g, '\\')
      store.set('installPath', defaultPath)
      console.log('Varsayılan yol manifest\'ten ayarlandı:', defaultPath)
      return defaultPath
    }
    
    // Manifest'te yoksa, uygulama dizinini kullan
    const defaultPath = path.join(app.getPath('exe'), '..', 'apps')
    store.set('installPath', defaultPath)
    console.log('Varsayılan yol uygulama dizininden ayarlandı:', defaultPath)
    return defaultPath
  } catch (error) {
    console.error('Varsayılan yol ayarlama hatası:', error)
    const defaultPath = path.join(app.getPath('exe'), '..', 'apps')
    store.set('installPath', defaultPath)
    return defaultPath
  }
}

function createWindow() {
  // Windows için AppUserModelId ayarla
  if (process.platform === 'win32') {
    // Windows 10/11 için sabit AppUserModelId kullan
    app.setAppUserModelId('HexCheat.Launcher')  

  }

  // Developer Tools'u devre dışı bırak
  app.on('browser-window-created', (event, window) => {
    window.webContents.on('before-input-event', (event, input) => {
      // F12 ve CTRL+SHIFT+I tuş kombinasyonlarını engelle
      if ((input.key === 'F12') || 
          (input.control && input.shift && input.key.toLowerCase() === 'i')) {
        event.preventDefault();
      }
    });
  });

  mainWindow = new BrowserWindow({
    width: 840,
    height: 520,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
      sandbox: false,
      enableRemoteModule: true,
      devTools: false, // DevTools'u devre dışı bırak
      v8CacheOptions: 'code',
      webSecurity: false
    },
    resizable: false,
    frame: false,
    transparent: true,
    show: true // Direkt göster
  })

  // Menüyü gizle (DevTools menüsünü de kapatır)
  mainWindow.setMenu(null)

  // Sanal makine ise GPU hata mesajlarını filtrele
  const isVirtualMachine = store.get('isVirtualMachine', false);
  
  if (isVirtualMachine) {
    // GPU hata mesajlarını filtrele - sadece sanal makinede
    mainWindow.webContents.on('console-message', (e, level, message) => {
      // Tüm GPU hata mesajlarını filtrele
      if (message.includes('GPU') || 
          message.includes('gpu') || 
          message.includes('process exited') ||
          message.includes('exit_code=1') ||
          message.includes('gpu_process_host.cc') ||
          message.includes('[') && message.includes(':ERROR:')) {
        e.preventDefault(); // Bu hata mesajlarını engelle
        return;
      }
    });

    // Process çıkış olaylarını gizle - sadece sanal makinede
    process.on('uncaughtException', (err) => {
      // GPU process hatalarını engelle
      if (err.message && (
        err.message.includes('GPU process') ||
        err.message.includes('gpu_process_host')
      )) {
        // Hata mesajını yoksay
        return;
      }
      
      // Diğer kritik hataları loglama
      console.error('Uncaught Exception:', err);
    });
  }

  // Vite build çıktısını yükle
  mainWindow.loadFile('dist/index.html')
}

// Dosya kontrolü için fonksiyonu güncelleyelim
async function checkFileDetails(filePath, expectedSize, expectedHash) {
  try {
    if (!fs.existsSync(filePath)) {
      return false;
    }

    const stats = fs.statSync(filePath);
    if (stats.size !== expectedSize) {
      return false;
    }

    // Hash kontrolü
    if (expectedHash) {
      const fileHash = await calculateFileHash(filePath);
      if (fileHash !== expectedHash) {
        return false;
      }
    }

    return true;
  } catch (error) {
    console.error(`Dosya kontrol hatası: ${error.message}`);
    return false;
  }
}

// Dosya hash'ini hesaplayan fonksiyon
async function calculateFileHash(filePath) {
  return new Promise((resolve, reject) => {
    try {
      const hash = crypto.createHash('sha256')
      const stream = fs.createReadStream(filePath)

      stream.on('data', data => hash.update(data))
      stream.on('end', () => resolve(hash.digest('hex')))
      stream.on('error', error => reject(error))
    } catch (error) {
      reject(error)
    }
  })
}

// Kontrol aralıklarını ayarla
function setupIntervalChecks() {
  const manifest = store.get('manifest')
  
  // Mevcut interval'i temizle
  if (manifestCheckInterval) {
    clearInterval(manifestCheckInterval)
  }
  
  // Manifest'ten kontrol aralıklarını al (varsa)
  // Varsayılan olarak 30 saniye (30000ms)
  const defaultInterval = 30000
  
  // Kontrol aralıklarını manifest'ten al
  let checkIntervals = {
    files: defaultInterval,
    news: defaultInterval,
    notice: defaultInterval,
    background: defaultInterval,
    menuItems: defaultInterval,
    socialMedia: defaultInterval,
    mp3_player: defaultInterval
  }
  
  // Manifest içerisinde tanımlı kontrol aralıklarını kullan
  if (manifest?.settings?.checkIntervals) {
    const manifestIntervals = manifest.settings.checkIntervals
    
    // Her bir kontrol tipinin aralığını manifest'ten al
    checkIntervals = {
      files: manifestIntervals.files || defaultInterval,
      news: manifestIntervals.news || defaultInterval,
      notice: manifestIntervals.notice || defaultInterval,
      background: manifestIntervals.background || defaultInterval,
      menuItems: manifestIntervals.menuItems || defaultInterval,
      socialMedia: manifestIntervals.socialMedia || defaultInterval,
      mp3_player: manifestIntervals.mp3_player || defaultInterval
    }
  }
  
 
  // En kısa aralığı kullan (en hızlı güncelleme için)
  // Bu şekilde tüm kontroller tek bir interval ile yapılacak
  const minInterval = Math.min(
    checkIntervals.files,
    checkIntervals.news,
    checkIntervals.notice,
    checkIntervals.background,
    checkIntervals.menuItems,
    checkIntervals.socialMedia,
    checkIntervals.mp3_player
  )
    
  // Yeni interval'i ayarla
  manifestCheckInterval = setInterval(checkManifestChanges, minInterval)
}

// Windows bildirimi gösterme fonksiyonu
function showWindowsNotification(title, message, onClick) {
  try {    
    
    // Icon dosyasının yolunu belirle
    let iconPath = path.join(__dirname, '..', '..', 'build', 'icon.png')

    // Packaged app kontrolü
    if (app.isPackaged) {
      iconPath = path.join(process.resourcesPath, 'build', 'icon.png')
    }

    // Windows platformunda
    if (process.platform === 'win32') {
      try {
        // Önce Electron'un kendi Notification sınıfını kullan
          const notification = new Notification({
          title: title,
          body: message,
          icon: iconPath,
          silent: false,
          urgency: 'critical'
        })
        
        notification.on('click', () => {
          if (onClick && typeof onClick === 'function') {
            onClick()
          }
        })
        
        notification.show()
      } catch (electronErr) {
        console.error('Electron bildirimi hatası:', electronErr)
        
        // Hata durumunda node-notifier kullan
        notifier.notify(
          {
            title: title,
            message: message,
            icon: iconPath,
            sound: true,
            wait: true,
            appID: 'HexCheat.Launcher'
          },
          function(err, response) {
            if (err) {
              console.error('Node-notifier bildirimi hatası:', err)
            } else if (response === 'activate' || response === 'clicked') {
              console.log('Node-notifier bildirimine tıklandı')
              if (onClick && typeof onClick === 'function') {
                onClick()
              }
            }
          }
        )
      }
    } else {
      // Diğer platformlar için Electron bildirimi
        const notification = new Notification({
        title: title,
        body: message,
        width: 44,
        height: 44,
        icon: iconPath,
        silent: false
      })
      
      notification.on('click', () => {
        if (onClick && typeof onClick === 'function') {
          onClick()
        }
      })
      
          notification.show()
    }
  } catch (error) {
    console.error('Bildirim gönderme hatası:', error)
  }
}

// Manifest kontrolü fonksiyonunu düzenleyelim
async function checkManifestChanges() {
  try {
    // Yeni manifestı al - her zaman güncel olan
    const newManifest = await fetchManifest()
    if (!newManifest) {
      console.log('Yeni manifest alınamadı')
      return
    }

    // Mevcut manifestı al
    const currentManifest = store.get('manifest')
    const currentLang = store.get('language') || 'tr'
    const installPath = store.get('installPath')

    // İlk kez manifest yükleniyorsa bildirim gösterme
    if (!currentManifest) {
      console.log('İlk manifest yükleniyor, bildirim gösterilmeyecek')
      store.set('manifest', newManifest)
      return
    }

    // Dosya güncellemeleri kontrolü - apps.files için kontrol ekleniyor
    const oldFiles = currentManifest.files || []
    const newFiles = newManifest.files || []
    
    // Yeni veya değişmiş dosyaları tespit et
    const updatedFiles = newFiles.filter(newFile => {
      const oldFile = oldFiles.find(f => f.path === newFile.path)
      return !oldFile || oldFile.hash !== newFile.hash || oldFile.size !== newFile.size
    })

    // Apps altındaki files değişikliklerini kontrol et
    const oldApps = currentManifest.apps || []
    const newApps = newManifest.apps || []
    
    // Apps içindeki dosya değişikliklerini tespit et
    let updatedAppFiles = []
    
    // Her app için değişikliği kontrol et
    newApps.forEach(newApp => {
      const oldApp = oldApps.find(a => a.id === newApp.id)
      
      // App var mı kontrol et
      if (!oldApp) {
        // Yeni app, tüm dosyalarını güncel olarak işaretle
        updatedAppFiles = updatedAppFiles.concat(newApp.files || [])
        return
      }
      
      // App var, dosyalarını kontrol et
      const oldAppFiles = oldApp.files || []
      const newAppFiles = newApp.files || []
      
      // Değişmiş veya yeni dosyaları bul
      const changedFiles = newAppFiles.filter(newFile => {
        const oldFile = oldAppFiles.find(f => f.path === newFile.path)
        return !oldFile || oldFile.hash !== newFile.hash || oldFile.size !== newFile.size
      })
      
      // Değişmiş dosyaları listeye ekle
      updatedAppFiles = updatedAppFiles.concat(changedFiles)
    })
    
    // Diğer kontroller devam ediyor
    const oldNews = currentManifest.news || []
    const newNews = newManifest.news || []
    const newNewsItems = newNews.filter(newItem => 
      !oldNews.some(oldItem => oldItem.id === newItem.id)
    )

    // Notice değişikliklerini kontrol et
    const oldNotice = currentManifest.importantNotice || {}
    const newNotice = newManifest.importantNotice || {}
    const noticeChanged = JSON.stringify(oldNotice) !== JSON.stringify(newNotice)

    // Apps değişikliklerini kontrol et
    const appsChanged = JSON.stringify(oldApps) !== JSON.stringify(newApps)

    // Diğer değişiklikleri kontrol et
    const backgroundChanged = JSON.stringify(currentManifest.background || {}) !== JSON.stringify(newManifest.background || {})
    const logoChanged = JSON.stringify(currentManifest.logo || {}) !== JSON.stringify(newManifest.logo || {})
    const socialMediaChanged = JSON.stringify(currentManifest.socialMedia || []) !== JSON.stringify(newManifest.socialMedia || [])
    const mp3PlayerChanged = JSON.stringify(currentManifest.mp3_player_control || {}) !== JSON.stringify(newManifest.mp3_player_control || {})
    
    // MenuItems değişikliklerini kontrol et - duyuru kontrolüne benzer şekilde
    let menuItemsChanged = false
    if (currentManifest.translations && newManifest.translations) {
      // Mevcut dil için menuItems kontrolü
      const currentLangMenuItems = currentManifest.translations[currentLang]?.menuItems || []
      const newLangMenuItems = newManifest.translations[currentLang]?.menuItems || []
      menuItemsChanged = JSON.stringify(currentLangMenuItems) !== JSON.stringify(newLangMenuItems)
    }

    // Değişiklik var mı kontrol et
    const hasChanges = updatedFiles.length > 0 || 
                       updatedAppFiles.length > 0 || 
                       newNewsItems.length > 0 || 
                       noticeChanged || 
                       appsChanged ||
                       backgroundChanged || 
                       logoChanged || 
                       socialMediaChanged || 
                       mp3PlayerChanged ||
                       menuItemsChanged

    // Değişiklikler varsa detaylı logla
    if (hasChanges) {
      /*console.log('Manifest değişiklikleri tespit edildi:', {
        updatedFiles: updatedFiles.length,
        updatedAppFiles: updatedAppFiles.length,
        newNews: newNewsItems.length,
        noticeChanged,
        appsChanged,
        backgroundChanged,
        logoChanged,
        socialMediaChanged,
        mp3PlayerChanged
      })*/
    } else {
      return // Değişiklik yoksa işlemi sonlandır
    }

    // ÖNEMLİ: Yeni manifest'i store'a kaydet
    store.set('manifest', newManifest)

    if (mainWindow) {
      // Önce tüm manifest'i gönder
      mainWindow.webContents.send('manifest-updated', newManifest)
      
      // Sonra özgün update eventlerini gönder - sıralama önemli
      if (updatedFiles.length > 0) {
        mainWindow.webContents.send('files-updated', updatedFiles)
      }
      
      // Apps içindeki dosya değişikliklerini bildir
      if (updatedAppFiles.length > 0) {
        mainWindow.webContents.send('app-files-updated', updatedAppFiles)
      }
      
      if (newNewsItems.length > 0) {
        mainWindow.webContents.send('news-updated', newNews)
      }
      
      if (appsChanged) {
        mainWindow.webContents.send('apps-updated', newApps)
      }
      
      if (backgroundChanged) {
        mainWindow.webContents.send('background-updated', newManifest.background)
      }

      if (logoChanged) {
        mainWindow.webContents.send('logo-updated', newManifest.logo)
      }
      
      if (socialMediaChanged) {
        mainWindow.webContents.send('social-media-updated', newManifest.socialMedia)
      }
      
      if (mp3PlayerChanged) {
        mainWindow.webContents.send('mp3-player-updated', newManifest.mp3_player_control)
      }

      if (noticeChanged) {
        mainWindow.webContents.send('notice-updated', newManifest.importantNotice)
      }
      
      // MenuItems değişikliklerini bildir - duyuru bildirimine benzer şekilde
      if (menuItemsChanged) {
        mainWindow.webContents.send('menu-updated', { 
          translations: newManifest.translations,
          currentLang 
        })
      }
      
     
      // Şimdi bildirim ayarlarını kontrol et
      const notificationsEnabled = store.get('notificationsEnabled', true);
      const newsNotificationsEnabled = store.get('newsNotificationsEnabled', true);
      const updateNotificationsEnabled = store.get('updateNotificationsEnabled', true);
      
      
      // Ana bildirim ayarı kapalıysa bildirim gösterme
      if (!notificationsEnabled) {
        return;
      }
      
      // Bildirimler - apps files değişiklikleri öncelikli olarak bildirilecek
      
      // 1. Apps içindeki dosya güncellemeleri için bildirim
      if (updatedAppFiles.length > 0 && updateNotificationsEnabled) {
        
        // Bildirim başlığını manifest.json'dan al
        let title = newManifest?.translations?.[currentLang]?.notificationTitle || 
                    newManifest?.translations?.['tr']?.notificationTitle || 
                    'Hex Cheat';
        
        // Bildirim mesajını manifest.json'dan dinamik olarak al
        let message = '';
        
        // Önce mevcut dildeki çeviriyi kontrol et
        if (newManifest?.translations?.[currentLang]?.updateAvailable) {
          message = newManifest.translations[currentLang].updateAvailable;
        } else {
          // Mevcut dilde çeviri yoksa, mevcut tüm diller içinde uygun çeviriyi ara
          const allLanguages = Object.keys(newManifest?.translations || {});
          
          // Önce Türkçe'yi kontrol et (varsayılan dil olarak)
          if (allLanguages.includes('tr') && newManifest.translations['tr'].updateAvailable) {
            message = newManifest.translations['tr'].updateAvailable;
          } 
          // Sonra İngilizce'yi kontrol et (ikinci varsayılan dil olarak)
          else if (allLanguages.includes('en') && newManifest.translations['en'].updateAvailable) {
            message = newManifest.translations['en'].updateAvailable;
          }
          // Hala çeviri bulunamadıysa, mevcut herhangi bir dili kullan
          else {
            for (const lang of allLanguages) {
              if (newManifest.translations[lang].updateAvailable) {
                message = newManifest.translations[lang].updateAvailable;
                break;
              }
            }
          }
          
          // Hiçbir çeviri bulunamazsa varsayılan bir mesaj kullan
          if (!message) {
            message = "Update available";
          }
        }
        
        try {
          // Windows bildirimi gönder
          notifier.notify({
            title: title,            
            message: message,
            icon: path.join(__dirname, '..', '..', 'build', 'icon.png'),
            sound: true,
            wait: true,
            appID: 'HexCheat.Launcher'
          });
          
          notifier.on('click', function() {
            if (mainWindow) {
              mainWindow.show();
              mainWindow.focus();
              mainWindow.webContents.send('show-apps-page');
            }
          });
          
        } catch (error) {
          //console.error('App files bildirimi gönderme hatası:', error);
        }
      }
      
      // 2. Normal dosya güncellemeleri için bildirim
      else if (updatedFiles.length > 0 && updateNotificationsEnabled) {
        console.log('Dosya güncellemeleri bildirimi gönderiliyor:', updatedFiles.length)
        
        // Bildirim başlığını manifest.json'dan al
        let title = newManifest?.translations?.[currentLang]?.notificationTitle || 
                   newManifest?.translations?.['tr']?.notificationTitle || 
                   'Hex Cheat';
        
        // Bildirim mesajını manifest.json'dan dinamik olarak al
        let message = '';
        
        // Önce mevcut dildeki çeviriyi kontrol et
        if (newManifest?.translations?.[currentLang]?.updateAvailable) {
          message = newManifest.translations[currentLang].updateAvailable;
        } else {
          // Mevcut dilde çeviri yoksa, mevcut tüm diller içinde uygun çeviriyi ara
          const allLanguages = Object.keys(newManifest?.translations || {});
          
          // Önce Türkçe'yi kontrol et (varsayılan dil olarak)
          if (allLanguages.includes('tr') && newManifest.translations['tr'].updateAvailable) {
            message = newManifest.translations['tr'].updateAvailable;
          } 
          // Sonra İngilizce'yi kontrol et (ikinci varsayılan dil olarak)
          else if (allLanguages.includes('en') && newManifest.translations['en'].updateAvailable) {
            message = newManifest.translations['en'].updateAvailable;
          }
          // Hala çeviri bulunamadıysa, mevcut herhangi bir dili kullan
          else {
            for (const lang of allLanguages) {
              if (newManifest.translations[lang].updateAvailable) {
                message = newManifest.translations[lang].updateAvailable;
                break;
              }
            }
          }
          
          // Hiçbir çeviri bulunamazsa varsayılan bir mesaj kullan
          if (!message) {
            message = "Update available";
          }
        }
        
        try {
          // Windows bildirimi gönder
          notifier.notify({
            title: title,            
            message: message,
            icon: path.join(__dirname, '..', '..', 'build', 'icon.png'),
            sound: true,
            wait: true,
            appID: 'HexCheat.Launcher'
          });          
          
          // Bildirime tıklandığında uygulamaya yönlendir
          notifier.on('click', function() {
            if (mainWindow) {
              mainWindow.show();
              mainWindow.focus();
              mainWindow.webContents.send('show-apps-page');
            }
          });

        } catch (error) {
          //console.error('Dosya bildirimi gönderme hatası:', error);
        }
      }
      
      // 3. Haber güncellemeleri için bildirim
      if (newNewsItems.length > 0 && newsNotificationsEnabled) {
        
        // Bildirim başlığını manifest.json'dan al
        let title = newManifest?.translations?.[currentLang]?.newsNotificationTitle || 
                   newManifest?.translations?.['tr']?.newsNotificationTitle || 
                   (currentLang === 'tr' ? 'Yeni Haber!' : 'New News!');
        
        // Mesajı dile göre ayarla - her dil için uygun çeviri
        let message = '';
        if (currentLang === 'tr') {
          message = `${newNewsItems.length} yeni haber mevcut`;
        } else if (currentLang === 'de') {
          message = `${newNewsItems.length} neue Nachrichten verfügbar`;
        } else if (currentLang === 'ro') {
          message = `${newNewsItems.length} știri noi disponibile`;
        } else if (currentLang === 'it') {
          message = `${newNewsItems.length} nuove notizie disponibili`;
        } else {
          message = `${newNewsItems.length} new news available`;
        }
        
        showWindowsNotification(title, message, () => {
          if (mainWindow) {
            mainWindow.focus()
            mainWindow.webContents.send('show-home-page')
          }
        })
      }
      
      // 4. Önemli duyuru için bildirim - sadece notice değiştiğinde
      if (noticeChanged && newNotice.enabled === 1 && newsNotificationsEnabled) {
        
        // Bildirim başlığını manifest.json'dan al
        let title = newManifest?.translations?.[currentLang]?.notificationTitle || 
                   newManifest?.translations?.['tr']?.notificationTitle || 
                   'Hex Cheat';
        
        const noticeMessage = newNotice.message?.[currentLang] || 
                         newNotice.message?.['tr'] || 
                         'Önemli duyuru'
        
        showWindowsNotification(title, 
          noticeMessage.substring(0, 100) + (noticeMessage.length > 100 ? '...' : ''), 
          () => {
            if (mainWindow) {
              mainWindow.focus()
              mainWindow.webContents.send('show-home-page')
            }
          }
        )
      }      

    }
  } catch (error) {
    console.error('Manifest kontrol hatası:', error)
  }
}

// Manifest'i çekme fonksiyonunu güncelleyelim
async function fetchManifest() {
  try {
    // Her zaman sunucudan taze veri çek - önbelleği kullanma
    const response = await axios.get(MANIFEST_URL, {
      params: {
        // Önbelleği atlamak için benzersiz bir sorgu parametresi ekle
        _nocache: Date.now()
      },
      headers: {
        // Browser cache'ini atlamak için ek önlem
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0'
      },
      timeout: 3000 // Daha kısa timeout süresi - hızlı yanıt için
    })
    
    if (!response.data) {
      throw new Error('Invalid manifest')
    }
    return response.data
  } catch (error) {
    console.error('Manifest indirme hatası:', error)
    // Hata durumunda mevcut manifest'i döndür
    return store.get('manifest')
  }
}

// IPC Handlers
function setupIPCHandlers() {
  // Show apps page handler'ı ekle
  ipcMain.on('show-apps-page', (event, options = {}) => {
    if (mainWindow) {
      mainWindow.webContents.send('show-apps-page', options)
    }
  })
  
  ipcMain.handle('get-manifest', async () => {
    try {
      // Burada da önbelleği atlayan bir sorgu yapın
      const response = await axios.get(MANIFEST_URL, {
        params: {
          _nocache: Date.now()
        },
        headers: {
          // Browser cache'ini atlamak için ek önlem
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache',
          'Expires': '0'
        },
        timeout: 3000
      })
      
      if (!response.data) {
        throw new Error('Invalid manifest')
      }
      
      // Mevcut manifestle karşılaştır
      const currentManifest = store.get('manifest')
      const newManifest = response.data
      let hasChanges = false
      
      // Her iki manifest de varsa, değişiklikleri kontrol et
      if (currentManifest && newManifest) {
        // Dosya kontrolü
        const oldFiles = currentManifest.files || []
        const newFiles = newManifest.files || []
        const updatedFiles = newFiles.filter(newFile => {
          const oldFile = oldFiles.find(f => f.path === newFile.path)
          return !oldFile || oldFile.hash !== newFile.hash || oldFile.size !== newFile.size
        })
        
        // App kontrolü
        const oldApps = currentManifest.apps || [] 
        const newApps = newManifest.apps || []
        const appsChanged = JSON.stringify(oldApps) !== JSON.stringify(newApps)
        
        // Değişiklikler varsa
        if (updatedFiles.length > 0 || appsChanged) {
          hasChanges = true
          console.log('Get-manifest: Değişiklikler tespit edildi')
        }
      }
      
      // Güncel manifestı sakla
      store.set('manifest', newManifest)
      
      // Arayüze bildir - manifest değişikliği varsa
      if (hasChanges && mainWindow) {
        console.log('Get-manifest: UI güncelleniyor')
        
        // Tüm yeni manifest'i gönder
        mainWindow.webContents.send('manifest-updated', newManifest)
        
        // Ayrıca apps güncellemesini gönder
        if (newManifest.apps) {
          mainWindow.webContents.send('apps-updated', newManifest.apps)
        }
        
        // Ayrıca dosya güncellemesini gönder
        if (newManifest.files) {
          mainWindow.webContents.send('files-updated', newManifest.files)
        }
      }
      
      return newManifest
    } catch (error) {
      console.error('Manifest indirme hatası:', error)
      return store.get('manifest') // Hata durumunda mevcut manifestı döndür
    }
  })

  ipcMain.handle('get-install-path', () => {
    return store.get('installPath')
  })

  ipcMain.handle('check-file', async (event, { path: filePath, size, hash }) => {
    return await checkFileDetails(filePath, size, hash)
  })

  ipcMain.handle('download-file', async (event, { url, path: filePath, hash }) => {
    try {
      const directory = filePath.substring(0, filePath.lastIndexOf('\\'))
      if (!fs.existsSync(directory)) {
        fs.mkdirSync(directory, { recursive: true })
      }

      // İndirme işlemini başlatmadan önce URL'in geçerli olup olmadığını kontrol et
      try {
        // İlk önce HEAD isteği ile dosyanın var olup olmadığını kontrol et
        await axios({
          method: 'HEAD',
          url,
          timeout: 5000
        });
      } catch (headError) {
        console.error(`Dosya kontrol hatası (${url}):`, headError.message);
        
        if (headError.response && headError.response.status === 404) {
          // 404 hatası - dosya bulunamadı
          throw new Error(`Dosya sunucuda bulunamadı: ${url}`);
        } else {
          // Diğer hatalar
          throw new Error(`Dosya kontrolü başarısız: ${headError.message}`);
        }
      }

      // Dosya var, indir
      const response = await axios({
        url,
        method: 'GET',
        responseType: 'stream',
        timeout: 30000
      })

      const totalLength = parseInt(response.headers['content-length'], 10)
      let downloadedLength = 0

      await new Promise((resolve, reject) => {
        const writer = fs.createWriteStream(filePath)
        response.data.pipe(writer)

        response.data.on('data', (chunk) => {
          downloadedLength += chunk.length
          const progress = Math.round((downloadedLength / totalLength) * 100)
          event.sender.send('download-progress', { progress, downloaded: downloadedLength })
        })

        writer.on('finish', resolve)
        writer.on('error', reject)
      })

      // İndirilen dosyanın hash'ini kontrol et
      if (hash) {
        const fileHash = await calculateFileHash(filePath)
        if (fileHash !== hash) {
          fs.unlinkSync(filePath) // Hatalı dosyayı sil
          throw new Error('Hash doğrulama hatası')
        }
      }

      return true
    } catch (error) {
      console.error('Dosya indirme hatası:', error)
      
      // Eğer dosya oluşturulmuşsa ve hata olduysa temizle
      if (fs.existsSync(filePath)) {
        try {
          fs.unlinkSync(filePath);
        } catch (unlinkError) {
          console.error('Hatalı dosya silinirken hata:', unlinkError);
        }
      }
      
      // Kullanıcıya daha anlaşılır hata mesajı göster
      let errorMessage = 'Dosya indirme hatası';
      
      if (error.response) {
        // Sunucu cevabı ile gelen hata
        if (error.response.status === 404) {
          errorMessage = `Dosya bulunamadı (404): ${url}`;
        } else {
          errorMessage = `Sunucu hatası (${error.response.status}): ${url}`;
        }
      } else if (error.request) {
        // İstek yapıldı ama cevap alınamadı
        errorMessage = `Sunucuya ulaşılamadı: ${url}`;
      } else {
        // İstek oluşturulurken hata
        errorMessage = error.message;
      }
      
      throw new Error(errorMessage);
    }
  })

  ipcMain.handle('start-client', async (event, exePath) => {
    try {
      if (!fs.existsSync(exePath)) {
        console.error('Client bulunamadı:', exePath);
        throw new Error(`Client dosyası bulunamadı: ${exePath}`);
      }

      const workingDir = path.dirname(exePath);
      //console.log('Çalışma dizini:', workingDir);
      //console.log('Başlatılacak client:', exePath);

      return new Promise((resolve, reject) => {
        // Client'ı başlat
        const clientProcess = require('child_process').exec(`"${exePath}"`, {
          cwd: workingDir,
          windowsHide: false
        }, (error) => {
          if (error) {
            //console.error('Client başlatma hatası:', error);
            reject(error);
            return;
          }
        });

        // Hata kontrolü
        clientProcess.on('error', (err) => {
          console.error('Client başlatma hatası:', err);
          reject(err);
        });

        // Başarılı başlatma
        //console.log('Client başlatıldı');
        
        // Launcher'ı kapatma kodunu kaldırdık
        // setTimeout(() => {
        //   console.log('Launcher kapatılıyor...');
        //   app.quit();
        // }, 1000);

        resolve(true);
      });

    } catch (error) {
      console.error('Client başlatma hatası:', error);
      throw error;
    }
  })

  ipcMain.handle('select-install-path', async () => {
    const result = await dialog.showOpenDialog(mainWindow, {
      properties: ['openDirectory'],
      title: 'Client kurulum klasörünü seçin'
    });
    
    if (!result.canceled) {
      const selectedPath = result.filePaths[0];
      
      // Seçilen yol içinde apps klasörünü oluştur (app yerine apps olarak değiştirildi)
      const appsDir = path.join(selectedPath, 'apps')
      if (!fs.existsSync(appsDir)) {
        fs.mkdirSync(appsDir, { recursive: true })
      }
      
      // Yeni yolu kaydet
      store.set('installPath', selectedPath);
      //console.log('Kullanıcı yeni kurulum yolu seçti:', selectedPath);
      
      return selectedPath;
    }
    return null;
  });

  // Harici link açma handler'ı - dialog'u kaldırıyoruz
  ipcMain.handle('open-external-link', async (event, url) => {
    try {
      const { shell } = require('electron')
      await shell.openExternal(url)
    } catch (error) {
      console.error('Link açma hatası:', error)
    }
  })

  ipcMain.handle('save-manifest', async (event, manifest) => {
    try {
      store.set('manifest', manifest)
      return true
    } catch (error) {
      console.error('Manifest kaydetme hatası:', error)
      throw error
    }
  })

  ipcMain.on('toggle-devtools', () => {
    if (mainWindow) {
      mainWindow.webContents.toggleDevTools()
    }
  })

  // Güncelleme bildirimleri için handlers
  ipcMain.handle('get-update-notifications-enabled', () => {
    return store.get('updateNotificationsEnabled', true)
  })

  ipcMain.handle('toggle-update-notifications', (event, enabled) => {
    store.set('updateNotificationsEnabled', enabled)
    return enabled
  })

  // Dil değiştirme handler'ı
  ipcMain.handle('change-language', (event, lang) => {
    store.set('language', lang)
    return lang
  })

  // GPU hata mesajlarını kontrol etmek için handler
  ipcMain.handle('set-debug-mode', (event, enabled) => {
    // Hata ayıklama modunu sakla
    store.set('debugMode', enabled)
    
    // Yalnızca sanal makine ise filtre mekanizmasını yönet
    const isVirtualMachine = store.get('isVirtualMachine', false);
    
    if (isVirtualMachine) {
      if (enabled) {
        // Hata ayıklama modunda iken GPU hatalarını göster - sadece VM'de
        mainWindow.webContents.off('console-message');
      } else {
        // Hata ayıklama modu kapalıyken GPU hata mesajlarını filtrele - sadece VM'de
        mainWindow.webContents.on('console-message', (e, level, message) => {
          // Tüm GPU hata mesajlarını filtrele
          if (message.includes('GPU') || 
              message.includes('gpu') || 
              message.includes('process exited') ||
              message.includes('exit_code=1') ||
              message.includes('gpu_process_host.cc') ||
              message.includes('[') && message.includes(':ERROR:')) {
            e.preventDefault(); // Bu hata mesajlarını engelle
            return;
          }
        });
      }
    }
    
    return enabled;
  })

  // Restart handler'ı ekle
  ipcMain.on('restart-app', () => {
    try {
      app.relaunch()
      app.exit()
    } catch (error) {
      console.error('Restart hatası:', error)
    }
  })

  // App indirme handler'ı
  ipcMain.handle('download-app', async (event, { url, path: filePath, hash }) => {
    try {
      const directory = filePath.substring(0, filePath.lastIndexOf('\\'))
      if (!fs.existsSync(directory)) {
        fs.mkdirSync(directory, { recursive: true })
      }

      const response = await axios({
        url,
        method: 'GET',
        responseType: 'stream',
        timeout: 30000
      })

      const totalLength = parseInt(response.headers['content-length'], 10)
      let downloadedLength = 0

      await new Promise((resolve, reject) => {
        const writer = fs.createWriteStream(filePath)
        response.data.pipe(writer)

        response.data.on('data', (chunk) => {
          downloadedLength += chunk.length
          const progress = Math.round((downloadedLength / totalLength) * 100)
          event.sender.send('app-download-progress', { progress, downloaded: downloadedLength })
        })

        writer.on('finish', resolve)
        writer.on('error', reject)
      })

      // İndirilen dosyanın hash'ini kontrol et
      if (hash) {
        const fileHash = await calculateFileHash(filePath)
        if (fileHash !== hash) {
          fs.unlinkSync(filePath) // Hatalı dosyayı sil
          throw new Error('Hash doğrulama hatası')
        }
      }

      return true
    } catch (error) {
      console.error('App indirme hatası:', error)
      throw error
    }
  })

  // Update settings
  ipcMain.handle('update-settings', (event, settings) => {
    try {
      // Ayarları güncelle
      for (const [key, value] of Object.entries(settings)) {
        store.set(key, value)
      }
      
      // Ayarları UI'a gönder
      if (mainWindow) {
        mainWindow.webContents.send('update-settings', settings)
      }
      
      return true
    } catch (error) {
      console.error('Ayarları güncelleme hatası:', error)
      return false
    }
  })

  // Masaüstü bildirimi gösterme handler'ı
  ipcMain.handle('show-desktop-notification', async (event, { title, message }) => {
    try {
      // Windows bildirimi göster
      showWindowsNotification(title, message, () => {
        if (mainWindow) {
          mainWindow.show()
          mainWindow.focus()
        }
      })
      return true
    } catch (error) {
      console.error('Masaüstü bildirimi gösterme hatası:', error)
      return false
    }
  })

  // Bağımsız bildirim penceresi gösterme
  ipcMain.handle('show-external-notification', async (event, { title, message, duration = 3000 }) => {
    try {
      // Mevcut bildirim penceresini kapatın
      if (global.notificationWindow && !global.notificationWindow.isDestroyed()) {
        global.notificationWindow.close()
      }
      
      // Ekran boyutunu alın
      const { width, height } = require('electron').screen.getPrimaryDisplay().workAreaSize
      
      // Yeni bildirim penceresi oluşturun
      const notificationWindow = new BrowserWindow({
        width: 320,
        height: 80,
        x: width - 340, // Sağ tarafa
        y: height - 100, // Alt tarafa
        frame: false,
        transparent: true,
        alwaysOnTop: true,
        skipTaskbar: true,
        resizable: false,
        webPreferences: {
          nodeIntegration: true,
          contextIsolation: false
        }
      })
      
      // HTML içeriği oluştur
      const htmlContent = `
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="UTF-8">
          <style>
            body {
              font-family: Arial, sans-serif;
              margin: 0;
              padding: 0;
              overflow: hidden;
              background-color: transparent;
              user-select: none;
            }
            
            .notification {
              background-color: rgba(0, 0, 0, 0.85);
              border-radius: 8px;
              border: 1px solid rgba(255, 255, 255, 0.1);
              box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
              padding: 12px 16px;
              color: white;
              backdrop-filter: blur(10px);
              animation: slideIn 0.3s ease forwards;
              display: flex;
              align-items: center;
              gap: 12px;
              margin: 10px;
            }
            
            .icon {
              font-size: 24px;
              color: #4CAF50;
            }
            
            .content {
              flex: 1;
            }
            
            .title {
              font-weight: bold;
              font-size: 14px;
              margin-bottom: 4px;
              opacity: 0.9;
            }
            
            .message {
              font-size: 12px;
              opacity: 0.7;
            }
            
            @keyframes slideIn {
              from {
                transform: translateY(20px);
                opacity: 0;
              }
              to {
                transform: translateY(0);
                opacity: 1;
              }
            }
            
            @keyframes slideOut {
              from {
                transform: translateY(0);
                opacity: 1;
              }
              to {
                transform: translateY(20px);
                opacity: 0;
              }
            }
            
            .slideOut {
              animation: slideOut 0.3s ease forwards;
            }
          </style>
        </head>
        <body>
          <div class="notification" id="notification">
            <div class="icon">✓</div>
            <div class="content">
              <div class="title">${title}</div>
              <div class="message">${message}</div>
            </div>
          </div>
          
          <script>
            // Belirli bir süre sonra kapat
            setTimeout(() => {
              document.getElementById('notification').classList.add('slideOut');
              setTimeout(() => {
                window.close();
              }, 300);
            }, ${duration});
            
            // Tıklandığında kapat
            document.getElementById('notification').addEventListener('click', () => {
              document.getElementById('notification').classList.add('slideOut');
              setTimeout(() => {
                window.close();
              }, 300);
            });
          </script>
        </body>
      </html>
      `
      
      // Pencere içeriğini ayarla
      notificationWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(htmlContent)}`)
      
      // Otomatik olarak kapanır
      setTimeout(() => {
        if (!notificationWindow.isDestroyed()) {
          notificationWindow.close()
        }
      }, duration + 500)
      
      // Genel değişkene kaydet
      global.notificationWindow = notificationWindow
      
      return true
    } catch (error) {
      console.error('External bildirim hatası:', error)
      return false
    }
  })

  // Eksik olan show-notification handler'ı
  ipcMain.handle('show-notification', async (event, { title, body }) => {
    try {
      // Windows bildirimi göster
      showWindowsNotification(title, body, () => {
        if (mainWindow) {
          mainWindow.show()
          mainWindow.focus()
        }
      })
      return true
    } catch (error) {
      console.error('Bildirim gösterme hatası:', error)
      return false
    }
  })
}

ipcMain.on('minimize-window', () => {
  mainWindow.minimize()
})

ipcMain.on('close-window', () => {
  app.quit()
})

ipcMain.on('move-window', (event, { x, y }) => {
  mainWindow.setPosition(x, y)
})

// Manifest kontrolünü başlat
async function startManifestCheck() {
  try {
    // İlk manifest'i al ve kaydet
    const manifest = await fetchManifest()
    if (manifest) {
    store.set('manifest', manifest)
    }
    
    // Interval'i ayarla - hızlı güncellemeler için 3 saniyeye düşürelim
    setupIntervalChecks()
  } catch (error) {
    console.error('Manifest başlatma hatası:', error)
  }
}

app.whenReady().then(async () => {
  try {
    // AppUserModelId ayarla - erken ayarla
    if (process.platform === 'win32') {
      app.setAppUserModelId('HexCheat.Launcher')
    }
    
    // Varsayılan kurulum yolu ayarlanıyor
    await setupDefaultPath()
    
    // Manifest'i önceden yükle
    store.get('manifest')
    
    // Kernel32.dll'nin istisnalarını ele al - Windows için
    if (process.platform === 'win32') {
      process.on('exit', (code) => {
        // Sadece çıkış kodlarını logla
        if (code !== 0) {
          console.log(`Process exited with code: ${code}`);
        }
      });
    }
    
    createWindow()
    setupIPCHandlers()
    startManifestCheck()
  } catch (error) {
    console.error('Başlangıç hatası:', error)
  }
})

app.on('window-all-closed', () => {
  if (manifestCheckInterval) {
    clearInterval(manifestCheckInterval)
  }
  if (process.platform !== 'darwin') {
    app.quit()
  }
})
