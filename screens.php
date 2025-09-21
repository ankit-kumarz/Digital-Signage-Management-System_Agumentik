<?php
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['screen_name']);
    $slug = sanitizeInput($_POST['screen_slug']);
    $passcode = $_POST['screen_passcode'];
    $mediaIds = $_POST['media'] ?? [];
    $chartIds = $_POST['charts'] ?? [];
    
    if (!empty($name) && !empty($slug) && !empty($passcode)) {
        if (createScreen($name, $slug, $passcode)) {
            $screenId = $pdo->lastInsertId();
            if (!empty($mediaIds)) {
                assignMediaToScreen($screenId, $mediaIds);
            }
            if (!empty($chartIds)) {
                assignChartsToScreen($screenId, $chartIds);
            }
            
            // Trigger sync for the new screen
            triggerScreenSync($screenId);
            
            $success = 'Screen created successfully!';
        } else {
            $error = 'Failed to create screen. Please try again.';
        }
    } else {
        $error = 'Please fill all required fields.';
    }
}

$media = getAllMedia();
$charts = getAllCharts();
$pageTitle = 'Create Screen';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-desktop me-2"></i>Create New Screen</h2>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="screen_form">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-cog me-2"></i>Screen Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="screen_name" class="form-label">Screen Name *</label>
                                    <input type="text" class="form-control" id="screen_name" name="screen_name" 
                                           placeholder="Enter screen name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="screen_slug" class="form-label">URL Slug *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">view/</span>
                                        <input type="text" class="form-control" id="screen_slug" name="screen_slug" 
                                               placeholder="auto-generated" required>
                                    </div>
                                    <small class="text-muted">Will be auto-generated from screen name</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="screen_passcode" class="form-label">Screen Passcode *</label>
                                    <input type="password" class="form-control" id="screen_passcode" name="screen_passcode" 
                                           placeholder="Enter secure passcode" required>
                                    <small class="text-muted">Required to access the screen</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-images me-2"></i>Select Media and Charts</h5>
                            </div>
                            <div class="card-body">
                                <h6><i class="fas fa-photo-video me-2"></i>Media Files</h6>
                                <?php if (empty($media)): ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No media available</p>
                                        <a href="storage.php" class="btn btn-sm btn-primary">
                                            <i class="fas fa-upload me-1"></i>Upload Media
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="select_all_media">
                                        <label class="form-check-label" for="select_all_media">Select All Media</label>
                                    </div>
                                    <div class="media-selection" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($media as $item): ?>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input media-checkbox" 
                                                       name="media[]" value="<?php echo $item['id']; ?>" 
                                                       id="media_<?php echo $item['id']; ?>">
                                                <label class="form-check-label d-flex align-items-center" 
                                                       for="media_<?php echo $item['id']; ?>">
                                                    <?php if ($item['file_type'] === 'image'): ?>
                                                        <img src="uploads/images/<?php echo $item['filename']; ?>" 
                                                             alt="<?php echo htmlspecialchars($item['original_name']); ?>" 
                                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <?php else: ?>
                                                        <div style="width: 40px; height: 40px; background: #6c757d; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-video text-white"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="ms-2">
                                                        <div class="fw-bold"><?php echo htmlspecialchars($item['original_name']); ?></div>
                                                        <small class="text-muted">
                                                            <?php echo ucfirst($item['file_type']); ?> • 
                                                            <?php echo number_format($item['file_size'] / 1024, 1); ?>KB
                                                        </small>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <h6><i class="fas fa-chart-bar me-2"></i>Charts</h6>
                                <?php if (empty($charts)): ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No charts available</p>
                                        <a href="storage.php" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus me-1"></i>Create Charts
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="select_all_charts">
                                        <label class="form-check-label" for="select_all_charts">Select All Charts</label>
                                    </div>
                                    <div class="charts-selection" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($charts as $chart): ?>
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input chart-checkbox" 
                                                       name="charts[]" value="<?php echo $chart['id']; ?>" 
                                                       id="chart_<?php echo $chart['id']; ?>">
                                                <label class="form-check-label d-flex align-items-center" 
                                                       for="chart_<?php echo $chart['id']; ?>">
                                                    <div style="width: 40px; height: 40px; background: linear-gradient(45deg, #007bff, #28a745); border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-chart-pie text-white"></i>
                                                    </div>
                                                    <div class="ms-2">
                                                        <div class="fw-bold"><?php echo htmlspecialchars($chart['name']); ?></div>
                                                        <small class="text-muted">
                                                            Chart • Created <?php echo date('M j, Y', strtotime($chart['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Create Screen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const screenNameInput = document.getElementById('screen_name');
    const screenSlugInput = document.getElementById('screen_slug');
    
    if (screenNameInput && screenSlugInput) {
        screenNameInput.addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            screenSlugInput.value = slug;
        });
    }
    
    const selectAllMedia = document.getElementById('select_all_media');
    if (selectAllMedia) {
        selectAllMedia.addEventListener('change', function() {
            const mediaCheckboxes = document.querySelectorAll('.media-checkbox');
            mediaCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    const selectAllCharts = document.getElementById('select_all_charts');
    if (selectAllCharts) {
        selectAllCharts.addEventListener('change', function() {
            const chartCheckboxes = document.querySelectorAll('.chart-checkbox');
            chartCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>