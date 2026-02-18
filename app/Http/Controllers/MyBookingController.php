<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class MyBookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status'); // PENDING / APPROVED / REJECTED / null
        $q = $request->query('q');
        $bookings = Booking::with(['room'])
            ->where('pic_user_id', auth()->id())
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($q, fn($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->orderByDesc('start_at')
            ->paginate(10)
            ->withQueryString();

        return view('pic.my-bookings', compact('bookings', 'status', 'q'));
    }
}
