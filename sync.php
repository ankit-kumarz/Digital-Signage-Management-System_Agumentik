<?php
require_once 'includes/functions.php';

// Set headers for Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Cache-Control');

// Prevent buffering
if (ob_get_level()) ob_end_clean();

// Function to send SSE message
function sendSSE($id, $data) {
    echo "id: $id\n";
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

// Get screen ID from URL parameters
$screenId = isset($_GET['screen_id']) ? (int)$_GET['screen_id'] : null;
$groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;

if (!$screenId && !$groupId) {
    sendSSE(time(), ['error' => 'Screen ID or Group ID required']);
    exit();
}

// Track last content update timestamp
$lastUpdate = isset($_GET['last_update']) ? (int)$_GET['last_update'] : 0;

try {
    // Send initial connection confirmation
    sendSSE(time(), [
        'type' => 'connection',
        'message' => 'Connected to sync service',
        'screen_id' => $screenId,
        'group_id' => $groupId,
        'timestamp' => time()
    ]);

    // Keep connection alive and check for updates
    $maxExecutionTime = 30; // 30 seconds
    $startTime = time();
    
    while (time() - $startTime < $maxExecutionTime) {
        // Check if content has been updated
        $contentUpdated = false;
        $updateData = [];
        
        if ($screenId) {
            // Check screen-specific updates
            $screen = getScreenById($screenId);
            if ($screen && strtotime($screen['updated_at']) > $lastUpdate) {
                $contentUpdated = true;
                $updateData = [
                    'type' => 'screen_update',
                    'screen_id' => $screenId,
                    'screen' => $screen,
                    'media' => getScreenMedia($screenId),
                    'charts' => getScreenCharts($screenId),
                    'timestamp' => time()
                ];
                $lastUpdate = strtotime($screen['updated_at']);
            }
        }
        
        if ($groupId) {
            // Check group-specific updates
            $group = getGroupById($groupId);
            if ($group && strtotime($group['updated_at']) > $lastUpdate) {
                $contentUpdated = true;
                $updateData = [
                    'type' => 'group_update',
                    'group_id' => $groupId,
                    'group' => $group,
                    'screens' => getScreensByGroup($groupId),
                    'timestamp' => time()
                ];
                $lastUpdate = strtotime($group['updated_at']);
            }
        }
        
        // Check for global media/chart updates
        $recentMedia = getRecentMedia($lastUpdate);
        $recentCharts = getRecentCharts($lastUpdate);
        
        if (!empty($recentMedia) || !empty($recentCharts)) {
            $contentUpdated = true;
            $updateData = [
                'type' => 'content_update',
                'new_media' => $recentMedia,
                'new_charts' => $recentCharts,
                'timestamp' => time()
            ];
        }
        
        if ($contentUpdated) {
            sendSSE(time(), $updateData);
            // Update last update time for next check
            $lastUpdate = time();
        }
        
        // Send heartbeat every 10 seconds
        if ((time() - $startTime) % 10 == 0) {
            sendSSE(time(), [
                'type' => 'heartbeat',
                'timestamp' => time(),
                'uptime' => time() - $startTime
            ]);
        }
        
        // Sleep for 1 second before next check
        sleep(1);
    }
    
    // Send connection close message
    sendSSE(time(), [
        'type' => 'connection_close',
        'message' => 'Connection timeout reached',
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    sendSSE(time(), [
        'type' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => time()
    ]);
}
?>