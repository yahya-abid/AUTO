CREATE DATABASE IF NOT EXISTS car_rental;
USE car_rental;

-- =============================================
-- 1. LOCATIONS TABLE
-- =============================================
CREATE TABLE location (
  location_id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL,
  ville VARCHAR(25) NOT NULL,
  country VARCHAR(25) NOT NULL,
  owner_name VARCHAR(30),
  capital DECIMAL(15,2) DEFAULT 0,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(100) NOT NULL,
  address TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- 2. CLIENTS TABLE
-- =============================================
CREATE TABLE client (
  client_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(25) NOT NULL,
  last_name VARCHAR(25) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  ville VARCHAR(25) NOT NULL,
  country VARCHAR(25) NOT NULL,
  date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  telephone_client VARCHAR(30) NOT NULL,
  mot_de_passe VARCHAR(255) NOT NULL,
  numero_permis VARCHAR(50) NOT NULL,
  date_naissance DATE,
  adresse TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  is_verified BOOLEAN DEFAULT FALSE,
  last_login TIMESTAMP NULL,
  INDEX idx_client_email (email),
  INDEX idx_client_name (last_name, first_name),
  INDEX idx_client_ville (ville)
) ENGINE=InnoDB;

-- =============================================
-- 3. EMPLOYEES TABLE
-- =============================================
CREATE TABLE employee (
  employee_id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(25) NOT NULL,
  last_name VARCHAR(25) NOT NULL,
  poste VARCHAR(50) NOT NULL,
  hire_date DATE NOT NULL,
  telephone_employee VARCHAR(30) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  location_id INT,
  is_owner BOOLEAN DEFAULT FALSE,
  is_active BOOLEAN DEFAULT TRUE,
  salary DECIMAL(12,2),
  mot_de_passe VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_employee_location FOREIGN KEY (location_id) 
    REFERENCES location(location_id) ON DELETE SET NULL,
  INDEX idx_employee_location (location_id),
  INDEX idx_employee_poste (poste)
) ENGINE=InnoDB;

-- =============================================
-- 4. CARS TABLE
-- =============================================
CREATE TABLE car (
  car_id INT AUTO_INCREMENT PRIMARY KEY,
  matriculation VARCHAR(30) NOT NULL UNIQUE,
  marque VARCHAR(50) NOT NULL,
  model VARCHAR(50) NOT NULL,
  annee SMALLINT NOT NULL,
  couleur VARCHAR(30),
  type_carburant ENUM('essence', 'diesel', 'electrique', 'hybride') DEFAULT 'essence',
  transmission ENUM('automatique', 'manuelle') DEFAULT 'manuelle',
  nombre_portes TINYINT DEFAULT 5,
  nombre_places TINYINT DEFAULT 5,
  puissance_fiscale SMALLINT,
  etat_car ENUM('excellent', 'bon', 'moyen', 'maintenance', 'hors_service') DEFAULT 'bon',
  kilometrage INT DEFAULT 0,
  prix_jour DECIMAL(10,2) NOT NULL,
  location_id INT NOT NULL,
  date_achat DATE,
  valeur_assuree DECIMAL(12,2),
  is_available BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_car_location FOREIGN KEY (location_id) 
    REFERENCES location(location_id) ON DELETE RESTRICT,
  CONSTRAINT chk_car_prix_jour CHECK (prix_jour > 0),
  CONSTRAINT chk_car_kilometrage CHECK (kilometrage >= 0),
  INDEX idx_car_matriculation (matriculation),
  INDEX idx_car_location (location_id),
  INDEX idx_car_marque_model (marque, model),
  INDEX idx_car_etat (etat_car),
  INDEX idx_car_disponible (is_available, location_id)
) ENGINE=InnoDB;

-- =============================================
-- 5. CAR FEATURES TABLE
-- =============================================
CREATE TABLE car_feature (
  feature_id INT AUTO_INCREMENT PRIMARY KEY,
  car_id INT NOT NULL,
  feature_type VARCHAR(50) NOT NULL,
  feature_name VARCHAR(100) NOT NULL,
  feature_value VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_car_feature_car FOREIGN KEY (car_id) 
    REFERENCES car(car_id) ON DELETE CASCADE,
  INDEX idx_car_feature_car (car_id),
  INDEX idx_feature_type (feature_type),
  UNIQUE INDEX idx_unique_feature (car_id, feature_type, feature_name)
) ENGINE=InnoDB;

-- =============================================
-- 6. CONTRACTS TABLE
-- =============================================
CREATE TABLE contrat (
  contrat_id INT AUTO_INCREMENT PRIMARY KEY,
  contrat_number VARCHAR(50) UNIQUE,
  date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  date_retour_prevu DATE,
  date_retour_reel DATE,
  depot_garantie DECIMAL(10,2) DEFAULT 0,
  prix_total DECIMAL(12,2) NOT NULL,
  prix_jour DECIMAL(10,2) NOT NULL,
  remise DECIMAL(10,2) DEFAULT 0,
  frais_supplementaires DECIMAL(10,2) DEFAULT 0,
  status_contrat ENUM('draft', 'pending', 'confirmed', 'active', 'completed', 'cancelled', 'overdue') DEFAULT 'draft',
  client_id INT NOT NULL,
  car_id INT NOT NULL,
  location_id INT NOT NULL,
  processed_by_employee_id INT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_contrat_client FOREIGN KEY (client_id) 
    REFERENCES client(client_id) ON DELETE RESTRICT,
  CONSTRAINT fk_contrat_car FOREIGN KEY (car_id) 
    REFERENCES car(car_id) ON DELETE RESTRICT,
  CONSTRAINT fk_contrat_location FOREIGN KEY (location_id) 
    REFERENCES location(location_id) ON DELETE RESTRICT,
  CONSTRAINT fk_contrat_employee FOREIGN KEY (processed_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  CONSTRAINT chk_contrat_dates CHECK (date_fin >= date_debut),
  CONSTRAINT chk_contrat_prix_total CHECK (prix_total > 0),
  INDEX idx_contrat_client (client_id),
  INDEX idx_contrat_car (car_id),
  INDEX idx_contrat_dates (date_debut, date_fin),
  INDEX idx_contrat_status (status_contrat),
  INDEX idx_contrat_number (contrat_number),
  INDEX idx_contrat_location (location_id)
) ENGINE=InnoDB;

-- =============================================
-- 7. PAYMENTS TABLE
-- =============================================
CREATE TABLE paiement (
  paiement_id INT AUTO_INCREMENT PRIMARY KEY,
  paiement_reference VARCHAR(100) UNIQUE,
  montant DECIMAL(12,2) NOT NULL,
  mode_payement ENUM('cash', 'credit_card', 'debit_card', 'bank_transfer', 'check', 'mobile_payment') NOT NULL,
  date_pay DATETIME DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  transaction_id VARCHAR(150),
  contrat_id INT NOT NULL,
  employee_id INT,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_paiement_contrat FOREIGN KEY (contrat_id) 
    REFERENCES contrat(contrat_id) ON DELETE CASCADE,
  CONSTRAINT fk_paiement_employee FOREIGN KEY (employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  CONSTRAINT chk_paiement_montant CHECK (montant > 0),
  INDEX idx_paiement_contrat (contrat_id),
  INDEX idx_paiement_date (date_pay),
  INDEX idx_paiement_status (status),
  INDEX idx_paiement_reference (paiement_reference)
) ENGINE=InnoDB;

-- =============================================
-- 8. INSURANCE TABLE
-- =============================================
CREATE TABLE insurance (
  insurance_id INT AUTO_INCREMENT PRIMARY KEY,
  contrat_id INT NOT NULL,
  provider_name VARCHAR(100) NOT NULL,
  policy_number VARCHAR(100),
  coverage_type ENUM('basic', 'premium', 'full') DEFAULT 'basic',
  coverage_limit DECIMAL(12,2),
  franchise DECIMAL(10,2) DEFAULT 0,
  daily_cost DECIMAL(10,2) NOT NULL,
  start_date DATE,
  end_date DATE,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_insurance_contrat FOREIGN KEY (contrat_id) 
    REFERENCES contrat(contrat_id) ON DELETE CASCADE,
  CONSTRAINT chk_insurance_dates CHECK (end_date >= start_date OR end_date IS NULL OR start_date IS NULL),
  INDEX idx_insurance_contrat (contrat_id),
  INDEX idx_insurance_provider (provider_name)
) ENGINE=InnoDB;

-- =============================================
-- 9. COMPENSATION TABLE
-- =============================================
CREATE TABLE compensation (
  compensation_id INT AUTO_INCREMENT PRIMARY KEY,
  type_comp ENUM('remboursement', 'remplacement', 'extension', 'discount') NOT NULL,
  montant DECIMAL(12,2) DEFAULT 0,
  description TEXT NOT NULL,
  date_issue DATETIME DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending', 'approved', 'completed', 'rejected') DEFAULT 'pending',
  contrat_id INT NOT NULL,
  replacement_car_id INT DEFAULT NULL,
  processed_by_employee_id INT DEFAULT NULL,
  approved_by_employee_id INT DEFAULT NULL,
  date_approval DATE,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_compensation_contrat FOREIGN KEY (contrat_id) 
    REFERENCES contrat(contrat_id) ON DELETE CASCADE,
  CONSTRAINT fk_compensation_replacement_car FOREIGN KEY (replacement_car_id) 
    REFERENCES car(car_id) ON DELETE SET NULL,
  CONSTRAINT fk_compensation_processed_employee FOREIGN KEY (processed_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  CONSTRAINT fk_compensation_approved_employee FOREIGN KEY (approved_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  INDEX idx_compensation_contrat (contrat_id),
  INDEX idx_compensation_status (status),
  INDEX idx_compensation_type (type_comp)
) ENGINE=InnoDB;

-- =============================================
-- 10. MAINTENANCE TABLE
-- =============================================
CREATE TABLE maintenance (
  maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
  maintenance_number VARCHAR(50) UNIQUE,
  car_id INT NOT NULL,
  type_maintenance ENUM('preventive', 'corrective', 'accident', 'inspection') NOT NULL,
  date_intervention DATE NOT NULL,
  date_fin DATE,
  cost DECIMAL(12,2) DEFAULT 0,
  description TEXT NOT NULL,
  status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
  kilometrage_entree INT,
  kilometrage_sortie INT,
  date_prochaine DATE DEFAULT NULL,
  performed_by_employee_id INT DEFAULT NULL,
  approved_by_employee_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_maintenance_car FOREIGN KEY (car_id) 
    REFERENCES car(car_id) ON DELETE CASCADE,
  CONSTRAINT fk_maintenance_performed_employee FOREIGN KEY (performed_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  CONSTRAINT fk_maintenance_approved_employee FOREIGN KEY (approved_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  INDEX idx_maintenance_car (car_id),
  INDEX idx_maintenance_date (date_intervention),
  INDEX idx_maintenance_status (status),
  INDEX idx_maintenance_type (type_maintenance)
) ENGINE=InnoDB;

-- =============================================
-- 11. INCIDENT REPORTS TABLE
-- =============================================
CREATE TABLE incident (
  incident_id INT AUTO_INCREMENT PRIMARY KEY,
  incident_number VARCHAR(50) UNIQUE,
  contrat_id INT NOT NULL,
  date_incident DATETIME NOT NULL,
  date_report DATETIME DEFAULT CURRENT_TIMESTAMP,
  description TEXT NOT NULL,
  damage_cost DECIMAL(10,2) DEFAULT 0,
  damage_description TEXT,
  status ENUM('reported', 'investigating', 'resolved', 'closed') DEFAULT 'reported',
  reported_by_employee_id INT,
  resolved_by_employee_id INT,
  date_resolved DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_incident_contrat FOREIGN KEY (contrat_id) 
    REFERENCES contrat(contrat_id) ON DELETE CASCADE,
  CONSTRAINT fk_incident_reported_employee FOREIGN KEY (reported_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  CONSTRAINT fk_incident_resolved_employee FOREIGN KEY (resolved_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  INDEX idx_incident_contrat (contrat_id),
  INDEX idx_incident_date (date_incident),
  INDEX idx_incident_status (status)
) ENGINE=InnoDB;

-- =============================================
-- 12. DAMAGE PHOTOS TABLE
-- =============================================
CREATE TABLE damage_photo (
  photo_id INT AUTO_INCREMENT PRIMARY KEY,
  incident_id INT NOT NULL,
  photo_url VARCHAR(500) NOT NULL,
  description VARCHAR(255),
  taken_by_employee_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_damage_photo_incident FOREIGN KEY (incident_id) 
    REFERENCES incident(incident_id) ON DELETE CASCADE,
  CONSTRAINT fk_damage_photo_employee FOREIGN KEY (taken_by_employee_id) 
    REFERENCES employee(employee_id) ON DELETE SET NULL,
  INDEX idx_damage_photo_incident (incident_id)
) ENGINE=InnoDB;

-- =============================================
-- 13. RESERVATIONS TABLE
-- =============================================
CREATE TABLE reservation (
  reservation_id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_number VARCHAR(50) UNIQUE,
  client_id INT NOT NULL,
  car_id INT NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  status ENUM('pending', 'confirmed', 'cancelled', 'no_show') DEFAULT 'pending',
  source ENUM('website', 'phone', 'in_person', 'mobile_app') DEFAULT 'website',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservation_client FOREIGN KEY (client_id) 
    REFERENCES client(client_id) ON DELETE CASCADE,
  CONSTRAINT fk_reservation_car FOREIGN KEY (car_id) 
    REFERENCES car(car_id) ON DELETE CASCADE,
  CONSTRAINT chk_reservation_dates CHECK (date_fin >= date_debut),
  INDEX idx_reservation_client (client_id),
  INDEX idx_reservation_car (car_id),
  INDEX idx_reservation_dates (date_debut, date_fin),
  INDEX idx_reservation_status (status)
) ENGINE=InnoDB;

-- =============================================
-- 14. AUDIT LOG TABLE
-- =============================================
CREATE TABLE audit_log (
  audit_id INT AUTO_INCREMENT PRIMARY KEY,
  table_name VARCHAR(50) NOT NULL,
  record_id INT NOT NULL,
  action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
  old_values JSON,
  new_values JSON,
  changed_by_employee_id INT,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_table (table_name, record_id),
  INDEX idx_audit_date (changed_at),
  INDEX idx_audit_employee (changed_by_employee_id)
) ENGINE=InnoDB;

-- =============================================
-- 15. PRICING RULES TABLE
-- =============================================
CREATE TABLE pricing_rule (
  rule_id INT AUTO_INCREMENT PRIMARY KEY,
  rule_name VARCHAR(100) NOT NULL,
  rule_type ENUM('seasonal', 'promotional', 'loyalty', 'group') NOT NULL,
  discount_percentage DECIMAL(5,2) DEFAULT 0,
  discount_amount DECIMAL(10,2) DEFAULT 0,
  min_days INT DEFAULT 1,
  max_days INT,
  start_date DATE,
  end_date DATE,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pricing_rule_type (rule_type),
  INDEX idx_pricing_rule_dates (start_date, end_date)
) ENGINE=InnoDB;

-- =============================================
-- 16. REVIEWS TABLE
-- =============================================
CREATE TABLE review (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  contrat_id INT NOT NULL,
  client_id INT NOT NULL,
  car_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comment TEXT,
  is_approved BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_contrat FOREIGN KEY (contrat_id) 
    REFERENCES contrat(contrat_id) ON DELETE CASCADE,
  CONSTRAINT fk_review_client FOREIGN KEY (client_id) 
    REFERENCES client(client_id) ON DELETE CASCADE,
  CONSTRAINT fk_review_car FOREIGN KEY (car_id) 
    REFERENCES car(car_id) ON DELETE CASCADE,
  CONSTRAINT chk_review_rating CHECK (rating >= 1 AND rating <= 5),
  UNIQUE INDEX idx_review_unique (contrat_id, client_id, car_id),
  INDEX idx_review_rating (rating),
  INDEX idx_review_car (car_id)
) ENGINE=InnoDB;

-- =============================================
-- 17. NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE notification (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  user_type ENUM('client', 'employee', 'admin') NOT NULL,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
  is_read BOOLEAN DEFAULT FALSE,
  action_url VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notification_user (user_id, user_type),
  INDEX idx_notification_read (is_read),
  INDEX idx_notification_created (created_at)
) ENGINE=InnoDB;

-- =============================================
-- ADDITIONAL INDEXES FOR PERFORMANCE
-- =============================================
CREATE INDEX idx_location_ville ON location(ville);
CREATE INDEX idx_location_country ON location(country);
CREATE INDEX idx_car_annee ON car(annee);
CREATE INDEX idx_car_prix_jour ON car(prix_jour);
CREATE INDEX idx_contrat_date_creation ON contrat(date_creation);
CREATE INDEX idx_paiement_mode ON paiement(mode_payement);
CREATE INDEX idx_reservation_source ON reservation(source);
CREATE INDEX idx_maintenance_dates ON maintenance(date_intervention, date_prochaine);
CREATE INDEX idx_incident_resolution ON incident(date_incident, date_resolved);
