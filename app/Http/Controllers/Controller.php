<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // public static function getView($view)
    // {
    //     if (request()->segment(1) == 'amp') {
    //         if (view()->exists('amp.' . $view)) {
    //             $view = 'amp.' . $view;
    //         } else {
    //             abort(404);
    //         }
    //     }
    //     return $view;
    // }
    // public static function getUrl($url)
    // {
    //     if (request()->segment(1) == 'amp') {
    //         $url = '/amp' . $url;
    //     }
    //     return $url;
    // }
}
