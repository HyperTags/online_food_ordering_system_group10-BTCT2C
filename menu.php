<?php
$page_title = 'Menu';
require_once 'includes/config.php';

$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$query = "SELECT * FROM menu_items WHERE is_available = 1";

if (!empty($category)) {
    $query .= " AND category = '$category'";
}

if (!empty($search)) {
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

$query .= " ORDER BY category, name";
$result = mysqli_query($conn, $query);

// Get categories
$categories_query = "SELECT DISTINCT category FROM menu_items WHERE is_available = 1";
$categories_result = mysqli_query($conn, $categories_query);

require_once 'includes/header.php';
?>

<div class="container">
    <h1>Our Menu</h1><br>
    
    <!-- Search and Filter -->
    <div style="margin-bottom: 25px;">
        <form method="GET" action="" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" 
                   placeholder="Search menu..." value="<?php echo htmlspecialchars($search); ?>"
                   style="flex: 1;">
            
            <select name="category" class="form-control" style="width: auto;">
                <option value="">All Categories</option>
                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                    <option value="<?php echo $cat['category']; ?>" 
                            <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo $cat['category']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <button type="submit" class="btn">Filter</button>
        </form>
    </div>
    
    <!-- Menu Items -->
    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="card-container">
            <?php while($item = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <div class="card-image">
                        <?php if(!empty($item['image'])): ?>
                            <img src="<?php echo MENU_IMAGES_URL . $item['image']; ?>" 
                                alt="<?php echo $item['name']; ?>">
                        <?php else: ?>
                            <div class="placeholder">
                                <?php
                                switch($item['category']) {
                                    case 'Pizza': echo '🍕'; break;
                                    case 'Burgers': echo '🍔'; break;
                                    case 'Salads': echo '🥗'; break;
                                    case 'Sides': echo '🍟'; break;
                                    case 'Beverages': echo '🥤'; break;
                                    default: echo '🍽️';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                    </div>
                    <div class="menu-card-actions">
                        <span class="menu-price"><?php echo number_format($item['price'], 2); ?></span>
                        <?php if(isLoggedIn()): ?>
                            <a href="order.php?item_id=<?php echo $item['id']; ?>" class="menu-order-btn">Order</a>
                        <?php else: ?>
                            <a href="login.php" class="menu-order-btn">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="alert alert-info">No menu items found.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>