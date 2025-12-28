<?php
// Keep your existing PHP code here
session_start();
// Check if user is logged in
// Fetch car details if car_id is provided
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Reservation - CarRental Pro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="../index.php" class="logo">CarRental Pro</a>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="cars.php">Available Cars</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Reservation Form -->
    <div class="container" style="margin-top: 50px; max-width: 1000px;">
        <h1 style="color: #1e3c72; margin-bottom: 30px; text-align: center;">Make a Reservation</h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px;">
            <!-- Car Details Card -->
            <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                <h3 style="color: #1e3c72; margin-bottom: 20px;">Selected Vehicle</h3>
                <img src="../assets/images/car1.jpg" alt="Car" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 20px;">
                
                <h2 style="margin-bottom: 10px;">Toyota Camry 2024</h2>
                
                <div class="car-specs" style="margin: 20px 0;">
                    <span class="spec">👥 5 Seats</span>
                    <span class="spec">⚙️ Automatic</span>
                    <span class="spec">⛽ Hybrid</span>
                </div>

                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #666;">Price per day:</span>
                        <span style="font-weight: bold; color: #2196f3;">$45</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #666;">Number of days:</span>
                        <span style="font-weight: bold;" id="total-days">0</span>
                    </div>
                    <hr style="border: none; border-top: 1px solid #ddd; margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: bold; font-size: 18px;">Total Price:</span>
                        <span style="font-weight: bold; font-size: 24px; color: #2196f3;" id="total-price">$0</span>
                    </div>
                </div>
            </div>

            <!-- Reservation Form -->
            <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                <h3 style="color: #1e3c72; margin-bottom: 20px;">Booking Details</h3>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="process-reservation.php" method="POST" id="reservation-form">
                    <input type="hidden" name="car_id" value="1">

                    <div class="form-group">
                        <label for="start_date">Pickup Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" 
                               required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_date">Return Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" 
                               required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="pickup_location">Pickup Location</label>
                        <select id="pickup_location" name="pickup_location" class="form-control" required>
                            <option value="">Select Location</option>
                            <option value="downtown">Downtown Branch</option>
                            <option value="airport">Airport Branch</option>
                            <option value="north">North Branch</option>
                            <option value="south">South Branch</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="return_location">Return Location</label>
                        <select id="return_location" name="return_location" class="form-control" required>
                            <option value="">Select Location</option>
                            <option value="downtown">Downtown Branch</option>
                            <option value="airport">Airport Branch</option>
                            <option value="north">North Branch</option>
                            <option value="south">South Branch</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests (Optional)</label>
                        <textarea id="special_requests" name="special_requests" class="form-control" 
                                  placeholder="Any special requirements or requests..."></textarea>
                    </div>

                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;">
                        <p style="margin: 0; color: #856404; font-size: 14px;">
                            <strong>Note:</strong> A valid driver's license is required at pickup. 
                            Full payment will be processed after approval.
                        </p>
                    </div>

                    <button type="submit" name="reserve" class="btn btn-block" style="margin-top: 20px;">
                        Confirm Reservation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 CarRental Pro. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Calculate total price based on dates
        document.getElementById('start_date').addEventListener('change', calculateTotal);
        document.getElementById('end_date').addEventListener('change', calculateTotal);

        function calculateTotal() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            const pricePerDay = 45;

            if (startDate && endDate && endDate > startDate) {
                const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
                const total = days * pricePerDay;
                
                document.getElementById('total-days').textContent = days;
                document.getElementById('total-price').textContent = '$' + total;
            } else {
                document.getElementById('total-days').textContent = '0';
                document.getElementById('total-price').textContent = '$0';
            }
        }
    </script>
</body>
</html>