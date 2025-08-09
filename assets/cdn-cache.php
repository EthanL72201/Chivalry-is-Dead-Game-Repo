<?php
/*
 * CDN Asset Caching Script
 * Caches CDN assets locally for faster loading
 */

$assets = [
    'bootstrap.min.css' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css',
    'popper.min.js' => 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js',
    'bootstrap.min.js' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js',
    'fontawesome.css' => 'https://use.fontawesome.com/releases/v5.7.1/css/all.css',
    'jquery.min.js' => 'https://code.jquery.com/jquery-3.4.0.min.js',
    'jquery-ui.css' => 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css'
];

$cache_dir = __DIR__ . '/cache/';

// Create cache directory if it doesn't exist
if (!file_exists($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Function to serve cached asset
function serveCachedAsset($filename) {
    global $cache_dir, $assets;
    
    $cache_file = $cache_dir . $filename;
    $cache_time = 86400 * 7; // Cache for 7 days
    
    // Check if cache exists and is fresh
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        // Serve from cache
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        if ($extension === 'css') {
            header('Content-Type: text/css');
        } elseif ($extension === 'js') {
            header('Content-Type: application/javascript');
        }
        
        header('Cache-Control: public, max-age=' . $cache_time);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
        
        readfile($cache_file);
        return true;
    }
    
    // Download and cache the file
    if (isset($assets[$filename])) {
        $content = @file_get_contents($assets[$filename]);
        if ($content !== false) {
            file_put_contents($cache_file, $content);
            
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            if ($extension === 'css') {
                header('Content-Type: text/css');
            } elseif ($extension === 'js') {
                header('Content-Type: application/javascript');
            }
            
            header('Cache-Control: public, max-age=' . $cache_time);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
            
            echo $content;
            return true;
        }
    }
    
    return false;
}

// Handle asset requests
if (isset($_GET['asset'])) {
    $asset = basename($_GET['asset']);
    if (!serveCachedAsset($asset)) {
        header('HTTP/1.0 404 Not Found');
        echo 'Asset not found';
    }
    exit;
}