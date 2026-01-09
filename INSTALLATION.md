# ProFix Masters - Installation Guide

## Quick Start Guide

### Step 1: Server Requirements

Ensure your server meets these requirements:
- PHP 5.6 or higher (Recommended: PHP 7.4+)
- MySQL 5.5 or higher
- Apache/Nginx web server
- mod_rewrite enabled

### Step 2: Download and Extract

```bash
git clone https://github.com/tejindersingh8877-blip/Akram.git
cd Akram
```

### Step 3: Database Setup

1. **Create Database:**
   - Open phpMyAdmin or MySQL command line
   - Create a new database: `CREATE DATABASE profix_masters;`

2. **Import Schema:**
   ```bash
   mysql -u root -p profix_masters < database/profix_masters.sql
   ```
   
   OR via phpMyAdmin:
   - Select `profix_masters` database
   - Click "Import" tab
   - Choose `database/profix_masters.sql`
   - Click "Go"

### Step 4: Configure Database Connection

Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');  // Change this
define('DB_PASS', 'your_password');  // Change this
define('DB_NAME', 'profix_masters');
```

### Step 5: Set File Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/services/
chmod 755 uploads/documents/
```

### Step 6: Configure PHP Settings

Edit `php.ini` or add to `.htaccess`:
```ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### Step 7: Access the Application

Open your browser and navigate to:
```
http://localhost/Akram/
```

## Default Login Credentials

### Admin Panel
- **URL:** http://localhost/Akram/admin/login.php
- **Username:** admin
- **Password:** Admin@123

### Test User Account
- **URL:** http://localhost/Akram/user/login.php
- **Email:** user@test.com
- **Password:** User@123

### Test Provider Account
- **URL:** http://localhost/Akram/provider/login.php
- **Email:** provider@test.com
- **Password:** Provider@123

**⚠️ IMPORTANT:** Change all default passwords immediately after installation!

## Post-Installation Steps

### 1. Change Admin Password
1. Login as admin
2. Go to Settings
3. Change password

### 2. Configure Commission Rate
Default commission is 15%. To change:
1. Edit `includes/functions.php`
2. Find `calculate_commission()` function
3. Modify default percentage

### 3. Add Service Categories
Pre-loaded categories include:
- AC Service
- Home Cleaning
- Plumbing
- Electrical
- Carpentry
- Painting
- Pest Control
- Catering
- Beauty & Salon
- Appliance Repair

Add more via Admin Panel → Categories

### 4. Test the System

#### Test User Flow:
1. Register as a user
2. Browse services
3. Book a service
4. Check booking status

#### Test Provider Flow:
1. Register as a provider
2. Wait for admin approval
3. Add services
4. Manage booking requests

#### Test Admin Flow:
1. Login as admin
2. Approve provider
3. Monitor bookings
4. View statistics

## Troubleshooting

### Database Connection Error
- Verify credentials in `config/db.php`
- Ensure MySQL service is running
- Check if database exists

### File Upload Error
- Check folder permissions (755)
- Verify PHP upload settings
- Ensure disk space available

### Session Errors
- Check PHP session directory is writable
- Verify session settings in php.ini
- Clear browser cookies

### 404 Errors
- Enable mod_rewrite
- Check .htaccess file exists
- Verify file paths are correct

## Server Configuration Examples

### Apache Virtual Host
```apache
<VirtualHost *:80>
    ServerName profixmasters.local
    DocumentRoot "/path/to/Akram"
    
    <Directory "/path/to/Akram">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name profixmasters.local;
    root /path/to/Akram;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

## Production Deployment Checklist

- [ ] Change all default passwords
- [ ] Update database credentials
- [ ] Configure SSL certificate
- [ ] Enable HTTPS in .htaccess
- [ ] Set display_errors = Off
- [ ] Configure email settings (if implementing)
- [ ] Set up backups
- [ ] Configure firewall
- [ ] Test all features
- [ ] Monitor error logs

## Maintenance

### Database Backup
```bash
mysqldump -u username -p profix_masters > backup_$(date +%Y%m%d).sql
```

### Update Application
```bash
git pull origin main
# Run any database migrations if needed
```

## Support

For issues and questions:
- Email: support@profixmasters.com
- GitHub Issues: https://github.com/tejindersingh8877-blip/Akram/issues

## License

MIT License

---

**Installation Complete!** 🎉

Your ProFix Masters service marketplace is now ready to use.
