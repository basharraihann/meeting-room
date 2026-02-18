<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Riwayat Ajukan Rapat
            </h2>

            <a href="{{ route('calendar') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800">
                Kembali ke Kalender
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm rounded-2xl p-4 sm:p-6">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-4">
                    <div class="flex gap-2">
                        @php
                            $tabs = [
                                '' => 'Semua',
                                'PENDING' => 'Pending',
                                'APPROVED' => 'Approved',
                                'REJECTED' => 'Rejected',
                                'CANCELED' => 'Canceled',
                            ];
                        @endphp

                        @foreach($tabs as $key => $label)
                            <a href="{{ route('my_bookings.index', array_filter(['status' => $key, 'q' => $q])) }}"
                                class="px-3 py-2 rounded-xl text-sm
                                   {{ ($status === $key || (empty($status) && $key === '')) ? 'bg-indigo-600 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-800' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex gap-2">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari judul rapat..."
                            class="border rounded-xl px-3 py-2 w-full sm:w-72" />
                        @if($status)
                            <input type="hidden" name="status" value="{{ $status }}">
                        @endif
                        <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
                            Cari
                        </button>
                    </div>
                </form>

                <div class="divide-y">
                    @forelse($bookings as $b)
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 truncate">{{ $b->title }}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Ruang: <span class="font-medium">{{ $b->room?->name ?? '-' }}</span> •
                                    {{ \Carbon\Carbon::parse($b->start_at)->translatedFormat('d M Y H:i') }}
                                    —
                                    {{ \Carbon\Carbon::parse($b->end_at)->translatedFormat('H:i') }}
                                </div>

                                @if($b->description)
                                    <div class="text-sm text-gray-700 mt-1 line-clamp-2">
                                        {{ $b->description }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                           @php
    $now = now();

    // default display
    $displayStatus = strtoupper($b->status);

    // DONE logic (approved but already ended)
    $isDone = ($b->status === 'APPROVED' && \Carbon\Carbon::parse($b->end_at)->lt($now));
    if ($isDone) {
        $displayStatus = 'DONE';
    }

    // badge color (DONE beda dari CANCELED)
    if ($displayStatus === 'DONE') {
        $badge = 'bg-blue-100 text-blue-700';   // ✅ DONE jadi biru muda
    } else {
        $badge = match ($b->status) {
            'APPROVED' => 'bg-green-100 text-green-700',
            'PENDING'  => 'bg-yellow-100 text-yellow-700',
            'REJECTED' => 'bg-red-100 text-red-700',
            'CANCELED' => 'bg-gray-200 text-gray-700', // ✅ CANCELED tetap abu
            default    => 'bg-gray-100 text-gray-700',
        };
    }
@endphp

<span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">
    {{ $displayStatus }}
</span>

{{-- Cancel button: only if PENDING/APPROVED AND NOT DONE --}}
@if(in_array($b->status, ['PENDING','APPROVED']) && !$isDone)
    <button type="button"
        class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-800 text-white hover:bg-gray-900"
        onclick="openCancelModal({{ $b->id }})">
        Cancel
    </button>
@endif

                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-gray-500">
                            Belum ada riwayat rapat.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $bookings->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- Cancel Modal --}}
    <div id="cancelModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Cancel Booking</h3>
                <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeCancelModal()">✕</button>
            </div>

            <form id="cancelForm" method="POST" class="mt-4">
                @csrf

                <label class="block text-sm text-gray-700 mb-1">Cancel Reason</label>
                <textarea name="cancel_reason" rows="4" required
                          class="w-full border rounded-xl px-3 py-2"
                          placeholder="Enter the reason for cancellation..."></textarea>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button"
                            class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200"
                            onclick="closeCancelModal()">
                        Close
                    </button>

                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-gray-800 text-white hover:bg-gray-900">
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(bookingId) {
            const modal = document.getElementById('cancelModal');
            const form = document.getElementById('cancelForm');
            form.action = `/bookings/${bookingId}/cancel`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCancelModal() {
            const modal = document.getElementById('cancelModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-app-layout>
