<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->input('mode', 'day');
        $date = $request->input('date', now()->toDateString());
        $unitKerja = $request->input('unit_kerja');
        $base = Carbon::parse($date);

        if ($mode === 'week') {
            $start = $base->copy()->startOfWeek();
            $end = $base->copy()->endOfWeek();
            $title = "Minggu ini (" . $start->format('d M') . " - " . $end->format('d M Y') . ")";
        } elseif ($mode === 'month') {
            $start = $base->copy()->startOfMonth();
            $end = $base->copy()->endOfMonth();
            $title = "Bulan ini (" . $base->format('F Y') . ")";
        } else {
            $start = $base->copy()->startOfDay();
            $end = $base->copy()->endOfDay();
            $title = "Hari ini (" . $base->format('d M Y') . ")";
        }

        $bookings = Booking::with('room')
            ->where('pic_user_id', Auth::id())
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where('start_at', '<=', $end)
            ->where('end_at', '>=', $start)
            ->when($unitKerja, fn($q) => $q->where('unit_kerja', $unitKerja))
            ->orderBy('start_at')
            ->get();

        // Semua unit_kerja unik milik user ini
        $unitKerjaOptions = Booking::where('pic_user_id', Auth::id())
            ->whereNotNull('unit_kerja')
            ->distinct()
            ->pluck('unit_kerja')
            ->sort()
            ->values();

        // Summary buat copas
        $summaryLines = [];
        $summaryLines[] = "Agenda Rapat - " . $title;
        $summaryLines[] = "PIC: " . Auth::user()->name;
        $summaryLines[] = "--------------------------------";

        if ($bookings->isEmpty()) {
            $summaryLines[] = "Tidak ada rapat.";
        } else {
            foreach ($bookings as $b) {
                $d = Carbon::parse($b->start_at)->format('d M');
                $time = Carbon::parse($b->start_at)->format('H:i') . "-" . Carbon::parse($b->end_at)->format('H:i');
                $room = $b->room?->name ?? '-';
                $summaryLines[] = "{$d} {$time} | {$b->title} | {$room} | {$b->status}";
            }
        }

        $summaryText = implode("\n", $summaryLines);

        return view('agenda.index', [
            'bookings' => $bookings,
            'date' => $date,
            'mode' => $mode,
            'title' => $title,
            'summaryText' => $summaryText,
            'unitKerja' => $unitKerja,
            'unitKerjaOptions' => $unitKerjaOptions,
        ]);
    }
}