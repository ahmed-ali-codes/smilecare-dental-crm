<?php
define('RATE_LIMIT_FILE', __DIR__ . '/../data/rate_limits.json');

/**
 * Check if the user's IP has exceeded the rate limit.
 * @param int $cooldown_seconds Minimum seconds between requests.
 * @return bool True if allowed, False if rate limited.
 */
function check_rate_limit($cooldown_seconds = 60) {
    if (!file_exists(RATE_LIMIT_FILE)) {
        file_put_contents(RATE_LIMIT_FILE, json_encode([]));
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $data = json_decode(file_get_contents(RATE_LIMIT_FILE), true) ?: [];
    
    $now = time();
    $allowed = true;

    // Clean up old entries
    $data = array_filter($data, function($timestamp) use ($now, $cooldown_seconds) {
        return ($now - $timestamp) <= ($cooldown_seconds * 2); // Keep entries a bit longer than cooldown
    });

    if (isset($data[$ip])) {
        if (($now - $data[$ip]) < $cooldown_seconds) {
            $allowed = false;
        } else {
            $data[$ip] = $now;
        }
    } else {
        $data[$ip] = $now;
    }

    file_put_contents(RATE_LIMIT_FILE, json_encode($data), LOCK_EX);

    return $allowed;
}
?>
