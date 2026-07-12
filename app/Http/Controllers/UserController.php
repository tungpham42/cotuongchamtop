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
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Room;
use App\Models\Session as DbSession;
use App\Actions\User\UpdateOnlineStatus;
use App\Presenters\UserPresenter;
use Creativeorange\Gravatar\Facades\Gravatar;
use Carbon\Carbon;
use DataTables;
use Avatar;
use App\Events\PlayersUpdated; // Import the event

class UserController extends Controller
{
    public function __construct(private UserPresenter $presenter) {}

    public function getUsersVi(Request $request)
    {
        return $this->getUsersDatatable($request, 'Thách đấu', 'Hồ sơ');
    }

    public function getUsersEn(Request $request)
    {
        return $this->getUsersDatatable($request, 'Challenge', 'Profile');
    }

    public function getUsersJa(Request $request)
    {
        return $this->getUsersDatatable($request, '挑戦', 'プロフィール');
    }

    public function getUsersKo(Request $request)
    {
        return $this->getUsersDatatable($request, '도전', '프로필');
    }

    public function getUsersZh(Request $request)
    {
        return $this->getUsersDatatable($request, '挑战', '个人资料');
    }

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
            $user->save();
        }

        return back()->with('success', __('Bạn đã cập nhật ảnh đại diện thành công!'));
    }

    public function removeProfilePicture(Request $request)
    {
        $user = auth()->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->profile_picture = null;
            $user->save();
        }

        return back()->with('success', __('Bạn đã xóa ảnh đại diện thành công!'));
    }

    private function getUsersDatatable(Request $request, string $challengeText, string $profileText)
    {
        if ($request->ajax()) {
            $users = User::select(['id', 'name', 'email', 'profile_picture', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at']);

            return Datatables::of($users)
                ->addColumn('rank', fn($row) => '<span class="badge badge-status"><i class="fas fa-medal"></i> ' . $this->presenter->renderUserRank($row->id) . '</span>')
                ->addColumn('name', fn($row) => $this->presenter->renderPlayerName($row->id, false, true))
                ->addColumn('elo', fn($row) => '<strong style="color: var(--royal-gold);">' . $this->presenter->renderElo($row->id) . '</strong>')
                ->addColumn('action', function($row) use ($challengeText, $profileText) {
                    if (auth()->check()) {
                        if (auth()->id() != $row->id) {
                            $actionBtn = '<a class="btn btn-danger text-light mr-1 pulse-red" style="width: 140px;" href="javascript:compete('.$row->id.');"><i class="far fa-mouse"></i> '.$challengeText.'</a>';
                        } else {
                            $actionBtn = '<a class="btn btn-dark text-light mr-1" style="width: 140px; cursor: not-allowed !important;" href="javascript:void(0);"><i class="far fa-ban"></i> '.$challengeText.'</a>';
                        }
                    } else {
                        $actionBtn = '<a class="btn btn-danger text-light mr-1 pulse-red" style="width: 140px;" href="'.localized_url('login').'"><i class="far fa-sign-in"></i> '.$challengeText.'</a>';
                    }
                    $actionBtn .= '<a class="btn btn-dark text-light" style="width: 140px;" href="'.localized_url('app.player', ['id' => $row->id]).'"><i class="far fa-user-alt"></i> '.$profileText.'</a>';
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

    // LEGACY METHODS BELOW (to be refactored to use UserPresenter)

    public static function getPlayers()
    {
        // 1. Grab all user IDs currently holding an active session
        $activeUserIds = DbSession::whereNotNull('user_id')->pluck('user_id')->unique();

        // 2. Fetch those specific players
        $data = User::select('id', 'name', 'email', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at')
                    ->whereIn('id', $activeUserIds)
                    ->orderBy('elo', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(12);

        return $data;
    }

    public static function getFirstPagePlayers()
    {
        $activeUserIds = DbSession::whereNotNull('user_id')->pluck('user_id')->unique();

        $data = User::select('id', 'name', 'email', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at')
                    ->whereIn('id', $activeUserIds)
                    ->orderBy('elo', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(12, ['*'], 'page', 1);

        return $data;
    }

    public function updateOnlineStatus(Request $request, UpdateOnlineStatus $action)
    {
        if (auth()->id() == $request->input('id')) {
            $action->execute($request->input('id'));
        }
    }

    public static function updatePlayerOnlineStatus($id)
    {
        if (isset($id) && auth()->id() == $id) {
            DbSession::where('user_id', $id)->update(['last_activity' => time()]);

            User::updateOrInsert(
                ['id' => $id],
                ['last_seen_at' => Carbon::now()]
            );

            // broadcast(new PlayersUpdated());
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
        // Check if the specific user ID exists in the active sessions table
        $hasActiveSession = DbSession::where('user_id', $id)->exists();

        // Determine the status class and title based on the session query
        $statusClass = $hasActiveSession ? 'text-success' : 'text-danger';
        $statusTitle = $hasActiveSession ? __('Trực tuyến') : __('Ngoại tuyến');

        // Wrap the icon in a targetable span for Pusher Echo to manipulate
        return '<span class="user-status-indicator" data-user-id="' . $id . '"> <i title="' . $statusTitle . '" class="' . $statusClass . ' fad fa-circle"></i></span>';
    }

    public static function onlinePlayers()
    {
        return Cache::remember('usersOnline', 60, function () {
            // Get a count of unique authenticated users currently in the sessions table
            return DbSession::whereNotNull('user_id')->pluck('user_id')->unique()->count();
        });
    }

    public static function renderOnlinePlayers()
    {
        // Use the same DbSession logic to get the real-time count without relying on the 'last_seen_at' timestamp
        $onlinePlayers = DbSession::whereNotNull('user_id')->pluck('user_id')->unique()->count();

        return trans_choice('messages.players_online_count', $onlinePlayers, ['count' => $onlinePlayers]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'new_confirm_password' => 'required|same:new_password',
        ],
        [
            'current_password.required' => __('Mật khẩu hiện tại bắt buộc điền.'),
            'new_password.required' => __('Mật khẩu mới bắt buộc điền.'),
            'new_password.min' => __('Mật khẩu mới phải ít nhất 8 ký tự.'),
            'new_confirm_password.required' => __('Mật khẩu lặp lại bắt buộc điền.'),
            'new_confirm_password.same' => __('Mật khẩu lặp lại và mật khẩu mới phải giống nhau.'),
        ]);

        $oldId = $request->input('current_id');
        $user = User::find($oldId);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => __('Mật khẩu hiện tại không khớp.')]);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return back()->with('success', __('Mật khẩu đã thay đổi thành công!'));
    }

    public function changeName(Request $request)
    {
        $request->validate([
            'current_name' => 'required',
            'new_name' => 'required|min:3|max:15|unique:users,name',
        ],
        [
            'current_name.required' => __('Tên hiện tại bắt buộc.'),
            'new_name.required' => __('Tên mới bắt buộc điền.'),
            'new_name.min' => __('Tên mới phải ít nhất 3 ký tự.'),
            'new_name.max' => __('Tên mới phải ít hơn 16 ký tự.'),
            'new_name.unique' => __('Tên này đã được sử dụng.'),
        ]);

        $oldId = $request->input('current_id');
        $newName = $request->input('new_name');

        $user = User::find($oldId);
        $user->name = $newName;
        $user->save();

        // broadcast(new PlayersUpdated()); // Refresh for name change

        return back()->with('success', __('Bạn đã thay đổi tên thành công!'));
    }

    public function changeUserInterface(Request $request)
    {
        $currentId = $request->input('current_id');
        $user = User::find($currentId);

        $user->board_theme = $request->input('board_theme');
        $user->pieces_theme = $request->input('pieces_theme');
        $user->save();

        return back()->with('success', __('Bạn đã thay đổi giao diện thành công!'));
    }

    public static function renderName($id)
    {
        $user = User::find($id);

        if ($user) {
            $onlineStatus = self::onlineStatus($id);
            $avatarSrc = $user->profile_picture ? asset('storage/' . $user->profile_picture) : Avatar::create($user->name)->setDimension(38)->setFontSize(19);
            $profileLink = localized_url('app.player', ['id' => $id]);

            return $onlineStatus . '&nbsp;<img src="' . $avatarSrc . '" style="width: 38px; height: 38px; object-fit: cover; border-radius: 4px;" />&nbsp;<a class="text-light showPromotion animate-light" href="' . $profileLink . '">' . $user->name . '</a>';
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
            $avatarSrc = $user->profile_picture ? asset('storage/' . $user->profile_picture) : Avatar::create($user->name)->setDimension(38)->setFontSize(19);
            $profileLink = localized_url('app.player', ['id' => $id]);

            return $onlineStatus . '&nbsp;<img src="' . $avatarSrc . '" style="width: 38px; height: 38px; object-fit: cover; border-radius: 4px;" />&nbsp;<a class="text-danger showPromotion animate" href="' . $profileLink . '">' . '# ' . $id . '  ' . $user->name . '</a>';
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
            $avatarSrc = $user->profile_picture ? asset('storage/' . $user->profile_picture) : Avatar::create($user->name)->setDimension(28)->setFontSize(14);
            $profileLink = localized_url('app.player', ['id' => $id]);

            return $onlineStatus . '&nbsp;<img alt="' . $user->name . '" src="' . $avatarSrc . '" style="width: 28px; height: 28px; object-fit: cover; border-radius: 4px;">&nbsp;<a class="text-light showPromotion animate-light" href="' . $profileLink . '">' . '# ' . $id . '  ' . $user->name . '</a>';
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

        // broadcast(new PlayersUpdated()); // Refresh Elo change
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
        $data = User::select('id', 'email', 'name', 'elo', 'last_seen_at', 'created_at')
                ->orderBy('elo', 'desc')
                ->paginate(10);
        return $data;
    }

    public static function getMatchUsers()
    {
        $data = User::select('id', 'email', 'name', 'elo', 'last_seen_at', 'created_at')
                ->orderBy('elo', 'desc')
                ->limit(10)
                ->get();
        return $data;
    }

    public static function getRankUsers()
    {
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

    public function search(Request $request)
    {
        $query = $request->input('query');

        $results = User::where('name', 'LIKE', '%'.$query.'%')
            ->orWhere('email', 'LIKE', '%'.$query.'%')
            ->paginate(10);

        return view('app.search', compact('results'));
    }

    public function create() {}
    public function store(Request $request) {}
    public function show($id) {}
    public function edit($id) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}
