<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;

// Routes publiques
Route::post('/login', [AuthController::class, 'login']); // Route de connexion
Route::post('/register', [AuthController::class, 'register']); // Route d'inscription

// Routes protégées par auth:sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']); // Liste tous les articles
    Route::post('/articles', [ArticleController::class, 'store']); // Crée un nouvel article
    Route::get('/articles/{id}', [ArticleController::class, 'show']); // Affiche un article spécifique
    Route::put('/articles/{id}', [ArticleController::class, 'update']); // Met à jour un article
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']); // Supprime un article
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']); // Déconnexion
});
