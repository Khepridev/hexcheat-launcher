<?php
// Yeni ID oluşturma fonksiyonu
function generateNewId($type, $manifest) {
    $maxId = 0;
    
    if ($type === 'new_server') {
        if (isset($manifest['new_server'])) {
            foreach ($manifest['new_server'] as $server) {
                if ($server['id'] > $maxId) {
                    $maxId = $server['id'];
                }
            }
        }
        return $maxId + 1;
    }
    
    if ($type === 'news') {
        foreach ($manifest['news'] as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
    } 
    elseif ($type === 'menuItems') {
        // TR menü öğelerini kontrol et
        foreach ($manifest['translations']['tr']['menuItems'] as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        // EN menü öğelerini kontrol et
        foreach ($manifest['translations']['en']['menuItems'] as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
    }
    
    if ($type === 'socialMedia') {
        if (isset($manifest['socialMedia'])) {
            foreach ($manifest['socialMedia'] as $item) {
                if ($item['id'] > $maxId) {
                    $maxId = $item['id'];
                }
            }
        }
    }
    
    return $maxId + 1;
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// getManifest, saveManifest, getSystemSetting, and setSystemSetting functions moved to config.php

/**
 * Kullanıcı yetki kontrolü
 * 
 * @param int $requiredRank Gereken minimum yetki seviyesi (0=kullanıcı, 1=admin)
 * @return bool Kullanıcı yetkili ise true, değilse false
 */
function checkUserPermission($requiredRank = 1) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rank'])) {
        return false;
    }
    
    return (int)$_SESSION['user_rank'] >= $requiredRank;
}

/**
 * Sistem güvenli URL döndürür
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $path = rtrim($path, '/') . '/';
    
    return $protocol . '://' . $host . $path;
} 