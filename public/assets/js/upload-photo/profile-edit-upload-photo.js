// script for updating profile photo without creating a form
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar-upload');
    const previewImg = document.getElementById('pp-img');

    if (!fileInput || !previewImg) return;

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        previewImg.classList.add('avatar-loading');

        const formData = new FormData();
        formData.append('avatar_input', file);

        fetch('/u/submit-edit-avatar', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
            },
            body: formData
        })
            .then(res => res.json())
            .then(data => {
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
