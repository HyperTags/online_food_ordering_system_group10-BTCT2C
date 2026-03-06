<?php
$page_title = 'My Profile';
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get current user data
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        
        $update_query = "UPDATE users SET full_name = '$full_name', phone = '$phone' WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success = 'Profile updated successfully';
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
            $_SESSION['full_name'] = $full_name;
        } else {
            $error = 'Failed to update profile';
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($current) || empty($new) || empty($confirm)) {
            $error = 'All password fields are required';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match';
        } elseif (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed' WHERE id = $user_id";
            
            if (mysqli_query($conn, $update_query)) {
                $success = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
        }
    }
    
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        $file_size = $_FILES['profile_image']['size'];
        
        if (in_array($file_type, $allowed) && $file_size <= 5 * 1024 * 1024) {
            // Delete old image
            if (!empty($user['profile_image']) && file_exists(UPLOAD_PATH . $user['profile_image'])) {
                unlink(UPLOAD_PATH . $user['profile_image']);
            }
            
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = UPLOAD_PATH . $filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $update_query = "UPDATE users SET profile_image = '$filename' WHERE id = $user_id";
                if (mysqli_query($conn, $update_query)) {
                    $success = 'Profile image updated';
                    $result = mysqli_query($conn, $query);
                    $user = mysqli_fetch_assoc($result);
                    $_SESSION['profile_image'] = $filename;
                }
            } else {
                $error = 'Failed to upload image';
            }
        } else {
            $error = 'Invalid file. Max 5MB. Allowed: JPG, PNG, GIF';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1>My Profile</h1><br>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
        <!-- Profile Image Section -->
        <div class="card" style="padding: 25px;">
            <div class="profile-image-container">
                <div class="profile-preview" id="profilePreview" onclick="document.getElementById('profile_image').click();">
                    <?php if(!empty($user['profile_image'])): ?>
                        <img id="imagePreview" src="<?php echo UPLOAD_URL . $user['profile_image']; ?>" alt="Profile">
                    <?php else: ?>
                        <div class="default-preview" id="defaultPreview">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                        <img id="imagePreview" src="#" alt="Preview" style="display: none;">
                    <?php endif; ?>
                    <div class="upload-overlay">Change Photo</div>
                </div>
                <p style="color: #666; font-size: 0.85rem;">Click the circle to change photo</p>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(this); this.form.submit();">
            </form>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #FFE4D6;">
            
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Member since:</strong> <?php echo date('jS F, Y', strtotime($user['registered_at'])); ?></p>
        </div>
        
        <!-- Profile Information -->
        <div class="card" style="padding: 25px;">
            <h2 style="color: var(--primary-color); margin-bottom: 20px;">Edit Information</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                           placeholder="Enter your full name">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           placeholder="024xxxxxxx">
                </div>
                
                <button type="submit" name="update_profile" class="btn" style="width: 100%;">Update Profile</button>
            </form>
            
            <hr style="margin: 25px 0; border: none; border-top: 1px solid #FFE4D6;">
            
            <h2 style="color: var(--primary-color); margin-bottom: 20px;">Change Password</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" name="change_password" class="btn btn-secondary" style="width: 100%;">Change Password</button>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const defaultPreview = document.getElementById('defaultPreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.display = 'block';
            if(defaultPreview) defaultPreview.style.display = 'none';
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>