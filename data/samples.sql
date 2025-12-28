INSERT INTO location (nom, ville, country, owner_name, capital, phone, email, address) VALUES
('CarRental Paris Central', 'Paris', 'France', 'Jean Dupont', 50000.00, '+33 1 45 67 89 00', 'paris@carrental.com', '123 Avenue des Champs-Élysées, 75008 Paris'),
('CarRental Lyon Station', 'Lyon', 'France', 'Marie Lambert', 35000.00, '+33 4 72 34 56 78', 'lyon@carrental.com', '45 Rue de la République, 69002 Lyon'),
('CarRental Nice Airport', 'Nice', 'France', 'Pierre Moreau', 40000.00, '+33 4 93 21 43 65', 'nice@carrental.com', 'Aéroport Nice Côte d Azur, Terminal 1, 06200 Nice'),
('CarRental Marseille Port', 'Marseille', 'France', 'Sophie Bernard', 30000.00, '+33 4 91 23 45 67', 'marseille@carrental.com', 'Port de Marseille, Quai d Arenc, 13002 Marseille');

=============================================
2. CLIENTS TABLE
=============================================
INSERT INTO client (client_id, first_name, last_name, email, ville, country, telephone_client, mot_de_passe, numero_permis, date_naissance, adresse, is_active, is_verified) VALUES
(1, 'Thomas', 'Martin', 'thomas.martin@email.com', 'Paris', 'France', '+33 6 12 34 56 78', '$2y$10$hashedpassword1', 'PERM123456789', '1985-03-15', '15 Rue de Rivoli, 75004 Paris', TRUE, TRUE),
(2, 'Emma', 'Dubois', 'emma.dubois@email.com', 'Lyon', 'France', '+33 6 23 45 67 89', '$2y$10$hashedpassword2', 'PERM987654321', '1990-07-22', '8 Rue de la Bourse, 69002 Lyon', TRUE, TRUE),
(3, 'Lucas', 'Petit', 'lucas.petit@email.com', 'Nice', 'France', '+33 6 34 56 78 90', '$2y$10$hashedpassword3', 'PERM456789123', '1992-11-30', '25 Promenade des Anglais, 06000 Nice', TRUE, TRUE),
(4, 'Chloé', 'Roux', 'chloe.roux@email.com', 'Marseille', 'France', '+33 6 45 67 89 01', '$2y$10$hashedpassword4', 'PERM789123456', '1988-05-18', '42 Cours Belsunce, 13001 Marseille', TRUE, TRUE);

-- =============================================
-- 3. EMPLOYEES TABLE
-- =============================================
INSERT INTO employee (employee_id, first_name, last_name, poste, hire_date, telephone_employee, email, location_id, is_owner, salary, is_active, mot_de_passe) VALUES
(1, 'Jean', 'Dupont', 'Manager', '2020-01-15', '+33 1 45 67 89 01', 'jean.dupont@carrental.com', 1, TRUE, 4500.00, TRUE, '$2y$10$empassword1'),
(2, 'Sarah', 'Lefevre', 'Rental Agent', '2021-03-10', '+33 1 45 67 89 02', 'sarah.lefevre@carrental.com', 1, FALSE, 2800.00, TRUE, '$2y$10$empassword2'),
(3, 'Antoine', 'Girard', 'Mechanic', '2019-06-22', '+33 4 72 34 56 79', 'antoine.girard@carrental.com', 2, FALSE, 3200.00, TRUE, '$2y$10$empassword3'),
(4, 'Laura', 'Mercier', 'Customer Service', '2022-02-14', '+33 4 93 21 43 66', 'laura.mercier@carrental.com', 3, FALSE, 2500.00, TRUE, '$2y$10$empassword4'),
(5, 'Marc', 'Lemoine', 'Rental Agent', '2021-08-05', '+33 4 91 23 45 68', 'marc.lemoine@carrental.com', 4, FALSE, 2700.00, TRUE, '$2y$10$empassword5');

