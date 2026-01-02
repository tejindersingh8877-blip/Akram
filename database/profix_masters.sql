-- ProFix Masters Database Schema
-- MySQL Database for Multi-Service Booking Platform

CREATE DATABASE IF NOT EXISTS profix_masters;
USE profix_masters;

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_city (city),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: providers
CREATE TABLE IF NOT EXISTS providers (
    provider_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    business_name VARCHAR(150) NOT NULL,
    business_address TEXT,
    city VARCHAR(100),
    service_category VARCHAR(100),
    experience_years INT DEFAULT 0,
    id_proof_path VARCHAR(255),
    verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT,
    INDEX idx_email (email),
    INDEX idx_verification_status (verification_status),
    INDEX idx_city (city),
    INDEX idx_status (status),
    FOREIGN KEY (approved_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: service_categories
CREATE TABLE IF NOT EXISTS service_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    category_icon VARCHAR(255),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category_name (category_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: services
CREATE TABLE IF NOT EXISTS services (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    provider_id INT NOT NULL,
    category_id INT NOT NULL,
    service_title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration INT DEFAULT 60 COMMENT 'Duration in minutes',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider_id (provider_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_price (price),
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES service_categories(category_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: service_images
CREATE TABLE IF NOT EXISTS service_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_id (service_id),
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: bookings
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    provider_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    service_address TEXT,
    special_notes TEXT,
    total_amount DECIMAL(10, 2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,
    INDEX idx_user_id (user_id),
    INDEX idx_service_id (service_id),
    INDEX idx_provider_id (provider_id),
    INDEX idx_booking_status (booking_status),
    INDEX idx_booking_date (booking_date),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: payments
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    provider_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    payment_status ENUM('pending', 'completed') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_provider_id (provider_id),
    INDEX idx_payment_status (payment_status),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: commissions
CREATE TABLE IF NOT EXISTS commissions (
    commission_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    provider_id INT NOT NULL,
    booking_amount DECIMAL(10, 2) NOT NULL,
    commission_percentage DECIMAL(5, 2) DEFAULT 15.00,
    commission_amount DECIMAL(10, 2) NOT NULL,
    provider_earnings DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_provider_id (provider_id),
    INDEX idx_payment_status (payment_status),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: reviews
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    provider_id INT NOT NULL,
    service_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_service_id (service_id),
    INDEX idx_provider_id (provider_id),
    INDEX idx_rating (rating),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(provider_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: cms_pages
CREATE TABLE IF NOT EXISTS cms_pages (
    page_id INT PRIMARY KEY AUTO_INCREMENT,
    page_slug VARCHAR(50) UNIQUE NOT NULL,
    page_title VARCHAR(200) NOT NULL,
    page_content TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page_slug (page_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin account
-- Username: admin, Password: Admin@123
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@profixmasters.com');

-- Insert default service categories
INSERT INTO service_categories (category_name, category_icon, description, status) VALUES
('AC Service', 'fa-snowflake', 'Air Conditioner installation, repair and maintenance services', 'active'),
('Home Cleaning', 'fa-broom', 'Professional home and office cleaning services', 'active'),
('Plumbing', 'fa-wrench', 'Plumbing repair, installation and maintenance', 'active'),
('Electrical', 'fa-bolt', 'Electrical repair, wiring and installation services', 'active'),
('Carpentry', 'fa-hammer', 'Furniture making, repair and carpentry services', 'active'),
('Painting', 'fa-paint-roller', 'Home and office painting services', 'active'),
('Pest Control', 'fa-bug', 'Pest control and fumigation services', 'active'),
('Catering', 'fa-utensils', 'Catering services for events and parties', 'active'),
('Beauty & Salon', 'fa-cut', 'Beauty, salon and grooming services at home', 'active'),
('Appliance Repair', 'fa-tools', 'Repair services for home appliances', 'active');

-- Insert default CMS pages
INSERT INTO cms_pages (page_slug, page_title, page_content) VALUES
('about', 'About Us', '<h2>About ProFix Masters</h2><p>ProFix Masters is your trusted platform for finding and booking reliable local service providers. We connect customers with verified professionals across various service categories.</p><p>Our mission is to make service booking simple, transparent, and efficient.</p>'),
('terms', 'Terms & Conditions', '<h2>Terms & Conditions</h2><p>Welcome to ProFix Masters. By accessing and using our platform, you agree to these terms and conditions.</p><h3>User Responsibilities</h3><p>Users must provide accurate information and maintain account security.</p><h3>Provider Responsibilities</h3><p>Service providers must deliver services as described and maintain professional standards.</p>'),
('privacy', 'Privacy Policy', '<h2>Privacy Policy</h2><p>At ProFix Masters, we take your privacy seriously. This policy describes how we collect, use, and protect your personal information.</p><h3>Information Collection</h3><p>We collect information necessary to provide our services including name, email, phone number, and address.</p><h3>Data Protection</h3><p>We implement security measures to protect your personal information.</p>'),
('contact', 'Contact Us', '<h2>Contact Us</h2><p>Have questions? We\'re here to help!</p><p><strong>Email:</strong> support@profixmasters.com</p><p><strong>Phone:</strong> +1 (555) 123-4567</p><p><strong>Address:</strong> 123 Business Street, Suite 100, City, State 12345</p><p><strong>Business Hours:</strong> Monday - Friday: 9:00 AM - 6:00 PM</p>');

-- Sample test data (optional - for development)
-- Test user: user@test.com / User@123
INSERT INTO users (full_name, email, phone, password, address, city, status) VALUES
('John Doe', 'user@test.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Main Street', 'New York', 'active');

-- Test provider: provider@test.com / Provider@123
INSERT INTO providers (full_name, email, phone, password, business_name, business_address, city, service_category, experience_years, verification_status, status) VALUES
('Jane Smith', 'provider@test.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Smith AC Services', '456 Business Ave', 'New York', 'AC Service', 5, 'approved', 'active');
