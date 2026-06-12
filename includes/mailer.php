<?php
require_once __DIR__ . '/config.php';

/**
 * Send an email using PHP's native mail() function.
 * This assumes the hosting environment has mail services configured.
 */
function send_email($to, $subject, $message) {
    $headers = "From: " . CLINIC_NAME . " <noreply@" . $_SERVER['SERVER_NAME'] . ">\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($to, $subject, $message, $headers);
}

/**
 * Send booking confirmation to patient
 */
function send_patient_confirmation($appt) {
    if (empty($appt['email'])) return false;

    $subject = "Appointment Confirmation - " . CLINIC_NAME;
    $date = date('l, F j, Y', strtotime($appt['appointment_date']));
    $time = date('h:i A', strtotime($appt['appointment_time']));

    $message = "Hello {$appt['patient_name']},\n\n";
    $message .= "We have received your appointment request at " . CLINIC_NAME . ".\n\n";
    $message .= "Details:\n";
    $message .= "- Date: {$date}\n";
    $message .= "- Time: {$time}\n";
    $message .= "- Reason: {$appt['reason']}\n\n";
    $message .= "Our staff will review your request and contact you shortly if any changes are needed.\n\n";
    $message .= "Thank you,\n" . CLINIC_NAME;

    return send_email($appt['email'], $subject, $message);
}

/**
 * Send alert to admin for new booking
 */
function send_admin_alert($appt) {
    if (empty(ADMIN_EMAIL)) return false;

    $subject = "New Appointment Booked - " . CLINIC_NAME;
    $date = date('l, F j, Y', strtotime($appt['appointment_date']));
    $time = date('h:i A', strtotime($appt['appointment_time']));

    $message = "A new appointment has been requested.\n\n";
    $message .= "Patient: {$appt['patient_name']}\n";
    $message .= "Phone: {$appt['phone']}\n";
    $message .= "Email: " . ($appt['email'] ?: 'N/A') . "\n";
    $message .= "Date: {$date}\n";
    $message .= "Time: {$time}\n";
    $message .= "Reason: {$appt['reason']}\n\n";
    $message .= "Please log in to the CRM to confirm or manage this appointment.";

    return send_email(ADMIN_EMAIL, $subject, $message);
}

/**
 * Send a 24-hour reminder to the patient
 */
function send_patient_reminder($appt) {
    if (empty($appt['email'])) return false;

    $subject = "Appointment Reminder - " . CLINIC_NAME;
    $date = date('l, F j, Y', strtotime($appt['appointment_date']));
    $time = date('h:i A', strtotime($appt['appointment_time']));

    $message = "Hello {$appt['patient_name']},\n\n";
    $message .= "This is a friendly reminder of your upcoming appointment at " . CLINIC_NAME . " tomorrow.\n\n";
    $message .= "Details:\n";
    $message .= "- Date: {$date}\n";
    $message .= "- Time: {$time}\n\n";
    $message .= "If you need to reschedule or cancel, please contact us immediately.\n\n";
    $message .= "We look forward to seeing you!\n\n";
    $message .= "Best regards,\n" . CLINIC_NAME;

    return send_email($appt['email'], $subject, $message);
}
?>
