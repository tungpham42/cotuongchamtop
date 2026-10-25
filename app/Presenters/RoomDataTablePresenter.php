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
            'es' => ['public' => 'Pública', 'private' => 'Privada', 'red' => 'Rojas', 'black' => 'Negras', 'guest_won' => 'Ganó el invitado', 'draw' => 'Tablas', 'host_won' => 'Ganó el anfitrión', 'not_started' => 'Sin empezar', 'ongoing' => 'En curso', 'play' => 'Jugar', 'watch' => 'Observar', 'finished_btn' => 'Terminada', 'finished_auth' => 'Terminada', 'play_now' => 'Jugar ya', 'login' => 'Iniciar sesión', 'preview' => 'Vista previa'],
            'fr' => ['public' => 'Publique', 'private' => 'Privée', 'red' => 'Rouge', 'black' => 'Noir', 'guest_won' => 'L\'invité a gagné', 'draw' => 'Match nul', 'host_won' => 'L\'hôte a gagné', 'not_started' => 'Pas commencée', 'ongoing' => 'En cours', 'play' => 'Jouer', 'watch' => 'Regarder', 'finished_btn' => 'Terminée', 'finished_auth' => 'Terminée', 'play_now' => 'Jouer maintenant', 'login' => 'Connexion', 'preview' => 'Aperçu'],
            'de' => ['public' => 'Öffentlich', 'private' => 'Privat', 'red' => 'Rot', 'black' => 'Schwarz', 'guest_won' => 'Gast hat gewonnen', 'draw' => 'Remis', 'host_won' => 'Gastgeber hat gewonnen', 'not_started' => 'Nicht gestartet', 'ongoing' => 'Läuft gerade', 'play' => 'Spielen', 'watch' => 'Zuschauen', 'finished_btn' => 'Beendet', 'finished_auth' => 'Beendet', 'play_now' => 'Jetzt spielen', 'login' => 'Anmelden', 'preview' => 'Vorschau'],
            'ru' => ['public' => 'Публичная', 'private' => 'Приватная', 'red' => 'Красные', 'black' => 'Чёрные', 'guest_won' => 'Победил гость', 'draw' => 'Ничья', 'host_won' => 'Победил хозяин', 'not_started' => 'Не начата', 'ongoing' => 'Идёт игра', 'play' => 'Играть', 'watch' => 'Наблюдать', 'finished_btn' => 'Завершена', 'finished_auth' => 'Завершена', 'play_now' => 'Играть сейчас', 'login' => 'Войти', 'preview' => 'Предпросмотр'],
            'ja' => ['public' => '公開', 'private' => '非公開', 'red' => '赤', 'black' => '黒', 'guest_won' => 'ゲストの勝ち', 'draw' => '引き分け', 'host_won' => 'ホストの勝ち', 'not_started' => '未開始', 'ongoing' => '対局中', 'play' => '参加', 'watch' => '観戦', 'finished_btn' => '終了', 'finished_auth' => '終了', 'play_now' => '今すぐ参加', 'login' => 'ログイン', 'preview' => 'プレビュー'],
            'ko' => ['public' => '공개', 'private' => '비공개', 'red' => '홍', 'black' => '흑', 'guest_won' => '손님 승리', 'draw' => '무승부', 'host_won' => '방장 승리', 'not_started' => '시작 전', 'ongoing' => '진행 중', 'play' => '참여', 'watch' => '관전', 'finished_btn' => '종료', 'finished_auth' => '종료', 'play_now' => '지금 참여', 'login' => '로그인', 'preview' => '미리보기'],
            'zh' => ['public' => '公开', 'private' => '私密', 'red' => '红方', 'black' => '黑方', 'guest_won' => '客方获胜', 'draw' => '和棋', 'host_won' => '房主获胜', 'not_started' => '未开始', 'ongoing' => '进行中', 'play' => '参加', 'watch' => '观战', 'finished_btn' => '已结束', 'finished_auth' => '已结束', 'play_now' => '立即参加', 'login' => '登录', 'preview' => '预览'],
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
        // 1. Determine the effective result, prioritizing timeouts
        $effectiveResult = $row->result ?? null;

        if (isset($row->red_time) && $row->red_time <= 0) {
            $effectiveResult = '-1'; // Red ran out of time, Guest (Black) wins
        } elseif (isset($row->black_time) && $row->black_time <= 0) {
            $effectiveResult = '1'; // Black ran out of time, Host (Red) wins
        }

        // 2. Render the appropriate badge based on the effective result
        if (isset($effectiveResult)) {
            switch ((string) $effectiveResult) {
                case '-1':
                    return '<span class="badge badge-status" style="background: linear-gradient(145deg, #252a36, #121418); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$this->t['guest_won'].'</span>';
                case '0':
                    return '<span class="badge badge-status badge-offline"><i class="fas fa-handshake"></i> '.$this->t['draw'].'</span>';
                case '1':
                    return '<span class="badge badge-status" style="background: linear-gradient(to bottom, #8a1515, #5c0a0a); color: var(--royal-gold); border: 1px solid var(--royal-gold);"><i class="fas fa-crown"></i> '.$this->t['host_won'].'</span>';
            }
        }

        // 3. Fallback logic for games without a definitive result
        if ($row->fen == RoomController::INITIAL_FEN) {
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
            if ($row->fen == RoomController::INITIAL_FEN) {
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
