<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/storage.php';
require_once '../includes/csrf.php';

// Handle status change or delete from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($token)) {
        if (isset($_POST['action']) && $_POST['action'] == 'status' && isset($_POST['id']) && isset($_POST['status'])) {
            $id = (int)$_POST['id'];
            $status = $_POST['status'];
            update_appointment_status($id, $status);
            header("Location: appointments.php");
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            delete_appointment($id);
            header("Location: appointments.php");
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] == 'update_notes' && isset($_POST['id']) && isset($_POST['notes'])) {
            $id = (int)$_POST['id'];
            $notes = $_POST['notes'];
            update_appointment_notes($id, $notes);
            header("Location: appointments.php?id=" . $id);
            exit;
        }
    } else {
        die("Invalid CSRF token.");
    }
}

// Determine mode: list vs detail
$view_mode = 'list';
$appt = null;

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $appt = get_appointment($id);
    if ($appt) {
        $view_mode = 'detail';
    }
}

// For list mode: get appointments with optional filters
$filter_date = $_GET['date'] ?? '';
$filter_status = $_GET['status_filter'] ?? '';
$appointments = [];

$total_appointments = 0;
$current_page = 1;
$total_pages = 1;

if ($view_mode === 'list') {
    $appointments = get_appointments($filter_date ?: null);

    // Additional status filter
    if ($filter_status) {
        $appointments = array_values(array_filter($appointments, function($a) use ($filter_status) {
            return $a['status'] === $filter_status;
        }));
    }

    $total_appointments = count($appointments);
    $per_page = 50;
    $total_pages = ceil($total_appointments / $per_page) ?: 1;
    $current_page = max(1, min($total_pages, isset($_GET['page']) ? (int)$_GET['page'] : 1));
    
    $appointments = array_slice($appointments, ($current_page - 1) * $per_page, $per_page);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $view_mode === 'detail' ? 'Appointment Detail' : 'All Appointments'; ?> - SmileCare CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .filter-bar {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-bar .form-control {
            width: auto;
        }
        .appointment-count {
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--bg-color);
            border-radius: var(--radius);
        }
        .detail-label {
            display: block;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .detail-value {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .notes-box {
            background: var(--bg-color);
            padding: 1.5rem;
            border-radius: var(--radius);
            min-height: 100px;
        }
        .status-actions {
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        @media (max-width: 1024px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
        }
        }
    </style>
    <?php if (isset($_GET['print']) && $_GET['print'] == '1'): ?>
    <style>
        .sidebar, .admin-header .btn, .status-actions, form { display: none !important; }
        .admin-main { margin: 0; padding: 2rem; width: 100%; max-width: 100%; }
        .card { box-shadow: none; border: 1px solid #ddd; max-width: 100% !important; }
        body { background: white; }
    </style>
    <script>window.onload = function() { window.print(); }</script>
    <?php endif; ?>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="dashboard.php" class="sidebar-logo">SmileCare</a>
        <nav class="sidebar-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="appointments.php" class="active">Appointments</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

    <?php if ($view_mode === 'detail'): ?>
        <!-- ============ DETAIL VIEW ============ -->
        <div class="admin-header">
            <h2>Appointment Details</h2>
            <div>
                <a href="appointments.php?id=<?php echo $appt['id']; ?>&print=1" class="btn" target="_blank" style="background: #e2e8f0; color: var(--text-main); margin-right: 0.5rem;">🖨️ Print</a>
                <a href="export_ical.php?id=<?php echo $appt['id']; ?>" class="btn" style="background: #e2e8f0; color: var(--text-main); margin-right: 0.5rem;">Export to Calendar</a>
                <a href="appointments.php" class="btn" style="background: #e2e8f0; color: var(--text-main);">← Back to All Appointments</a>
            </div>
        </div>

        <div class="card" style="max-width: 800px;">
            <div class="flex justify-between" style="align-items: flex-start; margin-bottom: 2rem;">
                <div>
                    <h3 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($appt['patient_name']); ?></h3>
                    <p style="margin: 0;">
                        <?php echo htmlspecialchars($appt['phone']); ?> 
                        <?php if(!empty($appt['email'])) echo " • " . htmlspecialchars($appt['email']); ?>
                    </p>
                </div>
                <div>
                    <?php
                    $badge_class = 'badge-' . strtolower($appt['status']);
                    echo "<span class='badge {$badge_class}' style='font-size: 1rem; padding: 0.5rem 1rem;'>{$appt['status']}</span>";
                    ?>
                </div>
            </div>

            <div class="detail-grid">
                <div>
                    <span class="detail-label">Appointment Date</span>
                    <div class="detail-value"><?php echo date('l, F j, Y', strtotime($appt['appointment_date'])); ?></div>
                </div>
                <div>
                    <span class="detail-label">Appointment Time</span>
                    <div class="detail-value"><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></div>
                </div>
                <div>
                    <span class="detail-label">Booked On</span>
                    <div class="detail-value"><?php echo isset($appt['created_at']) ? date('M d, Y \a\t h:i A', strtotime($appt['created_at'])) : 'N/A'; ?></div>
                </div>
                <div>
                    <span class="detail-label">Appointment ID</span>
                    <div class="detail-value">#<?php echo $appt['id']; ?></div>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <span class="detail-label">Reason / Notes</span>
                <p class="notes-box">
                    <?php echo nl2br(htmlspecialchars($appt['reason'] ?: 'No notes provided.')); ?>
                </p>
            </div>

            <!-- Internal Notes Section -->
            <div style="margin-bottom: 2rem;">
                <span class="detail-label">Internal Notes (Private)</span>
                <form action="appointments.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="update_notes">
                    <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                    <textarea name="notes" class="form-control" style="min-height: 100px; margin-bottom: 0.5rem;" placeholder="Add internal notes here..."><?php echo htmlspecialchars($appt['internal_notes'] ?? ''); ?></textarea>
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Save Internal Notes</button>
                </form>
            </div>

            <div class="status-actions">
                <span style="font-weight: 600;">Change Status:</span>
                <form action="appointments.php" method="POST" style="display: inline-block; margin: 0;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                    <input type="hidden" name="status" value="Confirmed">
                    <button type="submit" class="btn btn-success">Confirm</button>
                </form>
                <form action="appointments.php" method="POST" style="display: inline-block; margin: 0;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                    <input type="hidden" name="status" value="Completed">
                    <button type="submit" class="btn btn-primary">Mark Completed</button>
                </form>
                <form action="appointments.php" method="POST" style="display: inline-block; margin: 0;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                    <input type="hidden" name="status" value="Cancelled">
                    <button type="submit" class="btn btn-danger">Cancel</button>
                </form>
                <form action="appointments.php" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this appointment?');">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                    <button type="submit" class="btn" style="background: #fee2e2; color: #b91c1c;">Delete</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- ============ LIST VIEW ============ -->
        <div class="admin-header">
            <div>
                <h2>All Appointments</h2>
                <span class="appointment-count"><?php echo $total_appointments; ?> appointment<?php echo $total_appointments !== 1 ? 's' : ''; ?> found</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
            <form action="appointments.php" method="GET" class="filter-bar">
                <label for="date" style="font-weight: 600;">Filters:</label>
                
                <input type="date" name="date" id="date" class="form-control" value="<?php echo htmlspecialchars($filter_date); ?>" placeholder="Date">
                
                <select name="status_filter" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo $filter_status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Confirmed" <?php echo $filter_status === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="Completed" <?php echo $filter_status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="Cancelled" <?php echo $filter_status === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>

                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem;">Apply</button>
                <?php if ($filter_date || $filter_status): ?>
                    <a href="appointments.php" class="btn" style="background: #e2e8f0; padding: 0.5rem 1.25rem;">Clear</a>
                <?php endif; ?>
                <a href="export_csv.php?date=<?php echo urlencode($filter_date); ?>&status_filter=<?php echo urlencode($filter_status); ?>" class="btn" style="background: #10b981; color: white; margin-left: auto; padding: 0.5rem 1.25rem;">📥 Export CSV</a>
            </form>
        </div>

        <!-- Appointments Table -->
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Date & Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($appointments) > 0): ?>
                            <?php foreach ($appointments as $a): ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-weight: 500;"><?php echo $a['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($a['patient_name']); ?></strong><br>
                                        <small style="color: var(--text-muted);"><?php echo htmlspecialchars($a['phone']); ?></small>
                                        <?php if (!empty($a['email'])): ?>
                                            <br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($a['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($a['appointment_date'])); ?><br>
                                        <small style="color: var(--text-muted);"><?php echo date('h:i A', strtotime($a['appointment_time'])); ?></small>
                                    </td>
                                    <td style="max-width: 200px;">
                                        <small><?php echo htmlspecialchars(mb_strimwidth($a['reason'] ?: '—', 0, 60, '...')); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = 'badge-' . strtolower($a['status']);
                                        echo "<span class='badge {$badge_class}'>{$a['status']}</span>";
                                        ?>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <a href="appointments.php?id=<?php echo $a['id']; ?>" class="btn" style="background: var(--accent-color); color: var(--primary-dark); padding: 0.25rem 0.75rem; font-size: 0.85rem;">View</a>
                                        
                                        <form action="appointments.php" method="POST" style="display: inline-block; margin: 0;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="status">
                                            <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="form-control" style="display: inline-block; width: auto; padding: 0.25rem; font-size: 0.85rem;">
                                                <option value="">Status...</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Confirmed">Confirmed</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 3rem;">
                                    <p style="font-size: 1.1rem; margin: 0;">No appointments found.</p>
                                    <?php if ($filter_date || $filter_status): ?>
                                        <a href="appointments.php" style="color: var(--primary-color); font-weight: 500;">Clear filters</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1.5rem; margin-bottom: 2rem;">
                <?php 
                $query_params = $_GET;
                unset($query_params['page']);
                $query_string = http_build_query($query_params);
                $query_prefix = $query_string ? '&' . $query_string : '';
                ?>
                
                <?php if ($current_page > 1): ?>
                    <a href="appointments.php?page=<?php echo $current_page - 1; ?><?php echo $query_prefix; ?>" class="btn" style="background: #e2e8f0; color: var(--text-main);">← Previous</a>
                <?php else: ?>
                    <span class="btn" style="background: #f1f5f9; color: #94a3b8; cursor: not-allowed;">← Previous</span>
                <?php endif; ?>

                <span style="font-weight: 500; color: var(--text-muted);">
                    Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                </span>

                <?php if ($current_page < $total_pages): ?>
                    <a href="appointments.php?page=<?php echo $current_page + 1; ?><?php echo $query_prefix; ?>" class="btn" style="background: #e2e8f0; color: var(--text-main);">Next →</a>
                <?php else: ?>
                    <span class="btn" style="background: #f1f5f9; color: #94a3b8; cursor: not-allowed;">Next →</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    </main>
</div>

</body>
</html>
