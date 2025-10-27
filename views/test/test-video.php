<?php
require base_path('views/shared/header.php');
?>

<h2>🎥 Stream Video Test</h2>
<button id="connectBtn">Connect to Stream</button>
<button id="joinCallBtn">Join Test Call</button>
<div>
    <video id="localVideo" autoplay muted playsinline></video>
    <video id="remoteVideo" autoplay playsinline></video>
</div>

<script>
    console.log(window.StreamVideoClient);
</script>

<?php require base_path('views/shared/footer.php') ?>