<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarRental Pro - Rent Your Perfect Car</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="index.php" class="logo">CarRental Pro</a>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="client/cars.php">Available Cars</a></li>
                <li><a href="client/login.php">Client Login</a></li>
                <li><a href="admin/login.php">Admin Login</a></li>
                <li><a href="client/register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Rent Your Perfect Car</h1>
            <p>Choose from our wide selection of premium vehicles at competitive prices</p>
            <a href="client/cars.php" class="hero-btn">Browse Available Cars</a>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="section">
        <h2 class="section-title">Why Choose Us?</h2>
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">🚗</div>
                <h3>Wide Selection</h3>
                <p>Choose from hundreds of vehicles including luxury cars, SUVs, and economy options</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Best Prices</h3>
                <p>Competitive rates with no hidden fees. Get the best value for your money</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📞</div>
                <h3>24/7 Support</h3>
                <p>Our customer service team is always available to help you with any questions</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Easy Booking</h3>
                <p>Simple and fast reservation process. Book your car in just a few clicks</p>
            </div>
        </div>
    </section>

    <!-- Featured Cars Section -->
    <section class="section" style="background: white; padding: 80px 20px;">
        <h2 class="section-title">Featured Vehicles</h2>
        <div class="car-grid">
            <!-- Car Card 1 -->
            <div class="car-card">
                <img src="assets/images/car1.jpg" alt="Toyota Camry" class="car-image">
                <div class="car-details">
                    <h3>Toyota Camry 2024</h3>
                    <div class="car-specs">
                        <span class="spec">👥 5 Seats</span>
                        <span class="spec">⚙️ Automatic</span>
                        <span class="spec">⛽ Hybrid</span>
                    </div>
                    <div class="car-price">$45 <span>/ day</span></div>
                    <a href="client/cars.php" class="btn btn-block">Book Now</a>
                </div>
            </div>

            <!-- Car Card 2 -->
            <div class="car-card">
                <img src="assets/images/car2.jpg" alt="Honda CR-V" class="car-image">
                <div class="car-details">
                    <h3>Honda CR-V 2024</h3>
                    <div class="car-specs">
                        <span class="spec">👥 7 Seats</span>
                        <span class="spec">⚙️ Automatic</span>
                        <span class="spec">⛽ Petrol</span>
                    </div>
                    <div class="car-price">$55 <span>/ day</span></div>
                    <a href="client/cars.php" class="btn btn-block">Book Now</a>
                </div>
            </div>

            <!-- Car Card 3 -->
            <div class="car-card">
                <img src="assets/images/car3.jpg" alt="BMW X5" class="car-image">
                <div class="car-details">
                    <h3>BMW X5 2024</h3>
                    <div class="car-specs">
                        <span class="spec">👥 5 Seats</span>
                        <span class="spec">⚙️ Automatic</span>
                        <span class="spec">⛽ Diesel</span>
                    </div>
                    <div class="car-price">$95 <span>/ day</span></div>
                    <a href="client/cars.php" class="btn btn-block">Book Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="section text-center">
        <h2 class="section-title">Ready to Hit the Road?</h2>
        <p style="font-size: 18px; color: #666; margin-bottom: 30px;">
            Book your perfect car today and enjoy the freedom of the open road
        </p>
        <a href="client/register.php" class="btn" style="padding: 16px 40px; font-size: 18px;">
            Get Started Now
        </a>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 CarRental Pro. All rights reserved.</p>
            <p>Contact us: info@carrentalpro.com | +1 (555) 123-4567</p>
        </div>
    </footer>
</body>
</html>