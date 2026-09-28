<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/**
 * Menyamakan kapitalisasi prefix path untuk instalasi di subdirektori.
 *
 * Pada XAMPP aplikasi ini diakses lewat `http://localhost/simrs-rme/public`.
 * Windows selalu melaporkan `SCRIPT_NAME` mengikuti kapitalisasi folder di
 * disk (`SIMRS-RME`) sedangkan `REQUEST_URI` memakai kapitalisasi yang diketik
 * pengguna (`simrs-rme`). Symfony membandingkan keduanya secara case-sensitive
 * sehingga base URL gagal dikenali: setiap route membalas 404 dan semua URL
 * aset mengarah ke `http://localhost/build/...` yang tidak ada, sehingga
 * halaman tampil tanpa gaya dan tanpa JavaScript.
 *
 * Prefix pada `REQUEST_URI` disamakan dengan `SCRIPT_NAME` supaya instalasi
 * di subdirektori tetap jalan di kedua kapitalisasi. Pada instalasi di root
 * domain tidak ada yang berubah.
 */
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

$scriptPath = str_replace('\\', '/', parse_url($scriptName, PHP_URL_PATH) ?: '/index.php');
$basePath = rtrim(dirname($scriptPath), '/');

if ($basePath !== '' && $basePath !== '/' && PHP_SAPI !== 'cli') {
    $query = parse_url($requestUri, PHP_URL_QUERY);
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
    $prefix = $basePath.'/';
    $typed = substr($path, 0, strlen($prefix));

    if (strcasecmp($typed, $prefix) === 0 && $typed !== $prefix) {
        $path = $basePath.substr($path, strlen($basePath));
        $_SERVER['REQUEST_URI'] = $query === null ? $path : $path.'?'.$query;
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
