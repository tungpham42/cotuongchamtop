<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\GameController;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DataTables;
use App\Jobs\QuickMatchJob;
use App\Jobs\AnonymousQuickMatchJob;
use Illuminate\Support\Str;
use Atrox\Haikunator;

class RoomController extends Controller
{
    public function getRoomsVi(Request $request)
    {
        if ($request->ajax()) {
            // $data = Room::orderBy('modified_at', 'desc')->get();
            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);
            return Datatables::of($rooms)
                ->addColumn('code', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN') || str_contains($row->fen, ' r ')) {
                            $roomCode = '<a class="text-danger disabled" style="cursor: default !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-danger" data-toggle="tooltip" data-placement="top" data-original-title="Công khai"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-danger" data-toggle="tooltip" data-placement="top" data-original-title="Riêng tư"></i>';
                            }
                        } else {
                            $roomCode = '<a style="color: #222222 !important; cursor: default !important; text-decoration: none !important;" class="disabled" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-dark" data-toggle="tooltip" data-placement="top" data-original-title="Công khai"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-dark" data-toggle="tooltip" data-placement="top" data-original-title="Riêng tư"></i>';
                            }
                        }
                    } else {
                        if (auth()->check()) {
                            if (isset($row->result)) {
                                $roomCode = '<a class="text-dark showPromotion" href="javascript:void(0)" style="color: #222!important; cursor: default !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a><i class="ml-3 far fa-archive text-dark" data-toggle="tooltip" data-placement="top" data-original-title="Đã đấu xong"></i>';
                            } else {
                                $roomCode = '<a class="text-warning" href="javascript:joinMatch(`'.$row->code.'`)" data-fen="'.$row->fen.'" data-code="'.$row->code.'">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a><i class="ml-3 far fa-mouse text-warning" data-toggle="tooltip" data-placement="top" data-original-title="Chơi nào"></i>';
                            }
                        } else {
                            if (str_contains($row->fen, ' r ')) {
                                $roomCode = '<a style="cursor: default !important; text-decoration: none !important;" class="text-danger" href="javascript:void(0)" data-fen="'.$row->fen.'" data-code="'.$row->code.'">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a><i class="ml-3 far fa-sign-in text-danger" data-toggle="tooltip" data-placement="top" data-original-title="Đăng nhập"></i>';
                            } else if (str_contains($row->fen, ' b ')) {
                                $roomCode = '<a style="cursor: default !important; text-decoration: none !important;" class="text-dark" href="javascript:void(0)" data-fen="'.$row->fen.'" data-code="'.$row->code.'">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a><i class="ml-3 far fa-sign-in text-dark" data-toggle="tooltip" data-placement="top" data-original-title="Đăng nhập"></i>';
                            }
                        }
                    }
                    return $roomCode;
                })
                ->addColumn('result', function($row){
                    if (isset($row->result)) {
                        switch ($row->result) {
                            case '-1':
                                $roomResult = '<span class="text-dark">Đen thắng</span>';
                                break;
                            case '0':
                                $roomResult = '<span class="text-warning">Hòa</span>';
                                break;
                            case '1':
                                $roomResult = '<span class="text-danger">Đỏ thắng</span>';
                                break;
                        }
                    } else if ($row->fen == env('INITIAL_FEN')) {
                        $roomResult = '<span class="text-secondary">Chưa bắt đầu</span>';
                    } else {
                        $roomResult = '<span class="text-warning">Đang đấu</span>';
                    }
                    return $roomResult;
                })
                ->addColumn('turn', function($row){
                    if (str_contains($row->fen, ' r ')) {
                        $playerTurn = '<span class="text-danger">Đỏ</span>';
                    } else if (str_contains($row->fen, ' b ')) {
                        $playerTurn = '<span class="text-dark">Đen</span>';
                    }
                    return $playerTurn;
                })
                ->addColumn('action', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN')) {
                            if ($row->pass == '') {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'/do"><i class="far fa-mouse"></i> Chơi</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'"><i class="far fa-mouse"></i> Chơi</a>';
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'/theo-doi" data-toggle="tooltip" data-placement="top" data-original-title="Công khai"><i class="far fa-globe"></i> Theo dõi</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'/theo-doi" data-toggle="tooltip" data-placement="top" data-original-title="Riêng tư"><i class="far fa-lock"></i> Theo dõi</a>';
                            }
                        } else {
                            if (isset($row->result)) {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> Đã xong</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> Đã xong</a>';
                                }
                            } else {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'/den"><i class="far fa-mouse"></i> Chơi</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'/do"><i class="far fa-mouse"></i> Chơi</a>';
                                }
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'/theo-doi" data-toggle="tooltip" data-placement="top" data-original-title="Công khai"><i class="far fa-globe"></i> Theo dõi</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/phong/'.$row->code.'/theo-doi" data-toggle="tooltip" data-placement="top" data-original-title="Riêng tư"><i class="far fa-lock"></i> Theo dõi</a>';
                            }
                        }
                    } else {
                        if (auth()->check()) {
                            if (isset($row->result)) {
                                $actionBtn = '<a class="btn btn-dark text-light showPromotion" style="width: 200px;" href="'.URL::to('/').'/phong/'.$row->code.'/theo-doi"><i class="far fa-archive"></i> Đã đấu xong</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger text-light pulse-red" style="width: 200px;" href="javascript:joinMatch(`'.$row->code.'`)"><i class="far fa-mouse"></i> Chơi nào</a>';
                            }
                        } else {
                            if (str_contains($row->fen, ' r ')) {
                                $actionBtn = '<a class="btn btn-danger text-light showPromotion pulse-red" style="width: 200px;" href="'.URL::to('/dang-nhap/').'"><i class="far fa-sign-in"></i> Đăng nhập</a>';
                            } else if (str_contains($row->fen, ' b ')) {
                                $actionBtn = '<a class="btn btn-dark text-light showPromotion pulse-dark" style="width: 200px;" href="'.URL::to('/dang-nhap/').'"><i class="far fa-sign-in"></i> Đăng nhập</a>';
                            }
                        }
                    }
                    $actionBtn .= '<a class="ml-1 btn btn-warning previewBtn"><i class="far fa-eye""></i> Xem trước</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->modified_at));
                })
                ->escapeColumns([])
                ->orderColumn('code', 'code $1')
                ->orderColumn('result', 'result $1')
                ->orderColumn('time', 'modified_at $1')
                ->filterColumn('code', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('code', 'like', '%' . $keyword . '%')
                              ->orWhere('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "modified_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['code', 'turn', 'result', 'action', 'time'])
                ->make(true);
        }
    }

    public function getRoomsEn(Request $request)
    {
        if ($request->ajax()) {
            // $data = Room::orderBy('modified_at', 'desc')->get();
            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);
            return Datatables::of($rooms)
                ->addColumn('code', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN') || str_contains($row->fen, ' r ')) {
                            $roomCode = '<a class="text-danger disabled" style="cursor: default !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-danger" data-toggle="tooltip" data-placement="top" data-original-title="Public"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-danger" data-toggle="tooltip" data-placement="top" data-original-title="Private"></i>';
                            }
                        } else {
                            $roomCode = '<a style="color: #222222 !important; cursor: default !important; text-decoration: none !important;" class="disabled" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-dark" data-toggle="tooltip" data-placement="top" data-original-title="Public"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-dark" data-toggle="tooltip" data-placement="top" data-original-title="Private"></i>';
                            }
                        }
                    } else {
                        $roomCode = '<span class="text-dark showPromotion">This room is only available in Vietnamese</span>';
                    }
                    return $roomCode;
                })
                ->addColumn('turn', function($row){
                    if (str_contains($row->fen, ' r ')) {
                        $playerTurn = '<span class="text-danger">Red</span>';
                    } else if (str_contains($row->fen, ' b ')) {
                        $playerTurn = '<span class="text-dark">Black</span>';
                    }
                    return $playerTurn;
                })
                ->addColumn('result', function($row){
                    if (isset($row->result)) {
                        switch ($row->result) {
                            case '-1':
                                $roomResult = '<span class="text-dark">Guest won</span>';
                                break;
                            case '0':
                                $roomResult = '<span class="text-warning">Draw</span>';
                                break;
                            case '1':
                                $roomResult = '<span class="text-danger">Host won</span>';
                                break;
                        }
                    } else if ($row->fen == env('INITIAL_FEN')) {
                        $roomResult = '<span class="text-secondary">Not started</span>';
                    } else {
                        $roomResult = '<span class="text-warning">Ongoing</span>';
                    }
                    return $roomResult;
                })
                ->addColumn('action', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN')) {
                            if ($row->pass == '') {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'/red"><i class="far fa-mouse"></i> Play</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'"><i class="far fa-mouse"></i> Play</a>';
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'/watch" data-toggle="tooltip" data-placement="top" data-original-title="Public"><i class="far fa-globe"></i> Watch</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'/watch" data-toggle="tooltip" data-placement="top" data-original-title="Private"><i class="far fa-lock"></i> Watch</a>';
                            }
                        } else {
                            if (isset($row->result)) {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> Finished</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> Finished</a>';
                                }
                            } else {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'/black"><i class="far fa-mouse"></i> Play</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'/red"><i class="far fa-mouse"></i> Play</a>';
                                }
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'/watch" data-toggle="tooltip" data-placement="top" data-original-title="Public"><i class="far fa-globe"></i> Watch</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/room/'.$row->code.'/watch" data-toggle="tooltip" data-placement="top" data-original-title="Private"><i class="far fa-lock"></i> Watch</a>';
                            }
                        }
                    } else {
                        $actionBtn = '<a class="btn btn-dark text-light showPromotion" style="width: 182px;" href="'.URL::to('/sanh-cho/').'"><i class="far fa-language"></i> Switch language</a>';
                    }
                    $actionBtn .= '<a class="ml-1 btn btn-warning previewBtn"><i class="far fa-eye""></i> Preview</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->modified_at));
                })
                ->escapeColumns([])
                ->orderColumn('code', 'code $1')
                ->orderColumn('result', 'result $1')
                ->orderColumn('time', 'modified_at $1')
                ->filterColumn('code', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('code', 'like', '%' . $keyword . '%')
                              ->orWhere('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "modified_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['code', 'turn', 'result', 'action', 'time'])
                ->make(true);
        }
    }

    public function getRoomsJa(Request $request)
    {
        if ($request->ajax()) {
            // $data = Room::orderBy('modified_at', 'desc')->get();
            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);
            return Datatables::of($rooms)
                ->addColumn('code', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN') || str_contains($row->fen, ' r ')) {
                            $roomCode = '<a class="text-danger disabled" style="cursor: default !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-danger" data-toggle="tooltip" data-placement="top" data-original-title="公衆"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-danger" data-toggle="tooltip" data-placement="top" data-original-title="民間"></i>';
                            }
                        } else {
                            $roomCode = '<a style="color: #222222 !important; cursor: default !important; text-decoration: none !important;" class="disabled" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-dark" data-toggle="tooltip" data-placement="top" data-original-title="公衆"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-dark" data-toggle="tooltip" data-placement="top" data-original-title="民間"></i>';
                            }
                        }
                    } else {
                        $roomCode = '<span class="text-dark showPromotion">この部屋はベトナム語でしか利用できません</span>';
                    }
                    return $roomCode;
                })
                ->addColumn('turn', function($row){
                    if (str_contains($row->fen, ' r ')) {
                        $playerTurn = '<span class="text-danger">赤</span>';
                    } else if (str_contains($row->fen, ' b ')) {
                        $playerTurn = '<span class="text-dark">黒</span>';
                    }
                    return $playerTurn;
                })
                ->addColumn('result', function($row){
                    if (isset($row->result)) {
                        switch ($row->result) {
                            case '-1':
                                $roomResult = '<span class="text-dark">ゲストが勝ちました</span>';
                                break;
                            case '0':
                                $roomResult = '<span class="text-warning">ドローです</span>';
                                break;
                            case '1':
                                $roomResult = '<span class="text-danger">ホストが勝ちました</span>';
                                break;
                        }
                    } else if ($row->fen == env('INITIAL_FEN')) {
                        $roomResult = '<span class="text-secondary">開始されていない</span>';
                    } else {
                        $roomResult = '<span class="text-warning">現在進行中</span>';
                    }
                    return $roomResult;
                })
                ->addColumn('action', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN')) {
                            if ($row->pass == '') {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'/kuro"><i class="far fa-mouse"></i> 加入</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'"><i class="far fa-mouse"></i> 加入</a>';
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'/miru" data-toggle="tooltip" data-placement="top" data-original-title="公衆"><i class="far fa-globe"></i> 見る</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'/miru" data-toggle="tooltip" data-placement="top" data-original-title="民間"><i class="far fa-lock"></i> 見る</a>';
                            }
                        } else {
                            if (isset($row->result)) {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> 終わり</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> 終わり</a>';
                                }
                            } else {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'/aka"><i class="far fa-mouse"></i> 加入</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'/kuro"><i class="far fa-mouse"></i> 加入</a>';
                                }
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'/miru" data-toggle="tooltip" data-placement="top" data-original-title="公衆"><i class="far fa-globe"></i> 見る</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/rumu/'.$row->code.'/miru" data-toggle="tooltip" data-placement="top" data-original-title="民間"><i class="far fa-lock"></i> 見る</a>';
                            }
                        }
                    } else {
                        $actionBtn = '<a class="btn btn-dark text-light showPromotion" style="width: 170px;" href="'.URL::to('/sanh-cho/').'"><i class="far fa-language"></i> 言語を切り替える</a>';
                    }
                    $actionBtn .= '<a class="ml-1 btn btn-warning previewBtn"><i class="far fa-eye""></i> プレビュー</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->modified_at));
                })
                ->escapeColumns([])
                ->orderColumn('code', 'code $1')
                ->orderColumn('result', 'result $1')
                ->orderColumn('time', 'modified_at $1')
                ->filterColumn('code', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('code', 'like', '%' . $keyword . '%')
                              ->orWhere('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "modified_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['code', 'turn', 'result', 'action', 'time'])
                ->make(true);
        }
    }

    public function getRoomsKo(Request $request)
    {
        if ($request->ajax()) {
            // $data = Room::orderBy('modified_at', 'desc')->get();
            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);
            return Datatables::of($rooms)
                ->addColumn('code', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN') || str_contains($row->fen, ' r ')) {
                            $roomCode = '<a class="text-danger disabled" style="cursor: default !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-danger" data-toggle="tooltip" data-placement="top" data-original-title="공공의"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-danger" data-toggle="tooltip" data-placement="top" data-original-title="사적인"></i>';
                            }
                        } else {
                            $roomCode = '<a style="color: #222222 !important; cursor: default !important; text-decoration: none !important;" class="disabled" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-dark" data-toggle="tooltip" data-placement="top" data-original-title="공공의"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-dark" data-toggle="tooltip" data-placement="top" data-original-title="사적인"></i>';
                            }
                        }
                    } else {
                        $roomCode = '<span class="text-dark showPromotion">이 방은 베트남어로만 이용 가능합니다</span>';
                    }
                    return $roomCode;
                })
                ->addColumn('turn', function($row){
                    if (str_contains($row->fen, ' r ')) {
                        $playerTurn = '<span class="text-danger">홍</span>';
                    } else if (str_contains($row->fen, ' b ')) {
                        $playerTurn = '<span class="text-dark">검</span>';
                    }
                    return $playerTurn;
                })
                ->addColumn('result', function($row){
                    if (isset($row->result)) {
                        switch ($row->result) {
                            case '-1':
                                $roomResult = '<span class="text-danger">손님이 이겼어요</span>';
                                break;
                            case '0':
                                $roomResult = '<span class="text-warning">동점입니다</span>';
                                break;
                            case '1':
                                $roomResult = '<span class="text-dark">주최자가 이겼어요</span>';
                                break;
                        }
                    } else if ($row->fen == env('INITIAL_FEN')) {
                        $roomResult = '<span class="text-secondary">아직 시작되지 않음</span>';
                    } else {
                        $roomResult = '<span class="text-warning">진행 중인</span>';
                    }
                    return $roomResult;
                })
                ->addColumn('action', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN')) {
                            if ($row->pass == '') {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'/geom-eunsaeg"><i class="far fa-mouse"></i> 참여</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'"><i class="far fa-mouse"></i> 참여</a>';
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'/boda" data-toggle="tooltip" data-placement="top" data-original-title="공공의"><i class="far fa-globe"></i> 보다</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'/boda" data-toggle="tooltip" data-placement="top" data-original-title="사적인"><i class="far fa-lock"></i> 보다</a>';
                            }
                        } else {
                            if (isset($row->result)) {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> 끝났다</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1" style="width: 100px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> 끝났다</a>';
                                }
                            } else {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'/ppalgan"><i class="far fa-mouse"></i> 참여</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 100px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'/geom-eunsaeg"><i class="far fa-mouse"></i> 참여</a>';
                                }
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'/boda" data-toggle="tooltip" data-placement="top" data-original-title="공공의"><i class="far fa-globe"></i> 보다</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/bang/'.$row->code.'/boda" data-toggle="tooltip" data-placement="top" data-original-title="사적인"><i class="far fa-lock"></i> 보다</a>';
                            }
                        }
                    } else {
                        $actionBtn = '<a class="btn btn-dark text-light showPromotion" style="width: 168px;" href="'.URL::to('/sanh-cho/').'"><i class="far fa-language"></i> 언어 변경</a>';
                    }
                    $actionBtn .= '<a class="ml-1 btn btn-warning previewBtn"><i class="far fa-eye""></i> 미리보기</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->modified_at));
                })
                ->escapeColumns([])
                ->orderColumn('code', 'code $1')
                ->orderColumn('result', 'result $1')
                ->orderColumn('time', 'modified_at $1')
                ->filterColumn('code', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('code', 'like', '%' . $keyword . '%')
                              ->orWhere('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "modified_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['code', 'turn', 'result', 'action', 'time'])
                ->make(true);
        }
    }

    public function getRoomsZh(Request $request)
    {
        if ($request->ajax()) {
            // $data = Room::orderBy('modified_at', 'desc')->get();
            $rooms = Room::select(['fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at']);
            return Datatables::of($rooms)
                ->addColumn('code', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN') || str_contains($row->fen, ' r ')) {
                            $roomCode = '<a class="text-danger disabled" style="cursor: default !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-danger" data-toggle="tooltip" data-placement="top" data-original-title="平民的"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-danger" data-toggle="tooltip" data-placement="top" data-original-title="私有的"></i>';
                            }
                        } else {
                            $roomCode = '<a style="color: #222222 !important; cursor: default !important; text-decoration: none !important;" class="disabled" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0)">'.((isset($row->name) && $row->name != '') ? $row->name: $row->code).'</a>';
                            if ($row->pass == '') {
                                $roomCode .= '<i class="ml-3 far fa-globe text-dark" data-toggle="tooltip" data-placement="top" data-original-title="平民的"></i>';
                            } else {
                                $roomCode .= '<i class="ml-3 far fa-lock text-dark" data-toggle="tooltip" data-placement="top" data-original-title="私有的"></i>';
                            }
                        }
                    } else {
                        $roomCode = '<span class="text-dark showPromotion">这个房间只提供越南语服务</span>';
                    }
                    return $roomCode;
                })
                ->addColumn('turn', function($row){
                    if (str_contains($row->fen, ' r ')) {
                        $playerTurn = '<span class="text-danger">红</span>';
                    } else if (str_contains($row->fen, ' b ')) {
                        $playerTurn = '<span class="text-dark">黑</span>';
                    }
                    return $playerTurn;
                })
                ->addColumn('result', function($row){
                    if (isset($row->result)) {
                        switch ($row->result) {
                            case '-1':
                                $roomResult = '<span class="text-dark">客人赢了</span>';
                                break;
                            case '0':
                                $roomResult = '<span class="text-warning">平局</span>';
                                break;
                            case '1':
                                $roomResult = '<span class="text-danger">主办方赢了</span>';
                                break;
                        }
                    } else if ($row->fen == env('INITIAL_FEN')) {
                        $roomResult = '<span class="text-secondary">未开始</span>';
                    } else {
                        $roomResult = '<span class="text-warning">进行中的</span>';
                    }
                    return $roomResult;
                })
                ->addColumn('action', function($row){
                    if (!isset($row->host_id)) {
                        if ($row->fen == env('INITIAL_FEN')) {
                            if ($row->pass == '') {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 70px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'/heise"><i class="far fa-mouse"></i> 参加</a>';
                            } else {
                                $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 70px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'"><i class="far fa-mouse"></i> 参加</a>';
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" style="width: 60px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'/kan" data-toggle="tooltip" data-placement="top" data-original-title="平民的"><i class="far fa-globe"></i> 看</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" style="width: 60px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'/kan" data-toggle="tooltip" data-placement="top" data-original-title="私有的"><i class="far fa-lock"></i> 看</a>';
                            }
                        } else {
                            if (isset($row->result)) {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 70px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> 结束</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1" style="width: 70px; cursor: not-allowed !important;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="javascript:void(0);"><i class="far fa-ban"></i> 结束</a>';
                                }
                            } else {
                                if (str_contains($row->fen, ' b ')) {
                                    $actionBtn = '<a class="btn btn-dark text-light mr-1 showPromotion" style="width: 70px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'/hongse"><i class="far fa-mouse"></i> 参加</a>';
                                } else if (str_contains($row->fen, ' r ')) {
                                    $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 70px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'/heise"><i class="far fa-mouse"></i> 参加</a>';
                                }
                            }
                            if ($row->pass == '') {
                                $actionBtn .= '<a class="btn btn-light text-warning watch-btn border-warning showPromotion" style="width: 60px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'/kan" data-toggle="tooltip" data-placement="top" data-original-title="平民的"><i class="far fa-globe"></i> 看</a>';
                            } else {
                                $actionBtn .= '<a class="btn btn-warning text-light watch-btn border-warning showPromotion" style="width: 60px;" data-fen="'.$row->fen.'" data-code="'.$row->code.'" href="'.URL::to('/').'/fangjian/'.$row->code.'/kan" data-toggle="tooltip" data-placement="top" data-original-title="私有的"><i class="far fa-lock"></i> 看</a>';
                            }
                        }
                    } else {
                        $actionBtn = '<a class="btn btn-dark text-light showPromotion" style="width: 134px;" href="'.URL::to('/sanh-cho/').'"><i class="far fa-language"></i> 切换语言</a>';
                    }
                    $actionBtn .= '<a class="ml-1 btn btn-warning previewBtn"><i class="far fa-eye""></i> 预览</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->modified_at));
                })
                ->escapeColumns([])
                ->orderColumn('code', 'code $1')
                ->orderColumn('result', 'result $1')
                ->orderColumn('time', 'modified_at $1')
                ->filterColumn('code', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('code', 'like', '%' . $keyword . '%')
                              ->orWhere('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "modified_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['code', 'turn', 'result', 'action', 'time'])
                ->make(true);
        }
    }

    public static function quickMatch()
    {
        dispatch(new QuickMatchJob());
        return response()->json([
            'code' => 1,
            'message' => 'Quick match queued successfully.'
        ]);
    }

    public static function getLatestRoom(Request $request)
    {
        $offsetNumber = $request->input('offset');
        $latestRoom = Room::where('pass', NULL)->where('host_id', NULL)->where('result', NULL)->orderBy('modified_at', 'desc')->offset($offsetNumber)->first();
        if ($latestRoom != null) {
            if (str_contains($latestRoom->fen, ' r ')) {
                return response()->json([
                    'color' => 'red',
                    'room' => $latestRoom
                ]);
            } else if (str_contains($latestRoom->fen, ' b ')) {
                return response()->json([
                    'color' => 'black',
                    'room' => $latestRoom
                ]);
            }
        } else {
            return response()->json([
                'room' => null
            ]);
        }
    }
    public static function getRandomRoom() {
        return Room::where('pass', null)
            ->where('host_id', null)
            ->where('result', '=', null)
            ->where('fen', '!=', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1')
            ->where('fen', 'LIKE', '% b %')
            ->inRandomOrder()
            ->first();
    }
    public static function getNewRoom()
    {
        $firstRoom = Room::where('fen', env('INITIAL_FEN'))->where('pass', NULL)->where('host_id', NULL)->where('result', NULL)->orderBy('modified_at', 'desc')->first();
        return response()->json([
            'room' => $firstRoom
        ]);
    }
      
    public static function getRoomName($code)
    {
        $name = Room::where('code', $code)->value('name');
    
        return $name;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $code = $request->input('ma-phong');
        $name = $request->input('ten-phong');
        $fen = $request->input('FEN');
        $host_id = $request->input('host_id');
        $pass = $request->input('pass');
        Room::updateOrInsert(
            ['code' => $code],
            ['fen' => $fen, 'host_id' => $host_id, 'name' => $name, 'pass' => $pass, 'modified_at' => date('Y-m-d H:i:s')]
        );
    }

    public function compete(Request $request)
    {
        $code = $request->input('ma-phong');
        $name = $request->input('ten-phong');
        $fen = $request->input('FEN');
        $host_id = $request->input('host_id');
        $guest_id = $request->input('guest_id');
        $pass = $request->input('pass');
        Room::updateOrInsert(
            ['code' => $code],
            ['fen' => $fen, 'host_id' => $host_id, 'guest_id' => $guest_id, 'name' => $name, 'pass' => $pass, 'modified_at' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $code = $request->input('ma-phong');
        $fen = $request->input('FEN');
        Room::updateOrInsert(
            ['code' => $code],
            ['fen' => $fen, 'modified_at' => date('Y-m-d H:i:s')]
        );
    }

    public function join(Request $request)
    {
        $code = $request->input('ma-phong');
        $guest_id = $request->input('guest_id');
        Room::updateOrInsert(
            ['code' => $code],
            ['guest_id' => $guest_id, 'modified_at' => date('Y-m-d H:i:s')]
        );
    }

    public static function updateElo(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');

        $room = Room::select('host_id', 'guest_id')
                ->where('code', $code)
                ->first();;
    
        $host = User::find($room->host_id);
        $guest = User::find($room->guest_id);
    
        $eloRatings = GameController::getEloRatings($host->elo, $guest->elo, $result);
    
        [$host->elo, $guest->elo] = $eloRatings;
    
        $host->save();
        $guest->save();
    }

    public function updateResult(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');
        $auth_id = $request->input('id');

        // Update or insert the room result
        Room::updateOrInsert(
            ['code' => $code],
            ['result' => $result, 'modified_at' => now()]
        );

        // Retrieve host and guest IDs for the room
        $roomData = Room::select('host_id', 'guest_id')
            ->where('code', $code)
            ->first();

        $host_id = $roomData->host_id ?? null;
        $guest_id = $roomData->guest_id ?? null;

        // Define success messages
        $successMessages = [
            'host' => [
                '-1' => 'Chủ phòng thua! Cố lên nhé!',
                '0' => 'Hòa.',
                '1' => 'Chủ phòng thắng. Xin chúc mừng!',
            ],
            'guest' => [
                '-1' => 'Khách thắng. Xin chúc mừng!',
                '0' => 'Hòa.',
                '1' => 'Khách thua! Cố lên nhé!',
            ],
        ];

        // Determine the success message based on the user's role (host or guest) and the result
        if ($auth_id == $host_id) {
            $success_message = $successMessages['host'][$result] ?? '';
        } elseif ($auth_id == $guest_id) {
            $success_message = $successMessages['guest'][$result] ?? '';
        } else {
            // Handle the case when the user is neither the host nor the guest
            $success_message = 'You are not authorized to update this room.';
        }

        //self::updateElo($code); // Update the Elo ratings of the host and guest

        return response()->json([
            'success' => $success_message
        ]);
    }

    public function updateSideResult(Request $request)
    {
        $code = $request->input('ma-phong');
        $result = $request->input('result');
        $lang = $request->input('lang');
        $side = $request->input('side');
        Room::updateOrInsert(
            ['code' => $code],
            ['result' => $result, 'modified_at' => date('Y-m-d H:i:s')]
        );

        $success_message = '';

        switch ($lang) {
            case 'vi':
                if ($side == 'red') {
                    switch ($result) {
                        case '-1':
                            $success_message = 'Đỏ thua! Cố lên nhé!';
                            break;
                        case '0':
                            $success_message = 'Hòa.';
                            break;
                        case '1':
                            $success_message = 'Đỏ thắng. Xin chúc mừng!';
                            break;
                    }
                } elseif ($side == 'black') {
                    switch ($result) {
                        case '-1':
                            $success_message = 'Đen thắng. Xin chúc mừng!';
                            break;
                        case '0':
                            $success_message = 'Hòa.';
                            break;
                        case '1':
                            $success_message = 'Đen thua! Cố lên nhé!';
                            break;
                    }
                }
                break;
            case 'en':
                if ($side == 'red') {
                    switch ($result) {
                        case '-1':
                            $success_message = 'Red lost!';
                            break;
                        case '0':
                            $success_message = 'Draw.';
                            break;
                        case '1':
                            $success_message = 'Red won!';
                            break;
                    }
                } elseif ($side == 'black') {
                    switch ($result) {
                        case '-1':
                            $success_message = 'Black won!';
                            break;
                        case '0':
                            $success_message = 'Draw.';
                            break;
                        case '1':
                            $success_message = 'Black lost!';
                            break;
                    }
                }
                break;
            case 'ja':
                if ($side == 'red') {
                    switch ($result) {
                        case '-1':
                            $success_message = 'ホストが切断されました！';
                            break;
                        case '0':
                            $success_message = '描く';
                            break;
                        case '1':
                            $success_message = 'ホストが勝ちました！';
                            break;
                    }
                } elseif ($side == 'black') {
                    switch ($result) {
                        case '-1':
                            $success_message = 'ゲストが勝ちました！';
                            break;
                        case '0':
                            $success_message = '描く';
                            break;
                        case '1':
                            $success_message = 'ゲストが負けました！';
                            break;
                    }
                }
                break;
            case 'ko':
                if ($side == 'red') {
                    switch ($result) {
                        case '-1':
                            $success_message = '호스트가 패배했습니다!';
                            break;
                        case '0':
                            $success_message = '그리다';
                            break;
                        case '1':
                            $success_message = '호스트가 이겼습니다!';
                            break;
                    }
                } elseif ($side == 'black') {
                    switch ($result) {
                        case '-1':
                            $success_message = '게스트가 이겼습니다!';
                            break;
                        case '0':
                            $success_message = '그리다';
                            break;
                        case '1':
                            $success_message = '게스트가 졌습니다!';
                            break;
                    }
                }
                break;
            case 'zh':
                if ($side == 'red') {
                    switch ($result) {
                        case '-1':
                            $success_message = '主机失败了！';
                            break;
                        case '0':
                            $success_message = '画';
                            break;
                        case '1':
                            $success_message = '主机获胜了！';
                            break;
                    }
                } elseif ($side == 'black') {
                    switch ($result) {
                        case '-1':
                            $success_message = '客人获胜了！';
                            break;
                        case '0':
                            $success_message = '画';
                            break;
                        case '1':
                            $success_message = '客人失败了！';
                            break;
                    }
                }
                break;
        }
        return response()->json([
            'success' => $success_message
        ]);
    }

    public static function getHostId(Request $request)
    {
        $code = $request->input('ma-phong');
        $hostId = Room::where('code', $code)->value('host_id');
    
        return $hostId;
    }    

    public static function getHostIdRoute($code)
    {
        $hostId = Room::where('code', $code)->value('host_id');
    
        return $hostId;
    }

    public static function getRoomIds(Request $request)
    {
        $code = $request->input('ma-phong');
        $roomData = Room::select('host_id', 'guest_id')
                        ->where('code', '=', $code)
                        ->first();
    
        return $roomData ? $roomData->toArray() : [];
    }    

    public static function getMatchRooms()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getPlayingRooms()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getPlayedRooms()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getPlayerRooms($id)
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->orWhere('host_id', '=', $id)
                    ->orWhere('guest_id', '=', $id)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(10);
        return $data;
    }

    public static function getBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6);
        return $data;
    }

    public static function getFirstPageBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6, ['*'], 'page', 1);
        return $data;
    }

    public static function getPlayedBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6);
        return $data;
    }

    public static function getFirstPagePlayedBoards()
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'name', 'pass', 'modified_at')
                    ->where('host_id', '!=', NULL)
                    ->where('result', '!=', NULL)
                    ->orderBy('modified_at', 'desc')
                    ->paginate(6, ['*'], 'page', 1);
        return $data;
    }

    public static function hasRoomcode(Request $request)
    {
        $code = $request->input('ma-phong');

        $roomcodeCount = Room::where('code', '=', $code)->count();

        if ($roomcodeCount > 0) {
            return 'yes';
        } else if ($roomcodeCount == 0) {
            return 'no';
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show(Room $room, $code)
    {
        if (auth()->check()) {
            // Update user's online status
            auth()->user()->update(['last_seen_at' => now()]);
        }
    
        $fen = Room::where('code', $code)->value('fen');
    
        return $fen;
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function getPass(Room $room, $code)
    {
        $pass = Room::where('code', $code)->value('pass');
    
        return $pass;
    }    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePass(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => 'Password cannot be empty', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => 'Changed password successfully!', 'code' => 1));
            exit();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePassJa(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => 'パスワードを空にすることはできません', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => 'パスワードが正常に変更されました。', 'code' => 1));
            exit();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePassKo(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => '암호는 비워 둘 수 없습니다.', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => '암호가 성공적으로 변경되었습니다!', 'code' => 1));
            exit();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function changePassZh(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => '密码不能为空', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => '成功更改密码！', 'code' => 1));
            exit();
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function doiPass(Request $request, Room $room)
    {
        $code = $request->input('ma-phong');
        $pass = $request->input('pass');
        if (!$request->input('pass') || $pass === '') {
            echo json_encode(array('message' => 'Mật khẩu không được để trống', 'code' => 0));
            exit();
        } else {
            DB::update('update rooms set pass = ? where code = ?', [$pass, $code]);
            echo json_encode(array('message' => 'Đổi mật khẩu thành công!', 'code' => 1));
            exit();
        }
    }

    public function getEventStream(Room $room, $code)
    {
        $fen = Room::where('code', $code)->value('fen');
    
        $response = new StreamedResponse(function () use ($fen) {
            echo "data: $fen\n\n";
            ob_flush();
            flush();
            usleep(2500000); // Sleep for 2.5 seconds (adjust as needed)
        });
    
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cache-Control', 'no-cache');
    
        return $response;
    }
    
    public static function updateRoomScores($id)
    {
        $hostWinScores = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();
        $guestWinScores = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $hostDrawScores = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $guestDrawScores = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $hostScores = $hostWinScores + 0.5 * $hostDrawScores;
        $guestScores = $guestWinScores + 0.5 * $guestDrawScores;

        Room::updateOrInsert(
            ['id' => $id],
            ['host_score' => $hostScores]
        );
        Room::updateOrInsert(
            ['id' => $id],
            ['guest_score' => $guestScores]
        );
    }

    public static function updateRoomElo($id)
    {
        $hostId = Room::find($id)->host_id;
        $guestId = Room::find($id)->guest_id;

        $hostCurrentElo = User::find($hostId)->elo;
        $guestCurrentElo = User::find($guestId)->elo;

        $hostScores = Room::find($id)->host_score;
        $guestScores = Room::find($id)->guest_score;

        $roomHostElo = GameController::calculateElo($hostCurrentElo, $guestCurrentElo, $hostScores);
        $roomGuestElo = GameController::calculateElo($guestCurrentElo, $hostCurrentElo, $guestScores);

        Room::updateOrInsert(
            ['id' => $id],
            ['host_elo' => $roomHostElo]
        );
        Room::updateOrInsert(
            ['id' => $id],
            ['guest_elo' => $roomGuestElo]
        );
    }

    /**
     * Helper method for anonymous match status checking
     */
    private function checkAnonymousMatchStatusHelper(Request $request, $sideNames = ['do', 'den'], $colorNames = ['đỏ', 'đen'])
    {
        $sessionId = $request->input('session_id');
        if (!$sessionId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session ID required.',
            ], 400);
        }

        // First check if current session is already in a room
        $currentRoom = Room::where(function($query) use ($sessionId) {
            $query->where('host_session', $sessionId)
                  ->orWhere('guest_session', $sessionId);
        })->whereNull('result')->first();

        if ($currentRoom) {
            // Already in a room, check if opponent joined
            if ($currentRoom->host_session && $currentRoom->guest_session) {
                $isHost = $currentRoom->host_session == $sessionId;
                $side = $isHost ? $sideNames[0] : $sideNames[1];
                $color = $isHost ? $colorNames[0] : $colorNames[1];
                return response()->json([
                    'status' => 'matched',
                    'room_code' => $currentRoom->code,
                    'room_name' => $currentRoom->name,
                    'side' => $side,
                    'color' => $color,
                ]);
            }
            return response()->json(['status' => 'waiting']);
        }

        // Look for available room with host waiting for guest
        $availableRoom = Room::whereNotNull('host_session')
            ->whereNull('guest_session')
            ->whereNull('result')
            ->whereNull('pass')
            ->where('fen', '=', env('INITIAL_FEN'))
            ->where('modified_at', '>', now()->subMinutes(5))
            ->orderBy('modified_at', 'asc')
            ->first();

        if ($availableRoom) {
            // Join as guest
            $availableRoom->update([
                'guest_session' => $sessionId,
                'modified_at' => now(),
            ]);
            
            return response()->json([
                'status' => 'matched',
                'room_code' => $availableRoom->code,
                'room_name' => $availableRoom->name,
                'side' => $sideNames[1],
                'color' => $colorNames[1],
            ]);
        }

        // No available room, create new room and wait as host
        $roomCode = md5(time() . $sessionId);
        Room::create([
            'code' => $roomCode,
            'fen' => env('INITIAL_FEN'),
            'name' => Haikunator::haikunate(["tokenLength" => 0, "delimiter" => " "]),
            'host_session' => $sessionId,
            'guest_session' => null,
            'host_id' => null,
            'guest_id' => null,
            'pass' => null,
            'modified_at' => now(),
        ]);

        return response()->json(['status' => 'waiting']);
    }

    public function anonymousQuickMatch(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        dispatch(new AnonymousQuickMatchJob($sessionId));

        return response()->json([
            'code' => 1,
            'message' => 'Đang tìm trận...',
            'session_id' => $sessionId,
        ]);
    }

    public function checkAnonymousMatchStatus(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['do', 'den'], ['đỏ', 'đen']);
    }

    public function anonymousQuickMatchEn(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        dispatch(new AnonymousQuickMatchJob($sessionId));

        return response()->json([
            'code' => 1,
            'message' => 'Looking for opponent...',
            'session_id' => $sessionId,
        ]);
    }

    public function checkAnonymousMatchStatusEn(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['red', 'black'], ['red', 'black']);
    }

    public function anonymousQuickMatchJa(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        dispatch(new AnonymousQuickMatchJob($sessionId));

        return response()->json([
            'code' => 1,
            'message' => '対戦相手を探しています...',
            'session_id' => $sessionId,
        ]);
    }

    public function checkAnonymousMatchStatusJa(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['aka', 'kuro'], ['赤', '黒']);
    }

    public function anonymousQuickMatchKo(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        dispatch(new AnonymousQuickMatchJob($sessionId));

        return response()->json([
            'code' => 1,
            'message' => '상대를 찾고 있습니다...',
            'session_id' => $sessionId,
        ]);
    }

    public function checkAnonymousMatchStatusKo(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['ppalgan', 'geom-eunsaeg'], ['빨간색', '검은색']);
    }

    public function anonymousQuickMatchZh(Request $request)
    {
        $sessionId = $request->session()->get('anonymous_match_id', Str::random(32));
        $request->session()->put('anonymous_match_id', $sessionId);

        dispatch(new AnonymousQuickMatchJob($sessionId));

        return response()->json([
            'code' => 1,
            'message' => '寻找对手...',
            'session_id' => $sessionId,
        ]);
    }

    public function checkAnonymousMatchStatusZh(Request $request)
    {
        return $this->checkAnonymousMatchStatusHelper($request, ['hongse', 'heise'], ['红色的', '黑色的']);
    }
}