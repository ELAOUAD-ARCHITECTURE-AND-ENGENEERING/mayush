<?php

namespace App\Http\Middleware;

use Closure;

class IsAppUserUnbanned
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        if ($user->banned == 1) {
            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

            return response()->json([
                'result' => false,
                'status' => 'banned',
                'code' => 'ACCESS_DENIED',
                'message' => translate('user is banned')
            ], 403);
        }
        return $next($request);
    }
}
