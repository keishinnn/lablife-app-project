<?php
// path = Controllers\User\Messages\VideoCallController.php
namespace Controllers\User\Messages;

use Models\User\User;
use Models\Stream\Stream;

class VideoCallController
{
    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();

        view('test/test-video.php');
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

        // Optionally notify both users
        // ...

        json_response([
            'status' => 'ended',
            'callId' => $callId
        ]);
    }
}
