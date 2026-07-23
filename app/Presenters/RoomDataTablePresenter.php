<?php

namespace App\Presenters;

use App\Http\Controllers\RoomController;

class RoomDataTablePresenter
{
    protected string $locale;
    protected array $t;

    public function __construct(string $locale)
    {
        $this->locale = $locale;

        // This array could eventually be moved to standard Laravel lang/ files
        $texts = [
            'vi' => ['public' => 'Công khai', 'private' => 'Riêng tư', 'red' => 'Đỏ', 'black' => 'Đen', 'guest_won' => 'Đen thắng', 'draw' => 'Hòa', 'host_won' => 'Đỏ thắng', 'not_started' => 'Chưa bắt đầu', 'ongoing' => 'Đang đấu', 'play' => 'Chơi', 'watch' => 'Theo dõi', 'finished_btn' => 'Đã xong', 'finished_auth' => 'Đã đấu xong', 'play_now' => 'Chơi nào', 'login' => 'Đăng nhập', 'preview' => 'Xem trước'],
            'en' => ['public' => 'Public', 'private' => 'Private', 'red' => 'Red', 'black' => 'Black', 'guest_won' => 'Guest won', 'draw' => 'Draw', 'host_won' => 'Host won', 'not_started' => 'Not started', 'ongoing' => 'Ongoing', 'play' => 'Play', 'watch' => 'Watch', 'finished_btn' => 'Finished', 'finished_auth' => 'Finished', 'play_now' => 'Play now', 'login' => 'Login', 'preview' => 'Preview'],
            'ja' => ['public' => '公衆', 'private' => '民間', 'red' => '赤', 'black' => '黒', 'guest_won' => 'ゲストが勝ちました', 'draw' => 'ドローです', 'host_won' => 'ホストが勝ちました', 'not_started' => '開始されていない', 'ongoing' => '現在進行中', 'play' => '加入', 'watch' => '見る', 'finished_btn' => '終わり', 'finished_auth' => '終わり', 'play_now' => '加入', 'login' => 'ログイン', 'preview' => 'プレビュー'],
            'ko' => ['public' => '공공의', 'private' => '사적인', 'red' => '홍', 'black' => '검', 'guest_won' => '손님이 이겼어요', 'draw' => '동점입니다', 'host_won' => '주최자가 이겼어요', 'not_started' => '아직 시작되지 않음', 'ongoing' => '진행 중인', 'play' => '참여', 'watch' => '보다', 'finished_btn' => '끝났다', 'finished_auth' => '끝났다', 'play_now' => '참여', 'login' => '로그인', 'preview' => '미리보기'],
            'zh' => ['public' => '平民的', 'private' => '私有的', 'red' => '红', 'black' => '黑', 'guest_won' => '客人赢了', 'draw' => '平局', 'host_won' => '主办方赢了', 'not_started' => '未开始', 'ongoing' => '进行中的', 'play' => '参加', 'watch' => '看', 'finished_btn' => '结束', 'finished_auth' => '结束', 'play_now' => '参加', 'login' => '登录', 'preview' => '预览'],
        ];

        $this->t = $texts[$locale] ?? $texts['en'];
    }

    public function formatCode($row): string
    {
        $roomNameRaw = !empty($row->name) ? $row->name : $row->code;
        $roomNameHtml = '<span class="badge badge-status" style="background: rgba(20, 22, 28, 0.85); border: 1px solid var(--royal-gold); color: var(--royal-gold); box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);"><i class="fas fa-chess-board"></i> ' . $roomNameRaw . '</span>';

        $iconClass = empty($row->pass) ? 'fa-globe' : 'fa-lock';
        $iconTooltip = empty($row->pass) ? $this->t['public'] : $this->t['private'];
        $iconHtml = '<i class="ml-2 far ' . $iconClass . ' text-warning" data-toggle="tooltip" data-placement="top" data-original-title="' . $iconTooltip . '"></i>';

        if (!isset($row->host_id)) {
            return '<a style="text-decoration: none !important;" class="disabled" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">' . $roomNameHtml . '</a>' . $iconHtml;
        }

        if (auth()->check()) {
            if (isset($row->result)) {
                $statusIcon = '<i class="ml-2 far fa-archive text-secondary" data-toggle="tooltip" data-placement="top" data-original-title="'.$this->t['finished_auth'].'"></i>';
                return '<a class="showPromotion" href="javascript:void(0)" style="text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'">' . $roomNameHtml . '</a>' . $statusIcon;
            }

            $statusIcon = '<i class="ml-2 far fa-mouse text-warning pulse-gold" data-toggle="tooltip" data-placement="top" data-original-title="'.$this->t['play_now'].'"></i>';
            return '<a href="javascript:joinMatch(`'.$row->code.'`)" style="text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'">' . $roomNameHtml . '</a>' . $statusIcon;
        }

        $statusIcon = '<i class="ml-2 far fa-sign-in text-warning" data-toggle="tooltip" data-placement="top" data-original-title="'.$this->t['login'].'"></i>';
        return '<a style="text-decoration: none !important;" href="javascript:void(0)" data-fen="'.$row->fen.'" data-code="'.$row->code.'">' . $roomNameHtml . '</a>' . $statusIcon;
    }