-- =============================================
-- 4. CARS TABLE
-- =============================================
INSERT INTO car (car_id, matriculation, marque, model, annee, couleur, type_carburant, transmission, nombre_portes, nombre_places, puissance_fiscale, etat_car, kilometrage, prix_jour, location_id, date_achat, valeur_assuree, is_available) VALUES
(1, 'AB-123-CD', 'Peugeot', '208', 2022, 'Blanc', 'essence', 'manuelle', 5, 5, 6, 'excellent', 15000, 45.00, 1, '2022-03-10', 18000.00, TRUE),
(2, 'EF-456-GH', 'Renault', 'Clio', 2023, 'Bleu', 'diesel', 'automatique', 5, 5, 5, 'bon', 8000, 50.00, 1, '2023-01-15', 20000.00, TRUE),
(3, 'IJ-789-KL', 'Citroën', 'C3', 2021, 'Rouge', 'essence', 'manuelle', 5, 5, 5, 'excellent', 25000, 42.00, 2, '2021-06-20', 16000.00, TRUE),
(4, 'MN-012-OP', 'Volkswagen', 'Golf', 2023, 'Noir', 'hybride', 'automatique', 5, 5, 7, 'excellent', 5000, 65.00, 3, '2023-02-28', 28000.00, FALSE),
(5, 'QR-345-ST', 'Tesla', 'Model 3', 2023, 'Blanc', 'electrique', 'automatique', 4, 5, 9, 'excellent', 3000, 85.00, 3, '2023-03-01', 45000.00, TRUE),
(6, 'UV-678-WX', 'BMW', 'Serie 3', 2022, 'Gris', 'diesel', 'automatique', 4, 5, 8, 'moyen', 45000, 75.00, 4, '2022-04-10', 35000.00, TRUE),
(7, 'YZ-901-AB', 'Mercedes', 'Classe A', 2023, 'Noir', 'essence', 'automatique', 5, 5, 7, 'bon', 12000, 70.00, 2, '2023-03-15', 32000.00, TRUE),
(8, 'CD-234-EF', 'Ford', 'Focus', 2022, 'Bleu', 'diesel', 'manuelle', 5, 5, 6, 'bon', 30000, 48.00, 4, '2022-05-20', 19000.00, TRUE);

-- =============================================
-- 5. CAR FEATURES TABLE
-- =============================================
INSERT INTO car_feature (feature_id, car_id, feature_type, feature_name, feature_value) VALUES
(1, 1, 'Comfort', 'Air Conditioning', 'Manual'),
(2, 1, 'Entertainment', 'Radio', 'Bluetooth/USB'),
(3, 1, 'Safety', 'Airbags', '6'),
(4, 2, 'Comfort', 'Air Conditioning', 'Automatic'),
(5, 2, 'Entertainment', 'Navigation System', 'Built-in GPS'),
(6, 2, 'Safety', 'Parking Sensors', 'Rear'),
(7, 3, 'Comfort', 'Air Conditioning', 'Manual'),
(8, 3, 'Entertainment', 'Radio', 'FM/AM'),
(9, 4, 'Technology', 'Connectivity', 'Apple CarPlay/Android Auto'),
(10, 4, 'Safety', 'Lane Assist', 'Yes'),
(11, 5, 'Technology', 'Autopilot', 'Basic'),
(12, 5, 'Entertainment', 'Screen', '15-inch Touchscreen'),
(13, 5, 'Comfort', 'Seats', 'Heated'),
(14, 6, 'Comfort', 'Air Conditioning', 'Automatic 4-zone'),
(15, 6, 'Technology', 'Head-up Display', 'Yes'),
(16, 7, 'Safety', 'Emergency Brake Assist', 'Yes'),
(17, 7, 'Comfort', 'Seats', 'Leather'),
(18, 8, 'Entertainment', 'Radio', 'Bluetooth');

