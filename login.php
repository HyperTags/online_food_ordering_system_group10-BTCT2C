<?php
$page_title = 'Login';
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        $login_query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
        $login_result = mysqli_query($conn, $login_query);
        
        if (mysqli_num_rows($login_result) == 1) {
            $user = mysqli_fetch_assoc($login_result);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['profile_image'] = $user['profile_image'];
                $_SESSION['login_time'] = time();
                
                redirect('dashboard.php');
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container">
    <h2>Welcome Back! 🇬🇭</h2>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" class="form-control" required 
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                   placeholder="Enter your username or email">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required
                   placeholder="Enter your password">
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Login to 10Tables</button>
        
        <p style="margin-top: 20px; text-align: center;">
            New to 10Tables? <a href="register.php">Create an account</a>
        </p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>