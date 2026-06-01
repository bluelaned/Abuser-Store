<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('last_seen', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('discord_id', 'like', "%{$search}%");
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function searchJson(Request $request)
    {
        $q = $request->get('q', '');
        $query = User::orderBy('last_seen', 'desc');

        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('discord_id', 'like', "%{$q}%");
            });
        }

        $users = $query->limit(10)->get()->map(function($u) {
            $isOnline = $u->last_seen && \Carbon\Carbon::parse($u->last_seen)->diffInMinutes(now()) < 5;
            return [
                'id'               => $u->id,
                'name'             => $u->name,
                'email'            => $u->email,
                'discord_id'       => $u->discord_id ?? 'No data',
                'avatar'           => $u->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($u->name).'&background=random',
                'role'             => $u->role,
                'is_online'        => $isOnline,
                'last_seen'        => $u->last_seen ? \Carbon\Carbon::parse($u->last_seen)->diffForHumans() : 'Never',
                'last_ip'          => $u->last_ip ?? 'Unknown',
                'isp'              => $u->isp ?? 'Unknown ISP',
                'device_type'      => $u->device_type ?? '',
                'os'               => $u->os ?? 'Unknown OS',
                'browser'          => $u->browser ?? 'Unknown Browser',
                'location_flag'    => $u->location_flag ?? null,
                'location_city'    => $u->location_city ?? 'Unknown',
                'location_country' => $u->location_country ?? 'Unknown',
                'profile_url'      => route('profile.show', ['name' => strtolower($u->name), 'id' => $u->id]),
            ];
        });

        return response()->json($users);
    }

    public function history($id)
    {
        $user = User::findOrFail($id);
        $transactions = \App\Models\Transaction::where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->get();
        return response()->json($transactions);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'email' => $request->email,
            'role' => $request->role,
        ]);
        \App\Models\AdminLog::record('updated', 'user', $user->id, "Updated user role/email: {$user->name} → role={$request->role}");
        return back()->with('success', "Data {$user->name} berhasil diperbarui!");
    }

    public function destroy($id)
    {
        $user = User::find($id);
        \App\Models\AdminLog::record('deleted', 'user', $id, 'Deleted user: ' . ($user->name ?? 'Unknown'));
        User::destroy($id);
        return back()->with('success', 'User dibasmi!');
    }

    public function growthData(Request $request)
    {
        $period = $request->input('period', 'last30');
        $labels = [];
        $data   = [];

        if ($period === 'last30') {
            for ($i = 29; $i >= 0; $i--) {
                $date     = now()->subDays($i);
                $labels[] = $date->format('d M');
                $data[]   = \App\Models\User::whereDate('created_at', $date->format('Y-m-d'))->count();
            }
        } elseif ($period === 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = \Carbon\Carbon::create(now()->year, $m, 1)->format('M Y');
                $data[]   = \App\Models\User::whereYear('created_at', now()->year)
                    ->whereMonth('created_at', $m)
                    ->count();
            }
        }

        $totalUsers   = \App\Models\User::count();
        $newThisMonth = \App\Models\User::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $newToday     = \App\Models\User::whereDate('created_at', today())->count();

        return response()->json([
            'labels'         => $labels,
            'data'           => $data,
            'total_users'    => $totalUsers,
            'new_this_month' => $newThisMonth,
            'new_today'      => $newToday,
        ]);
    }
}
