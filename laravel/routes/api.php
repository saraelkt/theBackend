<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');


// Routes publiques
Route::match(['GET', 'POST'], '/register', [AuthController::class, 'register']);
 // Inscription
Route::post('/login', [AuthController::class, 'login'])->name('login'); // Connexion
 
// Routes protégées par Sanctum
Route::options('{any}', function () {
    return response()->json([], 204);
})->where('any', '.*');
Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::get('/articles/{id}/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']); // Créer un commentaire

    Route::get('/user/{id}/profile-with-articles', [UserController::class, 'getProfileWithArticles']);
    Route::put('/user/{id}/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/user/{id}/update-profile-image', [UserController::class, 'updateProfileImage'])->name('user.updateProfileImage');

   Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
 

   Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index'); 
    Route::get('/articles/{id}', [ArticleController::class, 'show'])->name('articles.show'); // Afficher un article spécifique
    Route::put('/articles/{id}', [ArticleController::class, 'update'])->name('articles.update'); // Mise à jour d'un article
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy'])->name('articles.destroy'); // Suppression d'un article

    // Récupération des informations de l'utilisateur connecté
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });    

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/comments/{commentId}/like', [CommentController::class, 'toggleLike']);
    Route::post('/articles/{id}/like', [ArticleController::class, 'toggleLike']);
});