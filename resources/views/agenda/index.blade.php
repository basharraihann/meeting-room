<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Agenda Saya
            </h2>

            <a href="{{ route('calendar') }}"
                class="px-3 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm">
                Kembali ke Calendar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Filter tanggal + quick range --}}
            <div
                class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <form method="GET" action="{{ route('agenda') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <input type="hidden" name="mode" value="{{ $mode ?? 'day' }}">

                    <div>
                        <label class="block text-sm text-gray-600">Tanggal</label>
                        <input type="date" name="date" value="{{ $date }}"
                            class="border rounded px-3 py-2 w-full sm:w-auto" />
                    </div>

                    <div class="flex gap-2 flex-wrap">
                        <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                            Lihat
                        </button>

                        <a href="{{ route('agenda', ['mode' => 'day', 'date' => now()->toDateString()]) }}"
                            class="px-4 py-2 rounded {{ (($mode ?? 'day') === 'day') ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                            Hari ini
                        </a>

                        <a href="{{ route('agenda', ['mode' => 'week', 'date' => now()->toDateString()]) }}"
                            class="px-4 py-2 rounded {{ (($mode ?? 'day') === 'week') ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                            Minggu ini
                        </a>

                        <a href="{{ route('agenda', ['mode' => 'month', 'date' => now()->toDateString()]) }}"
                            class="px-4 py-2 rounded {{ (($mode ?? 'day') === 'month') ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' }}">
                            Bulan ini
                        </a>
                    </div>
                </form>

                {{-- Copy summary --}}
                <button type="button" class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700"
                    onclick="copyAgenda()">
                    Copy agenda (buat pimpinan)
                </button>
            </div>

            {{-- List agenda --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b font-semibold">
                    Jadwal: {{ $title ?? \Carbon\Carbon::parse($date)->format('d M Y') }}
                </div>

                @if($bookings->isEmpty())
                    <div class="p-4 text-gray-600">
                        Tidak ada rapat pada periode ini.
                    </div>
                @else

                    @php
                        // Group by date kalau mode week/month biar enak dibaca
                        $isRange = in_array(($mode ?? 'day'), ['week', 'month']);
                        $grouped = $isRange
                            ? $bookings->groupBy(fn($b) => \Carbon\Carbon::parse($b->start_at)->toDateString())
                            : collect([\Carbon\Carbon::parse($date)->toDateString() => $bookings]);
                    @endphp

                    <div class="divide-y">
                        @foreach($grouped as $day => $items)
                            @if($isRange)
                                <div class="px-4 py-2 bg-gray-50 text-gray-800 font-semibold">
                                    {{ \Carbon\Carbon::parse($day)->translatedFormat('l, d M Y') }}
                                </div>
                            @endif

                            @foreach($items as $b)
                                <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            {{ $b->title }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ \Carbon\Carbon::parse($b->start_at)->format('H:i') }}
                                            -
                                            {{ \Carbon\Carbon::parse($b->end_at)->format('H:i') }}
                                            • Ruang: {{ $b->room?->name ?? '-' }}
                                        </div>

                                        @if($b->description)
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ $b->description }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-sm">
                                        @php
                                            $badge = match ($b->status) {
                                                'APPROVED' => 'bg-green-100 text-green-800',
                                                'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                'REJECTED' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded {{ $badge }}">
                                            {{ $b->status }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Hidden textarea buat copas --}}
            <textarea id="agendaText" class="hidden">{{ $summaryText }}</textarea>

        </div>
    </div>

    <script>
        function copyAgenda() {
            const text = document.getElementById('agendaText').value;
            navigator.clipboard.writeText(text).then(() => {
                alert('Agenda berhasil di-copy. Tinggal paste ke WA/Email pimpinan.');
            });
        }
    </script>
</x-app-layout>