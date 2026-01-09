<div class="dashboard-sidebar">
    <div class="dashboard-sidebar-header">
        <h3>Admin Panel</h3>
    </div>
    <nav class="dashboard-nav">
        <a href="/admin/index.php" class="dashboard-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="/admin/users.php" class="dashboard-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="/admin/providers.php" class="dashboard-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'providers.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i>
            <span>Providers</span>
        </a>
        <a href="/admin/services.php" class="dashboard-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'services.php' ? 'active' : ''; ?>">
            <i class="fas fa-briefcase"></i>
            <span>Services</span>
        </a>
        <a href="/admin/categories.php" class="dashboard-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        <a href="/admin/bookings.php" class="dashboard-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Bookings</span>
        </a>
        <a href="/admin/commissions.php" class="dashboard-nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'commissions.php' ? 'active' : ''; ?>">
            <i class="fas fa-money-bill-wave"></i>
            <span>Commissions</span>
        </a>
        <a href="/admin/logout.php" class="dashboard-nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>
