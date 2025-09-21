<?php
require_once 'includes/functions.php';

$groupId = (int)($_GET['id'] ?? 0);
$group = getGroupById($groupId);

if (!$group) {
    die('Group not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password'])) {
        header('Content-Type: application/json');
        // For group view, we need to check if password matches any screen in the group
        $groupScreens = getScreensByGroup($groupId);
        $validPassword = false;
        
        foreach ($groupScreens as $screen) {
            if (verifyScreenPassword($screen['id'], $_POST['password'])) {
                $validPassword = true;
                break;
            }
        }
        
        echo json_encode(['success' => $validPassword]);
        exit();
    }
}

$groupScreens = getScreensByGroup($groupId);

if (empty($groupScreens)) {
    echo '<div style="color: white; text-align: center; padding: 50px; background: #000;">
            <h2>No Screens in Group</h2>
            <p>This group has no screens assigned to it.</p>
            <p>Please assign screens to this group first.</p>
          </div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group: <?php echo htmlspecialchars($group['name']); ?></title>
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
        
        .group-container {
            display: none;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }
        
        .screens-grid {
            display: grid;
            width: 100%;
            height: 100%;
            gap: 2px;
            background: #222;
        }
        
        .screen-frame {
            background: #000;
            position: relative;
            overflow: hidden;
        }
        
        .screen-title {
            position: absolute;
            top: 10px;
            left: 10px;
            color: white;
            background: rgba(0, 0, 0, 0.7);
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            z-index: 10;
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
            padding: 10px;
            box-sizing: border-box;
        }
        
        .chart-container {
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 5px;
            padding: 10px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        
        .chart-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .chart-canvas {
            flex: 1;
            position: relative;
        }
        
        .error-message {
            color: #ff6b6b;
            margin-top: 10px;
        }
        
        /* Grid layouts based on screen count */
        .grid-1 { grid-template-columns: 1fr; grid-template-rows: 1fr; }
        .grid-2 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr; }
        .grid-3 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
        .grid-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
        .grid-5, .grid-6 { grid-template-columns: 1fr 1fr 1fr; grid-template-rows: 1fr 1fr; }
        .grid-7, .grid-8, .grid-9 { grid-template-columns: 1fr 1fr 1fr; grid-template-rows: 1fr 1fr 1fr; }
    </style>
</head>
<body>
    <div class="password-overlay" id="passwordOverlay">
        <div class="password-form">
            <h3>🔒 Group Access</h3>
            <p>Enter any screen passcode from this group:</p>
            <input type="password" id="passwordInput" placeholder="Enter passcode" onkeypress="if(event.key==='Enter') checkPassword()">
            <br>
            <button onclick="checkPassword()">Access Group</button>
            <div id="errorMessage" class="error-message"></div>
        </div>
    </div>
    
    <div class="group-container" id="groupContainer">
        <div class="screens-grid grid-<?php echo count($groupScreens); ?>" id="screensGrid">
            <?php foreach ($groupScreens as $index => $screen): ?>
                <?php 
                $media = getScreenMedia($screen['id']);
                $charts = getScreenCharts($screen['id']);
                $totalContent = count($media) + count($charts);
                ?>
                <div class="screen-frame" id="screen_<?php echo $screen['id']; ?>">
                    <div class="screen-title"><?php echo htmlspecialchars($screen['name']); ?></div>
                    
                    <?php if ($totalContent > 0): ?>
                        <?php 
                        $slideIndex = 0;
                        // Display media slides
                        foreach ($media as $item): ?>
                            <div class="slide <?php echo $slideIndex === 0 ? 'active' : ''; ?>" data-screen="<?php echo $screen['id']; ?>">
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
                            <div class="slide chart-slide <?php echo $slideIndex === 0 && empty($media) ? 'active' : ''; ?>" data-screen="<?php echo $screen['id']; ?>">
                                <div class="chart-container">
                                    <div class="chart-title"><?php echo htmlspecialchars($chart['name']); ?></div>
                                    <div class="chart-canvas">
                                        <canvas id="chart_<?php echo $chart['id']; ?>_screen_<?php echo $screen['id']; ?>"></canvas>
                                    </div>
                                </div>
                            </div>
                        <?php 
                        $slideIndex++;
                        endforeach; ?>
                    <?php else: ?>
                        <div class="slide active" data-screen="<?php echo $screen['id']; ?>">
                            <div style="color: white; text-align: center;">
                                <h3>No Content</h3>
                                <p>No media or charts assigned</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        let screenSlides = {};
        let slideIntervals = {};
        let charts = {};
        
        // Initialize slide data for each screen
        <?php foreach ($groupScreens as $screen): ?>
            screenSlides[<?php echo $screen['id']; ?>] = {
                current: 0,
                slides: document.querySelectorAll('#screen_<?php echo $screen['id']; ?> .slide'),
                charts: <?php 
                    $charts = getScreenCharts($screen['id']);
                    echo !empty($charts) ? '[' . implode(',', array_map(function($chart) use ($screen) {
                        return '{id:' . $chart['id'] . ',screenId:' . $screen['id'] . ',name:' . json_encode($chart['name']) . ',data:' . $chart['chart_data'] . '}';
                    }, $charts)) . ']' : '[]'; 
                ?>
            };
        <?php endforeach; ?>
        
        function checkPassword() {
            const password = document.getElementById('passwordInput').value;
            const errorDiv = document.getElementById('errorMessage');
            
            if (!password) {
                errorDiv.textContent = 'Please enter a passcode';
                return;
            }
            
            fetch('view_group.php?id=<?php echo $groupId; ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'password=' + encodeURIComponent(password)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('passwordOverlay').style.display = 'none';
                    document.getElementById('groupContainer').style.display = 'block';
                    initializeCharts();
                    startSlideshows();
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
            for (const screenId in screenSlides) {
                const screenData = screenSlides[screenId];
                screenData.charts.forEach(chartInfo => {
                    const canvas = document.getElementById('chart_' + chartInfo.id + '_screen_' + chartInfo.screenId);
                    if (canvas) {
                        const ctx = canvas.getContext('2d');
                        const chartKey = chartInfo.id + '_' + chartInfo.screenId;
                        charts[chartKey] = new Chart(ctx, {
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
        }
        
        function startSlideshows() {
            for (const screenId in screenSlides) {
                const screenData = screenSlides[screenId];
                if (screenData.slides.length > 1) {
                    slideIntervals[screenId] = setInterval(() => nextSlide(screenId), 5000);
                }
            }
        }
        
        function nextSlide(screenId) {
            const screenData = screenSlides[screenId];
            screenData.slides[screenData.current].classList.remove('active');
            screenData.current = (screenData.current + 1) % screenData.slides.length;
            screenData.slides[screenData.current].classList.add('active');
            
            // Handle video autoplay
            const video = screenData.slides[screenData.current].querySelector('video');
            if (video) {
                video.play();
            }
            
            // Handle chart animation
            const chartCanvas = screenData.slides[screenData.current].querySelector('canvas');
            if (chartCanvas) {
                const chartId = chartCanvas.id.replace('chart_', '').replace('_screen_' + screenId, '');
                const chartKey = chartId + '_' + screenId;
                if (charts[chartKey]) {
                    charts[chartKey].update('active');
                }
            }
        }
        
        // Focus on password input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('passwordInput').focus();
            console.log('Group loaded. Screen count:', Object.keys(screenSlides).length);
        });
    </script>
</body>
</html>