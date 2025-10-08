<?php
require_once 'includes/functions.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'upload') {
            $uploadSuccess = false;
            
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if (!empty($name)) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        if (uploadFile($file, 'image')) {
                            $uploadSuccess = true;
                        }
                    }
                }
            }
            
            if (isset($_FILES['videos']) && !empty($_FILES['videos']['name'][0])) {
                foreach ($_FILES['videos']['name'] as $key => $name) {
                    if (!empty($name)) {
                        $file = [
                            'name' => $_FILES['videos']['name'][$key],
                            'type' => $_FILES['videos']['type'][$key],
                            'tmp_name' => $_FILES['videos']['tmp_name'][$key],
                            'error' => $_FILES['videos']['error'][$key],
                            'size' => $_FILES['videos']['size'][$key]
                        ];
                        
                        if (uploadFile($file, 'video')) {
                            $uploadSuccess = true;
                        }
                    }
                }
            }
            
            if ($uploadSuccess) {
                $success = 'Files uploaded successfully!';
                
                // Trigger content sync for all screens
                triggerGlobalContentSync();
            } else {
                $error = 'Failed to upload files. Please check file types and sizes.';
            }
        } elseif ($_POST['action'] === 'delete') {
            header('Content-Type: application/json');
            echo json_encode(['success' => deleteMedia($_POST['id'])]);
            exit();
        } elseif ($_POST['action'] === 'create_chart') {
            $chartName = sanitizeInput($_POST['chart_name']);
            $chartLabels = array_filter(array_map('trim', explode(',', $_POST['chart_labels'])));
            $chartValues = array_filter(array_map('trim', explode(',', $_POST['chart_values'])));
            
            if (!empty($chartName) && !empty($chartLabels) && !empty($chartValues) && count($chartLabels) === count($chartValues)) {
                $chartData = [
                    'labels' => $chartLabels,
                    'values' => array_map('floatval', $chartValues), 
                    'type' => $_POST['chart_type'] ?? 'bar'
                ];
                
                if (createChart($chartName, $chartData)) {
                    $success = 'Chart created successfully!';
                    
                    // Trigger content sync for all screens
                    triggerGlobalContentSync();
                } else {
                    $error = 'Failed to create chart. Please try again.';
                }
            } else {
                $error = 'Please fill all chart fields. Labels and values must have the same count.';
            }
        } elseif ($_POST['action'] === 'delete_chart') {
            header('Content-Type: application/json');
            try {
                if (!isset($_POST['id']) || empty($_POST['id'])) {
                    echo json_encode(['success' => false, 'error' => 'Chart ID is required']);
                    exit();
                }
                
                $result = deleteChart($_POST['id']);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Chart deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to delete chart from database']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            }
            exit();
        }
    }
}

