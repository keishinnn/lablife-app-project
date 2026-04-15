<?php

require base_path('views/shared/header.php');

?>

<div class="verify-next-container">
    <h1>Verify with Face Recognition</h1>

    <div class="verify-next-img">
        <img src="/assets/images/default-avatar.png" alt="">
    </div>

    <p>Take a selfie to verify your account.</p>

    <div class="verify-next-texts">
        <p>Make sure your face is within the frame and you're in a well lit area.</p>
        <p>Remove anything on your face like a face mask or sunglasses.</p>
    </div>

    <a href="/u/verify" class="btn-primary" id="verify-next-btn">Next</a>
</div>

<?php require base_path('views/shared/footer.php') ?>