<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class BasePortailController extends Controller
{
    public function authorize($ability, $arguments = [])
    {
        $user = Auth::guard('portail')->user();

        if (!$user) {
            throw new AuthorizationException('User not authenticated');
        }

        if (!$user->can($ability)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
