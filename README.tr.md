# HexCheat Launcher

<p align="center">
  <img src="image/logo.png" width="80" height="80" alt="HexCheat Launcher Logo" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-v1.0.0-blue.svg" alt="Version">
  <a href="https://github.com/Khepridev/hexcheat-launcher/LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License">
  </a>
  <br>
  <a href="https://khepridev.xyz/">
    <img src="image/web-site.png" alt="WebSite"> khepridev.xyz
  </a>
  <a href="https://discord.gg/3GUgRzDpXM">
    <img src="image/discord.png" alt="Discord"> Discord
  </a>
  <br>
  <a href="README.md">🌏 English</a>
</p>

---

Modern, hafif, Electron tabanlı uygulama başlatıcı ve güncelleyici. HexCheat Launcher, birden fazla uygulamayı yönetmek, otomatik güncellemeleri almak ve bildirimleri takip etmek için kullanıcı dostu bir arayüz sunar.

> **Not:** Bu proje %80 Cursor AI ile yapıldı, kritik yerlerde veya müdahale gerektiği yerlerde manuel katkı sağlandı.

---

## 🚀 Özellikler

HexCheat Launcher, deneyiminizi geliştirmek için geniş bir özellik yelpazesi sunar:

- 🎵 **Müzik Çalma**: Sevdiğiniz şarkıları doğrudan launcher üzerinden dinleyin.
- 📂 **Dosya Yönetimi**: Dosyalarınızı kolayca düzenleyin ve yönetin.
- 📰 **Haber Güncellemeleri**: En son güncellemeler ve haberlerle haberdar olun.
- 🔄 **Otomatik Güncellemeler**: Uygulama güncellemelerini otomatik olarak indirir ve kurar.
- 🌐 **Çoklu Dil Desteği**: Türkçe, İngilizce, Almanca, Romence ve İtalyanca dahil.
- 🛠️ **Uygulama Yönetimi**: Tüm uygulamalarınızı tek bir yerden kolayca yönetin.
- 🔔 **Bildirim Sistemi**: Güncellemeler ve haberler için bildirim alın.
- 🎨 **Modern Arayüz**: Güzel ve duyarlı tasarım.
- ✅ **Hash Doğrulama**: İndirilen dosyaların bütünlüğünü garantiler.
- 🖼️ **Arkaplan Özelleştirme**: Hem resim hem de video arkaplan desteği.

### Ön Gereksinimler

- Node.js 14+ ve npm 7+
- Git

## 📂 Proje Yapısı

```
launcher/
├── 📂 build/              # Derleme kaynakları
├── 📂 dist/               # Derlenmiş dosyalar
├── 📂 src/
│   ├── 📂 main/           # Ana işlem kodu
│   ├── 📂 renderer/       # İşleyici işlem kodu
│   └── 📂 assets/         # Varlıklar (resimler, fontlar, vb.)
├── 📄 package.json
└── 📝 README.md
```

## ⚙️ Yapılandırma

Launcher, uygulama güncellemelerini ve yapılandırmalarını yönetmek için manifest.json dosyasını kullanır. Manifest yapısı şunları içerir:

- `version`: Launcher sürümü
- `files`: Yollar, URL'ler, boyutlar ve hash değerleri ile dosya nesneleri dizisi
- `apps`: Metadata bilgileriyle mevcut uygulamalar
- `translations`: Çok dil desteği
- `settings`: Uygulama ayarları

## Kurulum

Normal bir kurulum için:
```bash
# Bağımlılıkları yükleme
npm install

# Uygulamayı başlatın
npm start
```

Sorun yaşarsanız veya temiz bir kurulum gerekirse:
```powershell
# Temiz kurulum (PowerShell)
Remove-Item -Recurse -Force node_modules
Remove-Item -Recurse -Force dist
Remove-Item -Recurse -Force release-builds
Remove-Item package-lock.json
npm install
npm run package
```

## 🛠️ Kaynaktan Derleme

### Ön Gereksinimler

- [Node.js](https://nodejs.org/) 14+ ve npm 7+
- [Git](https://git-scm.com/)
- [NSIS](https://nsis.sourceforge.io/Download): Kurulum dosyasını oluşturmak için gereklidir.

### Adımlar

```bash
# Depoyu klonlayın
git clone https://github.com/hexlob/launcher.git
cd launcher

# Bağımlılıkları yükleyin
npm install

# Uygulamayı başlatın
npm start
```

📦 Temiz bir kurulum için:

```powershell
# Temiz kurulum (PowerShell)
Remove-Item -Recurse -Force node_modules
Remove-Item -Recurse -Force dist
Remove-Item -Recurse -Force release-builds
Remove-Item package-lock.json
npm install
npm run package
```

📚 Kurulum dosyasını oluşturmak için:

1. [NSIS](https://nsis.sourceforge.io/Download)'i yükleyin.
2. `Installation` klasörüne gidin.
3. `exe_setup.nsi` dosyasını NSIS ile açın ve derleyerek yükleyici oluşturun.

---

## 📹 Kurulum Videosu

Kurulum sürecinin adım adım anlatıldığı video rehberimizi izleyin:

[![Installation Video](https://github.com/Khepridev/hexcheat-launcher/blob/main/image/cover.png)](https://www.youtube.com/watch?v=haDMjpF03T8)

---

## 🌐 Manifest Yönetim Paneli

Manifest dosyası, uygulamalar, çeviriler ve medya dosyalarını yönetmek için PHP tabanlı bir kontrol paneli içerir. Kontrol paneline `php-dashboard` üzerinden erişilebilir.

## 📜 Lisans

Bu proje, yaratıcının atfını korumaya yönelik özel hükümler içeren değiştirilmiş bir MIT Lisansı altında lisanslanmıştır. Ayrıntılar için [LICENSE](LICENSE) dosyasına bakın.

- Yaratıcının web sitesi ve GitHub adresi ayarlar bölümünde kaldırılmamalıdır.
- Yazılımı değiştirme ve dağıtma özgürlüğüne sahipsiniz, ancak atfı koruduğunuz sürece.