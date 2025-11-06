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
        $userId = \Core\Auth::user();

        $token = Stream::getStreamVideoToken($userId);

        header('Content-Type: application/json');
        echo json_encode($token);
    }

    public function handleInitiateCall()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $receiverId = $_POST['receiverId'] ?? null;

        if (!$receiverId) {
            http_response_code(400);
            echo json_encode(['error' => 'Receiver ID missing']);
            return;
        }

        $call = \Models\Stream\Stream::createVideoCall($userId, $receiverId);

        // Optional: Push a notification (e.g., WebSocket or Stream chat event)
        // ...

        echo json_encode([
            'status' => 'initiated',
            'callId' => $call['callId'],
            'callType' => $call['callType']
        ]);
    }

    public function handleReceiveVideoCall()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $callId = $_POST['callId'] ?? null;

        if (!$callId) {
            http_response_code(400);
            echo json_encode(['error' => 'Call ID missing']);
            return;
        }

        echo json_encode([
            'status' => 'accepted',
            'callId' => $callId
        ]);
    }

    public function handleEndVideoCall()
    {
        $callId = $_POST['callId'] ?? null;

        if (!$callId) {
            http_response_code(400);
            echo json_encode(['error' => 'Call ID missing']);
            return;
        }

        // Optionally notify both users
        // ...

        echo json_encode([
            'status' => 'ended',
            'callId' => $callId
        ]);
    }
}
