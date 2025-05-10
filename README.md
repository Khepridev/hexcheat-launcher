# 🚀 HexCheat Launcher

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
  <a href="https://www.youtube.com/@Khepridev">
    <img src="image/youtube.png" alt="Youtube"> Youtube
  </a>
  <br>
  <a href="README.tr.md">🌏 Türkçe</a>
</p>

---

Hex Cheat Launcher is a modern, lightweight Electron-based application launcher and updater. It provides a streamlined interface for managing multiple applications, automatic updates, and notifications.

> **Note:** This project was developed 80% using Cursor AI, with manual contributions made in critical areas or where intervention was needed.

---

## 🚀 Features

HexCheat Launcher offers a wide range of features to enhance your experience:

- 🎵 **Music Playback**: Enjoy your favorite tunes directly within the launcher.
- 📂 **File Management**: Organize and manage your files effortlessly.
- 📰 **News Updates**: Stay informed with the latest updates and news.
- 🔄 **Automatic Updates**: Downloads and installs application updates automatically.
- 🌐 **Multi-Language Support**: Includes English, Turkish, German, Romanian, and Italian.
- 🛠️ **App Management**: Easy management of all your applications in one place.
- 🔔 **Notification System**: Receive notifications for updates and news.
- 🎨 **Modern UI**: Beautiful and responsive interface.
- ✅ **Hash Validation**: Ensures downloaded files' integrity.
- 🖼️ **Background Customization**: Supports both image and video backgrounds.

---

## 📂 Project Structure

```plaintext
launcher/
├── 📂 build/              # Build resources
├── 📂 dist/               # Compiled files
├── 📂 src/
│   ├── 📂 main/           # Main process code
│   ├── 📂 renderer/       # Renderer process code
│   └── 📂 assets/         # Assets (images, fonts, etc.)
├── 📄 package.json
└── 📝 README.md
```

---

## ⚙️ Configuration

The launcher uses a `manifest.json` file to manage application updates and configurations. The manifest structure includes:

- `version`: Launcher version.
- `files`: Array of file objects with paths, URLs, sizes, and hash values.
- `apps`: Available applications with their metadata.
- `translations`: Multi-language support.
- `settings`: Application settings.

---

## 🛠️ Building from Source

### Prerequisites

- [Node.js](https://nodejs.org/) 14+ and npm 7+
- [Git](https://git-scm.com/)
- [NSIS](https://nsis.sourceforge.io/Download): Required for creating the setup executable.

### Steps

```bash
# Clone the repository
git clone https://github.com/hexlob/launcher.git
cd launcher

# Install dependencies
npm install

# Start the application
npm start
```

📦 For a clean installation:

```powershell
# Clean installation (PowerShell)
Remove-Item -Recurse -Force node_modules
Remove-Item -Recurse -Force dist
Remove-Item -Recurse -Force release-builds
Remove-Item package-lock.json
npm install
npm run package
```

📚 To build the setup executable:

1. Install [NSIS](https://nsis.sourceforge.io/Download).
2. Navigate to the `Installation` folder.
3. Open `exe_setup.nsi` with NSIS and compile it to generate the installer.

---

## 🌐 Manifest Management Panel

A PHP-based dashboard is included for managing the manifest file, applications, translations, and media files. Access the control panel through `php-dashboard`.

---

## 📹 Installation Video

Watch our installation guide video for a step-by-step walkthrough of the setup process:

[![Installation Video](https://github.com/Khepridev/hexcheat-launcher/blob/main/image/cover.png)](https://www.youtube.com/watch?v=haDMjpF03T8)

---

## 📜 License

This project is licensed under a modified MIT License that includes special provisions for preserving the creator's attribution. See the [LICENSE](LICENSE) file for details.

- The creator's website and GitHub address in the settings section must not be removed.
- You are free to modify and distribute the software as long as you maintain the attribution.