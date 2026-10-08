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
            $title = "Minggu ini (" . $start->translatedFormat('d M') . " - " . $end->translatedFormat('d M Y') . ")";
        } elseif ($mode === 'month') {
            $start = $base->copy()->startOfMonth();
            $end = $base->copy()->endOfMonth();
            $title = "Bulan ini (" . $base->translatedFormat('F Y') . ")";
        } else {
            $start = $base->copy()->startOfDay();
            $end = $base->copy()->endOfDay();
            $title = "Hari ini (" . $base->translatedFormat('d M Y') . ")";
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

        $summaryText = $this->buildSummary($bookings, $title, $mode);

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

    /**
     * Ringkasan teks untuk di-copy-paste (WhatsApp friendly), dikelompokkan per unit kerja.
     */
    private function buildSummary($bookings, string $title, string $mode): string
    {
        $statusLabel = [
            'APPROVED' => 'Disetujui',
            'PENDING' => 'Menunggu',
        ];

        $lines = [];
        $lines[] = "*Agenda Rapat - {$title}*";
        $lines[] = "PIC: " . Auth::user()->name;

        if ($bookings->isEmpty()) {
            $lines[] = "";
            $lines[] = "Tidak ada rapat.";
            return implode("\n", $lines);
        }

        $noUnit = 'Tanpa unit kerja';

        $byUnit = $bookings
            ->groupBy(fn($b) => filled($b->unit_kerja) ? trim($b->unit_kerja) : $noUnit)
            ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);

        // "Tanpa unit kerja" di paling bawah
        if ($byUnit->has($noUnit)) {
            $none = $byUnit->pull($noUnit);
            $byUnit->put($noUnit, $none);
        }

        foreach ($byUnit as $unit => $items) {
            $lines[] = "";
            $lines[] = "*{$unit}* ({$items->count()} rapat)";

            foreach ($items as $b) {
                $s = Carbon::parse($b->start_at);
                $e = Carbon::parse($b->end_at);

                // Mode hari: tanggal tidak perlu diulang
                $prefix = $mode === 'day' ? '' : $s->translatedFormat('D, d M') . ' ';
                $time = $s->format('H.i') . '-' . $e->format('H.i');
                $room = $b->room?->name ?? '-';
                $status = $statusLabel[$b->status] ?? $b->status;

                $lines[] = "• {$prefix}{$time} | {$b->title} | {$room} | {$status}";
            }
        }

        return implode("\n", $lines);
    }
}