-- =============================================
-- 6. CONTRACTS TABLE
-- =============================================
INSERT INTO contrat (contrat_id, contrat_number, date_debut, date_fin, date_retour_prevu, depot_garantie, prix_total, prix_jour, remise, status_contrat, client_id, car_id, location_id, processed_by_employee_id, notes) VALUES
(1, 'CT-2023-001', '2023-10-01', '2023-10-05', '2023-10-05', 500.00, 180.00, 45.00, 0.00, 'completed', 1, 1, 1, 2, 'Client requested child seat'),
(2, 'CT-2023-002', '2023-10-10', '2023-10-15', '2023-10-15', 500.00, 250.00, 50.00, 0.00, 'active', 2, 2, 1, 2, 'Business trip'),
(3, 'CT-2023-003', '2023-10-08', '2023-10-12', '2023-10-12', 300.00, 168.00, 42.00, 0.00, 'completed', 3, 3, 2, 3, NULL),
(4, 'CT-2023-004', '2023-10-20', '2023-10-25', '2023-10-25', 800.00, 325.00, 65.00, 0.00, 'confirmed', 4, 4, 3, 4, 'Airport pickup'),
(5, 'CT-2023-005', '2023-11-01', '2023-11-07', '2023-11-07', 1000.00, 510.00, 85.00, 25.50, 'pending', 1, 5, 3, 4, 'Loyalty discount applied'),
(6, 'CT-2023-006', '2023-09-15', '2023-09-20', '2023-09-20', 600.00, 300.00, 75.00, 0.00, 'completed', 2, 6, 4, 5, NULL),
(7, 'CT-2023-007', '2023-11-05', '2023-11-10', '2023-11-10', 700.00, 350.00, 70.00, 0.00, 'draft', 3, 7, 2, 3, 'To be confirmed');

-- =============================================
-- 7. PAYMENTS TABLE
-- =============================================
INSERT INTO paiement (paiement_id, paiement_reference, montant, mode_payement, date_pay, status, contrat_id, employee_id, notes) VALUES
(1, 'PAY-2023-001', 180.00, 'credit_card', '2023-09-28 10:30:00', 'completed', 1, 2, 'Full payment'),
(2, 'PAY-2023-002', 250.00, 'credit_card', '2023-10-08 14:15:00', 'completed', 2, 2, 'Deposit + daily rate'),
(3, 'PAY-2023-003', 168.00, 'debit_card', '2023-10-07 09:45:00', 'completed', 3, 3, 'Full payment'),
(4, 'PAY-2023-004', 162.50, 'bank_transfer', '2023-10-18 11:20:00', 'completed', 4, 4, '50% deposit'),
(5, 'PAY-2023-005', 162.50, 'credit_card', '2023-10-20 16:30:00', 'pending', 4, 4, 'Balance to pay'),
(6, 'PAY-2023-006', 300.00, 'cash', '2023-09-14 15:45:00', 'completed', 6, 5, 'Full payment in cash'),
(7, 'PAY-2023-007', 100.00, 'credit_card', '2023-11-04 10:00:00', 'pending', 7, 3, 'Initial deposit');

-- =============================================
-- 8. INSURANCE TABLE
-- =============================================
INSERT INTO insurance (insurance_id, contrat_id, provider_name, policy_number, coverage_type, coverage_limit, franchise, daily_cost, start_date, end_date, is_active) VALUES
(1, 1, 'AXA Assurance', 'AXA-12345', 'basic', 100000.00, 500.00, 8.00, '2023-10-01', '2023-10-05', TRUE),
(2, 2, 'Allianz', 'ALL-67890', 'premium', 200000.00, 200.00, 12.00, '2023-10-10', '2023-10-15', TRUE),
(3, 3, 'Groupama', 'GRP-54321', 'basic', 100000.00, 500.00, 7.50, '2023-10-08', '2023-10-12', TRUE),
(4, 4, 'MAIF', 'MAIF-98765', 'full', 300000.00, 100.00, 15.00, '2023-10-20', '2023-10-25', TRUE),
(5, 5, 'AXA Assurance', 'AXA-13579', 'premium', 250000.00, 250.00, 13.50, '2023-11-01', '2023-11-07', TRUE),
(6, 6, 'Generali', 'GEN-24680', 'basic', 150000.00, 600.00, 10.00, '2023-09-15', '2023-09-20', TRUE);

