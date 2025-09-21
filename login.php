<?php
require_once 'includes/functions.php';
if (isLoggedIn()) {
    redirectToDashboard();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (authenticateUser($username, $password)) {
        redirectToDashboard();
    } else {
        $error= 'Invalid username or password';
    }
}
$pageTitle = 'Login - Signage System';
include 'includes/header.php';
?>

<div class="login-container">
    <div class="card login-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <h2><i class="fas fa-tv me-2"></i>Signage System</h2>
                <p class="text-muted">Please sign in to continue</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    Don't have an account? 
                    <a href="signup.php" class="text-decoration-none">
                        <i class="fas fa-user-plus me-1"></i>Sign up here
                    </a>
                </small>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>