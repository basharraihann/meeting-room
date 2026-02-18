<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approval Inbox (TU)
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Error Message --}}
            @if ($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- 2 columns: Sidebar + Content --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- Sidebar: Filter Ruang --}}
                <div class="lg:col-span-3">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <div class="font-semibold text-gray-900">Filter Ruang</div>
                        <div class="text-sm text-gray-600 mt-1">Klik untuk melihat request per ruang.</div>

                        <div class="mt-4 space-y-1">
                            <a href="{{ route('approvals.index') }}"
                               class="flex items-center justify-between px-3 py-2 rounded-xl
                               {{ empty($roomId) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'hover:bg-gray-50' }}">
                                <span>Semua Ruang</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                    {{ $pendingCounts->sum() }}
                                </span>
                            </a>

                            @foreach($rooms as $r)
                                @php $cnt = (int) ($pendingCounts[$r->id] ?? 0); @endphp
                                <a href="{{ route('approvals.index', ['room_id' => $r->id]) }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-xl
                                   {{ (string)$roomId === (string)$r->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'hover:bg-gray-50' }}">
                                    <span>{{ $r->name }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $cnt ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $cnt }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="lg:col-span-9">
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="p-4 border-b font-semibold">
                            Pending Requests ({{ $clusters->sum(fn($c) => $c['items']->count()) }})
                        </div>

                        <div class="p-4 sm:p-5 space-y-4">

                            @forelse ($clusters as $c)

                                <div class="rounded-2xl border border-gray-200 overflow-hidden">
                                    {{-- Cluster Header --}}
                                    <div class="p-4 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <div>
                                            <div class="font-semibold text-gray-900">
                                                {{ $c['room_name'] ?? 'Ruang #' . $c['room_id'] }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-0.5">
                                                {{ \Carbon\Carbon::parse($c['start'])->format('d M Y H:i') }}
                                                —
                                                {{ \Carbon\Carbon::parse($c['end'])->format('H:i') }}
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            @php $count = $c['items']->count(); @endphp
                                            <span
                                                class="px-3 py-1 rounded-full text-xs font-semibold
                                                {{ $count > 1 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $count > 1 ? 'BENTROK (' . $count . ')' : 'TUNGGAL (1)' }}
                                            </span>

                                            @if($count > 1)
                                                <span class="text-xs text-amber-700">
                                                    Pilih salah satu untuk APPROVE.
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Cluster Items: disandingkan --}}
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                            @foreach ($c['items'] as $b)
                                                <div class="rounded-2xl border border-gray-200 p-4">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <div class="font-semibold text-lg text-gray-900 truncate">
                                                                {{ $b->title }}
                                                            </div>

                                                            <div class="mt-1 text-sm text-gray-600">
                                                                Ruang: <b class="text-gray-800">{{ $b->room->name }}</b>
                                                                <span class="mx-1">•</span>
                                                                PIC: <b class="text-gray-800">{{ $b->pic->name }}</b>
                                                            </div>

                                                            <div class="text-sm text-gray-600 mt-1">
                                                                {{ \Carbon\Carbon::parse($b->start_at)->format('d M Y H:i') }}
                                                                —
                                                                {{ \Carbon\Carbon::parse($b->end_at)->format('H:i') }}
                                                            </div>
                                                        </div>

                                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                                            PENDING
                                                        </span>
                                                    </div>

                                                    @if($b->description)
                                                        <div class="mt-3 text-sm text-gray-700 whitespace-pre-wrap">
                                                            {{ $b->description }}
                                                        </div>
                                                    @endif

                                                    {{-- Actions --}}
                                                    <div class="mt-4 flex items-center justify-end gap-2">
                                                        <form method="POST" action="{{ route('approvals.approve', $b) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700 transition shadow-sm">
                                                                ✅ Approve
                                                            </button>
                                                        </form>

                                                        {{-- Reject modal trigger --}}
                                                        <div x-data="{ open:false }">
                                                            <button type="button" @click="open=true"
                                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 transition shadow-sm">
                                                                ✖ Reject
                                                            </button>

                                                            {{-- Modal --}}
                                                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                                                                <div class="absolute inset-0 bg-black/50" @click="open=false"></div>

                                                                <div class="relative w-full max-w-md mx-4 bg-white rounded-2xl shadow-xl overflow-hidden">
                                                                    <div class="p-5 border-b flex items-start justify-between">
                                                                        <div>
                                                                            <div class="font-semibold text-gray-900">Reject request</div>
                                                                            <div class="text-sm text-gray-500 mt-1">
                                                                                {{ $b->title }} • {{ $b->room->name }}
                                                                            </div>
                                                                        </div>
                                                                        <button class="text-gray-400 hover:text-gray-600" @click="open=false">✕</button>
                                                                    </div>

                                                                    <form method="POST" action="{{ route('approvals.reject', $b) }}">
                                                                        @csrf
                                                                        <div class="p-5 space-y-3">
                                                                            <label class="text-sm font-medium text-gray-700">
                                                                                Alasan reject
                                                                            </label>
                                                                            <textarea name="tu_note" required
                                                                                class="w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-red-200 focus:border-red-400"
                                                                                rows="3"
                                                                                placeholder="Tulis alasan reject..."></textarea>
                                                                        </div>

                                                                        <div class="p-5 border-t flex justify-end gap-2">
                                                                            <button type="button"
                                                                                class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200"
                                                                                @click="open=false">
                                                                                Batal
                                                                            </button>
                                                                            <button type="submit"
                                                                                class="px-4 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700">
                                                                                Kirim Reject
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- End reject --}}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <div class="p-6 text-gray-500 text-center">
                                    Tidak ada request pending.
                                </div>
                            @endforelse

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
