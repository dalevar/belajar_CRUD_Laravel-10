<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(5); // Get the latest 5 posts from the database
        //latest is a query scope that orders the posts by their created_at column in descending order
        //paginate(5) is a method that paginates the results, meaning that it will split the results into multiple pages, with 5 results per page


        return view('posts.index', compact('posts')); // render view with posts
    }

    public function create()
    {
        return view('posts.create'); // render view to create a new post
    }

    public function store(Request $request) // Request is a class that contains the data from the form
    {
        // validate the data from the form
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // validate image
            'title' => 'required|min:5', // validate title
            'content' => 'required|min:10' // validate content
        ]);

        // upload image
        $image = $request->file('image'); // get image from request
        $image->storeAs('public/posts', $image->hashName()); // store image in storage/app/public/posts folder

        // create post
        Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Disimpan!']); // redirect to posts.index after successfully saving the post, with success message
    }

    public function show($id)
    {
        // get post by id
        $post = Post::findOrFail($id); // get post by id

        return view('posts.show', compact('post')); // render view to show post detail
    }

    public function edit(string $id): View
    {
        // get post by id
        $post = Post::findOrFail($id); // get post by id

        return view('posts.edit', compact('post')); // render view to edit post
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        // validate Form
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // validate image
            'title' => 'required|min:5', // validate title
            'content' => 'required|min:10' // validate content
        ]);

        // get post by id
        $post = Post::findOrFail($id); // get post by id

        // check if request has image
        if ($request->hasFile('image')) {
            // upload new image
            $image = $request->file('image'); // get image from request
            $image->storeAs('public/posts', $image->hashName()); // store image in storage/app/public/posts folder

            // delete old image
            Storage::delete('public/posts/' . $post->image); // delete old image from storage

            // update post with new image
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {
            // update post without new image
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Diupdate!']); // redirect to posts.index after successfully updating the post, with success message
    }

    public function destroy(string $id)
    {
        // get post by id
        $post = Post::findOrFail($id); // get post by id

        // delete post
        Storage::delete('public/posts/' . $post->image); // delete image from storage
        $post->delete(); // delete post from database

        return redirect()->route('posts.index')->with(['success' => 'Data Berhasil Dihapus!']); // redirect to posts.index after successfully deleting the post, with success message
    }
}
