<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/storage.php';

$filter_date = $_GET['date'] ?? '';
$filter_status = $_GET['status_filter'] ?? '';

$appointments = get_appointments($filter_date ?: null);

if ($filter_status) {
    $appointments = array_values(array_filter($appointments, function($a) use ($filter_status) {
        return $a['status'] === $filter_status;
    }));
}

$filename = "appointments_export_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Patient Name', 'Phone', 'Email', 'Appointment Date', 'Appointment Time', 'Status', 'Reason', 'Internal Notes', 'Booked On'], ',', '"', '\\');

foreach ($appointments as $appt) {
    fputcsv($output, [
        $appt['id'],
        $appt['patient_name'],
        $appt['phone'],
        $appt['email'] ?? '',
        $appt['appointment_date'],
        $appt['appointment_time'],
        $appt['status'],
        $appt['reason'] ?? '',
        $appt['internal_notes'] ?? '',
        $appt['created_at'] ?? ''
    ], ',', '"', '\\');
}

fclose($output);
exit;
?>
