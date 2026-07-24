<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\PuzzleComment;
use App\Models\PuzzleCommentLike;
use App\Actions\Puzzle\GetPuzzleRankAction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Presenters\PuzzleDataTablePresenter;
use DataTables;

class PuzzleController extends Controller
{
    private $getPuzzleRankAction;

    public function __construct(GetPuzzleRankAction $getPuzzleRankAction)
    {
        $this->getPuzzleRankAction = $getPuzzleRankAction;
    }

    /**
     * Unified logic to generate the DataTables response for puzzles.
     */
    public function getPuzzlesData(Request $request)
    {
        if ($request->ajax()) {
            $puzzles = Puzzle::public()->select(['id', 'name', 'slug', 'fen', 'rating', 'likes_count', 'hard_count', 'unsolved_count', 'updated_at']);

            $presenter = new PuzzleDataTablePresenter(app()->getLocale(), $this->getPuzzleRankAction);

            return Datatables::of($puzzles)
                ->addColumn('rank', fn($row) => $presenter->formatRank($row))
                ->addColumn('name', fn($row) => $presenter->formatName($row))
                ->addColumn('rating', fn($row) => $presenter->formatRating($row))
                ->addColumn('action', fn($row) => $presenter->formatAction($row))
                ->addColumn('time', fn($row) => $presenter->formatTime($row))
                ->escapeColumns([])
                ->orderColumn('name', 'name $1')
                ->orderColumn('rating', 'likes_count $1')
                ->orderColumn('time', 'updated_at $1')
                ->filterColumn('name', function($query, $keyword) {
                    $query->where(function($query) use ($keyword) {
                        $query->orWhere('name', 'like', '%' . $keyword . '%')
                              ->orWhere('slug', 'like', '%' . $keyword . '%');
                    });
                })
                ->filterColumn('time', function($query, $keyword) {
                    $sql = "updated_at like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->rawColumns(['rank', 'name', 'rating', 'action', 'time'])
                ->make(true);
        }
    }

    public function getPuzzlesVi(Request $request) { return $this->getPuzzlesData($request, 'vi'); }
    public function getPuzzlesEn(Request $request) { return $this->getPuzzlesData($request, 'en'); }
    public function getPuzzlesJa(Request $request) { return $this->getPuzzlesData($request, 'ja'); }
    public function getPuzzlesKo(Request $request) { return $this->getPuzzlesData($request, 'ko'); }
    public function getPuzzlesZh(Request $request) { return $this->getPuzzlesData($request, 'zh'); }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'fen' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_public' => 'required|boolean',
        ]);

        $rating = (int) $request->input('rating', 0);

        if (Puzzle::where('name', $payload['name'])->exists()) {
            return response()->json([
                'message' => 'Tên thế cờ đã tồn tại trong hệ thống, vui lòng chọn tên khác!',
                'code' => 0,
            ], 422);
        }

        if (Puzzle::where('fen', $payload['fen'])->exists()) {
            return response()->json([
                'message' => 'Bàn cờ đã tồn tại trong hệ thống, vui lòng xếp lại!',
                'code' => 0,
            ], 422);
        }

        $slug = Puzzle::makeUniqueSlug($payload['name'], $request->input('slug'));

        $puzzle = Puzzle::create([
            'name' => $payload['name'],
            'slug' => $slug,
            'fen' => $payload['fen'],
            'rating' => $rating,
            'description' => $payload['description'] ?? null,
            'is_public' => $payload['is_public'],
        ]);

