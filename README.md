# ProFix Masters - Service Marketplace Platform

ProFix Masters is a comprehensive multi-service booking platform built with **Core PHP 5**, **HTML5**, **CSS3**, **JavaScript (Vanilla)**, and **MySQL**. It connects customers with verified service providers across various categories like AC Service, Home Cleaning, Plumbing, Electrical, and more.

## Features

### For Users
- Browse and search services by category, location, and keywords
- View detailed service information with ratings and reviews
- Book services with preferred date, time, and location
- Track booking status (Pending, Confirmed, Completed, Cancelled)
- Rate and review completed services
- Manage profile and booking history

### For Service Providers
- Register and get verified by admin
- List multiple services with images and pricing
- Manage incoming booking requests (Accept/Reject)
- Mark bookings as completed
- Track earnings with commission breakdown
- View ratings and reviews from customers

### For Administrators
- Complete platform management dashboard
- Approve/reject provider registrations
- Manage users, providers, and services
- Handle service categories
- Monitor all bookings and transactions
- Commission and payment management (10-20% configurable)
- Analytics and reports
- Content management (About, Terms, Privacy Policy, Contact)

## Technology Stack

- **Backend**: PHP 5+ (Core PHP, no frameworks)
- **Frontend**: HTML5, CSS3 (Responsive Design)
- **JavaScript**: Vanilla JS (No jQuery or libraries)
- **Database**: MySQL 5.5+
- **Icons**: Font Awesome 6.0

## Security Features

- Password hashing using `password_hash()` and `password_verify()`
- SQL injection prevention with prepared statements
- XSS protection using `htmlspecialchars()`
- Session management with timeout
- Role-based access control
- File upload validation

## Installation Instructions

### Prerequisites
- PHP 5.6+ (Recommended: PHP 7.4+)
- MySQL 5.5+
- Apache/Nginx web server
- mod_rewrite enabled (for clean URLs)

### Step 1: Clone the Repository
```bash
git clone https://github.com/tejindersingh8877-blip/Akram.git
cd Akram
```

### Step 2: Create Database
1. Open phpMyAdmin or MySQL command line
2. Create a new database named `profix_masters`
3. Import the SQL schema:
```bash
mysql -u root -p profix_masters < database/profix_masters.sql
```

### Step 3: Configure Database Connection
Edit `config/db.php` and update database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'profix_masters');
```

### Step 4: Set File Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/services/
chmod 755 uploads/documents/
```

### Step 5: Access the Application
```
http://localhost/Akram/
```

## Default Credentials

### Admin Access
- URL: `http://localhost/Akram/admin/login.php`
- Username: `admin`
- Password: `Admin@123`

### Test User Account
- Email: `user@test.com`
- Password: `User@123`

### Test Provider Account
- Email: `provider@test.com`
- Password: `Provider@123`

**Important:** Change all default passwords after first login!

## Commission Logic

```
Booking Total = Service Price
Admin Commission = (Booking Total × Commission Percentage) / 100
Provider Earnings = Booking Total - Admin Commission
```

Default commission: 15% (configurable)

## Support

Email: support@profixmasters.com

## License

MIT License

---

**Version:** 1.0.0  
**Last Updated:** January 2026