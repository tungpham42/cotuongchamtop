<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostsController extends Controller
{
    public function index(): View
    {
        return view('posts.index', [
            'posts' => Post::published()->newest()->paginate(5),
        ]);
    }

    public function show(string $slug): View
    {
        return view('posts.show', [
            'post' => Post::slug($slug)->status('publish')->firstOrFail(),
        ]);
    }

    public static function getBooks()
    {
        $books = Post::taxonomy('category', 'thu-vien-sach')->orderBy('post_date', 'desc')->get();
        return $books;
    }

    public static function getPuzzles()
    {
        $puzzles = Post::taxonomy('category', 'the-co-dac-sac')->orderBy('post_date', 'desc')->paginate(12);
        return $puzzles;
    }

    public static function getFirstPagePuzzles()
    {
        $puzzles = Post::taxonomy('category', 'the-co-dac-sac')->orderBy('post_date', 'desc')->paginate(12, ['*'], 'page', 1);
        return $puzzles;
    }
    
    public static function getFenCodes()
    {
        $posts = Post::taxonomy('category', 'the-co-dac-sac')->orderBy('post_date', 'desc')->get();
        $fenCodes = array();
        $postsCount = count($posts);
        for ($i = 0; $i < $postsCount; $i++) {
            $fenCodes[$i]["puzzleTitle"] = $posts[$i]->post_title;
            $fenCodes[$i]["fenCode"] = $posts[$i]->post_excerpt;
        }
        return json_encode($fenCodes);
    }

    public static function getPuzzleTitle($fen)
    {
        $posts = Post::taxonomy('category', 'the-co-dac-sac')->orderBy('post_date', 'desc')->get();
        $fenCodes = array();
        $puzzleTitle = '';
        $postsCount = count($posts);
        for ($i = 0; $i < $postsCount; $i++) {
            $fenCodes[$i]["puzzleTitle"] = $posts[$i]->post_title;
            $fenCodes[$i]["fenCode"] = $posts[$i]->post_excerpt;
        }
        if (strpos($fen, ' ')) {
            $fen = strstr($fen, ' ', true);
        }
        $fenCodesCount = count($fenCodes);
        for ($j = 0; $j < $fenCodesCount; $j++) {
            if ($fenCodes[$j]["fenCode"] == $fen) {
                $puzzleTitle = ' - '.$fenCodes[$j]["puzzleTitle"];
            }
        }
        return $puzzleTitle;
    }

    public static function getArticles()
    {
        $articles = Post::taxonomy('category', 'english-articles')->orderBy('post_date', 'desc')->paginate(12);
        return $articles;
    }

    public static function getInsuranceArticles()
    {
        $articles = Post::taxonomy('category', 'bao-hiem')->orderBy('post_date', 'desc')->get();
        return $articles;
    }

    public static function getVideos()
    {
        $videos = Post::taxonomy('category', 'video')->orderBy('post_date', 'desc')->get();
        return $videos;
    }
}