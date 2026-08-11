<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tournament;

class TournamentCreator
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('slug');
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        if ($tournament->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, __('Bạn không có quyền quản lý giải đấu này.'));
        }

        return $next($request);
    }
}
