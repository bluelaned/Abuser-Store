<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Admin: list semua announcement.
     */
    public function index()
    {
        $announcements = Announcement::with('creator')
                            ->orderByDesc('created_at')
                            ->get();
        return view('admin.announcements', compact('announcements'));
    }

    /**
     * Admin: simpan announcement baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'popup_style' => 'nullable|string|in:default,warning,success,promo',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);

        Announcement::create([
            'title'       => $request->title,
            'content'     => $request->content,
            'popup_style' => $request->popup_style ?? 'default',
            'is_active'   => $request->boolean('is_active'),
            'starts_at'   => $request->starts_at ?: null,
            'ends_at'     => $request->ends_at   ?: null,
            'created_by'  => Auth::id(),
        ]);

        \App\Models\AdminLog::record('created', 'announcement', null, "Created announcement: {$request->title}");

        return back()->with('success', 'Announcement created and ' . ($request->boolean('is_active') ? 'published!' : 'saved as draft.'));
    }

    /**
     * Admin: toggle aktif / nonaktif.
     */
    public function toggle($id)
    {
        $ann = Announcement::findOrFail($id);
        $ann->is_active = !$ann->is_active;
        $ann->save();

        return response()->json(['active' => $ann->is_active]);
    }

    /**
     * Admin: update existing announcement.
     */
    public function update(Request $request, $id)
    {
        $ann = Announcement::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'popup_style' => 'nullable|string|in:default,warning,success,promo',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);

        $ann->update([
            'title'       => $request->title,
            'content'     => $request->content,
            'popup_style' => $request->popup_style ?? 'default',
            'is_active'   => $request->boolean('is_active'),
            'starts_at'   => $request->starts_at ?: null,
            'ends_at'     => $request->ends_at   ?: null,
        ]);

        \App\Models\AdminLog::record('updated', 'announcement', $ann->id, "Updated announcement: {$ann->title}");

        return back()->with('success', 'Announcement updated successfully!');
    }

    /**
     * Admin: hapus announcement.
     */
    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();
        \App\Models\AdminLog::record('deleted', 'announcement', $id, "Deleted announcement ID: {$id}");
        return back()->with('success', 'Announcement deleted.');
    }

    /**
     * Admin: preview announcement before saving.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'popup_style' => 'nullable|string|in:default,warning,success,promo',
        ]);

        return response()->json([
            'id'          => 0,
            'title'       => $request->title,
            'content'     => $request->content,
            'popup_style' => $request->popup_style ?? 'default',
        ]);
    }

    /**
     * Public API: return active announcements as JSON (untuk popup user).
     */
    public function activeForUser()
    {
        $announcements = Announcement::active()
                            ->orderByDesc('created_at')
                            ->get(['id', 'title', 'content', 'popup_style', 'starts_at', 'ends_at']);

        return response()->json($announcements);
    }
}
