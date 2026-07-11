<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\TournamentService;
use App\Contracts\BracketGeneratorInterface;
use App\Actions\Room\GetRandomRoomAction;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function __construct(
        private TournamentService $tournamentService,
        private BracketGeneratorInterface $bracketGenerator
    ) {}

    private function getRouteName($key)
    {
        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'vi');
        return $locale === $defaultLocale ? $key : "{$locale}.{$key}";
    }

    public function index(Request $request, GetRandomRoomAction $getRandomRoom)
    {
        $tournaments = Tournament::withCount('users')->orderBy('start_date', 'desc')->paginate(10);

        return view('tournaments.index', localized_page_data('tournaments.index', app()->getLocale(), [
            'headTitle' => $request->route('headTitle'),
            'bodyClass' => 'dashboard',
            'tournaments' => $tournaments,
            'randomRoom' => $getRandomRoom->execute(), // Use injected action
            'roomCode' => '',
            'cdnUrl' => url(''),
        ]));
    }

    public function show(Request $request, string $slug, GetRandomRoomAction $getRandomRoom)
    {
        $tournament = Tournament::with(['creator', 'users', 'rooms.host', 'rooms.guest'])
            ->where('slug', $slug)
            ->firstOrFail();

        $rounds = $tournament->rooms->groupBy('tournament_round')->sortKeys();

        return view('tournaments.show', localized_page_data('tournaments.show', app()->getLocale(), [
            'headTitle'  => $tournament->name,
            'bodyClass' => 'dashboard',
            'tournament' => $tournament,
            'rounds' => $rounds,
            'randomRoom' => $getRandomRoom->execute(),
            'roomCode' => '',
            'cdnUrl' => url(''),
        ], ['slug' => $slug]));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tournament::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'start_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'max_players' => 'required|integer|min:2',
        ]);

        $validated['user_id'] = auth()->id(); // Assign creator
        $validated['cover_photo'] = $this->tournamentService->handleCoverPhotoUpload($request->file('cover_photo'));

        Tournament::create($validated);

        return redirect()->route($this->getRouteName('tournaments.index'))
            ->with('success', __('Tạo giải đấu thành công!'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'start_date' => 'required|date',
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'max_players' => 'required|integer|min:2',
        ]);

        $validated['cover_photo'] = $this->tournamentService->handleCoverPhotoUpload(
            $request->file('cover_photo'),
            $tournament->cover_photo
        );

        if ($validated['status'] === 'open' && $tournament->status !== 'open') {
            $this->tournamentService->clearBracket($tournament); // Clear bracket if status reverts
        }

        $tournament->update($validated);

        return redirect()->route($this->getRouteName('tournaments.show'), $tournament->slug)
            ->with('success', __('Cập nhật giải đấu thành công!'));
    }

    public function join(Request $request, Tournament $tournament)
    {
        if ($tournament->status !== 'open') {
            return back()->with('error', __('Đăng ký đã đóng hoặc giải đấu không ở trạng thái Mở.'));
        }

        if ($tournament->users()->count() >= $tournament->max_players) {
            return back()->with('error', __('Giải đấu đã đủ người tham gia.'));
        }

        $this->tournamentService->joinTournament($tournament, auth()->id());

        return back()->with('success', __('Bạn đã đăng ký tham gia giải đấu thành công!'));
    }

    public function generateBracket(Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        if ($tournament->users()->count() < 2) {
            return back()->with('error', __('Chưa đủ người chơi để tiến hành bốc thăm.'));
        }

        if ($tournament->status !== 'open') {
            return back()->with('error', __('Giải đấu đã bắt đầu hoặc đã bị hủy.'));
        }

        $this->tournamentService->clearBracket($tournament); // Clear existing
        $players = $tournament->users()->inRandomOrder()->get();

        $tournament->update(['status' => 'in_progress']);
        $this->bracketGenerator->generate($tournament, $players);

        return back()->with('success', __('Đã bốc thăm và tạo sơ đồ thi đấu thành công!'));
    }

    public function cancel(Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        if ($tournament->status !== 'open') {
            return back()->with('error', __('Chỉ có thể hủy giải đấu khi giải đang ở trạng thái Mở đăng ký.'));
        }

        $tournament->update(['status' => 'cancelled']);
        return back()->with('success', __('Giải đấu đã được chuyển sang trạng thái Đã Hủy.'));
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);

        if (in_array($tournament->status, ['in_progress', 'completed'])) {
            return back()->with('error', __('Không thể xóa giải đấu đang diễn ra hoặc đã kết thúc. Dữ liệu này cần được lưu trữ.'));
        }

        if ($tournament->status === 'open' && $tournament->users()->count() > 1) {
             return back()->with('error', __('Giải đấu đã có người đăng ký. Bạn chỉ có thể Hủy giải đấu để thông báo cho người tham gia, không được xóa vĩnh viễn.'));
        }

        $this->tournamentService->handleCoverPhotoUpload(null, $tournament->cover_photo); // Delete file
        $tournament->delete();

        return redirect()->route($this->getRouteName('tournaments.index'))
            ->with('success', __('Đã xóa vĩnh viễn giải đấu!'));
    }
}
