<?php
$page_title = 'Register';
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if user exists
        $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Username or email already exists';
        } else {
            // Handle file upload
            $profile_image = '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['profile_image']['type'];
                $file_size = $_FILES['profile_image']['size'];
                
                if (in_array($file_type, $allowed) && $file_size <= 5 * 1024 * 1024) {
                    $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $profile_image = time() . '_' . uniqid() . '.' . $ext;
                    $upload_path = UPLOAD_PATH . $profile_image;
                    
                    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                        $error = 'Failed to upload image';
                    }
                } else {
                    $error = 'Invalid file. Max 5MB. Allowed: JPG, PNG, GIF';
                }
            }
            
            if (empty($error)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $insert_query = "INSERT INTO users (username, email, password, full_name, phone, profile_image) 
                                VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', '$profile_image')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Join 10Tables 🇬🇭</h2>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <p style="text-align: center;">
            <a href="login.php" class="btn">Login Now</a>
        </p>
    <?php else: ?>
        <form method="POST" action="" enctype="multipart/form-data" id="registerForm">
            <!-- Profile Image Upload -->
            <div class="profile-image-container">
                <div class="profile-preview" id="profilePreview" onclick="document.getElementById('profile_image').click();">
                    <div class="default-preview" id="defaultPreview">📷</div>
                    <img id="imagePreview" src="#" alt="Preview" style="display: none;">
                    <div class="upload-overlay">Click to upload</div>
                </div>
                <p style="color: #666; font-size: 0.85rem;">Click to add profile picture</p>
            </div>

            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(this)">

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                       placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="your@email.com">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                       placeholder="024xxxxxxx">
            </div>

            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" class="form-control" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       placeholder="Choose a username"
                       onkeyup="updatePreview(this.value)">
            </div>
                        
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" class="form-control" required
                       placeholder="Minimum 6 characters">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                       placeholder="Re-enter password">
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Create Account</button>
            <p style="margin-top: 20px; text-align: center;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    <?php endif; ?>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const defaultPreview = document.getElementById('defaultPreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.display = 'block';
            defaultPreview.style.display = 'none';
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function updatePreview(username) {
    const defaultPreview = document.getElementById('defaultPreview');
    const imagePreview = document.getElementById('imagePreview');
    
    if (username && imagePreview.style.display === 'none') {
        defaultPreview.textContent = username.charAt(0).toUpperCase();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>