<?php if (!isset($no_footer)): ?>
    <footer style="background-color: #2c3e50; color: white; padding: 3rem 0; margin-top: 4rem;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div>
                    <h3 style="color: white; margin-bottom: 1rem;">CarRental Pro</h3>
                    <p>Your trusted partner for car rentals. We offer premium vehicles at competitive prices with exceptional customer service.</p>
                </div>
                
                <div>
                    <h4 style="color: white; margin-bottom: 1rem;">Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;">
                            <a href="<?php echo isset($admin_page) ? '../' : ''; ?>index.php" style="color: #bdc3c7; text-decoration: none;">
                                Home
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="<?php echo isset($admin_page) ? '../' : ''; ?>client/cars.php" style="color: #bdc3c7; text-decoration: none;">
                                Available Cars
                            </a>
                        </li>
                        <?php if (isset($auth) && $auth->isLoggedIn()): ?>
                            <?php if ($auth->checkRole('admin')): ?>
                                <li style="margin-bottom: 0.5rem;">
                                    <a href="admin/dashboard.php" style="color: #bdc3c7; text-decoration: none;">
                                        Admin Dashboard
                                    </a>
                                </li>
                            <?php elseif ($auth->checkRole('employee')): ?>
                                <li style="margin-bottom: 0.5rem;">
                                    <a href="employee/dashboard.php" style="color: #bdc3c7; text-decoration: none;">
                                        Employee Panel
                                    </a>
                                </li>
                            <?php else: ?>
                                <li style="margin-bottom: 0.5rem;">
                                    <a href="client/index.php" style="color: #bdc3c7; text-decoration: none;">
                                        My Account
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a href="<?php echo isset($admin_page) ? '../' : ''; ?>logout.php" style="color: #bdc3c7; text-decoration: none;">
                                    Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="<?php echo isset($admin_page) ? '../' : ''; ?>client/login.php" style="color: #bdc3c7; text-decoration: none;">
                                    Client Login
                                </a>
                            </li>
                            <li style="margin-bottom: 0.5rem;">
                                <a href="<?php echo isset($admin_page) ? '../' : ''; ?>client/register.php" style="color: #bdc3c7; text-decoration: none;">
                                    Register
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo isset($admin_page) ? '../' : ''; ?>admin/login.php" style="color: #bdc3c7; text-decoration: none;">
                                    Admin Login
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h4 style="color: white; margin-bottom: 1rem;">Contact Us</h4>
                    <p style="margin-bottom: 0.5rem;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i>
                        123 Rental Street, City, Country
                    </p>
                    <p style="margin-bottom: 0.5rem;">
                        <i class="fas fa-phone" style="margin-right: 0.5rem;"></i>
                        +1 (555) 123-4567
                    </p>
                    <p style="margin-bottom: 0.5rem;">
                        <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i>
                        info@carrentalpro.com
                    </p>
                    <p>
                        <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>
                        Open 24/7
                    </p>
                </div>
                
                <div>
                    <h4 style="color: white; margin-bottom: 1rem;">Business Hours</h4>
                    <p style="margin-bottom: 0.5rem;">Monday - Friday: 8:00 AM - 10:00 PM</p>
                    <p style="margin-bottom: 0.5rem;">Saturday: 9:00 AM - 8:00 PM</p>
                    <p>Sunday: 10:00 AM - 6:00 PM</p>
                    
                    <div style="margin-top: 1.5rem;">
                        <h4 style="color: white; margin-bottom: 1rem;">Follow Us</h4>
                        <div style="display: flex; gap: 1rem;">
                            <a href="#" style="color: white; font-size: 1.2rem;">
                                <i class="fab fa-facebook"></i>
                            </a>
                            <a href="#" style="color: white; font-size: 1.2rem;">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" style="color: white; font-size: 1.2rem;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" style="color: white; font-size: 1.2rem;">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <p>&copy; <?php echo date('Y'); ?> CarRental Pro. All rights reserved.</p>
                <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #bdc3c7;">
                    <a href="<?php echo isset($admin_page) ? '../' : ''; ?>privacy.php" style="color: #bdc3c7; text-decoration: none; margin: 0 1rem;">
                        Privacy Policy
                    </a>
                    |
                    <a href="<?php echo isset($admin_page) ? '../' : ''; ?>terms.php" style="color: #bdc3c7; text-decoration: none; margin: 0 1rem;">
                        Terms of Service
                    </a>
                    |
                    <a href="<?php echo isset($admin_page) ? '../' : ''; ?>contact.php" style="color: #bdc3c7; text-decoration: none; margin: 0 1rem;">
                        Contact Us
                    </a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button id="backToTop" style="display: none; position: fixed; bottom: 2rem; right: 2rem; 
           width: 50px; height: 50px; border-radius: 50%; background-color: #3498db; 
           color: white; border: none; cursor: pointer; font-size: 1.2rem; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Back to top button
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
<?php endif; ?>
