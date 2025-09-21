<?php
require_once 'includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update_screen_content':
            if (!isset($_POST['screen_id'])) {
                throw new Exception('Screen ID required');
            }
            
            $screenId = (int)$_POST['screen_id'];
            $success = updateScreenTimestamp($screenId);
            
            echo json_encode([
                'success' => $success,
                'message' => 'Screen content update triggered',
                'screen_id' => $screenId,
                'timestamp' => time()
            ]);
            break;
            
        case 'update_group_content':
            if (!isset($_POST['group_id'])) {
                throw new Exception('Group ID required');
            }
            
            $groupId = (int)$_POST['group_id'];
            $success = updateGroupTimestamp($groupId);
            
            // Also update all screens in the group
            $groupScreens = getScreensByGroup($groupId);
            foreach ($groupScreens as $screen) {
                updateScreenTimestamp($screen['id']);
            }
            
            echo json_encode([
                'success' => $success,
                'message' => 'Group content update triggered',
                'group_id' => $groupId,
                'screens_updated' => count($groupScreens),
                'timestamp' => time()
            ]);
            break;
            
        case 'broadcast_global_update':
            // Update all screens and groups
            $pdo = new PDO("mysql:host=localhost;dbname=signage_system", 'root', '4821');
            
            $pdo->exec("UPDATE screens SET updated_at = NOW()");
            $pdo->exec("UPDATE `groups` SET updated_at = NOW()");
            
            echo json_encode([
                'success' => true,
                'message' => 'Global content update triggered',
                'timestamp' => time()
            ]);
            break;
            
        case 'get_sync_status':
            $screenId = $_GET['screen_id'] ?? null;
            $groupId = $_GET['group_id'] ?? null;
            
            $status = [
                'timestamp' => time(),
                'server_time' => date('Y-m-d H:i:s')
            ];
            
            if ($screenId) {
                $screen = getScreenById($screenId);
                $status['screen'] = $screen;
                $status['screen_last_update'] = $screen ? strtotime($screen['updated_at']) : 0;
            }
            
            if ($groupId) {
                $group = getGroupById($groupId);
                $status['group'] = $group;
                $status['group_last_update'] = $group ? strtotime($group['updated_at']) : 0;
            }
            
            echo json_encode($status);
            break;
            
        case 'ping':
            echo json_encode([
                'success' => true,
                'message' => 'Sync API is online',
                'timestamp' => time(),
                'server_time' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ]);
}
?>