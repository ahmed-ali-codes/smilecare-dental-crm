<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment - SmileCare Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="hero">
        <div class="container">
            <h1>Premium Dental Care</h1>
            <p>Book your appointment today and get the perfect smile you deserve.</p>
        </div>
    </div>

    <div class="container">
        <div class="card booking-section">
            <h2 class="text-center">Book Appointment</h2>
            
            <?php
            if (isset($_SESSION['booking_success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['booking_success']) . '</div>';
                unset($_SESSION['booking_success']);
            }
            if (isset($_SESSION['booking_error'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['booking_error']) . '</div>';
                unset($_SESSION['booking_error']);
            }
            ?>

            <form action="book.php" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required placeholder="John Doe">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required placeholder="(123) 456-7890">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address (Optional)</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="pref_date">Preferred Date *</label>
                        <input type="date" id="pref_date" name="pref_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="pref_time">Preferred Time *</label>
                        <input type="time" id="pref_time" name="pref_time" class="form-control" required min="<?php echo CLINIC_OPEN_TIME; ?>" max="<?php echo CLINIC_CLOSE_TIME; ?>">
                        <small style="color: #64748b; font-size: 0.8rem;">Clinic hours: <?php echo CLINIC_OPEN_TIME; ?> - <?php echo CLINIC_CLOSE_TIME; ?></small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reason_type">Reason for Visit *</label>
                    <select id="reason_type" name="reason_type" class="form-control" required onchange="document.getElementById('other_reason_group').style.display = (this.value === 'Other' ? 'block' : 'none');">
                        <option value="">Select a reason...</option>
                        <option value="General Checkup">General Checkup</option>
                        <option value="Teeth Cleaning">Teeth Cleaning</option>
                        <option value="Toothache / Pain">Toothache / Pain</option>
                        <option value="Root Canal">Root Canal</option>
                        <option value="Teeth Whitening">Teeth Whitening</option>
                        <option value="Consultation">Consultation</option>
                        <option value="Other">Other (Please Specify)</option>
                    </select>
                </div>

                <div class="form-group" id="other_reason_group" style="display: none;">
                    <label class="form-label" for="reason">Please Specify Details</label>
                    <textarea id="reason" name="reason" class="form-control" placeholder="Tell us more about your visit..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Book Appointment</button>
            </form>
        </div>
    </div>

</body>
</html>
