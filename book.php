<?php
session_start();
require_once 'includes/storage.php';
require_once 'includes/csrf.php';
require_once 'includes/rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_rate_limit(60)) {
        $_SESSION['booking_error'] = "Please wait 60 seconds before submitting another request.";
        header("Location: index.php");
        exit;
    }

    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $_SESSION['booking_error'] = "Invalid session. Please refresh the page and try again.";
        header("Location: index.php");
        exit;
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pref_date = trim($_POST['pref_date'] ?? '');
    $pref_time = trim($_POST['pref_time'] ?? '');
    $reason_type = trim($_POST['reason_type'] ?? '');
    $reason_notes = trim($_POST['reason'] ?? '');

    $final_reason = $reason_type;
    if ($reason_type === 'Other' && !empty($reason_notes)) {
        $final_reason = $reason_notes;
    } elseif (!empty($reason_notes) && $reason_type !== 'Other') {
        $final_reason .= ' - ' . $reason_notes;
    }

    if (empty($full_name) || empty($phone) || empty($pref_date) || empty($pref_time) || empty($reason_type)) {
        $_SESSION['booking_error'] = "Please fill in all required fields.";
        header("Location: index.php");
        exit;
    }

    // Conflict Prevention (30 minute buffer)
    $existing = get_appointments($pref_date);
    $requested_ts = strtotime("$pref_date $pref_time");
    $conflict = false;
    foreach ($existing as $ex) {
        if ($ex['status'] === 'Cancelled') continue;
        $ex_ts = strtotime($ex['appointment_date'] . ' ' . $ex['appointment_time']);
        if (abs($ex_ts - $requested_ts) < 1800) {
            $conflict = true;
            break;
        }
    }

    if ($conflict) {
        $_SESSION['booking_error'] = "This time slot is unavailable. Please select a time at least 30 minutes away from other bookings.";
        header("Location: index.php");
        exit;
    }

    $appointment = [
        'patient_name'     => $full_name,
        'phone'            => $phone,
        'email'            => $email ?: null,
        'appointment_date' => $pref_date,
        'appointment_time' => $pref_time,
        'reason'           => $final_reason ?: null,
    ];

    $id = add_appointment($appointment);

    if ($id) {
        require_once 'includes/mailer.php';
        send_patient_confirmation($appointment);
        send_admin_alert($appointment);
        
        $_SESSION['booking_success'] = "Your appointment is booked! We will contact you to confirm.";
    } else {
        $_SESSION['booking_error'] = "An error occurred while booking. Please try again later.";
    }

    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
