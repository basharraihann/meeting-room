<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // Agenda hari ini
        $todayBookings = Booking::with('room')
            ->where('pic_user_id', $user->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->whereDate('start_at', $now->toDateString())
            ->orderBy('start_at')
            ->get();

        // Rapat berikutnya (terdekat setelah sekarang)
        $nextBooking = Booking::with('room')
            ->where('pic_user_id', $user->id)
            ->where('status', 'APPROVED')
            ->where('start_at', '>', $now)
            ->orderBy('start_at')
            ->first();

        // Sedang berlangsung
        $activeBooking = Booking::with('room')
            ->where('pic_user_id', $user->id)
            ->where('status', 'APPROVED')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->first();

        // Upcoming minggu ini (besok s/d akhir minggu)
        $startOfTomorrow = $now->copy()->addDay()->startOfDay();
        $endOfWeek = $now->copy()->endOfWeek();
        $upcomingThisWeek = Booking::with('room')
            ->where('pic_user_id', $user->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->whereBetween('start_at', [$startOfTomorrow, $endOfWeek])
            ->orderBy('start_at')
            ->get()
            ->groupBy(fn($b) => Carbon::parse($b->start_at)->toDateString());

        // 5 pengajuan terbaru
        $recentBookings = Booking::with('room')
            ->where('pic_user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Pending count
        $pendingCount = Booking::where('pic_user_id', $user->id)
            ->where('status', 'PENDING')
            ->count();

        return view('dashboard', compact(
            'todayBookings',
            'nextBooking',
            'activeBooking',
            'upcomingThisWeek',
            'recentBookings',
            'pendingCount',
            'now',
        ));
    }
}