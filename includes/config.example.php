<?php
// ============================================================
// ADMIN CREDENTIALS & SETTINGS — Change these before deploying!
// Default: username = admin, password = admin123
// ============================================================
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', ''); // Replace with your bcrypt password hash. Use: php -r echo password_hash('your_password', PASSWORD_BCRYPT);
define('ADMIN_EMAIL', 'admin@smilecare.com');
define('CLINIC_NAME', 'SmileCare Dental');
define('CLINIC_TIMEZONE', 'Asia/Dubai');
define('CLINIC_OPEN_TIME', '09:00');
define('CLINIC_CLOSE_TIME', '18:00');
define('CRON_SECRET_KEY', 'YourSecretKeyHere123!');

// Set default timezone for the application
date_default_timezone_set(CLINIC_TIMEZONE);
?>