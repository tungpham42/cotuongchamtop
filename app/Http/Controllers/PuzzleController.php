<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use DataTables;

class PuzzleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getPuzzlesVi(Request $request)
    {
        if ($request->ajax()) {
            $puzzles = Puzzle::select(['id', 'name', 'slug', 'fen', 'rating', 'updated_at']);
            return Datatables::of($puzzles)
                ->addColumn('rank', function($row){
                    $puzzleRank = self::renderPuzzleRank($row->id);
                    return $puzzleRank;
                })
                ->addColumn('name', function($row){
                    $puzzleName = '<a class="text-danger animate showPromotion" style="cursor: pointer !important; text-decoration: none !important;" data-fen="'.$row->fen.'" data-slug="'.$row->slug.'" href="'.URL::to('/').'/the-co/'.$row->slug.'">'.$row->name.'</a>';
                    return $puzzleName;
                })
                ->addColumn('rating', function($row){
                    $puzzleRating = $row->rating;
                    return $puzzleRating;
                })
                ->addColumn('action', function($row){
                    $actionBtn = '<a class="btn btn-danger text-light mr-1 showPromotion" style="width: 140px;" data-fen="'.$row->fen.'" data-slug="'.$row->slug.'" href="'.URL::to('/').'/giai-co-the/'.$row->fen.' r - - 0 1"><i class="far fa-mouse"></i> Giải cờ thế</a>';
                    $actionBtn .= '<a class="ml-1 btn btn-warning previewBtn"><i class="far fa-eye""></i> Xem trước</a>';
                    return $actionBtn;
                })
                ->addColumn('time', function($row){
                    return date('Y-m-d | H:i:s', strtotime($row->updated_at));
                })
                ->escapeColumns([])
                ->orderColumn('name', 'name $1')
                ->orderColumn('rating', 'rating $1')
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

    public static function renderPuzzleRank($id)
    {
        $puzzleRating = Puzzle::find($id)->rating;
    
        $rank = Puzzle::where('rating', '>', $puzzleRating)->count() + 1;
    
        return $rank;
    }    

    public static function getUserPuzzles()
    {
        return Puzzle::select('name', 'slug', 'fen', 'rating', 'updated_at')
                        ->orderByDesc('updated_at')
                        ->paginate(6);
    } 

    public static function getFirstUserPuzzles()
    {
        return Puzzle::select('name', 'slug', 'fen', 'rating', 'updated_at')
                        ->orderByDesc('updated_at')
                        ->paginate(6, ['*'], 'page', 1);
    }    

    public static function getSitemapPuzzles()
    {
        return Puzzle::select('name', 'slug', 'fen', 'rating', 'updated_at')
                        ->orderByDesc('updated_at')
                        ->paginate(4096);
    }    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = [
            'slug' => $request->input('slug'),
            'fen' => $request->input('fen'),
            'rating' => $request->input('rating'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];
    
        $name = $request->input('name');
        
        Puzzle::updateOrInsert(['name' => $name], $data);
    }    

    public function checkUniqueName(Request $request) {
        $name = $request->input('name');
        $slug = $request->input('slug');
        $fen = $request->input('fen');

        if (!$name || $name === '') {
            return response()->json(['message' => 'Tên thế cờ không được để trống', 'code' => 0]);
        }

        $name_count = Puzzle::where('name', $name)->count();
        $slug_count = Puzzle::where('slug', $slug)->count();
        $fen_count = Puzzle::where('fen', $fen)->count();

        if ($name_count > 0) {
            return response()->json(['message' => 'Tên thế cờ đã tồn tại trong hệ thống, vui lòng chọn tên khác!', 'code' => 0]);
        } elseif ($slug_count > 0) {
            return response()->json(['message' => 'Đường dẫn thế cờ đã tồn tại trong hệ thống, vui lòng chọn tên thế cờ khác!', 'code' => 0]);
        } elseif ($fen_count > 0) {
            return response()->json(['message' => 'Bàn cờ đã tồn tại trong hệ thống, vui lòng xếp lại!', 'code' => 0]);
        }

        return response()->json(['message' => 'Tạo thế cờ thành công!', 'code' => 1]);
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
    public function show(Puzzle $puzzle, $name)
    {
        return Puzzle::where('name', $name)->value('fen');
    }
    
    public static function getFen($slug)
    {
        return Puzzle::where('slug', $slug)->value('fen');
    }
    
    public static function getName($slug)
    {
        return Puzzle::where('slug', $slug)->value('name');
    }

    public function upvote(Request $request)
    {
        $slug = $request->input('slug');
        Puzzle::where('slug', $slug)->increment('rating');
    }    

    public function downvote(Request $request)
    {
        $slug = $request->input('slug');
        Puzzle::where('slug', $slug)->decrement('rating');
    }

    public function totalRating(Request $request)
    {
        $slug = $request->input('slug');
        $rating = Puzzle::where('slug', $slug)->value('rating');
        return $rating;
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
