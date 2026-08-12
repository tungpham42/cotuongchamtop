<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Room;
use App\Actions\User\UpdateOnlineStatus;
use App\Actions\User\UpdateUserStatusAction;
use App\Actions\User\ChangePasswordAction;
use App\Actions\User\ChangeNameAction;
use App\Actions\User\ChangeUserInterfaceAction;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ChangeNameRequest;
use App\Http\Requests\ChangeUserInterfaceRequest;
use App\Presenters\UserPresenter;
use App\Presenters\UserDataTablePresenter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use DataTables;

class UserController extends Controller
{
    public function __construct(
        private UserPresenter $userPresenter,
        private UpdateUserStatusAction $updateUserStatusAction
    ) {}

    public function getUsersData(Request $request): JsonResponse
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

        return response()->json([]);
    }

    public function uploadProfilePicture(Request $request): RedirectResponse
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

    public function removeProfilePicture(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->profile_picture = null;
            $user->save();
        }

        return back()->with('success', __('Bạn đã xóa ảnh đại diện thành công!'));
    }

    public function updateOnlineStatus(Request $request, UpdateOnlineStatus $action): JsonResponse
    {
        if (auth()->id() == $request->input('id')) {
            $action->execute($request->input('id'));
        }

        return response()->json(['success' => true]);
    }

    public function updatePlayersStatus(Request $request): JsonResponse
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

    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): RedirectResponse
    {
        // Validation errors (including the "current password wrong" case
        // thrown from the action) are caught by Laravel's exception handler
        // and redirected back with $errors automatically.
        $action->execute(
            auth()->user(),
            $request->validated('current_password'),
            $request->validated('new_password')
        );

        return back()->with('success', __('Mật khẩu đã thay đổi thành công!'));
    }

    public function changeName(ChangeNameRequest $request, ChangeNameAction $action): RedirectResponse
    {
        $action->execute(auth()->user(), $request->validated('new_name'));

        return back()->with('success', __('Bạn đã thay đổi tên thành công!'));
    }

    public function changeUserInterface(ChangeUserInterfaceRequest $request, ChangeUserInterfaceAction $action): RedirectResponse
    {
        $action->execute(
            auth()->user(),
            $request->validated('board_theme'),
            $request->validated('pieces_theme')
        );

        return back()->with('success', __('Bạn đã thay đổi giao diện thành công!'));
    }

    public function getName(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json(['name' => $user ? $user->name : null]);
    }

    public function getEmail(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json(['email' => $user ? $user->email : null]);
    }

    public function getNameEmail(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json([
            'name' => $user ? $user->name : null,
            'email' => $user ? $user->email : null,
        ]);
    }

    public function getPoints(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $user = User::find($id);

        return response()->json(['points' => $user ? $user->points : null]);
    }

    public function search(Request $request): View
    {
        $query = $request->input('query');

        $results = User::where('name', 'LIKE', '%'.$query.'%')
            ->orWhere('email', 'LIKE', '%'.$query.'%')
            ->paginate(10);

        return view('app.search', compact('results'));
    }

    public function renderPlayersTitle(Request $request): string
    {
        $roomCode = $request->input('ma-phong');

        if (!$roomCode) {
            return '';
        }

        return $this->userPresenter->renderPlayersTitle($roomCode);
    }
}