-- =============================================
-- 9. COMPENSATION TABLE
-- =============================================
INSERT INTO compensation (compensation_id, type_comp, montant, description, date_issue, status, contrat_id, processed_by_employee_id, approved_by_employee_id, notes) VALUES
(1, 'remboursement', 45.00, 'Late return refund - car returned 1 hour early', '2023-10-05 16:30:00', 'completed', 1, 2, 1, 'Refund processed to credit card'),
(2, 'discount', 25.50, 'Loyalty discount for returning customer', '2023-10-25 14:00:00', 'approved', 5, 4, 4, 'Applied to contract CT-2023-005'),
(3, 'remboursement', 30.00, 'Fuel refund - client returned car with more fuel', '2023-09-20 11:45:00', 'pending', 6, 5, NULL, 'To be approved by manager');

-- =============================================
-- 10. MAINTENANCE TABLE
-- =============================================
INSERT INTO maintenance (maintenance_id, maintenance_number, car_id, type_maintenance, date_intervention, date_fin, cost, description, status, kilometrage_entree, kilometrage_sortie, date_prochaine, performed_by_employee_id) VALUES
(1, 'MNT-2023-001', 1, 'preventive', '2023-09-20', '2023-09-20', 150.00, 'Oil change and filter replacement', 'completed', 14500, 14500, '2024-03-20', 3),
(2, 'MNT-2023-002', 6, 'corrective', '2023-10-05', '2023-10-06', 850.00, 'Brake system repair and pad replacement', 'completed', 44800, 44800, '2024-04-05', 3),
(3, 'MNT-2023-003', 3, 'inspection', '2023-10-10', '2023-10-10', 80.00, 'Annual technical inspection', 'completed', 24800, 24800, '2024-10-10', 3),
(4, 'MNT-2023-004', 8, 'preventive', '2023-11-01', NULL, 200.00, 'Scheduled maintenance', 'in_progress', 29800, NULL, '2024-05-01', 3),
(5, 'MNT-2023-005', 2, 'accident', '2023-10-12', '2023-10-15', 1200.00, 'Front bumper replacement after minor collision', 'scheduled', 8200, NULL, NULL, NULL);

-- =============================================
-- 11. INCIDENT REPORTS TABLE
-- =============================================
INSERT INTO incident (incident_id, incident_number, contrat_id, date_incident, date_report, description, damage_cost, damage_description, status, reported_by_employee_id) VALUES
(1, 'INC-2023-001', 3, '2023-10-10 14:30:00', '2023-10-10 15:00:00', 'Minor scratch on passenger side door', 250.00, '30cm scratch on right door panel', 'resolved', 3),
(2, 'INC-2023-002', 1, '2023-10-04 20:15:00', '2023-10-05 09:00:00', 'Flat tire during rental period', 120.00, 'Tire replacement due to puncture', 'closed', 2),
(3, 'INC-2023-003', 6, '2023-09-18 11:45:00', '2023-09-18 12:30:00', 'Windshield crack from stone impact', 350.00, 'Small crack on driver side windshield', 'investigating', 5);

-- =============================================
-- 12. DAMAGE PHOTOS TABLE
-- =============================================
INSERT INTO damage_photo (photo_id, incident_id, photo_url, description, taken_by_employee_id) VALUES
(1, 1, 'https://storage.example.com/damages/scratch-door-001.jpg', 'Close-up of door scratch', 3),
(2, 1, 'https://storage.example.com/damages/scratch-door-002.jpg', 'Full door view showing scratch', 3),
(3, 2, 'https://storage.example.com/damages/tire-damage-001.jpg', 'Flat tire close-up', 2),
(4, 3, 'https://storage.example.com/damages/windshield-001.jpg', 'Windshield crack detail', 5);

