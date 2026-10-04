<?php

namespace Tests\Unit\Models;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class RoomTimerTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Cache::flush();

        parent::tearDown();
    }

    public function test_room_is_not_timed_out_while_waiting(): void
    {
        $room = new Room([
            'code' => 'WAIT-1',
            'active_player' => 'waiting',
            'red_time' => 600,
            'black_time' => 600,
            'last_update' => now()->subMinutes(10),
        ]);

        $this->assertFalse($room->hasTimedOut());
    }

    public function test_room_is_not_timed_out_when_paused(): void
    {
        $room = new Room([
            'code' => 'PAUSE-1',
            'active_player' => 'paused:red',
            'red_time' => 600,
            'black_time' => 600,
            'last_update' => now()->subMinutes(10),
        ]);

        $this->assertFalse($room->hasTimedOut());
    }

    public function test_red_player_times_out_when_move_elapsed_reaches_120_seconds(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:00'));

        $room = new Room([
            'code' => 'TIME-1',
            'active_player' => 'red',
            'red_time' => 600,
            'black_time' => 600,
            'last_update' => now()->subSeconds(120),
        ]);

        $this->assertTrue($room->hasTimedOut());
    }

    public function test_black_player_times_out_when_remaining_clock_reaches_zero(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:00'));

        $room = new Room([
            'code' => 'TIME-2',
            'active_player' => 'black',
            'red_time' => 600,
            'black_time' => 1,
            'last_update' => now()->subSeconds(2),
        ]);

        $this->assertTrue($room->hasTimedOut());
    }

    public function test_calculated_times_subtract_elapsed_time_from_active_player(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:30'));

        $room = new Room([
            'code' => 'CALC-1',
            'active_player' => 'red',
            'red_time' => 600,
            'black_time' => 600,
            'last_update' => Carbon::parse('2026-10-04 12:00:00'),
        ]);

        $times = $room->getCalculatedTimes();

        $this->assertSame(570.0, $times['red_time']);
        $this->assertSame(600.0, $times['black_time']);
        $this->assertSame(30.0, $times['move_elapsed']);
        $this->assertSame('red', $times['active_player']);
    }

    public function test_buffered_move_elapsed_is_included_in_timeout_calculation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:10'));

        $room = new Room([
            'code' => 'BUF-1',
            'active_player' => 'red',
            'red_time' => 600,
            'black_time' => 600,
            'last_update' => Carbon::parse('2026-10-04 12:00:00'),
        ]);

        Cache::put('room_BUF-1_move_elapsed', 110);

        $this->assertTrue($room->hasTimedOut());
    }

    public function test_paused_clock_does_not_subtract_elapsed_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-04 12:00:30'));

        $room = new Room([
            'code' => 'PAUSE-2',
            'active_player' => 'paused:red',
            'red_time' => 600,
            'black_time' => 600,
            'last_update' => Carbon::parse('2026-10-04 12:00:00'),
        ]);

        $times = $room->getCalculatedTimes();

        $this->assertSame(600.0, $times['red_time']);
        $this->assertSame(600.0, $times['black_time']);
        $this->assertSame('paused:red', $times['active_player']);
    }

    public function test_process_timeout_maps_red_and_black_to_expected_results(): void
    {
        $redRoom = Mockery::mock(Room::class)->makePartial();
        $redRoom->active_player = 'red';
        $redRoom->shouldReceive('update')
            ->once()
            ->with(['result' => '-1', 'modified_at' => Mockery::type(Carbon::class)])
            ->andReturn(true);

        $this->assertTrue($redRoom->processTimeout());

        $blackRoom = Mockery::mock(Room::class)->makePartial();
        $blackRoom->active_player = 'black';
        $blackRoom->shouldReceive('update')
            ->once()
            ->with(['result' => '1', 'modified_at' => Mockery::type(Carbon::class)])
            ->andReturn(true);

        $this->assertTrue($blackRoom->processTimeout());
    }
}
