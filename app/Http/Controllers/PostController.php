<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Menampilkan daftar postingan
     */
    public function index(): View
    {
        $posts = Post::latest()->paginate(5);
        return view('posts.index', compact('posts'));
    }

    /**
     * Form tambah postingan
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * Simpan postingan baru
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'image'   => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'   => 'required|min:5',
            'content' => 'required|min:10',
        ]);

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        Post::create([
            'image'   => $image->hashName(),
            'title'   => $request->title,
            'content' => $request->content,
        ]);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Data berhasil disimpan!');
    }

    /**
     * Detail postingan
     */
    public function show(string $id): View
    {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }

    /**
 * Hapus postingan
 */
public function destroy(string $id): RedirectResponse
{
    // cari post berdasarkan id
    $post = Post::findOrFail($id);

    // hapus gambar dari storage
    if ($post->image && \Storage::exists('public/posts/' . $post->image)) {
        \Storage::delete('public/posts/' . $post->image);
    }

    // hapus data post dari database
    $post->delete();

    // redirect ke index dengan pesan sukses
    return redirect()
        ->route('posts.index')
        ->with('success', 'Data berhasil dihapus!');
}

/**
 * Form edit postingan
 */
public function edit(string $id): View
{
    // ambil data berdasarkan id
    $post = Post::findOrFail($id);

    return view('posts.edit', compact('post'));
}

/**
 * Update postingan
 */
public function update(Request $request, string $id): RedirectResponse
{
    $request->validate([
        'title'   => 'required|min:5',
        'content' => 'required|min:10',
        'image'   => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
    ]);

    $post = Post::findOrFail($id);

    // kalau ada gambar baru
    if ($request->hasFile('image')) {
        // hapus gambar lama
        if ($post->image && \Storage::exists('public/posts/' . $post->image)) {
            \Storage::delete('public/posts/' . $post->image);
        }

        // simpan gambar baru
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // update gambar di database
        $post->image = $image->hashName();
    }

    // update data lain
    $post->title   = $request->input('title');
    $post->content = $request->input('content');
    $post->save();

    return redirect()
        ->route('posts.index')
        ->with('success', 'Data berhasil diupdate!');
}

}
