<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class MyBookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = $request->query('q');
        $unitKerja = $request->query('unit_kerja');

        $bookings = Booking::with(['room', 'room.tuUser'])
            ->where('pic_user_id', auth()->id())
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($q, fn($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->when($unitKerja, fn($qq) => $qq->where('unit_kerja', $unitKerja))
            ->orderByDesc('start_at')
            ->paginate(10)
            ->withQueryString();

        $unitKerjaOptions = Booking::where('pic_user_id', auth()->id())
            ->whereNotNull('unit_kerja')
            ->distinct()
            ->pluck('unit_kerja')
            ->sort()
            ->values();

        return view('pic.my-bookings', compact('bookings', 'status', 'q', 'unitKerja', 'unitKerjaOptions'));
    }
}