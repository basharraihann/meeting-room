@php
    // ===== Default pilihan =====
    // Mulai dari slot 30 menit saat ini, durasi 1 jam.
    // Di luar jam kerja: >= 17.00 pindah ke besok 09.00, < 07.00 pindah ke hari ini 07.00.
    $defStart = now()->copy()->second(0);
    if ($defStart->hour >= 17) {
        $defStart = now()->copy()->addDay()->setTime(9, 0);
    } elseif ($defStart->hour < 7) {
        $defStart = now()->copy()->setTime(7, 0);
    } else {
        $defStart->minute(intdiv($defStart->minute, 30) * 30);
    }
    $defEnd = $defStart->copy()->addHour();

    // Pilihan jam: 07.00 – 20.00 per 30 menit (format 24 jam, WIB)
    $jamOpsi = [];
    for ($m = 7 * 60; $m <= 20 * 60; $m += 30) {
        $jamOpsi[sprintf('%02d:%02d', intdiv($m, 60), $m % 60)] = sprintf('%02d.%02d', intdiv($m, 60), $m % 60);
    }

    $finderCfg = [
        'today' => now()->toDateString(),
        'date' => $defStart->toDateString(),
        'start' => $defStart->format('H:i'),
        'end' => $defEnd->format('H:i'),
        'isPic' => (bool) $isPic,
        'base' => route('calendar'),
        'endpoint' => route('rooms.available'),
    ];

    $selectCls = 'mt-1 w-full rounded-xl border-slate-200 bg-slate-50 py-2 text-sm focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100';
@endphp

<script>
    window.roomFinder = function (cfg) {
        const pad = n => String(n).padStart(2, '0');
        const fmt = m => pad(Math.floor(m / 60)) + ':' + pad(m % 60);
        const toMin = t => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
        const [ty, tm, td] = cfg.today.split('-').map(Number);
        const idx = (y, m) => y * 12 + m;
        const lim = new Date(ty, tm - 1, td + 90); // maksimal 90 hari ke depan
        const maxIso = lim.getFullYear() + '-' + pad(lim.getMonth() + 1) + '-' + pad(lim.getDate());
        const [dy, dm] = cfg.date.split('-').map(Number);

        return {
            today: cfg.today, date: cfg.date, start: cfg.start, end: cfg.end,
            dur: toMin(cfg.end) - toMin(cfg.start),
            isPic: cfg.isPic, base: cfg.base, endpoint: cfg.endpoint,

            // Kalender
            open: false, vy: dy, vm: dm - 1,

            loading: false, error: '', res: null, seq: 0, timer: null,

            init() { this.search(); },

            // ----- Kalender -----
            get monthLabel() {
                return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(new Date(this.vy, this.vm, 1));
            },
            get dateLabel() {
                const [y, m, d] = this.date.split('-').map(Number);
                return new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(y, m - 1, d));
            },
            get cells() {
                const first = (new Date(this.vy, this.vm, 1).getDay() + 6) % 7; // Senin = 0
                const days = new Date(this.vy, this.vm + 1, 0).getDate();
                const out = [];
                for (let i = 0; i < first; i++) out.push({ d: null, key: 'e' + i });
                for (let d = 1; d <= days; d++) {
                    const iso = this.vy + '-' + pad(this.vm + 1) + '-' + pad(d);
                    out.push({ d, key: iso, iso, disabled: iso < cfg.today || iso > maxIso, weekend: out.length % 7 > 4 });
                }
                return out;
            },
            get canPrev() { return idx(this.vy, this.vm) > idx(ty, tm - 1); },
            get canNext() { return idx(this.vy, this.vm) < idx(lim.getFullYear(), lim.getMonth()); },
            prev() { if (!this.canPrev) return; if (--this.vm < 0) { this.vm = 11; this.vy--; } },
            next() { if (!this.canNext) return; if (++this.vm > 11) { this.vm = 0; this.vy++; } },
            pick(c) {
                if (!c.d || c.disabled) return;
                this.date = c.iso;
                this.open = false;
                this.queue();
            },

            // ----- Jam (WIB) -----
            // Saat jam mulai diganti, jam selesai ikut bergeser supaya durasi tetap sama
            setStart(v) {
                this.start = v;
                this.end = fmt(Math.min(toMin(v) + this.dur, 20 * 60));
                this.queue();
            },
            setEnd(v) {
                this.end = v;
                const d = toMin(v) - toMin(this.start);
                if (d > 0) this.dur = d;
                this.queue();
            },

            queue() { clearTimeout(this.timer); this.timer = setTimeout(() => this.search(), 250); },

            async search() {
                this.error = '';
                if (this.end <= this.start) {
                    this.res = null;
                    this.error = 'Jam selesai harus setelah jam mulai.';
                    return;
                }
                const my = ++this.seq;
                this.loading = true;
                try {
                    const qs = new URLSearchParams({ date: this.date, start: this.start, end: this.end });
                    const r = await fetch(this.endpoint + '?' + qs, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const j = await r.json();
                    if (my !== this.seq) return; // abaikan respon lama
                    if (!r.ok) {
                        this.res = null;
                        this.error = j.message || (j.errors ? Object.values(j.errors)[0][0] : 'Gagal memuat data.');
                    } else {
                        this.res = j;
                    }
                } catch (e) {
                    if (my === this.seq) { this.res = null; this.error = 'Gagal memuat data. Coba lagi.'; }
                } finally {
                    if (my === this.seq) this.loading = false;
                }
            },

            url(room) {
                return this.base + '?room=' + encodeURIComponent(room.id)
                    + '&date=' + encodeURIComponent(this.date)
                    + '&start=' + encodeURIComponent(this.start)
                    + '&until=' + encodeURIComponent(this.end);
            },
        };
    };
