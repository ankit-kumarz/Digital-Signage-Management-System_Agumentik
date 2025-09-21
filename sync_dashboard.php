<?php
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Sync Status Dashboard';
include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-sync-alt me-2"></i>Real-time Sync Dashboard</h4>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="refreshAllScreens()">
                            <i class="fas fa-broadcast-tower"></i> Refresh All Screens
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="location.reload()">
                            <i class="fas fa-redo"></i> Refresh Dashboard
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sync Status Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6>Total Screens</h6>
                                            <h3 id="totalScreens"><?php echo count(getAllScreens()); ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-tv fa-2x"></i>
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
                                            <h6>Connected Screens</h6>
                                            <h3 id="connectedScreens">0</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-wifi fa-2x"></i>
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
                                            <h6>Total Groups</h6>
                                            <h3><?php echo count(getAllGroups()); ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-layer-group fa-2x"></i>
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
                                            <h6>Last Global Update</h6>
                                            <h6 id="lastUpdate"><?php echo date('H:i:s'); ?></h6>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Screens Status Table -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-list"></i> Screen Status</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Screen Name</th>
                                                    <th>Status</th>
                                                    <th>Last Update</th>
                                                    <th>Content</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="screenStatusTable">
                                                <?php
                                                $screens = getAllScreens();
                                                foreach ($screens as $screen):
                                                    $mediaCount = count(getScreenMedia($screen['id']));
                                                    $chartCount = count(getScreenCharts($screen['id']));
                                                ?>
                                                <tr data-screen-id="<?php echo $screen['id']; ?>">
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($screen['name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($screen['slug']); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="status-indicator" id="status_<?php echo $screen['id']; ?>">
                                                            <i class="fas fa-circle text-secondary"></i> Unknown
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span id="lastUpdate_<?php echo $screen['id']; ?>">
                                                            <?php echo date('M j, Y H:i', strtotime($screen['updated_at'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?php echo $mediaCount; ?> media, 
                                                            <?php echo $chartCount; ?> charts
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" onclick="refreshScreen(<?php echo $screen['id']; ?>)">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                        <a href="view_screen.php?slug=<?php echo $screen['slug']; ?>" 
                                                           target="_blank" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sync Controls -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-cogs"></i> Sync Controls</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Trigger Content Update</label>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-warning" onclick="triggerGlobalUpdate()">
                                                <i class="fas fa-globe"></i> Global Update
                                            </button>
                                            <button class="btn btn-info" onclick="triggerGroupUpdate()">
                                                <i class="fas fa-layer-group"></i> Group Update
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Auto-refresh Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                                            <label class="form-check-label" for="autoRefresh">
                                                Auto-refresh every 5 seconds
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Connection Log</label>
                                        <div id="connectionLog" class="border rounded p-2" style="height: 200px; overflow-y: auto; font-size: 12px; background: #f8f9fa;">
                                            <div class="text-muted">Monitoring connections...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval;
let connectionLog = [];

document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
    logMessage('Dashboard initialized');
});

function startAutoRefresh() {
    const autoRefreshCheckbox = document.getElementById('autoRefresh');
    
    if (autoRefreshCheckbox.checked) {
        autoRefreshInterval = setInterval(checkScreenStatus, 5000);
        logMessage('Auto-refresh enabled');
    }
    
    autoRefreshCheckbox.addEventListener('change', function() {
        if (this.checked) {
            autoRefreshInterval = setInterval(checkScreenStatus, 5000);
            logMessage('Auto-refresh enabled');
        } else {
            clearInterval(autoRefreshInterval);
            logMessage('Auto-refresh disabled');
        }
    });
    
    // Initial status check
    checkScreenStatus();
}

function checkScreenStatus() {
    const screens = document.querySelectorAll('[data-screen-id]');
    let connectedCount = 0;
    
    screens.forEach(row => {
        const screenId = row.dataset.screenId;
        
        // For demonstration, randomly assign status
        // In a real implementation, this would check actual connection status
        const statuses = ['online', 'offline', 'syncing'];
        const randomStatus = statuses[Math.floor(Math.random() * statuses.length)];
        
        const statusElement = document.getElementById(`status_${screenId}`);
        
        switch(randomStatus) {
            case 'online':
                statusElement.innerHTML = '<i class="fas fa-circle text-success"></i> Online';
                connectedCount++;
                break;
            case 'offline':
                statusElement.innerHTML = '<i class="fas fa-circle text-danger"></i> Offline';
                break;
            case 'syncing':
                statusElement.innerHTML = '<i class="fas fa-circle text-warning"></i> Syncing';
                connectedCount++;
                break;
        }
    });
    
    document.getElementById('connectedScreens').textContent = connectedCount;
    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
}

function refreshScreen(screenId) {
    logMessage(`Triggering refresh for screen ${screenId}`);
    
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_screen_content&screen_id=${screenId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            logMessage(`Screen ${screenId} refresh triggered successfully`);
            // Update status indicator
            const statusElement = document.getElementById(`status_${screenId}`);
            statusElement.innerHTML = '<i class="fas fa-circle text-info"></i> Refreshing';
            
            setTimeout(() => {
                statusElement.innerHTML = '<i class="fas fa-circle text-success"></i> Online';
            }, 2000);
        } else {
            logMessage(`Failed to refresh screen ${screenId}: ${data.error}`);
        }
    })
    .catch(error => {
        logMessage(`Error refreshing screen ${screenId}: ${error.message}`);
    });
}

function refreshAllScreens() {
    logMessage('Triggering global content refresh');
    
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=broadcast_global_update'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            logMessage('Global refresh triggered successfully');
            
            // Update all status indicators
            const screens = document.querySelectorAll('[data-screen-id]');
            screens.forEach(row => {
                const screenId = row.dataset.screenId;
                const statusElement = document.getElementById(`status_${screenId}`);
                statusElement.innerHTML = '<i class="fas fa-circle text-info"></i> Refreshing';
                
                setTimeout(() => {
                    statusElement.innerHTML = '<i class="fas fa-circle text-success"></i> Online';
                }, 3000);
            });
        } else {
            logMessage(`Failed to trigger global refresh: ${data.error}`);
        }
    })
    .catch(error => {
        logMessage(`Error triggering global refresh: ${error.message}`);
    });
}

function triggerGlobalUpdate() {
    refreshAllScreens();
}

function triggerGroupUpdate() {
    // For simplicity, this will also trigger global update
    // In a real implementation, you'd show a group selector
    refreshAllScreens();
}

function logMessage(message) {
    const timestamp = new Date().toLocaleTimeString();
    const logEntry = `[${timestamp}] ${message}`;
    
    connectionLog.unshift(logEntry);
    if (connectionLog.length > 50) {
        connectionLog.pop();
    }
    
    const logElement = document.getElementById('connectionLog');
    logElement.innerHTML = connectionLog.map(entry => `<div>${entry}</div>`).join('');
}
</script>

<?php include 'includes/footer.php'; ?>