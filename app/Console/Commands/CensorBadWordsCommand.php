<?php

namespace App\Console\Commands;

use App\Models\Room;
use App\Models\Puzzle;
use App\Models\PuzzleComment;
use App\Models\User;
use Illuminate\Console\Command;

class CensorBadWordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanitize:badWords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace bad words with *** in Rooms, Users, Puzzles, and Comments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $badWords = [
            ' Cu', ' cu', 'vl', 'dcm', 'dm', 'vailon', 'dume', 'ditme', ' ngu ', 'chó', 'Chó',
            'Cặc', ' lồn ', 'con cac', 'con cặc', 'con cu', 'cặc', 'cac', 'ccc', 'cc', 'vcl',
            'vú', 'địt', 'dit', 'đụ', 'stupid', 'shit', 'piss', 'fuck', 'cunt', 'cocksucker',
            'motherfucker', 'tits', 'sex', 'sexy', 'nude', 'naked', 'porn'
        ];

        $this->info('Starting to sanitize bad words...');

        // Xử lý từng Model với các field tương ứng cần lọc
        $this->sanitizeModel(Room::class, ['name'], $badWords);
        $this->sanitizeModel(Puzzle::class, ['name'], $badWords);
        $this->sanitizeModel(User::class, ['name'], $badWords);
        $this->sanitizeModel(PuzzleComment::class, ['author_name', 'content'], $badWords);

        $this->info('Sanitization completed. Bad words have been replaced with ***.');

        return Command::SUCCESS;
    }

    /**
     * Hàm dùng chung để truy vấn và update các bản ghi chứa từ khóa vi phạm
     */
    private function sanitizeModel(string $modelClass, array $fields, array $badWords)
    {
        $query = $modelClass::query();

        // Chỉ query những bản ghi có chứa ít nhất 1 bad word để tối ưu performance
        $query->where(function ($q) use ($fields, $badWords) {
            foreach ($fields as $field) {
                foreach ($badWords as $word) {
                    $q->orWhere($field, 'LIKE', "%{$word}%");
                }
            }
        });

        // Dùng chunk để không làm tràn memory nếu table có hàng trăm nghìn records
        $query->chunk(200, function ($records) use ($fields, $badWords) {
            foreach ($records as $record) {
                $needsUpdate = false;

                foreach ($fields as $field) {
                    // str_ireplace tự động không phân biệt hoa thường (case-insensitive)
                    $sanitizedText = str_ireplace($badWords, '***', $record->$field);

                    if ($sanitizedText !== $record->$field) {
                        $record->$field = $sanitizedText;
                        $needsUpdate = true;
                    }
                }

                if ($needsUpdate) {
                    // Bạn có thể dùng saveQuietly() nếu không muốn trigger các Event/Observer khi update
                    $record->save();
                }
            }
        });
    }
}
