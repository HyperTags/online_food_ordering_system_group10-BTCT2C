<?php
$page_title = 'Dashboard';
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please login to access dashboard';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get order statistics
$orders_query = "SELECT 
                    COUNT(*) as total_orders, 
                    COALESCE(SUM(total_amount), 0) as total_spent,
                    COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as delivered_orders,
                    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_orders
                 FROM orders WHERE user_id = $user_id";
$orders_result = mysqli_query($conn, $orders_query);
$stats = mysqli_fetch_assoc($orders_result);

// Get recent orders
$recent_query = "SELECT o.*, COUNT(oi.id) as item_count 
                 FROM orders o 
                 LEFT JOIN order_items oi ON o.id = oi.order_id 
                 WHERE o.user_id = $user_id 
                 GROUP BY o.id 
                 ORDER BY o.order_date DESC 
                 LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);

require_once 'includes/header.php';
?>

<div class="container">
    <h1>My Dashboard</h1><br>
    
    <!-- Profile Section -->
    <div class="dashboard-profile">
        <div class="profile-image-container" style="margin: 0;">
            <div class="profile-preview" style="width: 100px; height: 100px; margin: 0; cursor: default;">
                <?php if(!empty($user['profile_image'])): ?>
                    <img src="<?php echo UPLOAD_URL . $user['profile_image']; ?>" alt="Profile">
                <?php else: ?>
                    <div class="default-preview">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard-profile-info">
            <h3><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
            <p><strong>Member since:</strong> <?php echo date('jS F, Y', strtotime($user['registered_at'])); ?></p>
            <a href="profile.php" class="btn btn-small">Edit Profile</a>
        </div>
    </div>
    
    <!-- Stats -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p class="stat-number"><?php echo $stats['total_orders'] ?? 0; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Total Spent</h3>
            <p class="stat-number">₵<?php echo number_format($stats['total_spent'] ?? 0, 2); ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Delivered</h3>
            <p class="stat-number"><?php echo $stats['delivered_orders'] ?? 0; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Pending</h3>
            <p class="stat-number"><?php echo $stats['pending_orders'] ?? 0; ?></p>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="recent-orders">
        <h2>Recent Orders</h2>
        
        <?php if(mysqli_num_rows($recent_result) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date & Time</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = mysqli_fetch_assoc($recent_result)): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo date('d M, Y - h:i A', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $order['item_count']; ?> items</td>
                                <td><strong>₵<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td>
                                    <?php
                                    $status = $order['status'];
                                    $badge_class = '';
                                    switch($status) {
                                        case 'Pending': $badge_class = 'status-pending'; break;
                                        case 'Processing': $badge_class = 'status-processing'; break;
                                        case 'Delivered': $badge_class = 'status-delivered'; break;
                                        case 'Cancelled': $badge_class = 'status-cancelled'; break;
                                        default: $badge_class = 'status-pending';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>
                                <td><?php echo $order['payment_method'] ?? 'Cash'; ?></td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="action-btn action-view">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="alert alert-info" style="text-align: center; padding: 30px;">
                You haven't placed any orders yet. 
                <a href="menu.php" style="color: var(--primary-color);">Browse our menu!</a>
            </p>
        <?php endif; ?>
    </div>
    
    <!-- Quick Actions -->
    <div style="margin-top: 25px; text-align: center;">
        <a href="menu.php" class="btn">🍕 Order Food</a>
        <a href="profile.php" class="btn btn-secondary" style="margin-left: 10px;">👤 Edit Profile</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>