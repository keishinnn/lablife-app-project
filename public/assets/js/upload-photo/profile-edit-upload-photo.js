// script for updating profile photo without creating a form
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar-upload');
    const previewImg = document.getElementById('pp-img');

    if (!fileInput || !previewImg) return;

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // Add loading effect to current avatar
        previewImg.classList.add('avatar-loading');

        // Prepare upload
        const formData = new FormData();
        formData.append('avatar_input', file);

        // Upload to backend
        fetch('/u/submit-edit-avatar', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': "<?= $_SESSION['csrf_token'] ?>"
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                console.log("Upload response:", data); // 👀 DEBUG
                if (data.success && data.avatarUrl) {
                    previewImg.src = data.avatarUrl + "?t=" + Date.now();
                } else {
                    alert(data.message || 'Failed to upload avatar.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Something went wrong while uploading.');
            })
            .finally(() => {
                previewImg.classList.remove('avatar-loading');
            });
    });
});