-- =============================================
-- 13. RESERVATIONS TABLE
-- =============================================
INSERT INTO reservation (reservation_id, reservation_number, client_id, car_id, date_debut, date_fin, status, source, notes) VALUES
(1, 'RES-2023-006', 2, 5, '2023-11-15', '2023-11-20', 'confirmed', 'website', 'Airport pickup requested'),
(2, 'RES-2023-007', 3, 1, '2023-11-10', '2023-11-12', 'pending', 'phone', 'To confirm availability'),
(3, 'RES-2023-008', 1, 4, '2023-12-01', '2023-12-08', 'confirmed', 'mobile_app', 'Winter tires needed'),
(4, 'RES-2023-009', 4, 6, '2023-11-25', '2023-11-30', 'confirmed', 'in_person', 'Business meeting transportation'),
(5, 'RES-2023-010', 2, 7, '2023-12-15', '2023-12-22', 'cancelled', 'website', 'Client cancelled due to flight change');

-- =============================================
-- 14. AUDIT LOG TABLE
-- =============================================
INSERT INTO audit_log (audit_id, table_name, record_id, action, old_values, new_values, changed_by_employee_id) VALUES
(1, 'car', 1, 'UPDATE', '{"kilometrage": 14800}', '{"kilometrage": 15000}', 2),
(2, 'contrat', 2, 'INSERT', NULL, '{"contrat_number": "CT-2023-002", "status_contrat": "draft"}', 2),
(3, 'client', 1, 'UPDATE', '{"telephone_client": "+33 6 12 34 56 77"}', '{"telephone_client": "+33 6 12 34 56 78"}', 2),
(4, 'car', 4, 'UPDATE', '{"is_available": true}', '{"is_available": false}', 4),
(5, 'contrat', 5, 'UPDATE', '{"prix_total": 535.50}', '{"prix_total": 510.00, "remise": 25.50}', 4);

-- =============================================
-- 15. PRICING RULES TABLE
-- =============================================
INSERT INTO pricing_rule (rule_id, rule_name, rule_type, discount_percentage, discount_amount, min_days, max_days, start_date, end_date, is_active) VALUES
(1, 'Summer Promotion', 'seasonal', 15.00, 0.00, 3, 14, '2023-06-01', '2023-09-30', TRUE),
(2, 'Loyalty Discount', 'loyalty', 10.00, 0.00, 1, NULL, '2023-01-01', '2023-12-31', TRUE),
(3, 'Weekend Special', 'promotional', 20.00, 0.00, 2, 3, NULL, NULL, TRUE),
(4, 'Long Term Rental', 'promotional', 0.00, 5.00, 7, 30, NULL, NULL, TRUE),
(5, 'Winter Special', 'seasonal', 10.00, 0.00, 2, 10, '2023-12-01', '2024-02-28', TRUE);

-- =============================================
-- 16. REVIEWS TABLE
-- =============================================
INSERT INTO review (review_id, contrat_id, client_id, car_id, rating, comment, is_approved) VALUES
(1, 1, 1, 1, 4, 'Good car, clean and reliable. Would rent again.', TRUE),
(2, 3, 3, 3, 5, 'Excellent service, car was perfect for our trip. Staff very helpful!', TRUE),
(3, 6, 2, 6, 3, 'Car was comfortable but had higher fuel consumption than expected.', TRUE),
(4, 2, 2, 2, 4, 'Smooth automatic transmission, easy to drive in city traffic.', FALSE);

-- =============================================
-- 17. NOTIFICATIONS TABLE
-- =============================================
INSERT INTO notification (notification_id, user_id, user_type, title, message, type, is_read, action_url) VALUES
(1, 1, 'client', 'Reservation Confirmed', 'Your reservation RES-2023-008 has been confirmed for December 1-8, 2023.', 'success', FALSE, '/reservations/RES-2023-008'),
(2, 2, 'employee', 'Maintenance Due', 'Car AB-123-CD (Peugeot 208) is due for maintenance in 30 days.', 'warning', FALSE, '/maintenance/schedule'),
(3, 3, 'client', 'Payment Received', 'Thank you for your payment of €168.00 for contract CT-2023-003.', 'info', TRUE, '/payments/history'),
(4, 1, 'client', 'Car Return Reminder', 'Your rental car is due for return tomorrow. Please ensure timely return.', 'warning', FALSE, '/contracts/CT-2023-002'),
(5, 4, 'employee', 'New Reservation', 'New reservation RES-2023-009 created for BMW Serie 3.', 'info', FALSE, '/reservations/RES-2023-009');
