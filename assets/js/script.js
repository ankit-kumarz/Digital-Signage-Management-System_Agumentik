function openScreen(slug) {
    window.open('view_screen.php?slug=' + slug, '_blank');
}
function deleteMediaItem(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('storage.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Your file has been deleted.', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error!', 'Failed to delete file.', 'error');
                }
            });
        }
    });
}

function generateSlug() {
    const name = document.getElementById('screen_name').value;
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('screen_slug').value = slug;
}

function toggleAllMedia() {
    const selectAll = document.getElementById('select_all_media');
    const checkboxes = document.querySelectorAll('input[name="media[]"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function validateScreenForm() {
    const name = document.getElementById('screen_name').value.trim();
    const slug = document.getElementById('screen_slug').value.trim();
    const passcode = document.getElementById('screen_passcode').value.trim();
    const selectedMedia = document.querySelectorAll('input[name="media[]"]:checked');
    
    if (!name || !slug || !passcode) {
        Swal.fire('Error!', 'Please fill all required fields.', 'error');
        return false;
    }
    
    if (selectedMedia.length === 0) {
        Swal.fire('Error!', 'Please select at least one media item.', 'error');
        return false;
    }
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const screenNameInput = document.getElementById('screen_name');
    if (screenNameInput) {
        screenNameInput.addEventListener('input', generateSlug);
    }
    
    const screenForm = document.getElementById('screen_form');
    if (screenForm) {
        screenForm.addEventListener('submit', function(e) {
            if (!validateScreenForm()) {
                e.preventDefault();
            }
        });
    }
    
    const selectAllCheckbox = document.getElementById('select_all_media');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', toggleAllMedia);
    }
});