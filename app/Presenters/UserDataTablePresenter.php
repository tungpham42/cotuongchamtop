<?php

namespace App\Presenters;

use App\Presenters\UserPresenter;

class UserDataTablePresenter
{
    protected string $locale;
    protected array $t;
    protected UserPresenter $userPresenter;

    public function __construct(string $locale, UserPresenter $userPresenter)
    {
        $this->locale = $locale;
        $this->userPresenter = $userPresenter;

        $texts = [
            'vi' => ['challenge' => 'Thách đấu', 'profile' => 'Hồ sơ'],
            'en' => ['challenge' => 'Challenge', 'profile' => 'Profile'],
            'ja' => ['challenge' => '挑戦', 'profile' => 'プロフィール'],
            'ko' => ['challenge' => '도전', 'profile' => '프로필'],
            'zh' => ['challenge' => '挑战', 'profile' => '个人资料'],
        ];

        $this->t = $texts[$locale] ?? $texts['en'];
    }

    public function formatRank($row): string
    {
        return '<span class="badge badge-status"><i class="fas fa-medal"></i> ' . $this->userPresenter->renderUserRank($row->id) . '</span>';
    }

    public function formatName($row): string
    {
        return $this->userPresenter->renderPlayerName($row->id, false, true);
    }

    public function formatElo($row): string
    {
        return '<strong style="color: var(--royal-gold);">' . $this->userPresenter->renderElo($row->id) . '</strong>';
    }

    public function formatTime($row): string
    {
        return date('Y-m-d | H:i:s', strtotime($row->created_at));
    }

    public function formatAction($row): string
    {
        if (auth()->check()) {
            if (auth()->id() != $row->id) {
                $actionBtn = '<a class="btn btn-danger text-light mr-1 pulse-red" style="width: 140px;" href="javascript:compete('.$row->id.');"><i class="far fa-mouse"></i> '.$this->t['challenge'].'</a>';
            } else {
                $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 140px; cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> '.$this->t['challenge'].'</a>';
            }
        } else {
            $actionBtn = '<a class="btn btn-danger text-light mr-1 pulse-red" style="width: 140px;" href="'.localized_url('login').'"><i class="far fa-sign-in"></i> '.$this->t['challenge'].'</a>';
        }

        $actionBtn .= '<a class="btn btn-dark text-light" style="width: 140px;" href="'.localized_url('app.player', ['id' => $row->id]).'"><i class="far fa-user-alt"></i> '.$this->t['profile'].'</a>';

        return $actionBtn;
    }
}
