<?php
require_once 'includes/functions.php';
$slug = $_GET['slug'] ?? '';
$screen = getScreenBySlug($slug);

if (!$screen) {
    die('Screen not found');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => verifyScreenPassword($screen['id'], $_POST['password'])]);
        exit();
    }
}

$media = getScreenMedia($screen['id']);
$charts = getScreenCharts($screen['id']);

if (empty($media) && empty($charts)) {
    echo '<div style="color: white; text-align: center; padding: 50px; background: #000;">
            <h2>No Content Found</h2>
            <p>No media files or charts are assigned to this screen.</p>
            <p>Please go back to the dashboard and add content to this screen.</p>
          </div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($screen['name']); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #000;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        
        .password-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            color: white;
            text-align: center;
        }
        
        .password-form {
            background: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }
        
        .password-form h3 {
            margin-top: 0;
            color: #fff;
        }
        
        .password-form input {
            width: 200px;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .password-form button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .password-form button:hover {
            background: #0056b3;
        }
        
        .slideshow-container {
            display: none;
            position: relative;
            width: 100%;
            height: 100vh;
        }
        
        .slide {
            display: none;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        .slide.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .slide img, .slide video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .chart-slide {
            background: #1a1a1a;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .chart-container {
            width: 90%;
            height: 80%;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        
        .chart-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .chart-canvas {
            flex: 1;
            position: relative;
        }
        
        .error-message {
            color: #ff6b6b;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="password-overlay" id="passwordOverlay">
        <div class="password-form">
            <h3>🔒 Screen Access</h3>
            <p>Enter screen passcode:</p>
            <input type="password" id="passwordInput" placeholder="Enter passcode" onkeypress="if(event.key==='Enter') checkPassword()">
            <br>
            <button onclick="checkPassword()">Access Screen</button>
            <div id="errorMessage" class="error-message"></div>
        </div>
    </div>
    
    <div class="slideshow-container" id="slideshowContainer">
        <?php 
        $slideIndex = 0;
        // Display media slides
        foreach ($media as $item): ?>
            <div class="slide <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
                <?php if ($item['file_type'] === 'image'): ?>
                    <img src="<?php echo UPLOAD_PATH . 'images/' . $item['filename']; ?>" 
                         alt="<?php echo htmlspecialchars($item['original_name']); ?>">
                <?php else: ?>
                    <video autoplay muted loop>
                        <source src="<?php echo UPLOAD_PATH . 'videos/' . $item['filename']; ?>" 
                                type="video/<?php echo pathinfo($item['filename'], PATHINFO_EXTENSION); ?>">
                    </video>
                <?php endif; ?>
            </div>
        <?php 
        $slideIndex++;
        endforeach; 
        
        // Display chart slides
        foreach ($charts as $chart): ?>
            <div class="slide chart-slide <?php echo $slideIndex === 0 && empty($media) ? 'active' : ''; ?>">
                <div class="chart-container">
                    <div class="chart-title"><?php echo htmlspecialchars($chart['name']); ?></div>
                    <div class="chart-canvas">
                        <canvas id="chart_<?php echo $chart['id']; ?>"></canvas>
                    </div>
                </div>
            </div>
        <?php 
        $slideIndex++;
        endforeach; ?>
    </div>
    
    <script>
        let currentSlide = 0;
        let slides = document.querySelectorAll('.slide');
        let slideInterval;
        let charts = {};
        
        // Chart data from PHP
        const chartData = <?php echo !empty($charts) ? '[' . implode(',', array_map(function($chart) {
            return '{id:' . $chart['id'] . ',name:' . json_encode($chart['name']) . ',data:' . $chart['chart_data'] . '}';
        }, $charts)) . ']' : '[]'; ?>;
        
        function checkPassword() {
            const password = document.getElementById('passwordInput').value;
            const errorDiv = document.getElementById('errorMessage');
            
            if (!password) {
                errorDiv.textContent = 'Please enter a passcode';
                return;
            }
            
            console.log('Checking password:', password);
            
            fetch('view_screen.php?slug=<?php echo $slug; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if (data.success) {
                    document.getElementById('passwordOverlay').style.display = 'none';
                    document.getElementById('slideshowContainer').style.display = 'block';
                    initializeCharts();
                    startSlideshow();
                } else {
                    errorDiv.textContent = 'Invalid passcode. Please try again.';
                    document.getElementById('passwordInput').value = '';
                    document.getElementById('passwordInput').focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = 'Connection error. Please try again.';
            });
        }
        
        function initializeCharts() {
            chartData.forEach(chartInfo => {
                const canvas = document.getElementById('chart_' + chartInfo.id);
                if (canvas) {
                    const ctx = canvas.getContext('2d');
                    charts[chartInfo.id] = new Chart(ctx, {
                        type: chartInfo.data.type || 'bar',
                        data: chartInfo.data.data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: chartInfo.data.type !== 'pie' && chartInfo.data.type !== 'doughnut' ? {
                                y: {
                                    beginAtZero: true
                                }
                            } : {}
                        }
                    });
                }
            });
        }
        
        function startSlideshow() {
            if (slides.length > 1) {
                slideInterval = setInterval(nextSlide, 5000);
            }
        }
        
        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
            
            // Handle video autoplay
            const video = slides[currentSlide].querySelector('video');
            if (video) {
                video.play();
            }
            
            // Handle chart animation
            const chartCanvas = slides[currentSlide].querySelector('canvas');
            if (chartCanvas) {
                const chartId = chartCanvas.id.replace('chart_', '');
                if (charts[chartId]) {
                    charts[chartId].update('active');
                }
            }
        }
        
        // Focus on password input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('passwordInput').focus();
            console.log('Page loaded. Media count:', <?php echo count($media); ?>, 'Chart count:', <?php echo count($charts); ?>);
        });
        
        // Module 4: Real-time Content Sync
        let syncConnection = null;
        let lastUpdateTime = <?php echo time(); ?>;
        let syncRetryCount = 0;
        const maxRetries = 5;
        
        function initializeSync() {
            console.log('Initializing real-time sync for screen ID: <?php echo $screen['id']; ?>');
            connectToSyncService();
        }
        
        function connectToSyncService() {
            if (syncConnection) {
                syncConnection.close();
            }
            
            const syncUrl = `sync.php?screen_id=<?php echo $screen['id']; ?>&last_update=${lastUpdateTime}`;
            syncConnection = new EventSource(syncUrl);
            
            syncConnection.onopen = function(event) {
                console.log('Connected to sync service');
                syncRetryCount = 0;
                showSyncStatus('Connected', 'green');
            };
            
            syncConnection.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    handleSyncMessage(data);
                } catch (error) {
                    console.error('Error parsing sync message:', error);
                }
            };
            
            syncConnection.onerror = function(event) {
                console.error('Sync connection error:', event);
                showSyncStatus('Disconnected', 'red');
                
                // Retry connection with exponential backoff
                syncRetryCount++;
                if (syncRetryCount <= maxRetries) {
                    const retryDelay = Math.pow(2, syncRetryCount) * 1000;
                    console.log(`Retrying sync connection in ${retryDelay}ms (attempt ${syncRetryCount}/${maxRetries})`);
                    setTimeout(connectToSyncService, retryDelay);
                } else {
                    console.log('Max sync retries reached. Manual refresh may be required.');
                    showSyncStatus('Offline', 'orange');
                }
            };
        }
        
        function handleSyncMessage(data) {
            console.log('Received sync message:', data);
            
            switch (data.type) {
                case 'connection':
                    console.log('Sync service connection confirmed');
                    break;
                    
                case 'screen_update':
                    console.log('Screen content updated, refreshing...');
                    refreshScreenContent();
                    break;
                    
                case 'content_update':
                    console.log('New content available, checking relevance...');
                    checkContentRelevance(data);
                    break;
                    
                case 'heartbeat':
                    // Update last seen time
                    lastUpdateTime = data.timestamp;
                    showSyncStatus('Online', 'green');
                    break;
                    
                case 'connection_close':
                    console.log('Sync connection closed by server');
                    setTimeout(connectToSyncService, 2000);
                    break;
                    
                case 'error':
                    console.error('Sync error:', data.message);
                    showSyncStatus('Error', 'red');
                    break;
            }
        }
        
        function refreshScreenContent() {
            // Show loading indicator
            showSyncStatus('Refreshing...', 'blue');
            
            // Reload the page to get updated content
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
        
        function checkContentRelevance(data) {
            // Check if the new content is relevant to this screen
            const screenId = <?php echo $screen['id']; ?>;
            
            // For now, refresh on any content update
            // In a more sophisticated implementation, we could check if the content is assigned to this screen
            if (data.new_media && data.new_media.length > 0 || data.new_charts && data.new_charts.length > 0) {
                console.log('Relevant content update detected');
                setTimeout(refreshScreenContent, 2000); // Delay to allow content assignment
            }
        }
        
        function showSyncStatus(status, color) {
            // Create or update sync status indicator
            let statusIndicator = document.getElementById('syncStatus');
            if (!statusIndicator) {
                statusIndicator = document.createElement('div');
                statusIndicator.id = 'syncStatus';
                statusIndicator.style.cssText = `
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    background: rgba(0,0,0,0.7);
                    color: white;
                    padding: 5px 10px;
                    border-radius: 5px;
                    font-size: 12px;
                    z-index: 1000;
                    border-left: 4px solid ${color};
                `;
                document.body.appendChild(statusIndicator);
            }
            
            statusIndicator.textContent = `Sync: ${status}`;
            statusIndicator.style.borderLeftColor = color;
            
            // Auto-hide after 3 seconds for success messages
            if (status === 'Online' || status === 'Connected') {
                setTimeout(() => {
                    if (statusIndicator && statusIndicator.textContent.includes(status)) {
                        statusIndicator.style.opacity = '0.3';
                    }
                }, 3000);
            } else {
                statusIndicator.style.opacity = '1';
            }
        }
        
        // Enhanced password check with sync initialization
        function checkPassword() {
            const password = document.getElementById('passwordInput').value;
            const errorDiv = document.getElementById('passwordError');
            
            fetch('view_screen.php?slug=<?php echo $screen['slug']; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.password-overlay').style.display = 'none';
                    document.querySelector('.slideshow-container').style.display = 'block';
                    
                    // Initialize content display
                    initializeCharts();
                    startSlideshow();
                    
                    // Initialize real-time sync
                    initializeSync();
                } else {
                    errorDiv.textContent = 'Incorrect password. Please try again.';
                    document.getElementById('passwordInput').value = '';
                    document.getElementById('passwordInput').focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = 'Connection error. Please try again.';
            });
        }
    </script>
</body>
</html>
