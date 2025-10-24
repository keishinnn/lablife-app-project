<!-- this must be manipulated by subscription js -->
<div class="chat-interface-section">
    <!-- using DOM manipulation, manipulate all the chats -->
    <!-- add conditional styling here, justify start if other user
          then justify end if you -->
    <div class="chat-interface-section-messages">
    </div>

    <!-- if is typing then show this
          set the display of this as display: none; -->
    <div class="chat-interface-section-three">
        <div class="chat-interface-section-four">
            <div class="chat-interface-section-five">
                <div class="chat-interface-section-six"></div>
                <div class="chat-interface-section-seven">
                </div>
                <div class="chat-interface-section-eight">
                </div>
            </div>
        </div>
    </div>

    <!-- show scroll button here -->
    <div class="chat-interface-section-nine">
        <button title="Please enter to send">
            <svg
                style="width: 1.25rem; height: 1.25rem;"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24">
                <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </button>
    </div>

    <div class="chat-interface-section-ten">
        <form class="chat-interface-form">
            <input type="text" placeholder="Aa" id="send-message-input">
            <button type="submit" id="send-message-btn" disabled title="Send">
                <svg width="1.25rem" height="1.25rem"
                    viewBox="0 0 28 28"
                    fill="#ec4899">
                    <path d="M3.78963301,2.77233335 L24.8609339,12.8499121 C25.4837277,13.1477699 25.7471402,13.8941055 25.4492823,14.5168992 C25.326107,14.7744476 25.1184823,14.9820723 24.8609339,15.1052476 L3.78963301,25.1828263 C3.16683929,25.4806842 2.42050372,25.2172716 2.12264586,24.5944779 C1.99321184,24.3238431 1.96542524,24.015685 2.04435886,23.7262618 L4.15190935,15.9983421 C4.204709,15.8047375 4.36814355,15.6614577 4.56699265,15.634447 L14.7775879,14.2474874 C14.8655834,14.2349166 14.938494,14.177091 14.9721837,14.0981464 L14.9897199,14.0353553 C15.0064567,13.9181981 14.9390703,13.8084248 14.8334007,13.7671556 L14.7775879,13.7525126 L4.57894108,12.3655968 C4.38011873,12.3385589 4.21671819,12.1952832 4.16392965,12.0016992 L2.04435886,4.22889788 C1.8627142,3.56286745 2.25538645,2.87569101 2.92141688,2.69404635 C3.21084015,2.61511273 3.51899823,2.64289932 3.78963301,2.77233335 Z" id="🎨-Color">

                    </path>
                </svg>
            </button>
        </form>
    </div>
    <!-- show incoming video call -->
</div>