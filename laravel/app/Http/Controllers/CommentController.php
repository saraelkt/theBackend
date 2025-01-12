<?php

namespace App\Http\Controllers;
use App\Models\Comment; // Assurez-vous que ce chemin est correct

use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($articleId)
{   
    $userId = auth()->id(); // Récupère l'ID de l'utilisateur connecté
    $comments = Comment::where('article_id', $articleId)
        ->whereNull('parent_id')
        ->with(['user', 'replies.user', 'replies.replies.user', 'likedBy']) 
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($comment) use ($userId) {
            $comment->liked = $comment->likedBy->contains('id', $userId); // Vérifiez si l'utilisateur actuel a liké
            $comment->date = $comment->created_at->format('d/m/Y H:i'); // Ajoute un champ date formaté

            // Fonction récursive pour formater les dates et likes pour les réponses imbriquées
            $comment->replies = $this->formatReplies($comment->replies, $userId);

            return $comment;
        });

    return response()->json($comments);
}

private function formatReplies($replies, $userId)
{
    return $replies->map(function ($reply) use ($userId) {
        $reply->liked = $reply->likedBy->contains('id', $userId);
        $reply->date = $reply->created_at->format('d/m/Y H:i'); // Formater la date des réponses

        if ($reply->replies) {
            // Appeler récursivement la fonction pour formater les réponses imbriquées
            $reply->replies = $this->formatReplies($reply->replies, $userId);
        }

        return $reply;
    });
}


    

    public function store(Request $request)
{
    try {
        \Log::info('Received data:', $request->all()); // Log les données reçues
        $inappropriateWords = [
            'suicide', 'rape', 'murder', 'violence', 'terrorism', 'hate', 
            'kill', 'bomb', 'drugs', 'pornography', 'pedophile', 'molestation', 
            'abuse', 'torture', 'racist', 'nazi', 'genocide', 'isis', 'terrorist',
            'death', 'harm', 'weapon', 'bombing', 'execution', 'slavery',
            'terrorist attack', 'child abuse', 'sexual assault', 'sexual harassment', 
            'self-harm'
        ];
        foreach ($inappropriateWords as $word) {
            if (stripos($request->content, $word) !== false) {
                return response()->json([
                    'message' => 'Contenu inapproprié détecté dans le commentaire, ne peut pas être publié.'
                ], 422); // Erreur 422 - Contenu inapproprié
            }
        }
        $validatedData = $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content' => 'required|string|min:1',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $validatedData['user_id'] = auth()->id();

        $comment = Comment::create($validatedData);
        $comment->load('user');
        

        return response()->json($comment, 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation errors:', $e->errors()); // Log les erreurs de validation
        return response()->json([
            'errors' => $e->errors(),
        ], 422);
    }
}


    // public function getComments($articleId) {
    //     $comments = Comment::where('article_id', $articleId)->get();
    //     return response()->json($comments);
    // }
    
    public function toggleLike($commentId)
{
    $user = auth()->user();
    $comment = Comment::find($commentId);

    if (!$comment) {
        return response()->json(['error' => 'Comment not found'], 404);
    }

    // Vérifier si l'utilisateur a déjà liké ce commentaire
    $alreadyLiked = $comment->likedBy()->where('user_id', $user->id)->exists();

    if ($alreadyLiked) {
        // Supprimer le like
        $comment->likedBy()->detach($user->id);
        $comment->likes -= 1;
        $comment->save();

        return response()->json([
            'liked' => false,
            'likes' => $comment->likes,
        ]);
    }

    // Ajouter un like
    $comment->likedBy()->attach($user->id);
    $comment->likes += 1;
    $comment->save();

    return response()->json([
        'liked' => true,
        'likes' => $comment->likes,
    ]);
}   
}    