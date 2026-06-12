<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/storage.php';
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    die("No appointment ID provided.");
}

$id = (int)$_GET['id'];
$appt = get_appointment($id);

if (!$appt) {
    die("Appointment not found.");
}

$date_str = $appt['appointment_date'] . ' ' . $appt['appointment_time'];
$start_ts = strtotime($date_str);
$end_ts = $start_ts + (60 * 60); // Assume 1 hour duration

$dtstart = gmdate('Ymd\THis\Z', $start_ts);
$dtend = gmdate('Ymd\THis\Z', $end_ts);
$dtstamp = gmdate('Ymd\THis\Z');

$uid = $appt['id'] . '-' . $dtstamp . '@' . $_SERVER['SERVER_NAME'];

$description = "Patient: {$appt['patient_name']}\\n";
$description .= "Phone: {$appt['phone']}\\n";
if (!empty($appt['email'])) $description .= "Email: {$appt['email']}\\n";
$description .= "Reason: {$appt['reason']}";

$filename = "appointment_" . $appt['id'] . ".ics";

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//" . CLINIC_NAME . "//NONSGML v1.0//EN\r\n";
echo "BEGIN:VEVENT\r\n";
echo "UID:{$uid}\r\n";
echo "DTSTAMP:{$dtstamp}\r\n";
echo "DTSTART:{$dtstart}\r\n";
echo "DTEND:{$dtend}\r\n";
echo "SUMMARY:Appointment with {$appt['patient_name']}\r\n";
echo "DESCRIPTION:{$description}\r\n";
echo "LOCATION:" . CLINIC_NAME . "\r\n";
echo "END:VEVENT\r\n";
echo "END:VCALENDAR\r\n";
?>
