<?php
/**
 * DATABASE + SITE SETTINGS  —  the only file you need to edit if your
 * XAMPP MySQL setup is different from the defaults.
 */
const DB_HOST = 'localhost';
const DB_PORT = '3306';
const DB_NAME = 'feminine_jewels';
const DB_USER = 'root';
const DB_PASS = '';            // XAMPP default is an empty password

const SITE_NAME    = 'Feminine Jewells';   // brand text exactly as in your original design
const FREE_SHIPPING_ABOVE = 2999;
const SHIPPING_FEE        = 99;

// Never show raw PHP warnings / database errors to visitors.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $ex) {
        error_log('DB connection failed: ' . $ex->getMessage());
        http_response_code(500);
        echo '<!DOCTYPE html><meta charset="utf-8"><title>Database unavailable</title>'
           . '<body style="font-family:sans-serif;max-width:560px;margin:15vh auto;padding:0 20px;color:#4a2c3d">'
           . '<h2>We can\'t reach the database right now</h2>'
           . '<p>Please make sure <b>MySQL</b> is started in the XAMPP Control Panel and that the '
           . '<b>feminine_jewels</b> database has been imported in phpMyAdmin. '
           . 'Connection settings live in <code>includes/db.php</code>.</p></body>';
        exit;
    }
    return $pdo;
}
