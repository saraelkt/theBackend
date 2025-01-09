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

        // Ajouter une valeur fictive pour le mot de passe
        $user->password = '*****';

        // Ajouter une URL complète pour l'image de profil
        $user->profile_image_url = $user->image
            ? url('storage/' . $user->image)
            : url('https://via.placeholder.com/150'); // Utiliser une image par défaut si aucune image

        // Ajouter une URL complète pour les images des articles
        $user->articles->map(function ($article) {
            $article->image_url = $article->image_path
                ? url('storage/' . $article->image_path)
                : url('https://via.placeholder.com/150'); // Utiliser une image par défaut
            return $article;
        });

        return response()->json([
            'user' => $user,
            'articles' => $user->articles,
        ]);
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Valider les données entrantes
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:6',
            'bio' => 'nullable|string|max:500',
        ]);

        // Mettre à jour les données utilisateur
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->bio = $validatedData['bio'] ?? $user->bio;

        // Mettre à jour le mot de passe seulement s'il est fourni
        if (!empty($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function updateProfileImage(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Valider l'image uploadée
        $validatedData = $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($user->image && file_exists(public_path('storage/' . $user->image))) {
                unlink(public_path('storage/' . $user->image)); // Supprime le fichier de `public/storage`
            }

            // Conserver le nom d'origine du fichier
            $originalName = $request->file('profile_image')->getClientOriginalName();

            // Déplacer le fichier dans "public/storage"
            $request->file('profile_image')->move(public_path('storage'), $originalName);

            // Stocker uniquement le nom du fichier dans la base de données
            $user->image = $originalName;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile image updated successfully',
            'image' => $user->image, // Renvoie le chemin brut
        ]);
    }
}
