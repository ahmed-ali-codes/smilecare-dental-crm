<?php
require_once __DIR__ . '/includes/config.php';

// Simple security check
if (!isset($_GET['key']) || $_GET['key'] !== CRON_SECRET_KEY) {
    // If run from CLI, allow it without key
    if (php_sapi_name() !== 'cli') {
        http_response_code(403);
        die("Unauthorized");
    }
}

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/mailer.php';

$tomorrow = date('Y-m-d', strtotime('+1 day'));
$reminders_sent = 0;

$data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
if (!is_array($data)) {
    die("No data found.");
}

$updated = false;

foreach ($data as &$appt) {
    if ($appt['status'] === 'Confirmed' && $appt['appointment_date'] === $tomorrow) {
        if (!isset($appt['reminder_sent']) || $appt['reminder_sent'] !== true) {
            
            // Try to send email
            if (!empty($appt['email'])) {
                send_patient_reminder($appt);
                $reminders_sent++;
            }
            
            // Mark as sent to avoid double-sending
            $appt['reminder_sent'] = true;
            $updated = true;
        }
    }
}

if ($updated) {
    file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

echo "Cron complete. Reminders sent: " . $reminders_sent . "\n";
?>
