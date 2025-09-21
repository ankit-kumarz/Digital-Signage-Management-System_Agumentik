<?php
require_once 'config/database.php';
require_once 'config/config.php';

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function redirectToDashboard() {
    header('Location: dashboard.php');
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function isValidFileType($filename, $type) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($type === 'image') {
        return in_array($extension, ALLOWED_IMAGE_TYPES);
    } elseif ($type === 'video') {
        return in_array($extension, ALLOWED_VIDEO_TYPES);
    }
    return false;
}

function uploadFile($file, $type) {
    global $pdo;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    if (!isValidFileType($file['name'], $type)) {
        return false;
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . $type . 's/' . $filename;
    
    if (!file_exists(dirname($uploadPath))) {
        mkdir(dirname($uploadPath), 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $stmt = $pdo->prepare("INSERT INTO media (filename, original_name, file_type, file_size, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$filename, $file['name'], $type, $file['size']]);
        return $pdo->lastInsertId();
    }
    
    return false;
}

function getAllMedia() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM media ORDER BY uploaded_at DESC");
    return $stmt->fetchAll();
}

function deleteMedia($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $media = $stmt->fetch();
    
    if ($media) {
        $filePath = UPLOAD_PATH . $media['file_type'] . 's/' . $media['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $stmt = $pdo->prepare("DELETE FROM screen_media WHERE media_id = ?");
        $stmt->execute([$id]);
        
        $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    }
    return false;
}

function createScreen($name, $slug, $passcode) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO screens (name, slug, passcode, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    return $stmt->execute([$name, $slug, password_hash($passcode, PASSWORD_DEFAULT)]);
}

function assignMediaToScreen($screenId, $mediaIds) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM screen_media WHERE screen_id = ?");
    $stmt->execute([$screenId]);
    
    foreach ($mediaIds as $index => $mediaId) {
        $stmt = $pdo->prepare("INSERT INTO screen_media (screen_id, media_id, order_position) VALUES (?, ?, ?)");
        $stmt->execute([$screenId, $mediaId, $index + 1]);
    }
}

function getAllScreens() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM screens ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function getScreenBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM screens WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getScreenMedia($screenId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.* FROM media m 
        JOIN screen_media sm ON m.id = sm.media_id 
        WHERE sm.screen_id = ? 
        ORDER BY sm.order_position
    ");
    $stmt->execute([$screenId]);
    return $stmt->fetchAll();
}

function verifyScreenPassword($screenId, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT passcode FROM screens WHERE id = ?");
    $stmt->execute([$screenId]);
    $screen = $stmt->fetch();
    
    if ($screen) {
        return password_verify($password, $screen['passcode']);
    }
    return false;
}

function authenticateUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function registerUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return false;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$username, $hashedPassword]);
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

function createChart($name, $chartData) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO charts (name, chart_data, created_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$name, json_encode($chartData)]);
}

function getAllCharts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM charts ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function deleteChart($id) {
    global $pdo;
    
    try {
        // Delete from screen_charts first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM screen_charts WHERE chart_id = ?");
        $stmt->execute([$id]);
        
        // Delete the chart itself
        $stmt = $pdo->prepare("DELETE FROM charts WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        // Check if any rows were actually deleted
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Error deleting chart: " . $e->getMessage());
        return false;
    }
}

function assignChartsToScreen($screenId, $chartIds) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM screen_charts WHERE screen_id = ?");
    $stmt->execute([$screenId]);
    
    foreach ($chartIds as $chartId) {
        $stmt = $pdo->prepare("INSERT INTO screen_charts (screen_id, chart_id) VALUES (?, ?)");
        $stmt->execute([$screenId, $chartId]);
    }
}

function getScreenCharts($screenId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT c.* FROM charts c 
        JOIN screen_charts sc ON c.id = sc.chart_id 
        WHERE sc.screen_id = ?
    ");
    $stmt->execute([$screenId]);
    return $stmt->fetchAll();
}

function createGroup($name, $description = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO `groups` (name, description, created_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$name, $description]);
}

function getAllGroups() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM `groups` ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function assignScreensToGroup($groupId, $screenIds) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM screen_groups WHERE group_id = ?");
    $stmt->execute([$groupId]);
    
    foreach ($screenIds as $screenId) {
        $stmt = $pdo->prepare("INSERT INTO screen_groups (group_id, screen_id) VALUES (?, ?)");
        $stmt->execute([$groupId, $screenId]);
    }
}

function getGroupScreens($groupId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT s.* FROM screens s 
        JOIN screen_groups gs ON s.id = gs.screen_id 
        WHERE gs.group_id = ?
    ");
    $stmt->execute([$groupId]);
    return $stmt->fetchAll();
}

function getScreenGroup($screenId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT g.* FROM `groups` g 
        JOIN screen_groups gs ON g.id = gs.group_id 
        WHERE gs.screen_id = ?
    ");
    $stmt->execute([$screenId]);
    return $stmt->fetch();
}

function deleteGroup($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM screen_groups WHERE group_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM `groups` WHERE id = ?");
    $stmt->execute([$id]);
    return true;
}

function getGroupById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM `groups` WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getScreenById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM screens WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getScreensByGroup($groupId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT s.* FROM screens s 
        INNER JOIN screen_groups gs ON s.id = gs.screen_id 
        WHERE gs.group_id = ? 
        ORDER BY s.name
    ");
    $stmt->execute([$groupId]);
    return $stmt->fetchAll();
}

// Module 4: Content Sync Functions

function getRecentMedia($lastUpdate) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM media WHERE uploaded_at > FROM_UNIXTIME(?) ORDER BY uploaded_at DESC");
    $stmt->execute([$lastUpdate]);
    return $stmt->fetchAll();
}

function getRecentCharts($lastUpdate) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM charts WHERE created_at > FROM_UNIXTIME(?) ORDER BY created_at DESC");
    $stmt->execute([$lastUpdate]);
    return $stmt->fetchAll();
}

function updateScreenTimestamp($screenId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE screens SET updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$screenId]);
}

function updateGroupTimestamp($groupId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE `groups` SET updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$groupId]);
}

function broadcastContentUpdate($type, $data) {
    // This function can be used to trigger immediate updates
    // For now, we rely on the timestamp checking mechanism
    // In a production environment, this could use Redis or other pub/sub systems
    return true;
}

function triggerGlobalContentSync() {
    // Update all screens and groups timestamps to trigger sync
    global $pdo;
    
    try {
        $pdo->exec("UPDATE screens SET updated_at = NOW()");
        $pdo->exec("UPDATE `groups` SET updated_at = NOW()");
        return true;
    } catch (Exception $e) {
        error_log("Error triggering global content sync: " . $e->getMessage());
        return false;
    }
}

function triggerScreenSync($screenId) {
    // Update specific screen timestamp
    return updateScreenTimestamp($screenId);
}

function triggerGroupSync($groupId) {
    // Update specific group and its screens
    updateGroupTimestamp($groupId);
    
    $groupScreens = getScreensByGroup($groupId);
    foreach ($groupScreens as $screen) {
        updateScreenTimestamp($screen['id']);
    }
    
    return true;
}
?>