<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Actions\User\UpdateOnlineStatus;
use App\Actions\User\UpdateUserStatusAction;
use App\Presenters\UserPresenter;
use App\Presenters\UserDataTablePresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use DataTables;

class UserController extends Controller
{
    public function __construct(
        private UserPresenter $userPresenter,
        private UpdateUserStatusAction $updateUserStatusAction
    ) {}

    public function getUsersData(Request $request)
    {
        if ($request->ajax()) {
            $users = User::select(['id', 'name', 'email', 'profile_picture', 'elo', 'points', 'last_seen_at', 'created_at', 'updated_at']);

            $presenter = new UserDataTablePresenter(app()->getLocale(), $this->userPresenter);

            return Datatables::of($users)
                ->addColumn('rank', fn($row) => $presenter->formatRank($row))
                ->addColumn('name', fn($row) =>$presenter->formatName($row))
                ->addColumn('elo', fn($row) => $presenter->formatElo($row))
                ->addColumn('action', fn($row) => $presenter->formatAction($row))
                ->addColumn('time', fn($row) => $presenter->formatTime($row))
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

    public function updateOnlineStatus(Request $request, UpdateOnlineStatus $action)
    {
        if (auth()->id() == $request->input('id')) {
            $action->execute($request->input('id'));
        }

        return response()->json(['success' => true]);
    }

    public function updatePlayersStatus(Request $request)
    {
        $code = $request->input('ma-phong');

        $roomData = Room::select('host_id', 'guest_id')
            ->where('code', '=', $code)
            ->first();

        if ($roomData) {
            if ($roomData->host_id) {
                $this->updateUserStatusAction->execute($roomData->host_id);
            }
            if ($roomData->guest_id) {
                $this->updateUserStatusAction->execute($roomData->guest_id);
            }
        }

        return response()->json(['success' => true]);
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

        $user = auth()->user(); // Fix: Use authenticated user instead of $request->input('current_id')

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

        $user = auth()->user(); // Fix: Use authenticated user
        $user->name = $request->input('new_name');
        $user->save();

        return back()->with('success', __('Bạn đã thay đổi tên thành công!'));
    }

    public function changeUserInterface(Request $request)
    {
        $user = auth()->user(); // Fix: Use authenticated user

        $user->board_theme = $request->input('board_theme');
        $user->pieces_theme = $request->input('pieces_theme');
        $user->save();

        return back()->with('success', __('Bạn đã thay đổi giao diện thành công!'));
    }

    public function getName(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json(['name' => $user ? $user->name : null]);
    }

    public function getEmail(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json(['email' => $user ? $user->email : null]);
    }

    public function getNameEmail(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json([
            'name' => $user ? $user->name : null,
            'email' => $user ? $user->email : null,
        ]);
    }

    public function getPoints(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json(['points' => $user ? $user->points : null]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $results = User::where('name', 'LIKE', '%'.$query.'%')
            ->orWhere('email', 'LIKE', '%'.$query.'%')
            ->paginate(10);

        return view('app.search', compact('results'));
    }
}
