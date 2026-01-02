# ProFix Masters - Quick Start & Testing Guide

## 🚀 Quick Setup (5 Minutes)

### Step 1: Start Your Local Server

**Using XAMPP (Windows/Mac/Linux):**
1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Place the project in: `C:\xampp\htdocs\Akram` (Windows) or `/Applications/XAMPP/htdocs/Akram` (Mac)
3. Start Apache and MySQL from XAMPP Control Panel

**Using WAMP (Windows):**
1. Download and install WAMP from [http://www.wampserver.com/](http://www.wampserver.com/)
2. Place the project in: `C:\wamp64\www\Akram`
3. Start WAMP server

**Using Built-in PHP Server (Any OS):**
```bash
cd /path/to/Akram
php -S localhost:8000
```

### Step 2: Setup Database (2 Minutes)

**Option A - Using phpMyAdmin:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `profix_masters`
4. Click "Import" tab
5. Choose file: `database/profix_masters.sql`
6. Click "Go"
7. Done! ✅

**Option B - Using Command Line:**
```bash
# Create database and import
mysql -u root -p
CREATE DATABASE profix_masters;
exit

mysql -u root -p profix_masters < database/profix_masters.sql
```

### Step 3: Configure Database Connection (30 Seconds)

Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password (empty for XAMPP)
define('DB_NAME', 'profix_masters');
```

### Step 4: Set Permissions (Linux/Mac only)

```bash
chmod 755 uploads/
chmod 755 uploads/services/
chmod 755 uploads/documents/
```

### Step 5: Access the Platform

Open your browser and go to:
```
http://localhost/Akram/
```

---

## 🧪 Testing Guide

### Test Scenario 1: Admin Functions (5 minutes)

**1. Login as Admin:**
- URL: `http://localhost/Akram/admin/login.php`
- Username: `admin`
- Password: `Admin@123`

**2. View Dashboard:**
- You should see 8 statistics cards
- Check: Total Users, Providers, Services, Bookings, Revenue, Commission
- Verify numbers match sample data

**3. Approve a Provider:**
- Click "Providers" in sidebar
- Click "Pending Approval" filter
- You'll see test provider (if not already approved)
- Click "Approve" button
- Verify success message appears
- Provider status should change to "Approved"

**4. View Analytics:**
- Go back to Dashboard
- Verify all metrics are displayed correctly
- Check recent bookings table
- Verify booking details are visible

**✅ Expected Result:** Admin can view all platform statistics and approve providers

---

### Test Scenario 2: Provider Functions (10 minutes)

**1. Login as Provider:**
- URL: `http://localhost/Akram/provider/login.php`
- Email: `provider@test.com`
- Password: `Provider@123`

**2. Check Verification Status:**
- You should see dashboard with earnings stats
- If pending: "Your account is pending verification" message
- If approved: Full access to add services

**3. Add a New Service:**
- Click "Add Service" in sidebar
- Fill in the form:
  - Service Title: "Professional AC Installation"
  - Category: Select "AC Service"
  - Description: "Expert AC installation and setup"
  - Price: 150.00
  - Duration: 120 (minutes)
- Upload 1-2 images (JPEG/PNG)
- Click "Add Service"
- Verify success message

**4. View My Services:**
- Click "My Services" in sidebar
- Verify your new service appears
- Check service details display correctly
- Test "View", "Edit", and "Delete" buttons

**5. Manage Bookings:**
- Click "Bookings" in sidebar
- View pending booking requests
- Click "Accept" on a pending booking
- Verify booking status changes to "Confirmed"
- Test "Mark Complete" button on confirmed booking

**6. Check Earnings:**
- Click "Earnings" in sidebar
- View earnings statistics
- Verify commission calculation (85% to provider, 15% to admin)

**✅ Expected Result:** Provider can add services and manage bookings

---

### Test Scenario 3: User Functions (10 minutes)

**1. Register New User:**
- URL: `http://localhost/Akram/user/register.php`
- Fill in registration form:
  - Full Name: "Test Customer"
  - Email: "customer@test.com"
  - Phone: "1234567890"
  - Password: "Test@123"
  - Address: "123 Test Street"
  - City: "New York"
- Click "Register"
- Verify redirect to login page

**2. Login as User:**
- Email: "customer@test.com"
- Password: "Test@123"
- Verify redirect to dashboard

**3. Browse Services:**
- Click "Browse Services" or go to `http://localhost/Akram/user/search.php`
- You should see list of available services
- Test filters:
  - Select a category
  - Enter a city
  - Set price range
  - Change sort order
- Click "Search"
- Verify results update

**4. View Service Details:**
- Click "View Details" on any service
- Verify all information displays:
  - Service title and description
  - Price and duration
  - Provider name and rating
  - Service images
  - Reviews (if any)

**5. Book a Service:**
- Click "Book This Service"
- Fill in booking form:
  - Select future date
  - Select time
  - Enter service address
  - Add special notes (optional)
- Click "Confirm Booking"
- Verify success message
- Verify redirect to "My Bookings"

**6. View My Bookings:**
- Click "My Bookings" in sidebar
- Verify your booking appears
- Check booking status (should be "Pending")
- View booking details

**✅ Expected Result:** User can search, view, and book services

---

### Test Scenario 4: Complete Booking Flow (15 minutes)

**Complete End-to-End Test:**

1. **User books service** (as user@test.com)
   - Search and book a service
   - Status: Pending

2. **Provider receives request** (as provider@test.com)
   - Login as provider
   - Go to Bookings
   - See pending request
   - Click "Accept"
   - Status: Confirmed

3. **User sees confirmation** (as user@test.com)
   - Login as user
   - Go to My Bookings
   - See confirmed status

4. **Provider completes service** (as provider@test.com)
   - Login as provider
   - Go to Bookings
   - Find confirmed booking
   - Click "Mark Complete"
   - Status: Completed

5. **Admin monitors** (as admin)
   - Login as admin
   - View Dashboard
   - Check updated statistics
   - View all bookings
   - Verify commission calculated

**✅ Expected Result:** Complete booking workflow functions properly

---

## 🔍 Feature Testing Checklist

### User Features:
- [ ] User registration
- [ ] User login/logout
- [ ] View dashboard statistics
- [ ] Search services with filters
- [ ] View service details
- [ ] Book a service
- [ ] View booking history
- [ ] Filter bookings by status

### Provider Features:
- [ ] Provider registration
- [ ] Document upload (ID proof)
- [ ] Provider login/logout
- [ ] View pending verification status
- [ ] Add new service
- [ ] Upload service images
- [ ] View my services
- [ ] Accept booking request
- [ ] Reject booking request
- [ ] Mark booking complete
- [ ] View earnings with commission breakdown

### Admin Features:
- [ ] Admin login/logout
- [ ] View analytics dashboard
- [ ] View all providers
- [ ] Approve provider
- [ ] Reject provider
- [ ] Block/unblock provider
- [ ] View all bookings
- [ ] View commission reports
- [ ] Monitor platform statistics

### General Features:
- [ ] Responsive design on mobile
- [ ] Form validation works
- [ ] Password strength indicator
- [ ] Image upload and preview
- [ ] Status badges display correctly
- [ ] Navigation menus work
- [ ] Search functionality
- [ ] Filter and sort options

---

## 🐛 Common Issues & Solutions

### Issue 1: "Database connection failed"
**Solution:**
- Check MySQL is running
- Verify credentials in `config/db.php`
- Ensure database `profix_masters` exists
- Run: `mysql -u root -p profix_masters < database/profix_masters.sql`

### Issue 2: "Cannot find database/profix_masters.sql"
**Solution:**
- Ensure you're in the correct directory
- Check file exists: `ls database/profix_masters.sql`
- Download fresh copy if missing

### Issue 3: "File upload failed"
**Solution:**
```bash
# Linux/Mac
chmod 755 uploads/
chmod 755 uploads/services/
chmod 755 uploads/documents/

# Windows: Check folder permissions in Properties
```

### Issue 4: "Headers already sent" error
**Solution:**
- Check for any output before `<?php` tags
- Remove any BOM characters from PHP files
- Ensure no spaces/newlines before `<?php`

### Issue 5: Blank page or white screen
**Solution:**
- Enable error display in `config/db.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Issue 6: "Call to undefined function password_hash()"
**Solution:**
- Upgrade PHP to 5.5+
- Check: `php -v`

---

## 📊 Test Data Reference

### Pre-loaded Accounts:

| Role | Username/Email | Password |
|------|---------------|----------|
| Admin | admin | Admin@123 |
| Test User | user@test.com | User@123 |
| Test Provider | provider@test.com | Provider@123 |

### Pre-loaded Categories:
1. AC Service
2. Home Cleaning
3. Plumbing
4. Electrical
5. Carpentry
6. Painting
7. Pest Control
8. Catering
9. Beauty & Salon
10. Appliance Repair

---

## 🎯 Quick Test Commands

### Test Database Connection:
```bash
mysql -u root -p -e "USE profix_masters; SELECT COUNT(*) FROM users;"
```

### Verify PHP Version:
```bash
php -v
# Should be 5.6 or higher
```

### Check Apache/PHP Info:
Create `info.php` in root:
```php
<?php phpinfo(); ?>
```
Access: `http://localhost/Akram/info.php`

### Test File Permissions:
```bash
ls -la uploads/
# Should show drwxr-xr-x
```

---

## 📱 Mobile Testing

### Test Responsive Design:

1. **Chrome DevTools:**
   - Press F12
   - Click device icon (mobile view)
   - Test: iPhone SE, iPad, Desktop

2. **Viewport Sizes:**
   - Mobile: 375px (iPhone SE)
   - Tablet: 768px (iPad)
   - Desktop: 1920px

3. **Check:**
   - Navigation menu (hamburger on mobile)
   - Forms display correctly
   - Tables are scrollable
   - Buttons are tap-friendly
   - Images scale properly

---

## 🎉 Success Criteria

Your setup is successful when:
- ✅ Homepage loads without errors
- ✅ Can login as admin/user/provider
- ✅ Dashboard displays statistics
- ✅ Can create and view services
- ✅ Can create and manage bookings
- ✅ Images upload successfully
- ✅ Commission calculates correctly (15%)
- ✅ Responsive design works on mobile

---

## 📞 Need Help?

If you encounter issues:
1. Check the error message carefully
2. Review `Common Issues & Solutions` section above
3. Verify all prerequisites are installed
4. Check MySQL and Apache are running
5. Ensure database is imported correctly
6. Review `INSTALLATION.md` for detailed setup

---

**Testing Time: ~30 minutes for complete platform test** ⏱️

**Tip:** Start with Admin functions, then Provider, then User for logical flow!