    public function formatTurn($row): string
    {
        if (str_contains($row->fen, ' r ')) {
            return '<span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold); box-shadow: 0 0 8px rgba(138, 21, 21, 0.6);"><i class="fas fa-chess-knight"></i> '.$this->t['red'].'</span>';
        }
        if (str_contains($row->fen, ' b ')) {
            return '<span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold-light); border: 1px solid rgba(212, 175, 55, 0.3); box-shadow: 0 0 8px rgba(0, 0, 0, 0.8);"><i class="fas fa-chess-knight"></i> '.$this->t['black'].'</span>';
        }
        return '';
    }

    public function formatResult($row): string
    {
        if (isset($row->result)) {
            switch ($row->result) {
                case '-1': return '<span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$this->t['guest_won'].'</span>';
                case '0': return '<span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> '.$this->t['draw'].'</span>';
                case '1': return '<span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$this->t['host_won'].'</span>';
            }
        } else if ($row->fen == env('INITIAL_FEN', RoomController::INITIAL_FEN)) {
            return '<span class="badge badge-status" style="background: rgba(255,255,255,0.05); color: #aaa; border: 1px dashed rgba(212, 175, 55, 0.3);"><i class="fas fa-hourglass-start"></i> '.$this->t['not_started'].'</span>';
        }
        return '<span class="badge badge-status badge-online"><i class="fas fa-circle"></i> '.$this->t['ongoing'].'</span>';
    }

    public function formatAction($row): string
    {
        $urlRed   = localized_url('room.red', ['code' => $row->code], $this->locale);
        $urlBlack = localized_url('room.black', ['code' => $row->code], $this->locale);
        $urlWatch = localized_url('room.watch', ['code' => $row->code], $this->locale);
        $urlHost  = localized_url('room.host', ['code' => $row->code], $this->locale);
        $urlLogin = localized_url('login', [], $this->locale);

        $actionBtn = '';
        if (!isset($row->host_id)) {
            if ($row->fen == env('INITIAL_FEN', RoomController::INITIAL_FEN)) {
                $btnHref = empty($row->pass) ? $urlRed : $urlHost;
                $actionBtn = '<a class="btn btn-danger pulse-red text-light mr-1 showPromotion" style="min-width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$btnHref.'"><i class="far fa-mouse"></i> '.$this->t['play'].'</a>';

                if (empty($row->pass)) {
                    $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$this->t['public'].'"><i class="far fa-globe"></i> '.$this->t['watch'].'</a>';
                } else {
                    $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$this->t['private'].'"><i class="far fa-lock"></i> '.$this->t['watch'].'</a>';
                }
            } else {
                if (isset($row->result) && (str_contains($row->fen, ' b ') || str_contains($row->fen, ' r '))) {
                    $actionBtn = '<a class="btn btn-dark text-light mr-1" style="min-width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> '.$this->t['finished_btn'].'</a>';
                } else {
                    if (str_contains($row->fen, ' b ')) {
                        $actionBtn = '<a class="btn btn-dark text-light mr-1 showPromotion pulse-dark" style="min-width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlBlack.'"><i class="far fa-mouse"></i> '.$this->t['play'].'</a>';
                    } else if (str_contains($row->fen, ' r ')) {
                        $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion pulse-red" style="min-width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlRed.'"><i class="far fa-mouse"></i> '.$this->t['play'].'</a>';
                    }
                }
                if (empty($row->pass)) {
                    $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$this->t['public'].'"><i class="far fa-globe"></i> '.$this->t['watch'].'</a>';
                } else {
                    $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.$urlWatch.'" data-toggle="tooltip" data-placement="top" data-original-title="'.$this->t['private'].'"><i class="far fa-lock"></i> '.$this->t['watch'].'</a>';
                }
            }
        } else {
            if (auth()->check()) {
                if (isset($row->result)) {
                    $actionBtn = '<a class="btn btn-dark text-light showPromotion" style="min-width: 200px;" href="'.$urlWatch.'"><i class="far fa-archive"></i> '.$this->t['finished_auth'].'</a>';
                } else {
                    $actionBtn = '<a class="btn btn-danger text-light pulse-red" style="min-width: 200px;" href="javascript:joinMatch(`'.$row->code.'`)"><i class="far fa-mouse"></i> '.$this->t['play_now'].'</a>';
                }
            } else {
                if (str_contains($row->fen, ' r ')) {
                    $actionBtn = '<a class="btn btn-danger text-light showPromotion pulse-red" style="min-width: 200px;" href="'.$urlLogin.'"><i class="far fa-sign-in"></i> '.$this->t['login'].'</a>';
                } else if (str_contains($row->fen, ' b ')) {
                    $actionBtn = '<a class="btn btn-dark text-light showPromotion pulse-dark" style="min-width: 200px;" href="'.$urlLogin.'"><i class="far fa-sign-in"></i> '.$this->t['login'].'</a>';
                } else {
                    $actionBtn = '<a class="btn btn-secondary text-light showPromotion" style="min-width: 200px;" href="'.$urlLogin.'"><i class="far fa-sign-in"></i> '.$this->t['login'].'</a>';
                }
            }
        }

        $actionBtn .= '<a class="ml-1 btn previewBtn"><i class="far fa-eye"></i> '.$this->t['preview'].'</a>';
        return $actionBtn;
    }

    public function formatTime($row): string
    {
        return date('Y-m-d | H:i:s', strtotime($row->modified_at));
    }
}
