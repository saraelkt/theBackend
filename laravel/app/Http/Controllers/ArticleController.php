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
        $article->image_url = $article->image_path
                ? url('storage/' . $article->image_path)
                : url('https://via.placeholder.com/150'); // Utiliser une image par défaut si aucune image
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
            
            'title' => 'required|max:255|nullable',
            
            'published_at'=>'required|nullable',
            'content' => 'required|nullable',
            'category' => 'required|nullable',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $validatedData['user_id'] = auth()->id();
        $user = auth()->user();
        if ($user) {
            $validatedData['author'] = $user->name; // Remplit automatiquement le champ `author`
        }
       
    
        if ($request->hasFile('image_path')) {
            // Conserver le nom original du fichier pour plus de lisibilité
            $originalName = $request->file('image_path')->getClientOriginalName();

            // Déplacer le fichier dans "public/storage"
            $request->file('image_path')->move(public_path('storage'), $originalName);

            // Stocker uniquement le nom du fichier dans la base de données
            $validatedData['image_path'] = $originalName;
        }
        
    
        $article = Article::create($validatedData);
    
        return response()->json($article, 201);
       
    }
    

   /**
    * Affiche un article spécifique.
    */
    public function show($id)
    {
        \Log::info('Requête show reçue pour l\'article :', ['id' => $id, 'user' => auth()->user()]);
        $user = auth()->user(); // Utilisateur connecté
        $article = Article::with('user', 'comments')->find($id);
    
        if (!$article) {
            \Log::error('Article non trouvé :', ['id' => $id]);
            return response()->json(['message' => 'Article not found'], 404);
        }
    
        \Log::info('Article trouvé :', ['article' => $article]);

        $article->comments_count = $article->comments()->count(); // Total des commentaires
        $article->userLiked = $article->likedBy()->where('user_id', $user->id)->exists(); // Si l'utilisateur a liké
        
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

       // Supprimer l'image associée si elle existe
       if ($article->image_path && file_exists(public_path('storage/' . $article->image_path))) {
        unlink(public_path('storage/' . $article->image_path)); // Supprime le fichier
    }

       $article->delete(); // Supprime l'article

       return response()->json(['message' => 'Article deleted successfully'], 200);
   }

   public function toggleLike($id)
{
    $user = auth()->user();
    $article = Article::find($id);

    if (!$article) {
        return response()->json(['error' => 'Article not found'], 404);
    }

    // Vérifier si l'utilisateur a déjà liké cet article
    $alreadyLiked = $article->likedBy()->where('user_id', $user->id)->exists();

    if ($alreadyLiked) {
        $article->likedBy()->detach($user->id);
        $article->likes -= 1;
        $article->save();
        return response()->json([
            'liked' => false,
            'likes' => $article->likes,
        ]);
    }

    // Si pas encore liké, ajouter un like
    $article->likedBy()->attach($user->id);
    $article->likes += 1;
    $article->save();

    return response()->json([
        'liked' => true,
        'likes' => $article->likes,
    ]);
}

}