$media = getAllMedia();
$charts = getAllCharts();
$pageTitle = 'Storage Management';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-folder me-2"></i>Storage Management</h2>
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
            
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Create Charts</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_chart">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="chart_name" class="form-label">Chart Name *</label>
                                    <input type="text" class="form-control" id="chart_name" name="chart_name" 
                                           placeholder="Enter chart name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chart_type" class="form-label">Chart Type</label>
                                    <select class="form-control" id="chart_type" name="chart_type">
                                        <option value="bar">Bar Chart</option>
                                        <option value="pie">Pie Chart</option>
                                        <option value="line">Line Chart</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="chart_labels" class="form-label">Chart Labels *</label>
                                    <input type="text" class="form-control" id="chart_labels" name="chart_labels" 
                                           placeholder="Label1, Label2, Label3" required>
                                    <small class="text-muted">Separate labels with commas</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chart_values" class="form-label">Chart Values *</label>
                                    <input type="text" class="form-control" id="chart_values" name="chart_values" 
                                           placeholder="10, 20, 30" required>
                                    <small class="text-muted">Separate values with commas</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Create Chart
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-chart-line me-2"></i>Charts Library (<?php echo count($charts); ?> charts)</h5>
                    <button type="button" id="loadChartsBtn" class="btn btn-primary btn-sm">
                        <i class="fas fa-play"></i> Load Charts
                    </button>
                </div>
                <div class="card-body">
                    <div id="chartsLoadingMessage" class="text-center py-3">
                        <i class="fas fa-info-circle text-info"></i>
                        <p class="mb-0">Click "Load Charts" to display chart visualizations</p>
                    </div>
                    <div id="chartsContainer" style="display: none;">
                        <?php if (empty($charts)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                                <h4>No charts created yet</h4>
                                <p class="text-muted">Create some charts to get started</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($charts as $chart): ?>
                                    <?php 
                                    $chartData = json_decode($chart['chart_data'], true);
                                    // Only display charts with valid data
                                    if ($chartData && isset($chartData['labels']) && isset($chartData['values']) && isset($chartData['type']) && 
                                        is_array($chartData['labels']) && is_array($chartData['values']) && 
                                        count($chartData['labels']) > 0 && count($chartData['values']) > 0): 
                                    ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6><?php echo htmlspecialchars($chart['name']); ?></h6>
                                                
                                                <!-- Simple Text-Based Chart Display -->
                                                <div class="chart-display p-3 bg-light rounded" style="height: 150px; overflow-y: auto;">
                                                    <strong>📊 <?php echo ucfirst($chartData['type']); ?> Chart</strong>
                                                    <hr class="my-2">
                                                    <?php for ($i = 0; $i < count($chartData['labels']); $i++): ?>
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="fw-bold"><?php echo htmlspecialchars($chartData['labels'][$i]); ?>:</span>
                                                            <span class="badge bg-primary"><?php echo htmlspecialchars($chartData['values'][$i]); ?></span>
                                                        </div>
                                                        <!-- Simple visual bar -->
                                                        <?php 
                                                        $maxValue = max($chartData['values']);
                                                        $percentage = $maxValue > 0 ? ($chartData['values'][$i] / $maxValue) * 100 : 0;
                                                        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
                                                        $color = $colors[$i % count($colors)];
                                                        ?>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $color; ?>;"></div>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                                
                                                <small class="text-muted d-block mt-2">
                                                    Type: <?php echo ucfirst($chartData['type']); ?><br>
                                                    Data points: <?php echo count($chartData['labels']); ?><br>
                                                    Created: <?php echo date('M j, Y', strtotime($chart['created_at'])); ?>
                                                </small>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteChartItem(<?php echo $chart['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-upload me-2"></i>Upload Media</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="upload-area">
                                    <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                    <h5>Upload Images</h5>
                                    <p class="text-muted">JPG, JPEG, PNG, GIF (Max: 50MB)</p>
                                    <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="upload-area">
                                    <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                    <h5>Upload Videos</h5>
                                    <p class="text-muted">MP4, AVI, MOV, WMV (Max: 50MB)</p>
                                    <input type="file" class="form-control" name="videos[]" accept="video/*" multiple>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Upload Files
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-th me-2"></i>Media Gallery (<?php echo count($media); ?> files)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($media)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                            <h4>No media files found</h4>
                            <p class="text-muted">Upload some images or videos to get started</p>
                        </div>
                    <?php else: ?>
                        <div class="media-grid">
                            <?php foreach ($media as $item): ?>
                                <div class="media-item">
                                    <?php if ($item['file_type'] === 'image'): ?>
                                        <img src="<?php echo UPLOAD_PATH . 'images/' . $item['filename']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['original_name']); ?>" 
                                             class="media-thumbnail">
                                    <?php else: ?>
                                        <video class="media-thumbnail" controls>
                                            <source src="<?php echo UPLOAD_PATH . 'videos/' . $item['filename']; ?>" 
                                                    type="video/<?php echo pathinfo($item['filename'], PATHINFO_EXTENSION); ?>">
                                        </video>
                                    <?php endif; ?>
                                    
                                    <div class="media-info">
                                        <h6><?php echo htmlspecialchars($item['original_name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo ucfirst($item['file_type']); ?> • 
                                            <?php echo number_format($item['file_size'] / 1024, 1); ?>KB • 
                                            <?php echo date('M j, Y', strtotime($item['uploaded_at'])); ?>
                                        </small>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="deleteMediaItem(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

<script>
// Simple show/hide functionality - NO Chart.js, NO infinite loops
document.addEventListener('DOMContentLoaded', function() {
    console.log('Simple chart display system loaded');
    
    const loadBtn = document.getElementById('loadChartsBtn');
    const loadingMsg = document.getElementById('chartsLoadingMessage');
    const chartsContainer = document.getElementById('chartsContainer');
    let chartsLoaded = false;
    
    loadBtn.addEventListener('click', function() {
        if (chartsLoaded) return;
        
        console.log('Showing charts...');
        loadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadBtn.disabled = true;
        
        // Simple show/hide with a small delay for effect
        setTimeout(() => {
            loadingMsg.style.display = 'none';
            chartsContainer.style.display = 'block';
            
            loadBtn.innerHTML = '<i class="fas fa-check"></i> Charts Loaded';
            loadBtn.classList.remove('btn-primary');
            loadBtn.classList.add('btn-success');
            chartsLoaded = true;
            
            console.log('Charts displayed successfully - no Chart.js rendering');
        }, 500);
    });
});

function deleteChartItem(id) {
    if (confirm('Are you sure you want to delete this chart?')) {
        console.log('Deleting chart with ID:', id);
        
        fetch('storage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete_chart&id=' + id
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Delete response data:', data);
            if (data.success) {
                alert('Chart deleted successfully!');
                location.reload();
            } else {
                alert('Failed to delete chart: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('Error deleting chart: ' + error.message);
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
