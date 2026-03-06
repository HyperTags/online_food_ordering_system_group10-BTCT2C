<?php
$page_title = 'Home';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Using switch statement for time-based greeting (Ghana time)
$hour = (int)date('H');
switch(true) {
    case ($hour >= 5 && $hour < 12):
        $greeting = "Good Morning";
        $meal = "Breakfast";
        break;
    case ($hour >= 12 && $hour < 17):
        $greeting = "Good Afternoon";
        $meal = "Lunch";
        break;
    case ($hour >= 17 && $hour < 22):
        $greeting = "Good Evening";
        $meal = "Dinner";
        break;
    default:
        $greeting = "Hello";
        $meal = "Late Night Snack";
}
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1><?php echo $greeting; ?>! Welcome to 10Tables</h1>
        <p>Taste the best of Ghanaian & Continental dishes. It's <?php echo $meal; ?> time!</p>
        <a href="menu.php" class="btn">View Full Menu 🍽️</a>
    </div>
</section>

<section class="featured-menu">
    <div class="container">
        <h2>Today's Specials</h2>
        <?php
        // Fetch menu items
        $featured_query = "SELECT * FROM menu_items WHERE is_available = 1 ORDER BY RAND() LIMIT 6";
        $featured_result = mysqli_query($conn, $featured_query);
        
        if(mysqli_num_rows($featured_result) > 0) {
            echo '<div class="card-container">';
            while($item = mysqli_fetch_assoc($featured_result)) {
                ?>
                <div class="card">
                    <div class="card-image">
                        <?php if(!empty($item['image'])): ?>
                            <img src="<?php echo MENU_IMAGES_URL . $item['image']; ?>" 
                                alt="<?php echo htmlspecialchars($item['name']); ?>">
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
                        <p><?php echo htmlspecialchars(substr($item['description'], 0, 80)) . '...'; ?></p>
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
                <?php
            }
            echo '</div>';
        } else {
            echo '<p class="alert alert-info">No menu items available. Please check back later!</p>';
        }
        ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>