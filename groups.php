<?php
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_group'])) {
        $name = sanitizeInput($_POST['group_name']);
        $description = sanitizeInput($_POST['group_description']);
        
        if (!empty($name)) {
            if (createGroup($name, $description)) {
                $success = 'Group created successfully!';
            } else {
                $error = 'Failed to create group. Please try again.';
            }
        } else {
            $error = 'Please enter a group name.';
        }
    } elseif (isset($_POST['assign_screens'])) {
        $groupId = (int)$_POST['group_id'];
        $screenIds = $_POST['screens'] ?? [];
        
        if (!empty($groupId) && !empty($screenIds)) {
            if (assignScreensToGroup($groupId, $screenIds)) {
                $success = 'Screens assigned to group successfully!';
            } else {
                $error = 'Failed to assign screens. Please try again.';
            }
        } else {
            $error = 'Please select screens to assign.';
        }
    } elseif (isset($_POST['delete_group'])) {
        $groupId = (int)$_POST['group_id'];
        if (!empty($groupId)) {
            if (deleteGroup($groupId)) {
                $success = 'Group deleted successfully!';
            } else {
                $error = 'Failed to delete group. Please try again.';
            }
        }
    }
}

$groups = getAllGroups();
$screens = getAllScreens();
$pageTitle = 'Screen Groups';
include 'includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-layer-group me-2"></i>Screen Groups</h2>
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
            
            <div class="row">
                <!-- Create Group Form -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-plus me-2"></i>Create New Group</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="group_name" class="form-label">Group Name *</label>
                                    <input type="text" class="form-control" id="group_name" name="group_name" 
                                           placeholder="Enter group name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="group_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="group_description" name="group_description" 
                                              rows="3" placeholder="Enter group description (optional)"></textarea>
                                </div>
                                
                                <button type="submit" name="create_group" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Group
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Assign Screens to Group -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-desktop me-2"></i>Assign Screens to Group</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($groups)): ?>
                                <p class="text-muted">No groups available. Create a group first.</p>
                            <?php elseif (empty($screens)): ?>
                                <p class="text-muted">No screens available. Create screens first.</p>
                            <?php else: ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="group_id" class="form-label">Select Group *</label>
                                        <select class="form-control" id="group_id" name="group_id" required>
                                            <option value="">Choose a group...</option>
                                            <?php foreach ($groups as $group): ?>
                                                <option value="<?php echo $group['id']; ?>">
                                                    <?php echo htmlspecialchars($group['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Select Screens *</label>
                                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                                            <?php foreach ($screens as $screen): ?>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" 
                                                           name="screens[]" value="<?php echo $screen['id']; ?>" 
                                                           id="screen_<?php echo $screen['id']; ?>">
                                                    <label class="form-check-label" for="screen_<?php echo $screen['id']; ?>">
                                                        <strong><?php echo htmlspecialchars($screen['name']); ?></strong>
                                                        <small class="text-muted d-block">Slug: <?php echo $screen['slug']; ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="assign_screens" class="btn btn-success">
                                        <i class="fas fa-link me-2"></i>Assign Screens
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Existing Groups -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-list me-2"></i>Existing Groups</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($groups)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Groups Found</h5>
                                    <p class="text-muted">Create your first group using the form above.</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($groups as $group): ?>
                                        <?php 
                                        $groupScreens = getScreensByGroup($group['id']);
                                        ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="fas fa-layer-group me-2"></i>
                                                        <?php echo htmlspecialchars($group['name']); ?>
                                                    </h6>
                                                    
                                                    <?php if (!empty($group['description'])): ?>
                                                        <p class="card-text text-muted small">
                                                            <?php echo htmlspecialchars($group['description']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mb-2">
                                                        <span class="badge bg-primary">
                                                            <?php echo count($groupScreens); ?> screens
                                                        </span>
                                                        <small class="text-muted">
                                                            Created: <?php echo date('M j, Y', strtotime($group['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <?php if (!empty($groupScreens)): ?>
                                                        <div class="mb-2">
                                                            <small class="text-muted">Screens:</small>
                                                            <?php foreach ($groupScreens as $screen): ?>
                                                                <div class="small text-primary">
                                                                    • <?php echo htmlspecialchars($screen['name']); ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="card-footer">
                                                    <div class="d-flex justify-content-between">
                                                        <a href="view_group.php?id=<?php echo $group['id']; ?>" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </a>
                                                        
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this group?');">
                                                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                                            <button type="submit" name="delete_group" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash me-1"></i>Delete
                                                            </button>
                                                        </form>
                                                    </div>
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
    </div>
</div>

<?php include 'includes/footer.php'; ?>