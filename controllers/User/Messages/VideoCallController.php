<?php
// path = Controllers\User\Messages\VideoCallController.php
namespace Controllers\User\Messages;

use Models\User\User;
use Models\Stream\Stream;

class VideoCallController
{
    private function registerActiveCall(string $callId, string $role): void
    {
        if (!isset($_SESSION['active_video_calls']) || !is_array($_SESSION['active_video_calls'])) {
            $_SESSION['active_video_calls'] = [];
        }

        $_SESSION['active_video_calls'][$callId] = [
            'role' => $role,
            'started_at' => time(),
        ];
    }

    private function clearActiveCall(string $callId): void
    {
        if (!isset($_SESSION['active_video_calls']) || !is_array($_SESSION['active_video_calls'])) {
            return;
        }

        unset($_SESSION['active_video_calls'][$callId]);
    }

    public function View()
    {
        \Core\Middleware::auth();
        $callId = $_GET['callId'] ?? null;
        $activeCalls = $_SESSION['active_video_calls'] ?? [];
        $callState = $callId ? ($activeCalls[$callId] ?? null) : null;

        if (
            !$callId ||
            !$callState ||
            !is_array($callState) ||
            (($callState['started_at'] ?? 0) < (time() - 7200))
        ) {
            redirect('/u/messages');
        }

        $isCaller = ($callState['role'] ?? '') === 'caller';

        view('user/video-call/index.view.php', compact('callId', 'isCaller'));
    }

    public function handleGetStreamVideoToken()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();

        try {
            $token = Stream::getStreamVideoToken($userId);
            json_response($token);
        } catch (\Throwable $e) {
            app_log_exception($e, 'Get video token failed');
            json_response(['error' => generic_error_message()], 500);
        }
    }

    public function handleInitiateCall()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();
        $receiverId = $_POST['receiverId'] ?? null;

        if (!$receiverId) {
            json_response(['error' => 'Receiver ID missing'], 400);
        }

        try {
            $call = \Models\Stream\Stream::createVideoCall($userId, $receiverId);
            $this->registerActiveCall($call['callId'], 'caller');

            json_response([
                'status' => 'initiated',
                'callId' => $call['callId'],
                'callType' => $call['callType']
            ]);
        } catch (\Throwable $e) {
            app_log_exception($e, 'Initiate call failed');
            json_response(['error' => generic_error_message()], 500);
        }
    }

    public function handleReceiveVideoCall()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();
        $callId = $_POST['callId'] ?? null;

        if (!$callId) {
            json_response(['error' => 'Call ID missing'], 400);
        }

        $this->registerActiveCall($callId, 'callee');

        json_response([
            'status' => 'accepted',
            'callId' => $callId
        ]);
    }

    public function handleEndVideoCall()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $callId = $_POST['callId'] ?? null;

        if (!$callId) {
            json_response(['error' => 'Call ID missing'], 400);
        }

        $this->clearActiveCall($callId);

        json_response([
            'status' => 'ended',
            'callId' => $callId
        ]);
    }
}
