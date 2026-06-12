<?php
/**
 * Flat-file JSON storage for appointments.
 * No database required — works on any PHP hosting (Hostinger, etc.)
 */

define('DATA_DIR', __DIR__ . '/../data');
define('APPOINTMENTS_FILE', DATA_DIR . '/appointments.json');
define('META_FILE', DATA_DIR . '/meta.json');

// Auto-create data directory and protect it
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Protect data directory from direct web access
$htaccess_path = DATA_DIR . '/.htaccess';
if (!file_exists($htaccess_path)) {
    file_put_contents($htaccess_path, "Deny from all\n");
}

// Initialize appointments file if it doesn't exist
if (!file_exists(APPOINTMENTS_FILE)) {
    file_put_contents(APPOINTMENTS_FILE, json_encode([], JSON_PRETTY_PRINT));
}

// Initialize meta file (stores next ID)
if (!file_exists(META_FILE)) {
    file_put_contents(META_FILE, json_encode(['next_id' => 1], JSON_PRETTY_PRINT));
}

/**
 * Get the next auto-increment ID
 */
function get_next_id() {
    $meta = json_decode(file_get_contents(META_FILE), true);
    $id = $meta['next_id'];
    $meta['next_id'] = $id + 1;
    file_put_contents(META_FILE, json_encode($meta, JSON_PRETTY_PRINT), LOCK_EX);
    return $id;
}

/**
 * Get all appointments, optionally filtered
 */
function get_appointments($filter_date = null) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) {
        $data = [];
    }

    if ($filter_date) {
        $data = array_filter($data, function($appt) use ($filter_date) {
            return $appt['appointment_date'] === $filter_date;
        });
    }

    // Sort by date ASC, then time ASC
    usort($data, function($a, $b) {
        $date_cmp = strcmp($a['appointment_date'], $b['appointment_date']);
        if ($date_cmp !== 0) return $date_cmp;
        return strcmp($a['appointment_time'], $b['appointment_time']);
    });

    return $data;
}

/**
 * Get a single appointment by ID
 */
function get_appointment($id) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return null;

    foreach ($data as $appt) {
        if ((int)$appt['id'] === (int)$id) {
            return $appt;
        }
    }
    return null;
}

/**
 * Add a new appointment
 */
function add_appointment($appointment) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) {
        $data = [];
    }

    $appointment['id'] = get_next_id();
    $appointment['status'] = 'Pending';
    $appointment['created_at'] = date('Y-m-d H:i:s');

    $data[] = $appointment;

    file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    return $appointment['id'];
}

/**
 * Update appointment status
 */
function update_appointment_status($id, $status) {
    $allowed = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
    if (!in_array($status, $allowed)) return false;

    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return false;

    $updated = false;
    foreach ($data as &$appt) {
        if ((int)$appt['id'] === (int)$id) {
            $appt['status'] = $status;
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }
    return $updated;
}

/**
 * Update appointment internal notes
 */
function update_appointment_notes($id, $notes) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return false;

    $updated = false;
    foreach ($data as &$appt) {
        if ((int)$appt['id'] === (int)$id) {
            $appt['internal_notes'] = $notes;
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    }
    return $updated;
}

/**
 * Delete an appointment by ID
 */
function delete_appointment($id) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return false;

    $data = array_values(array_filter($data, function($appt) use ($id) {
        return (int)$appt['id'] !== (int)$id;
    }));

    file_put_contents(APPOINTMENTS_FILE, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    return true;
}

/**
 * Count appointments by criteria
 */
function count_appointments($date = null, $status = null, $exclude_status = null) {
    $data = json_decode(file_get_contents(APPOINTMENTS_FILE), true);
    if (!is_array($data)) return 0;

    $count = 0;
    foreach ($data as $appt) {
        if ($date && $appt['appointment_date'] !== $date) continue;
        if ($status && $appt['status'] !== $status) continue;
        if ($exclude_status && $appt['status'] === $exclude_status) continue;
        $count++;
    }
    return $count;
}
?>
