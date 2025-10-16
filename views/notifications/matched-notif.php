<div class="notification-container" id="matchModal">
    <div class="notification-section">
        <div class="notification-section-one">
            <div class="notification-section-img">
                <img src="<?php echo $partner->avatarUrl ?>" alt="<?php echo $partner->fullName ?>">
            </div>

            <div class="notification-section-two">
                <div class="notification-section-three">
                    <div>
                        <h3>
                            It's a Match! 🎉
                        </h3>
                    </div>
                </div>

                <p>
                    You and <span style="font-weight: 600;"><?php echo htmlspecialchars($partner->fullName)  ?> </span>liked each other!
                </p>

                <div class="notification-section-four">
                    <button class="notification-start-chat-button">Start Chat</button>
                    <button class="notification-later-button" id="keep-searching-btn">Keep Searching</button>
                </div>
            </div>
        </div>
    </div>
</div>