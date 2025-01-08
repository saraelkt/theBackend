<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
class ArticleController extends Controller
{ /**
    * Liste tous les articles.
    */
   public function index()
   {
       $articles = Article::all();
       $articles->map(function ($article) {
        $article->image_url = url('storage/' . $article->image_path);
        return $article;
    }); // Récupère tous les articles
       return response()->json($articles, 200); // Retourne les articles en JSON
   }

   /**
    * Crée un nouvel article.
    */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:255',
            'published_at'=>'required',
            'content' => 'required',
            'category' => 'required',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        if ($request->hasFile('image_path')) {
            $validatedData['image_path'] = $request->file('image_path')->store('articles', 'public');
        }
    
        $article = Article::create($validatedData);
    
        return response()->json($article, 201);
    }
    

   /**
    * Affiche un article spécifique.
    */
    public function show($id)
    {
        $article = Article::with('user')->find($id);
    
        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }
    
        return response()->json($article, 200);
    }    

   /**
    * Met à jour un article.
    */
   public function update(Request $request, $id)
   {
       $article = Article::find($id);

       if (!$article) {
           return response()->json(['message' => 'Article not found'], 404);
       }

       // Valider les données mises à jour
       $validatedData = $request->validate([
           'title' => 'sometimes|required|max:255',
           'content' => 'sometimes|required',
           'category' => 'sometimes|required',
           'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
           'author' => 'nullable|string|max:255',
        'published_at' => 'nullable|date',
       ]);

       // Gérer l'upload de l'image si elle existe
       if ($request->hasFile('image_path')) {
           $validatedData['image_path'] = $request->file('image_path')->store('articles', 'public');
       }

       // Mettre à jour les données
       $article->update($validatedData);

       return response()->json($article, 200); // Retourne l'article mis à jour
   }

   /**
    * Supprime un article.
    */
   public function destroy($id)
   {
       $article = Article::find($id);

       if (!$article) {
           return response()->json(['message' => 'Article not found'], 404);
       }

       $article->delete(); // Supprime l'article

       return response()->json(['message' => 'Article deleted successfully'], 200);
   }
}
