<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\RoomController;

// Khởi tạo Laravel app
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "🧪 Testing Matching Logic Fix\n";
echo "===============================\n\n";

// Cleanup old test data
echo "🧹 Cleaning up old test data...\n";
Room::where('name', 'like', '%test%')->delete();

$controller = new RoomController();

// Simulate Session ID for anonymous users
$sessionId1 = 'test_session_' . time() . '_1';
$sessionId2 = 'test_session_' . time() . '_2';

echo "👤 Session 1: $sessionId1\n";
echo "👤 Session 2: $sessionId2\n\n";

// Test 1: First player creates room
echo "🎮 Test 1: First player requests quick match\n";
$request1 = new Request(['session_id' => $sessionId1]);
$response1 = $controller->anonymousQuickMatch($request1);
$data1 = json_decode($response1->getContent(), true);

echo "Response 1: " . json_encode($data1, JSON_PRETTY_PRINT) . "\n\n";

// Check database state after first player
$rooms = Room::where('host_session', $sessionId1)->orWhere('guest_session', $sessionId1)->get();
echo "🏠 Rooms in database after Player 1:\n";
foreach ($rooms as $room) {
    echo "  - Room {$room->code}: host_session={$room->host_session}, guest_session={$room->guest_session}\n";
}
echo "\n";

// Test 2: Second player should join existing room
echo "🎮 Test 2: Second player requests quick match\n";
$request2 = new Request(['session_id' => $sessionId2]);
$response2 = $controller->anonymousQuickMatch($request2);
$data2 = json_decode($response2->getContent(), true);

echo "Response 2: " . json_encode($data2, JSON_PRETTY_PRINT) . "\n\n";

// Check final database state
$rooms = Room::where('host_session', $sessionId1)
    ->orWhere('guest_session', $sessionId1)
    ->orWhere('host_session', $sessionId2)
    ->orWhere('guest_session', $sessionId2)
    ->get();

echo "🏠 Final rooms in database:\n";
foreach ($rooms as $room) {
    echo "  - Room {$room->code}: host_session={$room->host_session}, guest_session={$room->guest_session}\n";
}

// Test Results Analysis
echo "\n📊 Test Results Analysis:\n";
echo "===========================\n";

if (isset($data1['room_code']) && isset($data2['room_code'])) {
    if ($data1['room_code'] === $data2['room_code']) {
        echo "✅ SUCCESS: Both players joined the same room ({$data1['room_code']})\n";
        echo "✅ Matching logic is working correctly!\n";
    } else {
        echo "❌ FAIL: Players joined different rooms\n";
        echo "   Player 1 room: {$data1['room_code']}\n";
        echo "   Player 2 room: {$data2['room_code']}\n";
        echo "❌ Matching logic still has issues\n";
    }
} else {
    echo "❌ ERROR: Could not get room codes from responses\n";
}

// Check status endpoints
echo "\n🔍 Testing status check endpoints:\n";
echo "===================================\n";

$statusRequest1 = new Request(['session_id' => $sessionId1]);
$statusResponse1 = $controller->checkAnonymousMatchStatus($statusRequest1);
$statusData1 = json_decode($statusResponse1->getContent(), true);

echo "Status check for Player 1: " . json_encode($statusData1, JSON_PRETTY_PRINT) . "\n\n";

$statusRequest2 = new Request(['session_id' => $sessionId2]);
$statusResponse2 = $controller->checkAnonymousMatchStatus($statusRequest2);
$statusData2 = json_decode($statusResponse2->getContent(), true);

echo "Status check for Player 2: " . json_encode($statusData2, JSON_PRETTY_PRINT) . "\n\n";

// Cleanup test data
echo "🧹 Cleaning up test data...\n";
Room::where('host_session', $sessionId1)
    ->orWhere('guest_session', $sessionId1)
    ->orWhere('host_session', $sessionId2)
    ->orWhere('guest_session', $sessionId2)
    ->delete();

echo "✅ Test completed!\n";