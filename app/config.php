<?php
// Cache cleanup: runs on every 5th request, deletes files older than 7 days
session_start();
if (!isset($_SESSION['cache_cleanup_counter'])) {
    $_SESSION['cache_cleanup_counter'] = 0;
}
$_SESSION['cache_cleanup_counter']++;
if ($_SESSION['cache_cleanup_counter'] >= 5) {
    $cacheDirs = [
        __DIR__ . '/../cache/amadeus/',
        __DIR__ . '/../cache/iata/',
    ];
    $maxAge = 7 * 24 * 60 * 60; // 7 days in seconds
    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            foreach (glob($dir . '*') as $file) {
                if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                    @unlink($file);
                }
            }
        }
    }
    $_SESSION['cache_cleanup_counter'] = 0;
}
return [
    'airtable_token' => getenv('AIRTABLE_TOKEN') ?: 'Your_Airtable_Token',
    'airtable_base' => getenv('AIRTABLE_BASE') ?: 'Your_Airtable_Base',
    'airtable_table' => getenv('AIRTABLE_TABLE') ?: 'Your_Airtable_Table',
]; 