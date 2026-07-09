<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateRoomsCommand extends Command
{
    protected $signature = 'update:rooms';
    protected $description = 'Update rooms results for timeouts and resignations';

    public function handle()
    {
        $timeoutCount = 0;
        $processedCount = 0;

        $this->info('Starting room timeout checks...');

        try {
            // Using chunkById avoids shifting offsets while updating records
            Room::where(function($query) {
                    $query->whereNotNull('host_id')->orWhereNotNull('host_session');
                })
                ->where(function($query) {
                    $query->whereNotNull('guest_id')->orWhereNotNull('guest_session');
                })
                ->whereNull('result')
                ->whereNotNull('active_player')
                ->where('active_player', '!=', 'waiting')
                ->where('active_player', 'NOT LIKE', 'paused:%')
                ->chunkById(100, function ($rooms) use (&$timeoutCount, &$processedCount) {
                    foreach ($rooms as $room) {
                        $processedCount++;

                        try {
                            if ($room->hasTimedOut()) {
                                $room->processTimeout();
                                $timeoutCount++;
                            }
                        } catch (Throwable $e) {
                            Log::error("Failed to process timeout for room {$room->code}: " . $e->getMessage());
                            $this->warn("Error processing room {$room->code}. Moving to next...");
                        }
                    }
                });

            $this->info("Processed {$processedCount} active rooms.");
            $this->info("{$timeoutCount} ongoing rooms updated due to timeouts!");

            return Command::SUCCESS;

        } catch (Throwable $e) {
            Log::critical('UpdateRoomsCommand failed entirely: ' . $e->getMessage());
            $this->error('A critical error occurred while updating rooms. Check logs for details.');

            return Command::FAILURE;
        }
    }
}
