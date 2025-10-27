<?php require base_path('views/shared/header.php'); ?>

<div class="video-call-container">
    <!-- Remote Video -->
    <div class="remote-video-container">
        <video id="remoteVideo" autoplay playsinline></video>
    </div>

    <!-- Local Video (small preview) -->
    <div class="local-video-container">
        <video id="localVideo" autoplay muted playsinline></video>
    </div>

    <!-- Bottom Control Bar -->
    <div class="control-bar">
        <button id="toggleMic" class="control-btn">
            <i class="fas fa-microphone"></i>
        </button>
        <button id="toggleCam" class="control-btn">
            <i class="fas fa-video"></i>
        </button>
        <button id="screenShare" class="control-btn">
            <i class="fas fa-desktop"></i>
        </button>
        <button id="endCall" class="control-btn end-btn">
            <i class="fas fa-phone-slash"></i>
        </button>
    </div>
</div>

<!-- Optional -->
<button id="connectBtn">Connect</button>
<button id="joinCallBtn">Join Call</button>

<script src="https://kit.fontawesome.com/a2b4cbb1e4.js" crossorigin="anonymous"></script>
<script src="/assets/js/video-call.js"></script>

<?php require base_path('views/shared/footer.php'); ?>
