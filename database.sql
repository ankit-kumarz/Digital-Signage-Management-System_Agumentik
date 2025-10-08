-- ===============================================
-- Digital Signage Management System Database
-- Developer: Ankit Kumar
-- Project: Agumentik Group of company Campus Recruitment
-- Date: September 2025
-- ===============================================

-- Create database
CREATE DATABASE IF NOT EXISTS `signage_system` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `signage_system`;

-- ===============================================
-- Users Table
-- ===============================================
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `full_name` varchar(100) DEFAULT NULL,
    `role` enum('admin', 'user') DEFAULT 'user',
    `status` enum('active', 'inactive') DEFAULT 'active',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Screens Table
-- ===============================================
CREATE TABLE `screens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL UNIQUE,
    `passcode` varchar(10) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `resolution` varchar(20) DEFAULT '1920x1080',
    `orientation` enum('landscape', 'portrait') DEFAULT 'landscape',
    `status` enum('active', 'inactive', 'maintenance') DEFAULT 'active',
    `location` varchar(255) DEFAULT NULL,
    `last_sync` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Media Table
-- ===============================================
CREATE TABLE `media` (
    `id` int(11) NOT NULL AUTO_INCREMENT, 
    `filename` varchar(255) NOT NULL,
    `original_name` varchar(255) NOT NULL,
    `file_type` enum('image', 'video') NOT NULL,
    `file_extension` varchar(10) NOT NULL,
    `file_size` bigint(20) NOT NULL,
    `file_path` varchar(500) NOT NULL,
    `duration` int(11) DEFAULT NULL COMMENT 'Duration in seconds for videos',
    `dimensions` varchar(20) DEFAULT NULL COMMENT 'Width x Height',
    `description` text DEFAULT NULL,
    `tags` varchar(500) DEFAULT NULL,
    `status` enum('active', 'inactive', 'processing') DEFAULT 'active',
    `uploaded_by` int(11) DEFAULT NULL,
    `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_file_type` (`file_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_uploaded_at` (`uploaded_at`),
    FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Groups Table
-- ===============================================
CREATE TABLE `groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `color` varchar(7) DEFAULT '#007bff' COMMENT 'Hex color code for UI',
    `status` enum('active', 'inactive') DEFAULT 'active',
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_name` (`name`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Screen Media Assignment Table
-- ===============================================
CREATE TABLE `screen_media` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `screen_id` int(11) NOT NULL,
    `media_id` int(11) NOT NULL,
    `order_position` int(11) DEFAULT 0,
    `display_duration` int(11) DEFAULT 10 COMMENT 'Duration in seconds',
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `status` enum('active', 'inactive', 'scheduled') DEFAULT 'active',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_screen_media_order` (`screen_id`, `media_id`, `order_position`),
    INDEX `idx_screen_order` (`screen_id`, `order_position`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Screen Groups Assignment Table
-- ===============================================
CREATE TABLE `screen_groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `group_id` int(11) NOT NULL,
    `screen_id` int(11) NOT NULL,
    `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_group_screen` (`group_id`, `screen_id`),
    INDEX `idx_group_id` (`group_id`),
    INDEX `idx_screen_id` (`screen_id`),
    FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Playlists Table
-- ===============================================
CREATE TABLE `playlists` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `total_duration` int(11) DEFAULT 0 COMMENT 'Total duration in seconds',
    `loop_enabled` boolean DEFAULT true,
    `shuffle_enabled` boolean DEFAULT false,
    `status` enum('active', 'inactive', 'draft') DEFAULT 'draft',
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_name` (`name`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Playlist Media Table
-- ===============================================
CREATE TABLE `playlist_media` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `playlist_id` int(11) NOT NULL,
    `media_id` int(11) NOT NULL,
    `order_position` int(11) DEFAULT 0,
    `display_duration` int(11) DEFAULT 10 COMMENT 'Duration in seconds',
    `transition_effect` enum('none', 'fade', 'slide', 'zoom') DEFAULT 'none',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_playlist_media_order` (`playlist_id`, `media_id`, `order_position`),
    INDEX `idx_playlist_order` (`playlist_id`, `order_position`),
    FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Screen Activity Logs Table
-- ===============================================
CREATE TABLE `screen_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `screen_id` int(11) NOT NULL,
    `action` enum('sync', 'connect', 'disconnect', 'error', 'update') NOT NULL,
    `message` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_screen_action` (`screen_id`, `action`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`screen_id`) REFERENCES `screens` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- System Settings Table
-- ===============================================
CREATE TABLE `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL UNIQUE,
    `setting_value` text DEFAULT NULL,
    `setting_type` enum('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    `description` text DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_setting_key` (`setting_key`),
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Sample Data Insertion
-- ===============================================

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@signage.com', 'System Administrator', 'admin', 'active'),
('demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'demo@signage.com', 'Demo User', 'user', 'active');

-- Insert sample groups
INSERT INTO `groups` (`name`, `description`, `color`, `created_by`) VALUES
('Main Lobby', 'Screens in the main entrance area', '#007bff', 1),
('Conference Rooms', 'Meeting and conference room displays', '#28a745', 1),
('Cafeteria', 'Food court and dining area screens', '#ffc107', 1),
('Reception Areas', 'Reception and waiting area displays', '#dc3545', 1);

-- Insert sample screens
INSERT INTO `screens` (`name`, `slug`, `passcode`, `description`, `resolution`, `location`, `status`) VALUES
('Main Entrance Display', 'main-entrance', '1234', 'Primary display at main entrance', '1920x1080', 'Main Lobby', 'active'),
('Conference Room A', 'conf-room-a', '5678', 'Meeting room A display board', '1920x1080', 'Conference Room A', 'active'),
('Cafeteria Menu Board', 'cafeteria-menu', '9012', 'Digital menu display', '1366x768', 'Cafeteria', 'active'),
('Reception Info', 'reception-info', '3456', 'Information display at reception', '1920x1080', 'Reception', 'active');

-- Assign screens to groups
INSERT INTO `screen_groups` (`group_id`, `screen_id`) VALUES
(1, 1), -- Main Lobby
(2, 2), -- Conference Rooms
(3, 3), -- Cafeteria
(4, 4); -- Reception Areas

-- Insert default system settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('system_name', 'Digital Signage System', 'string', 'Name of the signage system'),
('default_display_duration', '10', 'integer', 'Default media display duration in seconds'),
('max_file_size', '52428800', 'integer', 'Maximum file upload size in bytes (50MB)'),
('auto_sync_interval', '30', 'integer', 'Auto sync interval in seconds'),
('maintenance_mode', 'false', 'boolean', 'System maintenance mode'),
('allowed_image_types', 'jpg,jpeg,png,gif', 'string', 'Allowed image file extensions'),
('allowed_video_types', 'mp4,avi,mov,wmv', 'string', 'Allowed video file extensions'),
('timezone', 'UTC', 'string', 'System timezone'),
('date_format', 'Y-m-d H:i:s', 'string', 'Date format for display'),
('company_name', 'Argumentik Group', 'string', 'Company name for branding');

-- ===============================================
-- Views for easier data access
-- ===============================================

-- View for screen media with details
CREATE VIEW `screen_media_view` AS
SELECT 
    sm.id,
    sm.screen_id,
    s.name as screen_name,
    s.slug as screen_slug,
    sm.media_id,
    m.filename,
    m.original_name,
    m.file_type,
    m.file_path,
    sm.order_position,
    sm.display_duration,
    sm.status,
    sm.created_at
FROM `screen_media` sm
JOIN `screens` s ON sm.screen_id = s.id
JOIN `media` m ON sm.media_id = m.id
WHERE sm.status = 'active' AND s.status = 'active'
ORDER BY sm.screen_id, sm.order_position;

-- View for group screens
CREATE VIEW `group_screens_view` AS
SELECT 
    g.id as group_id,
    g.name as group_name,
    g.description as group_description,
    s.id as screen_id,
    s.name as screen_name,
    s.slug as screen_slug,
    s.location,
    s.status as screen_status,
    sg.assigned_at
FROM `groups` g
JOIN `screen_groups` sg ON g.id = sg.group_id
JOIN `screens` s ON sg.screen_id = s.id
WHERE g.status = 'active'
ORDER BY g.name, s.name;

-- ===============================================
-- Stored Procedures
-- ===============================================

DELIMITER //

-- Procedure to get screen sync data
CREATE PROCEDURE GetScreenSyncData(IN screen_slug VARCHAR(100))
BEGIN
    SELECT 
        s.id,
        s.name,
        s.slug,
        s.last_sync,
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'media_id', m.id,
                'filename', m.filename,
                'file_type', m.file_type,
                'file_path', m.file_path,
                'display_duration', sm.display_duration,
                'order_position', sm.order_position
            )
        ) as media_content
    FROM screens s
    LEFT JOIN screen_media sm ON s.id = sm.screen_id AND sm.status = 'active'
    LEFT JOIN media m ON sm.media_id = m.id AND m.status = 'active'
    WHERE s.slug = screen_slug AND s.status = 'active'
    GROUP BY s.id;
    
    -- Update last sync time
    UPDATE screens SET last_sync = NOW() WHERE slug = screen_slug;
END //

-- Procedure to clean up old logs
CREATE PROCEDURE CleanupOldLogs(IN days_to_keep INT)
BEGIN
    DELETE FROM screen_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL days_to_keep DAY);
    
    SELECT ROW_COUNT() as deleted_records;
END //

DELIMITER ;

-- ===============================================
-- Triggers
-- ===============================================

-- Trigger to update playlist total duration
DELIMITER //
CREATE TRIGGER update_playlist_duration 
AFTER INSERT ON playlist_media
FOR EACH ROW
BEGIN
    UPDATE playlists 
    SET total_duration = (
        SELECT SUM(display_duration) 
        FROM playlist_media 
        WHERE playlist_id = NEW.playlist_id
    )
    WHERE id = NEW.playlist_id;
END //

CREATE TRIGGER update_playlist_duration_delete
AFTER DELETE ON playlist_media
FOR EACH ROW
BEGIN
    UPDATE playlists 
    SET total_duration = (
        SELECT COALESCE(SUM(display_duration), 0)
        FROM playlist_media 
        WHERE playlist_id = OLD.playlist_id
    )
    WHERE id = OLD.playlist_id;
END //
DELIMITER ;

-- ===============================================
-- Indexes for Performance
-- ===============================================

-- Additional composite indexes for performance
CREATE INDEX idx_screen_media_status ON screen_media (screen_id, status, order_position);
CREATE INDEX idx_media_type_status ON media (file_type, status, uploaded_at);
CREATE INDEX idx_screen_status_slug ON screens (status, slug);
CREATE INDEX idx_groups_status_name ON groups (status, name);

-- ===============================================
-- Database Complete
-- ===============================================
-- This database schema supports:
-- ✓ User authentication and management
-- ✓ Screen management with unique slugs
-- ✓ Media file handling (images/videos)
-- ✓ Group organization of screens
-- ✓ Playlist functionality
-- ✓ Screen-media assignments with ordering
-- ✓ Activity logging
-- ✓ System settings
-- ✓ Performance optimization
-- ✓ Data integrity with foreign keys
-- ===============================================
