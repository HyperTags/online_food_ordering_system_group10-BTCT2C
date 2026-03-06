-- Create database
CREATE DATABASE IF NOT EXISTS food_ordering_system;
USE food_ordering_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255),
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    payment_method VARCHAR(50),
    delivery_address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    menu_item_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

-- Insert sample menu items WITH IMAGE FILENAMES
INSERT INTO menu_items (name, description, price, category, image, is_available) VALUES
('Jollof Rice with Chicken', 'Delicious Ghanaian jollof rice served with grilled chicken and plantain', 35.00, 'Ghanaian', 'jollof.jpg', 1),
('Waakye', 'Traditional rice and beans served with shito, spaghetti, and boiled egg', 25.00, 'Ghanaian', 'waakye.jpg', 1),
('Fufu with Light Soup', 'Soft pounded cassava and plantain served with aromatic light soup', 40.00, 'Ghanaian', 'fufu.jpg', 1),
('Banku with Tilapia', 'Fermented corn and cassava dough served with grilled tilapia and pepper', 45.00, 'Ghanaian', 'banku.jpg', 1),
('Margherita Pizza', 'Classic cheese pizza with tomato sauce', 55.00, 'Pizza', 'pizza.jpg', 1),
('Chicken Burger', 'Grilled chicken patty with lettuce, tomato, and mayo', 35.00, 'Burgers', 'burger.jpg', 1),
('French Fries', 'Crispy golden fries with salt', 15.00, 'Sides', 'fries.jpg', 1),
('Caesar Salad', 'Fresh romaine lettuce with Caesar dressing and croutons', 28.00, 'Salads', 'salad.jpg', 1),
('Coca Cola', 'Refreshing soft drink', 8.00, 'Beverages', 'coke.jpg', 1),
('Fresh Coconut Juice', 'Natural coconut water straight from the source', 12.00, 'Beverages', 'coconut.jpg', 1);