<?php
$page_title = 'Place Order';
require_once 'includes/config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please login to place order';
    redirect('login.php');
}

$error = '';
$success = '';

$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$item = null;

if ($item_id > 0) {
    $item_query = "SELECT * FROM menu_items WHERE id = $item_id AND is_available = 1";
    $item_result = mysqli_query($conn, $item_query);
    
    if (mysqli_num_rows($item_result) == 1) {
        $item = mysqli_fetch_assoc($item_result);
    } else {
        $error = 'Item not found or unavailable';
    }
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    $payment_method = sanitize_input($_POST['payment_method'] ?? '');
    $delivery_address = sanitize_input($_POST['delivery_address'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if ($quantity < 1) {
        $error = 'Please select valid quantity';
    } elseif (empty($payment_method)) {
        $error = 'Please select payment method';
    } elseif (empty($delivery_address)) {
        $error = 'Please enter delivery address';
    } else {
        // Get item price
        $price_query = "SELECT price FROM menu_items WHERE id = $item_id";
        $price_result = mysqli_query($conn, $price_query);
        $item_data = mysqli_fetch_assoc($price_result);
        $total_amount = $item_data['price'] * $quantity;
        
        // Insert order
        $order_query = "INSERT INTO orders (user_id, total_amount, payment_method, delivery_address, status) 
                       VALUES ($user_id, $total_amount, '$payment_method', '$delivery_address', 'Pending')";
        
        if (mysqli_query($conn, $order_query)) {
            $order_id = mysqli_insert_id($conn);
            
            // Insert order item
            $item_query = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) 
                          VALUES ($order_id, $item_id, $quantity, {$item_data['price']})";
            
            if (mysqli_query($conn, $item_query)) {
                $success = 'Order placed successfully! Order #' . str_pad($order_id, 4, '0', STR_PAD_LEFT);
            } else {
                $error = 'Failed to add order items';
            }
        } else {
            $error = 'Failed to create order';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <?php if($success): ?>
        <div class="alert alert-success" style="text-align: center;">
            <h3><?php echo $success; ?></h3>
            <p style="margin-top: 15px;">Thank you for ordering with 10Tables! 🇬🇭</p>
            <div style="margin-top: 20px;">
                <a href="dashboard.php" class="btn">View Dashboard</a>
                <a href="menu.php" class="btn btn-secondary" style="margin-left: 10px;">Order More</a>
            </div>
        </div>
    <?php elseif($item): ?>
        <div class="form-container">
            <h2>Place Your Order</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card" style="margin-bottom: 20px; padding: 15px;">
                <h3 style="color: var(--primary-color);"><?php echo htmlspecialchars($item['name']); ?></h3>
                <p style="color: #666;"><?php echo htmlspecialchars($item['description']); ?></p>
                <p style="font-size: 1.3rem; font-weight: 800; color: var(--secondary-color); margin-top: 10px;">
                    ₵<?php echo number_format($item['price'], 2); ?>
                </p>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                <input type="hidden" name="place_order" value="1">
                
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" 
                           min="1" max="10" value="1" required>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-control" required>
                        <option value="">Select Payment Method</option>
                        <option value="Cash">Cash on Delivery</option>
                        <option value="Mobile Money">Mobile Money (MoMo)</option>
                        <option value="Card">Credit/Debit Card</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="delivery_address">Delivery Address</label>
                    <textarea id="delivery_address" name="delivery_address" class="form-control" 
                              rows="3" required placeholder="Enter your full delivery address in Ghana"></textarea>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Confirm Order</button>
                <p style="text-align: center; margin-top: 15px;">
                    <a href="menu.php" style="color: #666;">← Back to Menu</a>
                </p>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-error" style="text-align: center;">
            <p><?php echo $error ?: 'Invalid order request'; ?></p>
            <a href="menu.php" class="btn" style="margin-top: 15px;">Browse Menu</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>