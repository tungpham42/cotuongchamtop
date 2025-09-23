<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\RoomController;
use Illuminate\Console\Command;

class TestMatchingLogic extends Command
{
    protected $signature = 'test:matching';
    protected $description = 'Test the anonymous matching logic';

    public function handle()
    {
        $this->info('🧪 Testing Matching Logic Fix');
        $this->info('===============================');
        $this->newLine();

        // Cleanup old test data
        $this->info('🧹 Cleaning up old test data...');
        Room::where('name', 'like', '%test%')->delete();

        $controller = new RoomController();

        // Simulate Session ID for anonymous users
        $sessionId1 = 'test_session_' . time() . '_1';
        $sessionId2 = 'test_session_' . time() . '_2';

        $this->info("👤 Session 1: $sessionId1");
        $this->info("👤 Session 2: $sessionId2");
        $this->newLine();

        // Test 1: First player creates room
        $this->info('🎮 Test 1: First player requests quick match');
        $request1 = new Request(['session_id' => $sessionId1]);
        $response1 = $controller->anonymousQuickMatch($request1);
        $data1 = json_decode($response1->getContent(), true);

        $this->info('Response 1: ' . json_encode($data1, JSON_PRETTY_PRINT));
        $this->newLine();

        // Check database state after first player
        $rooms = Room::where('host_session', $sessionId1)->orWhere('guest_session', $sessionId1)->get();
        $this->info('🏠 Rooms in database after Player 1:');
        foreach ($rooms as $room) {
            $this->info("  - Room {$room->code}: host_session={$room->host_session}, guest_session={$room->guest_session}");
        }
        $this->newLine();

        // Test 2: Second player should join existing room
        $this->info('🎮 Test 2: Second player requests quick match');
        $request2 = new Request(['session_id' => $sessionId2]);
        $response2 = $controller->anonymousQuickMatch($request2);
        $data2 = json_decode($response2->getContent(), true);

        $this->info('Response 2: ' . json_encode($data2, JSON_PRETTY_PRINT));
        $this->newLine();

        // Check final database state
        $rooms = Room::where('host_session', $sessionId1)
            ->orWhere('guest_session', $sessionId1)
            ->orWhere('host_session', $sessionId2)
            ->orWhere('guest_session', $sessionId2)
            ->get();

        $this->info('🏠 Final rooms in database:');
        foreach ($rooms as $room) {
            $this->info("  - Room {$room->code}: host_session={$room->host_session}, guest_session={$room->guest_session}");
        }

        // Test Results Analysis
        $this->newLine();
        $this->info('📊 Test Results Analysis:');
        $this->info('===========================');

        if (isset($data1['room_code']) && isset($data2['room_code'])) {
            if ($data1['room_code'] === $data2['room_code']) {
                $this->info("✅ SUCCESS: Both players joined the same room ({$data1['room_code']})");
                $this->info('✅ Matching logic is working correctly!');
            } else {
                $this->error('❌ FAIL: Players joined different rooms');
                $this->error("   Player 1 room: {$data1['room_code']}");
                $this->error("   Player 2 room: {$data2['room_code']}");
                $this->error('❌ Matching logic still has issues');
            }
        } else {
            $this->error('❌ ERROR: Could not get room codes from responses');
        }

        // Check status endpoints
        $this->newLine();
        $this->info('🔍 Testing status check endpoints:');
        $this->info('===================================');

        $statusRequest1 = new Request(['session_id' => $sessionId1]);
        $statusResponse1 = $controller->checkAnonymousMatchStatus($statusRequest1);
        $statusData1 = json_decode($statusResponse1->getContent(), true);

        $this->info('Status check for Player 1: ' . json_encode($statusData1, JSON_PRETTY_PRINT));
        $this->newLine();

        $statusRequest2 = new Request(['session_id' => $sessionId2]);
        $statusResponse2 = $controller->checkAnonymousMatchStatus($statusRequest2);
        $statusData2 = json_decode($statusResponse2->getContent(), true);

        $this->info('Status check for Player 2: ' . json_encode($statusData2, JSON_PRETTY_PRINT));
        $this->newLine();

        // Cleanup test data
        $this->info('🧹 Cleaning up test data...');
        Room::where('host_session', $sessionId1)
            ->orWhere('guest_session', $sessionId1)
            ->orWhere('host_session', $sessionId2)
            ->orWhere('guest_session', $sessionId2)
            ->delete();

        $this->info('✅ Test completed!');
        
        return Command::SUCCESS;
    }
}