</script>

<div id="cari-ruangan-kosong" class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5"
    x-data="roomFinder(@js($finderCfg))" @keydown.escape.window="open = false">

    <div class="mb-3">
        <h2 class="text-base font-bold text-slate-900">Cari Ruangan Kosong</h2>
        <p class="text-xs text-slate-500">Pilih tanggal dan jam, ruangan yang tersedia langsung ditampilkan.</p>
    </div>

    <div class="grid gap-2 sm:grid-cols-[1.6fr_1fr_1fr]">
        {{-- Tanggal (kalender) --}}
        <div class="relative" @click.outside="open = false">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Tanggal</span>
            <button type="button" @click="open = !open"
                class="mt-1 flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-sm text-slate-800 transition hover:border-indigo-300"
                :class="open ? 'border-indigo-300 ring-2 ring-indigo-100' : ''">
                <span class="truncate" x-text="dateLabel"></span>
                <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition.opacity
                class="absolute left-0 z-30 mt-1.5 w-72 max-w-[calc(100vw-3rem)] rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
                <div class="mb-2 flex items-center justify-between">
                    <button type="button" @click="prev()" :disabled="!canPrev" aria-label="Bulan sebelumnya"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-40">‹</button>
                    <span class="text-sm font-bold capitalize text-slate-900" x-text="monthLabel"></span>
                    <button type="button" @click="next()" :disabled="!canNext" aria-label="Bulan berikutnya"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-40">›</button>
                </div>

                <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-slate-400">
                    <div class="py-1">Sen</div>
                    <div class="py-1">Sel</div>
                    <div class="py-1">Rab</div>
                    <div class="py-1">Kam</div>
                    <div class="py-1">Jum</div>
                    <div class="py-1 text-rose-500">Sab</div>
                    <div class="py-1 text-rose-500">Min</div>
                </div>
                <div class="grid grid-cols-7 gap-y-0.5">
                    <template x-for="c in cells" :key="c.key">
                        <button type="button" :disabled="!c.d || c.disabled" @click="pick(c)" :class="[
                                c.d ? '' : 'invisible',
                                date === c.iso ? 'bg-indigo-600 text-white' :
                                    (c.disabled ? 'cursor-not-allowed text-slate-300' :
                                        (c.iso === today ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200' :
                                            (c.weekend ? 'text-rose-500 hover:bg-rose-50' : 'text-slate-700 hover:bg-slate-100')))
                            ]"
                            class="mx-auto flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold transition"
                            x-text="c.d"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Jam mulai --}}
        <label class="block">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jam mulai (WIB)</span>
            <select :value="start" @change="setStart($event.target.value)" class="{{ $selectCls }}">
                @foreach($jamOpsi as $val => $lbl)
                    <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
            </select>
        </label>

        {{-- Jam selesai --}}
        <label class="block">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Jam selesai (WIB)</span>
            <select :value="end" @change="setEnd($event.target.value)" class="{{ $selectCls }}">
                @foreach($jamOpsi as $val => $lbl)
                    <option value="{{ $val }}">{{ $lbl }}</option>
                @endforeach
            </select>
        </label>
    </div>

    {{-- Error --}}
    <p x-show="error" x-cloak x-text="error"
        class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700"></p>

    {{-- Loading --}}
    <p x-show="loading && !res" x-cloak class="mt-4 text-center text-xs text-slate-400">Memeriksa ketersediaan…</p>

    {{-- Hasil --}}
    <div x-show="res" x-cloak class="mt-4" :class="loading ? 'opacity-60' : ''">
        <template x-if="res">
            <div>
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Tersedia</span>
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700"
                        x-text="res.free.length + ' ruangan'"></span>
                </div>

                <div class="grid gap-2 sm:grid-cols-2" x-show="res.free.length">
                    <template x-for="r in res.free" :key="r.id">
                        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-3">
                            <span class="h-3 w-3 shrink-0 rounded-full" :style="'background:' + r.color"></span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-[13px] font-semibold text-slate-900" x-text="r.name"></div>
                                <div class="text-xs text-emerald-700">Kosong</div>
                            </div>
                            <template x-if="isPic">
                                <a :href="url(r)"
                                    class="shrink-0 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Ajukan</a>
                            </template>
                        </div>
                    </template>
                </div>

                <p x-show="!res.free.length" class="rounded-xl bg-slate-50 p-4 text-center text-sm text-slate-500">
                    Tidak ada ruangan yang kosong pada waktu tersebut.
                </p>

                <template x-if="res.busy.length">
                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Terpakai</span>
                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-700"
                                x-text="res.busy.length + ' ruangan'"></span>
                        </div>
                        <div class="space-y-2">
                            <template x-for="r in res.busy" :key="r.id">
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="flex items-center gap-2">
                                        <span class="h-3 w-3 shrink-0 rounded-full"
                                            :style="'background:' + r.color"></span>
                                        <div class="truncate text-[13px] font-semibold text-slate-900" x-text="r.name">
                                        </div>
                                    </div>
                                    <div class="mt-2 space-y-1 border-t border-slate-100 pt-2">
                                        <template x-for="(c, idx) in r.conflicts" :key="idx">
                                            <div class="flex flex-wrap items-center gap-x-2 text-xs text-slate-600">
                                                <span class="font-semibold tabular-nums text-slate-800"
                                                    x-text="c.time"></span>
                                                <span class="min-w-0 break-words"
                                                    x-text="c.title + ' · ' + c.unit"></span>
                                                <span x-show="c.pending"
                                                    class="rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">Menunggu
                                                    persetujuan</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="res.maintenance.length">
                    <p class="mt-3 text-[11px] text-slate-400"
                        x-text="'Tidak dihitung (maintenance): ' + res.maintenance.map(r => r.name).join(', ')"></p>
                </template>
            </div>
        </template>
    </div>
</div>