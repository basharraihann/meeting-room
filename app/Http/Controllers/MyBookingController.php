<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MyBookingController extends Controller
{
    /** Jumlah rapat per unit kerja di setiap halaman. */
    private const PER_UNIT = 5;

    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));
        $unitKerja = $request->query('unit_kerja');
        $now = now();

        // Ekspresi SQL dipakai berulang (semua kolom diberi prefix tabel)
        $unitExpr = "COALESCE(NULLIF(TRIM(bookings.unit_kerja), ''), '__none')";
        $activeCond = "bookings.status IN ('PENDING','APPROVED') AND bookings.end_at >= ?";

        // ===== Query dasar + semua filter (tanpa order & paginate) =====
        $base = Booking::query()
            ->where('bookings.pic_user_id', auth()->id())
            ->when($status, function ($qq) use ($status) {
                // ejaan lama/baru "CANCELED" / "CANCELLED" dianggap sama
                if (in_array(strtoupper($status), ['CANCELED', 'CANCELLED'], true)) {
                    return $qq->whereIn('bookings.status', ['CANCELED', 'CANCELLED']);
                }
                return $qq->where('bookings.status', $status);
            })
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $qq->where(function ($w) use ($like) {
                    $w->where('bookings.title', 'like', $like)
                        ->orWhere('bookings.unit_kerja', 'like', $like)
                        ->orWhere('bookings.description', 'like', $like)
                        ->orWhereHas('room', fn($r) => $r->where('name', 'like', $like));
                });
            })
            ->when($unitKerja, fn($qq) => $qq->where('bookings.unit_kerja', $unitKerja));

        // ===== 1) Jumlah sebenarnya per unit -> menentukan jumlah halaman =====
        $unitTotals = (clone $base)
            ->selectRaw("$unitExpr as u, COUNT(*) as c")
            ->groupByRaw($unitExpr)
            ->pluck('c', 'u');                       // ['Biro A' => 230, '__none' => 3, ...]

        $totalAll = (int) $unitTotals->sum();
        $lastPage = max(1, (int) ceil(($unitTotals->max() ?? 0) / self::PER_UNIT));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $from = ($page - 1) * self::PER_UNIT + 1;
        $to = $page * self::PER_UNIT;

        // ===== 2) Nomor urut per unit: aktif dulu (terdekat), lalu riwayat (terbaru) =====
        $rows = collect();
        if ($totalAll > 0) {
            $ranked = (clone $base)
                ->select('bookings.id')
                ->selectRaw("ROW_NUMBER() OVER (
                    PARTITION BY $unitExpr
                    ORDER BY CASE WHEN $activeCond THEN 0 ELSE 1 END,
                             CASE WHEN $activeCond THEN bookings.start_at END ASC,
                             bookings.start_at DESC,
                             bookings.id DESC
                ) as rn", [$now, $now]);

            $ids = DB::query()
                ->fromSub($ranked, 'r')
                ->whereBetween('rn', [$from, $to])
                ->pluck('id');

            // ===== 3) Ambil datanya (urutan sama seperti di atas) =====
            $rows = Booking::with(['room', 'room.tuUser'])
                ->whereIn('bookings.id', $ids)
                ->orderByRaw("CASE WHEN $activeCond THEN 0 ELSE 1 END", [$now])
                ->orderByRaw("CASE WHEN $activeCond THEN bookings.start_at END ASC", [$now])
                ->orderByDesc('bookings.start_at')
                ->orderByDesc('bookings.id')
                ->get();
        }

        // ===== 4) Paginator manual: "item" = nomor halaman =====
        $bookings = new LengthAwarePaginator($rows, $lastPage, 1, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        $unitKerjaOptions = Booking::where('pic_user_id', auth()->id())
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->distinct()
            ->pluck('unit_kerja')
            ->map(fn($u) => trim($u))
            ->unique()
            ->sort()
            ->values();

        return view('pic.my-bookings', compact(
            'bookings',
            'status',
            'q',
            'unitKerja',
            'unitKerjaOptions',
            'unitTotals',
            'totalAll',
            'lastPage'
        ));
    }
}