<?php

namespace App\Presenters;

use App\Actions\Puzzle\GetPuzzleRankAction;

class PuzzleDataTablePresenter
{
    protected string $locale;
    protected array $t;
    protected GetPuzzleRankAction $getPuzzleRankAction;

    public function __construct(string $locale, GetPuzzleRankAction $getPuzzleRankAction)
    {
        $this->locale = $locale;
        $this->getPuzzleRankAction = $getPuzzleRankAction;

        $texts = [
            'vi' => ['solve' => 'Giải cờ thế', 'preview' => 'Xem trước'],
            'en' => ['solve' => 'Solve puzzle', 'preview' => 'Preview'],
            'ja' => ['solve' => 'パズルを解く', 'preview' => 'プレビュー'],
            'ko' => ['solve' => '퍼즐 풀기', 'preview' => '미리보기'],
            'zh' => ['solve' => '解谜', 'preview' => '预览'],
        ];

        $this->t = $texts[$locale] ?? $texts['en'];
    }

    public function formatRank($row)
    {
        return $this->getPuzzleRankAction->execute($row->id);
    }

    public function formatName($row): string
    {
        return '<a class="text-danger animate showPromotion" style="cursor: pointer !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-slug="'.$row->slug.'" href="'.url('/').__("/the-co/").$row->slug.'">'.$row->name.'</a>';
    }

    public function formatRating($row): int
    {
        return (int) $row->likes_count;
    }

    public function formatAction($row): string
    {
        $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 160px;" data-fen="'.$row->fen.'" data-slug="'.$row->slug.'" href="'.url('/').__("/giai-co-the/").$row->fen.' r - - 0 1"><i class="far fa-mouse"></i> '.$this->t['solve'].'</a>';
        $actionBtn .= '<a class="ml-1 btn btn-warning previewBtn"><i class="far fa-eye"></i> '.$this->t['preview'].'</a>';
        return $actionBtn;
    }

    public function formatTime($row): string
    {
        return date('Y-m-d | H:i:s', strtotime($row->updated_at));
    }
}
