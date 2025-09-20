<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\GameController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Models\Room;
use Creativeorange\Gravatar\Facades\Gravatar;
use Carbon\Carbon;
use DataTables;
use Avatar;

class UserController extends Controller
{
    public function getUsersVi(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select(['id', 'name', 'email', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at']);
            return Datatables::of($users)
                ->addColumn('rank', function($row){
                    $userRank = self::renderUserRank($row->id);
                    return $userRank;
                })
                ->addColumn('name', function($row){
                    $onlineStatus = self::onlineStatus($row->id);
                    $avatar = Avatar::create($row->name)->setDimension(28)->setFontSize(14);
                    $userName = '<img src="' . $avatar . '" />&nbsp;<a class="text-danger animate showPromotion" style="cursor: pointer !important; text-decoration: none !important;" href="'.URL::to('/').'/ky-thu/'.$row->id.'">'.$row->name.'</a>&nbsp;' . $onlineStatus;
                    return $userName;
                })
                ->addColumn('elo', function($row){
                    $userElo = self::renderElo($row->id);
                    return $userElo;
                })
                ->addColumn('action', function($row){
                    if (auth()->check()) {
                        if (auth()->id() != $row->id) {
                            $actionBtn = '<a class="btn btn-danger text-light mr-1" style="width: 140px;" href="javascript:compete('.$row->id.');"><i class="far fa-mouse"></i> Thách đấu</a>';
                        } else {
                            $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 140px; cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> Thách đấu</a>';
                        }
                    } else {
                        $actionBtn = '<a class="btn btn-danger text-light mr-1" style="width: 140px;" href="'.URL::to('/dang-nhap').'"><i class="far fa-sign-in"></i> Thách đấu</a>';
                    }
                    $actionBtn .= '<a class="btn btn-dark text-light" style="width: 90px;" href="'.URL::to('/').'/ky-thu/'.$row->id.'"><i class="far fa-user-alt"></i> Hồ sơ</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->created_at));
                })
                ->escapeColumns([])
                ->orderColumn('name', 'name $1')
                ->orderColumn('elo', 'elo $1')
                ->orderColumn('time', 'created_at $1')
                ->filterColumn('name', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('name', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "created_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['rank', 'name', 'elo', 'action', 'time'])
                ->make(true);
        }
    }

    public static function getPlayers()
    {
        $data = User::select('id', 'name', 'email', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at')
                    ->where('last_seen_at', '>=', now()->subMinutes(2))
                    ->orderBy('elo', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(12);
        return $data;
    }

    public static function getFirstPagePlayers()
    {
        $data = User::select('id', 'name', 'email', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at')
                    ->where('last_seen_at', '>=', now()->subMinutes(2))
                    ->orderBy('elo', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(12, ['*'], 'page', 1);
        return $data;
    }
    public function updateOnlineStatus(Request $request)
    {
        $id = $request->input('id');
        if (auth()->id() == $id) {
            User::updateOrInsert(
                ['id' => $id],
                ['last_seen_at' => Carbon::now()]
            );
        }
    }

    public static function updatePlayerOnlineStatus($id)
    {
        if (isset($id) && auth()->id() == $id) {
            User::updateOrInsert(
                ['id' => $id],
                ['last_seen_at' => Carbon::now()]
            );
        }
    }

    public static function updatePlayerStatus($id)
    {
        User::updateOrInsert(
            ['id' => $id],
            ['last_seen_at' => Carbon::now()]
        );
    }

    public static function updatePlayersStatus(Request $request)
    {
        $code = $request->input('ma-phong');
    
        $roomData = Room::select('host_id', 'guest_id')
            ->where('code', '=', $code)
            ->first();
    
        if ($roomData) {
            self::updatePlayerStatus($roomData->host_id);
            self::updatePlayerStatus($roomData->guest_id);
        }
    }    

    public static function onlineStatus($id)
    {
        if (isset($id)) {
            self::updatePlayerOnlineStatus($id);
        }

        $user = User::find($id);

        if (isset($user->last_seen_at)) {
            if (Carbon::parse($user->last_seen_at)->diffInMinutes() < 2) {
                return ' <i title="Trực tuyến" class="text-success fad fa-circle"></i>';
            } else {
                return ' <i title="Ngoại tuyến" class="text-danger fad fa-circle"></i>';
            }
        } else {
            return ' <i title="Ngoại tuyến" class="text-danger fad fa-circle"></i>';
        }
    }

    public static function onlinePlayers()
    {
        $usersOnline = Cache::remember('usersOnline', 60, function () {
            $sessions = Session::all();
            return count($sessions);
        });
        return $usersOnline;
    }

    public static function renderOnlinePlayers()
    {
        $onlinePlayers = User::where('last_seen_at', '>=', Carbon::now()->subMinutes(2))
                ->count();

        return (($onlinePlayers == 0) ? 'không có ai': numberToWordsVi($onlinePlayers).' kỳ thủ').' đang trực tuyến';
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'new_confirm_password' => 'required|same:new_password',
        ],
        [
            'current_password.required' => 'Mật khẩu hiện tại bắt buộc điền.',
            'new_password.required' => 'Mật khẩu mới bắt buộc điền.',
            'new_password.min' => 'Mật khẩu mới phải ít nhất 8 ký tự.',
            'new_confirm_password.required' => 'Mật khẩu lặp lại bắt buộc điền',
            'new_confirm_password.same' => 'Mật khẩu lặp lại và mật khẩu mới phải giống nhau.',
        ]);
        
        $oldId = $request->input('current_id');
        $user = User::find($oldId);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không khớp']);
        }
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        // return redirect('/thi-dau')->with('success', 'Mật khẩu đã thay đổi thành công!');
        $previousUrl = Session::get('previousUrl');

        // Redirect the user to the previous URL
        return Redirect::to($previousUrl)->with('success', 'Mật khẩu đã thay đổi thành công!');
    }

    public function changeName(Request $request)
    {
        $request->validate([
            'current_name' => 'required',
            'new_name' => 'required|min:3|max:15|unique:users,name',
        ],
        [
            'current_name.required' => 'Tên hiện tại bắt buộc.',
            'new_name.required' => 'Tên mới bắt buộc điền.',
            'new_name.min' => 'Tên mới phải ít nhất 3 ký tự.',
            'new_name.max' => 'Tên mới phải ít hơn 16 ký tự.',
            'new_name.unique' => 'Tên này đã được sử dụng.',
        ]);

        $oldId = $request->input('current_id');
        $oldName = $request->input('current_name');
        $newName = $request->input('new_name');

        $user = User::find($oldId);

        $user->name = $newName;

        $user->save();

        // return redirect('/thi-dau')->with('success', 'Bạn đã thay đổi tên thành công!');
        $previousUrl = Session::get('previousUrl');

        // Redirect the user to the previous URL
        return Redirect::to($previousUrl)->with('success', 'Bạn đã thay đổi tên thành công!');
    }

    public function changeUserInterface(Request $request)
    {
        $currentId = $request->input('current_id');
        $user = User::find($currentId);
        $user->board_theme = $request->input('board_theme');
        $user->pieces_theme = $request->input('pieces_theme');
        $user->save();
    
        // return redirect('/thi-dau')->with('success', 'Bạn đã thay đổi giao diện thành công!');
        $previousUrl = Session::get('previousUrl');

        // Redirect the user to the previous URL
        return Redirect::to($previousUrl)->with('success', 'Bạn đã thay đổi giao diện thành công!');
    }

    public static function renderName($id)
    {
        $user = User::find($id);
    
        if ($user) {
            $onlineStatus = self::onlineStatus($id);
            $avatar = Avatar::create($user->name)->setDimension(38)->setFontSize(19);
            $profileLink = URL::to('/ky-thu/') . '/' . $id;
    
            return '<img src="' . $avatar . '" />&nbsp;<a class="text-light showPromotion animate-light" href="' . $profileLink . '">' . $user->name . '</a>&nbsp;' . $onlineStatus;
        } else {
            return '<span class="waitingIndicator">
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                    </span>';
        }
    }    

    public static function renderPlayerName($id)
    {
        $user = User::find($id);
    
        if ($user) {
            $onlineStatus = self::onlineStatus($id);
            $avatar = Avatar::create($user->name)->setDimension(38)->setFontSize(19);
            $profileLink = URL::to('/ky-thu/') . '/' . $id;
    
            return '<img src="' . $avatar . '" />&nbsp;<a class="text-danger showPromotion animate" href="' . $profileLink . '">' . '# ' . $id . '  ' . $user->name . '</a>&nbsp;' . $onlineStatus;
        } else {
            return '<span class="waitingIndicator">
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                        <span class="indicator bg-danger"></span>
                    </span>';
        }
    }    

    public static function renderPlayerRank($id)
    {
        $user = User::find($id);

        $rank = User::where('elo', '>', ceil($user->elo))->count() + 1;
        if ($rank == User::all()->count() + 1) {
            $rank = User::all()->count();
        }
        $totalUsers = User::all()->count();

        return $rank.'/'.$totalUsers;
    }

    public static function renderUserRank(int $id): ?int
    {
        // Find the user by ID
        $user = User::find($id);

        // Return null if the user is not found
        if (!$user) {
            return null;
        }

        // Calculate the rank based on users with a higher elo
        $rank = User::where('elo', '>', ceil($user->elo))->count() + 1;

        if ($rank == User::all()->count() + 1) {
            return User::all()->count();
        }

        return $rank;
    }

    public static function renderPlayerEmail($id)
    {
        $user = User::find($id);

        return '<a class="text-danger showPromotion animate" href="mailto:'.$user->email.'">'.$user->email.'</a>';
    }

    public static function renderPlayerNameRoom($id)
    {
        $user = User::find($id);
    
        if ($user) {
            $onlineStatus = self::onlineStatus($id);
            $avatar = Avatar::create($user->name)->setDimension(28)->setFontSize(14);
            $profileLink = URL::to('/ky-thu/') . '/' . $id;
    
            return '<img alt="' . $user->name . '" src="' . $avatar . '">&nbsp;<a class="text-light showPromotion animate-light" href="' . $profileLink . '">' . '# ' . $id . '  ' . $user->name . '</a>&nbsp;' . $onlineStatus;
        } else {
            return '<span class="waitingIndicator">
                        <span class="indicator bg-light"></span>
                        <span class="indicator bg-light"></span>
                        <span class="indicator bg-light"></span>
                        <span class="indicator bg-light"></span>
                        <span class="indicator bg-light"></span>
                    </span>';
        }
    }    

    public static function renderPlayersTitle(Request $request)
    {
        $code = $request->input('ma-phong');
    
        $roomData = Room::select('host_id', 'guest_id')
            ->where('code', '=', $code)
            ->first();
    
        if ($roomData) {
            $hostTitle = self::renderPlayerNameRoom($roomData->host_id);
            $guestTitle = self::renderPlayerNameRoom($roomData->guest_id);
    
            return '<span class="host-title">' . $hostTitle . '</span> <span class="guest-title">' . $guestTitle . '</span>';
        }
    
        return '';
    }    

    public static function getUserName($id)
    {
        $user = User::find($id);
    
        if ($user) {
            return $user->name;
        }
    
        return null;
    }    

    public static function getUserEmail($id)
    {
        $user = User::find($id);
    
        if ($user) {
            return $user->email;
        }
    
        return null;
    }    

    public static function getName(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);
    
        if ($user) {
            return $user->name;
        }
    
        return null;
    }    

    public static function getEmail(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);
    
        if ($user) {
            return $user->email;
        }
    
        return null;
    }    

    public static function getNameEmail(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);
    
        if ($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
            ];
        }
    
        return null;
    }    

