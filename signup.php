<?php
require_once 'includes/functions.php';
if (isLoggedIn()) {
    redirectToDashboard();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (registerUser($username, $password)) {
        $success = 'Account created successfully! You can now login.';
    } else {
        $error = 'Username already exists. Please choose another.';
    }
}

$pageTitle = 'Sign Up - Signage System';
include 'includes/header.php';
?>

<div class="login-container">
    <div class="card login-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <h2><i class="fas fa-user-plus me-2"></i>Create Account</h2>
                <p class="text-muted">Sign up for Signage System</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <div class="mt-2">
                        <a href="login.php" class="btn btn-success btn-sm">
                            <i class="fas fa-sign-in-alt me-1"></i>Login Now
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($success)): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               placeholder="Choose a username" required>
                    </div>
                    <small class="text-muted">At least 3 characters</small>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Create a password" required>
                    </div>
                    <small class="text-muted">At least 6 characters</small>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Already have an account? 
                    <a href="login.php" class="text-decoration-none">
                        <i class="fas fa-sign-in-alt me-1"></i>Login here
                    </a>
                </small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>