        return response()->json([
            'message' => 'Tạo thế cờ thành công!',
            'code' => 1,
            'slug' => $puzzle->slug,
            'url' => url('/').__("/the-co/").$puzzle->slug,
        ], 201);
    }

    public function checkUniqueName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fen' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
        ]);

        $name = $request->input('name');
        $fen = $request->input('fen');

        if (Puzzle::where('name', $name)->exists()) {
            return response()->json([
                'message' => 'Tên thế cờ đã tồn tại trong hệ thống, vui lòng chọn tên khác!',
                'code' => 0,
            ]);
        }

        if ($fen && Puzzle::where('fen', $fen)->exists()) {
            return response()->json([
                'message' => 'Bàn cờ đã tồn tại trong hệ thống, vui lòng xếp lại!',
                'code' => 0,
            ]);
        }

        $slug = Puzzle::makeUniqueSlug($name, $request->input('slug'));

        return response()->json([
            'message' => 'Bạn có thể sử dụng tên thế cờ này.',
            'code' => 1,
            'slug' => $slug,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Puzzle $puzzle, $name)
    {
        return Puzzle::where('name', $name)->value('fen');
    }

    public function upvote(Request $request)
    {
        $slug = $request->input('slug');
        return $this->react($request, $slug ?? '');
    }

    public function downvote(Request $request)
    {
        $slug = $request->input('slug');
        $request->merge(['type' => 'unsolved']);
        return $this->react($request, $slug ?? '');
    }

    public function totalRating(Request $request)
    {
        $slug = $request->input('slug');
        $puzzle = $this->getPuzzleOrFail($slug);

        return response()->json([
            'likes' => $puzzle->likes_count,
            'hard' => $puzzle->hard_count,
            'unsolved' => $puzzle->unsolved_count,
            'rating' => $puzzle->rating,
        ]);
    }

    public function getReactions(string $slug)
    {
        $puzzle = $this->getPuzzleOrFail($slug);

        return response()->json([
            'likes' => $puzzle->likes_count,
            'hard' => $puzzle->hard_count,
            'unsolved' => $puzzle->unsolved_count,
            'rating' => $puzzle->rating,
        ]);
    }

    public function react(Request $request, string $slug = null)
    {
        $slug = $slug ?? $request->input('slug');

        $data = $request->validate([
            'type' => 'sometimes|required|string|in:like,hard,unsolved',
            'slug' => 'nullable|string|max:255',
        ]);

        $reaction = $data['type'] ?? $request->input('type');
        if (!$reaction) {
            $reaction = 'like';
        }

        $puzzle = $this->getPuzzleOrFail($slug);

        switch ($reaction) {
            case 'like':
                $puzzle->increment('likes_count');
                $puzzle->increment('rating');
                break;
            case 'hard':
                $puzzle->increment('hard_count');
                break;
            case 'unsolved':
                $puzzle->increment('unsolved_count');
                break;
        }

        $puzzle->refresh();

        return response()->json([
            'likes' => $puzzle->likes_count,
            'hard' => $puzzle->hard_count,
            'unsolved' => $puzzle->unsolved_count,
            'rating' => $puzzle->rating,
        ]);
    }

    public function comments(Request $request, string $slug)
    {
        $puzzle = $this->getPuzzleOrFail($slug);

        $selectColumns = ['id', 'puzzle_id', 'parent_id', 'author_name', 'content', 'created_at'];
        $replyColumns = ['id', 'puzzle_id', 'parent_id', 'author_name', 'content', 'created_at'];

        if (Schema::hasColumn('puzzle_comments', 'likes_count')) {
            $selectColumns[] = 'likes_count';
            $replyColumns[] = 'likes_count';
        }

        $comments = $puzzle->comments()
            ->where('is_public', true)
            ->whereNull('parent_id')
            ->with(['replies' => function ($query) use ($replyColumns) {
                $query->where('is_public', true)
                    ->select($replyColumns);
            }])
            ->latest()
            ->get($selectColumns);

        return response()->json([
            'comments' => $comments->map(fn($comment) => $this->transformComment($comment)),
        ]);
    }

    public function addComment(Request $request, string $slug)
    {
        $puzzle = $this->getPuzzleOrFail($slug);

        $data = $request->validate([
            'author_name' => 'nullable|string|max:120',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer|exists:puzzle_comments,id',
        ]);

        $parentId = $data['parent_id'] ?? null;
        $parentComment = null;

        if ($parentId) {
            $parentComment = PuzzleComment::where('id', $parentId)
                ->where('puzzle_id', $puzzle->id)
                ->first();

            if (!$parentComment) {
                return response()->json([
                    'message' => 'Bình luận gốc không tồn tại.',
                ], 422);
            }
        }

        $author = $data['author_name'] ?? (Auth::check() ? Auth::user()->name : null);
        $author = $author ? Str::limit(strip_tags($author), 120, '') : null;
        $content = trim(strip_tags($data['content']));

        if ($content === '') {
            return response()->json([
                'message' => 'Nội dung bình luận không được để trống.',
            ], 422);
        }

        $comment = $puzzle->comments()->create([
            'user_id' => Auth::id(),
            'parent_id' => $parentId,
            'author_name' => $author,
            'content' => $content,
            'is_public' => true,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'comment' => $this->transformComment($comment->load('replies')),
        ], 201);
    }

    public function likeComment(Request $request, string $slug, PuzzleComment $comment)
    {
        $puzzle = $this->getPuzzleOrFail($slug);

        if (!Schema::hasColumn('puzzle_comments', 'likes_count') || !Schema::hasTable('puzzle_comment_likes')) {
            return response()->json([
                'message' => 'Tính năng thích bình luận chưa được kích hoạt. Vui lòng thử lại sau.',
            ], 503);
        }

        if ((int) $comment->puzzle_id !== (int) $puzzle->id || !$comment->is_public) {
            abort(404);
        }

        $identifier = $this->buildCommentLikeIdentifier($request);
        $wasCreated = false;

        DB::transaction(function () use ($comment, $identifier, $request, &$wasCreated) {
            $like = PuzzleCommentLike::firstOrCreate(
                [
                    'puzzle_comment_id' => $comment->id,
                    'identifier' => $identifier,
                ],
                [
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                ]
            );

            if ($like->wasRecentlyCreated) {
                $comment->increment('likes_count');
                $wasCreated = true;
            }
        });

        $comment->refresh();

        return response()->json([
            'likes_count' => (int) $comment->likes_count,
            'already_liked' => !$wasCreated,
        ]);
    }

    protected function transformComment(PuzzleComment $comment)
    {
        $replies = $comment->relationLoaded('replies')
            ? $comment->replies
            : $comment->replies()->where('is_public', true)->orderBy('created_at')->get();

        return [
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'author_name' => $comment->author_name,
            'content' => $comment->content,
            'likes_count' => (int) ($comment->likes_count ?? 0),
            'created_at' => $comment->created_at,
            'replies' => $replies->map(fn($reply) => $this->transformComment($reply))->values(),
        ];
    }

    protected function getPuzzleOrFail(?string $slug): Puzzle
    {
        if (!$slug) {
            abort(404);
        }

        return Puzzle::where('slug', $slug)->firstOrFail();
    }

    protected function buildCommentLikeIdentifier(Request $request): string
    {
        if (Auth::check()) {
            return 'user:' . Auth::id();
        }

        $ip = (string) $request->ip();
        $agent = (string) $request->userAgent();

        return 'guest:' . hash('sha256', $ip . '|' . $agent);
    }
}