    public static function getPoints(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);
    
        if ($user) {
            return $user->points;
        }
    
        return null;
    }

    public function updatePoints(Request $request)
    {
        $id = $request->input('id');

        $hostPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $guestPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $hostDrawPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $guestDrawPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $userPoints = 3 * ($hostPoints + $guestPoints) + $hostDrawPoints + $guestDrawPoints;

        User::updateOrInsert(
            ['id' => $id],
            ['points' => $userPoints]
        );
    }

    public function getWinMatchPoints(Request $request)
    {
        $id = $request->input('id');

        $winHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $winGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $winMatchPoints = $winHostMatchPoints + $winGuestMatchPoints;
        
        return $winMatchPoints;
    }

    public function getLoseMatchPoints(Request $request)
    {
        $id = $request->input('id');

        $loseHostMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $loseGuestMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $loseMatchPoints = $loseHostMatchPoints + $loseGuestMatchPoints;
        
        return $loseMatchPoints;
    }

    public static function getPlayerBoards($id)
    {
        $data = Room::select('fen', 'code', 'host_id', 'guest_id', 'result', 'pass', 'modified_at')
                ->orWhere('host_id', '=', $id)
                ->orWhere('guest_id', '=', $id)
                ->orderBy('modified_at', 'desc')
                ->paginate(12);
        return $data;
    }

    public function getDrawMatchPoints(Request $request)
    {
        $id = $request->input('id');

        $drawHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $drawGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $drawMatchPoints = $drawHostMatchPoints + $drawGuestMatchPoints;
        
        return $drawMatchPoints;
    }

    public function getTotalMatchPoints(Request $request)
    {
        $id = $request->input('id');

        $winHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $winGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $loseHostMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $loseGuestMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $drawHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $drawGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $totalMatchPoints = $winHostMatchPoints + $winGuestMatchPoints + $loseHostMatchPoints + $loseGuestMatchPoints + $drawHostMatchPoints + $drawGuestMatchPoints;

        return $totalMatchPoints;
    }

    public static function updatePlayerElo($id)
    {
        $hostPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $guestPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $hostDrawPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $guestDrawPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $userPoints = 3 * ($hostPoints + $guestPoints) + $hostDrawPoints + $guestDrawPoints;

        list($newRatingA, $newRatingB) = calculateElo($ratingA, $ratingB, $scoreA);

        User::updateOrInsert(
            ['id' => $id],
            ['elo' => $playerElo]
        );
    }

    public static function updatePlayerPoints($id)
    {
        $hostPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $guestPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $hostDrawPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $guestDrawPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $userPoints = 3 * ($hostPoints + $guestPoints) + $hostDrawPoints + $guestDrawPoints;

        User::updateOrInsert(
            ['id' => $id],
            ['points' => $userPoints]
        );
    }

    public static function getUsers()
    {
        // $users = DB::table('users')
        //         ->select('id')
        //         ->get();
        
        // foreach ($users as $user) {
        //     self::updatePlayerPoints($user->id);
        // }
        
        $data = User::select('id', 'email', 'name', 'elo', 'last_seen_at', 'created_at')
                ->orderBy('elo', 'desc')
                ->paginate(10);
        return $data;
    }

    public static function getMatchUsers()
    {
        // $users = DB::table('users')
        //         ->select('id')
        //         ->limit(10)
        //         ->get();
        
        // foreach ($users as $user) {
        //     self::updatePlayerPoints($user->id);
        // }

        $data = User::select('id', 'email', 'name', 'elo', 'last_seen_at', 'created_at')
                ->orderBy('elo', 'desc')
                ->limit(10)
                ->get();
        return $data;
    }

    public static function getRankUsers()
    {
        // $users = DB::table('users')
        //         ->select('id')
        //         ->get();
        
        // foreach ($users as $user) {
        //     self::updatePlayerPoints($user->id);
        // }

        $data = User::select('id')
                ->get();
        return $data;
    }

    public static function renderPoints($id)
    {
        self::updatePlayerPoints($id);
    
        $user = User::find($id);
    
        if ($user) {
            return $user->points;
        }
    
        return null;
    }

    public static function renderElo($id)
    {
        $user = User::find($id);
    
        if ($user) {
            return ceil($user->elo);
        }
    
        return null;
    }

    public static function renderWinMatchPoints($id)
    {
        self::updatePlayerPoints($id);
        
        $winHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $winGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $winMatchPoints = $winHostMatchPoints + $winGuestMatchPoints;
        
        return $winMatchPoints;
    }

    public static function renderLoseMatchPoints($id)
    {
        self::updatePlayerPoints($id);
        
        $loseHostMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $loseGuestMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $loseMatchPoints = $loseHostMatchPoints + $loseGuestMatchPoints;
        
        return $loseMatchPoints;
    }

    public static function renderDrawMatchPoints($id)
    {
        self::updatePlayerPoints($id);
        
        $drawHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $drawGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $drawMatchPoints = $drawHostMatchPoints + $drawGuestMatchPoints;
        
        return $drawMatchPoints;
    }

    public static function renderTotalMatchPoints($id)
    {
        self::updatePlayerPoints($id);
        
        $winHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $winGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $loseHostMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '1')
                ->count();

        $loseGuestMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '-1')
                ->count();

        $drawHostMatchPoints = Room::where('host_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $drawGuestMatchPoints = Room::where('guest_id', '=', $id)
                ->where('result', '=', '0')
                ->count();

        $totalMatchPoints = $winHostMatchPoints + $winGuestMatchPoints + $loseHostMatchPoints + $loseGuestMatchPoints + $drawHostMatchPoints + $drawGuestMatchPoints;

        return $totalMatchPoints;
    }

    public function searchPlayers(Request $request)
    {
        $query = $request->input('query');

        $results = User::where('name', 'LIKE', '%'.$query.'%')
            ->orWhere('email', 'LIKE', '%'.$query.'%')
            ->paginate(10);

        return view('app/search', compact('results'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
