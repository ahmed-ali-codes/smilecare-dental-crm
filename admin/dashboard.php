<?php
require_once '../includes/auth.php';
require_login();
require_once '../includes/storage.php';
require_once '../includes/csrf.php';

// Handle status change or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (verify_csrf_token($token)) {
        if (isset($_POST['action']) && $_POST['action'] == 'status' && isset($_POST['id']) && isset($_POST['status'])) {
            $id = (int)$_POST['id'];
            $status = $_POST['status'];
            update_appointment_status($id, $status);
            header("Location: dashboard.php");
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            delete_appointment($id);
            header("Location: dashboard.php");
            exit;
        }
    } else {
        // CSRF verification failed
        die("Invalid CSRF token.");
    }
}

// Filtering
$filter_date = $_GET['date'] ?? '';
$appointments = get_appointments($filter_date ?: null);

// Limit to 10 most recent/upcoming for dashboard
$appointments = array_slice($appointments, 0, 10);

// Stats
$today = date('Y-m-d');
$today_count = count_appointments($today, null, 'Cancelled');
$pending_count = count_appointments(null, 'Pending');
$total_count = count_appointments();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmileCare CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="dashboard.php" class="sidebar-logo">SmileCare</a>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="appointments.php">Appointments</a>
            <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <p>Today's Appointments</p>
                <h3><?php echo $today_count; ?></h3>
            </div>
            <div class="stat-card">
                <p>Pending Requests</p>
                <h3><?php echo $pending_count; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Appointments</p>
                <h3><?php echo $total_count; ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="admin-header" style="margin-bottom: 1.5rem;">
                <h3 style="margin: 0;">Recent Appointments (10)</h3>
                
                <form action="dashboard.php" method="GET" class="filter-bar">
                    <label for="date" style="font-weight: 500; white-space: nowrap;">Filter by Date:</label>
                    <input type="date" name="date" id="date" class="form-control">
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filter</button>
                    <?php if ($filter_date): ?>
                        <a href="dashboard.php" class="btn" style="background: #e2e8f0;">Clear</a>
                        <a href="appointments.php?date=<?php echo urlencode($filter_date); ?>" class="btn" style="background: var(--accent-color); color: var(--primary-dark); padding: 0.5rem 1rem;">View All Filtered →</a>
                    <?php else: ?>
                        <a href="appointments.php" class="btn" style="background: var(--accent-color); color: var(--primary-dark); padding: 0.5rem 1rem;">View All →</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Date & Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($appointments) > 0): ?>
                            <?php foreach ($appointments as $appt): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($appt['patient_name']); ?></strong><br>
                                        <small style="color: var(--text-muted);"><?php echo htmlspecialchars($appt['phone']); ?></small>
                                        <?php if (!empty($appt['email'])): ?>
                                            <br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($appt['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($appt['appointment_date'])); ?><br>
                                        <small style="color: var(--text-muted);"><?php echo date('h:i A', strtotime($appt['appointment_time'])); ?></small>
                                    </td>
                                    <td style="max-width: 200px;">
                                        <small><?php echo htmlspecialchars(mb_strimwidth($appt['reason'] ?: '—', 0, 60, '...')); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = 'badge-' . strtolower($appt['status']);
                                        echo "<span class='badge {$badge_class}'>{$appt['status']}</span>";
                                        ?>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <a href="appointments.php?id=<?php echo $appt['id']; ?>" class="btn" style="background: var(--accent-color); color: var(--primary-dark); padding: 0.25rem 0.75rem; font-size: 0.85rem;">View</a>
                                        
                                        <form action="dashboard.php" method="POST" style="display: inline-block; margin: 0;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="status">
                                            <input type="hidden" name="id" value="<?php echo $appt['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="form-control" style="display: inline-block; width: auto; padding: 0.25rem; font-size: 0.85rem;">
                                                <option value="">Change Status...</option>
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
                                <td colspan="5" class="text-center" style="padding: 2rem;">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>
