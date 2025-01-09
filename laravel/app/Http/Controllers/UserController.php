<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getProfileWithArticles($id)
    {
        $user = User::with('articles')->find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json([
            'user' => $user,
            'articles' => $user->articles,
        ]);
    }

}
