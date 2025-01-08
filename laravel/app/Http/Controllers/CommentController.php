<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{   
    public function index($articleId)
    {
        $comments = Comment::where('article_id', $articleId)
            ->whereNull('parent_id') // Seulement les commentaires principaux
            ->with('replies') // Charger les réponses
            ->orderBy('created_at', 'desc') // Trier par date
            ->get();
    
        return response()->json($comments);
    }
    
    public function store(Request $request)
    {   dd(auth()->id());
         
        $request->validate([
            'article_id' => 'required|exists:articles,id', // Lien avec l'article
            'user_id' => 'required|exists:users,id', // Lien avec l'utilisateur
            'content' => 'required|string|min:1', // Contenu du commentaire
        ]);

        $comment = Comment::create($request->all());
        return response()->json($comment, 201); // Retourne le commentaire créé avec un code 201
    }

}
