<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use App\Models\AdminLog;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function index()
    {
        $pages = StaticPage::all()->keyBy('slug');
        return view('admin.static_pages', compact('pages'));
    }

    public function update(Request $request, $slug)
    {
        if (!in_array($slug, ['tos', 'privacy'])) {
            abort(404);
        }

        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        StaticPage::updateOrCreate(
            ['slug' => $slug],
            ['title' => $request->title, 'content' => $request->content]
        );

        AdminLog::record('updated', 'static_page', null, "Updated static page: {$slug}");

        return back()->with('success', 'Halaman \'' . $slug . '\' berhasil diperbarui!');
    }

    public function showTos()
    {
        $page = StaticPage::where('slug', 'tos')->first();
        return view('tos', compact('page'));
    }

    public function showPrivacy()
    {
        $page = StaticPage::where('slug', 'privacy')->first();
        return view('privacy', compact('page'));
    }
}
