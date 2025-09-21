<?php
require_once 'includes/functions.php';
requireLogin();

$screens = getAllScreens();
$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                <a href="screens.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Screen
                </a>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count($screens); ?></h4>
                                    <p class="mb-0">Total Screens</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-desktop fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count(getAllMedia()); ?></h4>
                                    <p class="mb-0">Media Files</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-images fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count(array_filter(getAllMedia(), function($m) { return $m['file_type'] === 'image'; })); ?></h4>
                                    <p class="mb-0">Images</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-image fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count(array_filter(getAllMedia(), function($m) { return $m['file_type'] === 'video'; })); ?></h4>
                                    <p class="mb-0">Videos</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-video fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="screens.php" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-plus me-2"></i>Create Screen
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="storage.php" class="btn btn-success w-100 mb-2">
                                        <i class="fas fa-upload me-2"></i>Manage Storage
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="groups.php" class="btn btn-info w-100 mb-2">
                                        <i class="fas fa-layer-group me-2"></i>Manage Groups
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="logout.php" class="btn btn-secondary w-100 mb-2">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-th me-2"></i>All Screens (<?php echo count($screens); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($screens)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-desktop fa-4x text-muted mb-3"></i>
                            <h4>No screens created yet</h4>
                            <p class="text-muted">Create your first screen to get started</p>
                            <a href="screens.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Create First Screen
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="screen-grid">
                            <?php foreach ($screens as $screen): ?>
                                <div class="screen-card" onclick="openScreen('<?php echo $screen['slug']; ?>')">
                                    <div class="screen-preview">
                                        <i class="fas fa-tv"></i>
                                    </div>
                                    <div class="screen-info">
                                        <h5><?php echo htmlspecialchars($screen['name']); ?></h5>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-link me-1"></i>
                                            <?php echo BASE_URL; ?>view_screen.php?slug=<?php echo $screen['slug']; ?>
                                        </p>
                                        <small class="text-muted">
                                            Created: <?php echo date('M j, Y', strtotime($screen['created_at'])); ?>
                                        </small>
                                        <div class="mt-3">
                                            <span class="badge bg-primary">
                                                <?php 
                                                    $mediaCount = count(getScreenMedia($screen['id']));
                                                    echo $mediaCount . ' Media File' . ($mediaCount !== 1 ? 's' : '');
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>