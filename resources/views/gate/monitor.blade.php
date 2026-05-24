<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Layar Monitor Gerbang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-white min-h-screen flex flex-col justify-between p-8 select-none">

    <form action="{{ route('gate.scan') }}" method="POST" id="scan-form">
        @csrf
        <input type="text" name="uid" id="uid-input" class="opacity-0 absolute top-0 left-0" autofocus autocomplete="off">
    </form>

    <div class="flex justify-between items-center border-b border-slate-800 pb-4">
        <div>
            <h1 class="text-2xl font-black text-amber-500 tracking-wider">GATE MONITORING KIOSK</h1>
            <p class="text-xs text-slate-400">Petugas aktif: <span class="text-slate-200 font-bold">{{ Auth::user()->name }}</span></p>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <div id="clock" class="text-3xl font-mono font-bold text-emerald-400">00:00:00</div>
            </div>

            <button type="button" onclick="openIzinModal()" class="bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-slate-950 px-4 py-2 rounded-xl text-xs font-bold transition">
                IZIN KHUSUS
            </button>

            <form action="{{ route('gate.logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white px-4 py-2 rounded-xl text-xs font-bold transition">LOGOUT</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 my-auto items-center">
        <div class="bg-slate-900 p-8 rounded-3xl border border-slate-800 text-center flex flex-col items-center justify-center min-h-[250px]">
            <div class="relative flex h-16 w-16 mb-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20"></span>
                <div class="relative rounded-full h-16 w-16 bg-emerald-500/10 border border-emerald-500 flex items-center justify-center text-2xl">📡</div>
            </div>
            <h2 class="text-lg font-bold tracking-wide">SYSTEM READY</h2>
            <p class="text-xs text-slate-500 mt-1 max-w-xs">Silakan tempelkan kartu RFID siswa pada alat sensor</p>
        </div>

        <div class="lg:col-span-2">
            @if (session('success'))
                <div class="bg-emerald-500 text-slate-950 p-8 rounded-3xl shadow-2xl flex items-center gap-6 animate-pulse">
                    <span class="text-5xl">✅</span>
                    <div>
                        <h3 class="text-3xl font-black uppercase tracking-wide">AKSES DITERIMA</h3>
                        <p class="text-lg font-semibold text-emerald-950 mt-1">{{ session('success') }}</p>
                    </div>
                </div>
            @elseif (session('error'))
                <div class="bg-rose-500 text-white p-8 rounded-3xl shadow-2xl flex items-center gap-6 animate-bounce">
                    <span class="text-5xl">❌</span>
                    <div>
                        <h3 class="text-3xl font-black uppercase tracking-wide">AKSES DITOLAK</h3>
                        <p class="text-lg font-semibold text-rose-100 mt-1">{{ session('error') }}</p>
                    </div>
                </div>
            @else
                <div class="bg-slate-900 p-8 rounded-3xl border border-slate-800 border-dashed flex items-center justify-center min-h-[145px] text-slate-500 italic">
                    Menunggu aktivitas scanning...
                </div>
            @endif
        </div>
    </div>

    <div class="bg-slate-900/50 p-6 rounded-3xl border border-slate-800">
        <h3 class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-3">Riwayat Scanning Terakhir</h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @forelse($latestLogs as $log)
                <div class="p-3 rounded-xl border {{ str_contains($log->status, 'DIIZINKAN') || str_contains($log->status, 'IZIN') ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-rose-500/5 border-rose-500/20' }}">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] font-mono text-slate-500">{{ $log->created_at->format('H:i:s') }}</span>
                        <span class="text-[9px] px-2 py-0.5 rounded-full font-black {{ str_contains($log->status, 'DIIZINKAN') || str_contains($log->status, 'IZIN') ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }} truncate max-w-[65px]">{{ $log->status }}</span>
                    </div>
                    <p class="font-bold truncate text-sm text-slate-200">{{ $log->student->name ?? 'Tidak Dikenal' }}</p>
                </div>
            @empty
                <p class="text-xs text-slate-600 col-span-5 text-center py-2">Belum ada aktivitas scan hari ini.</p>
            @endforelse
        </div>
    </div>

    <div id="izin-modal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-slate-900 p-6 rounded-3xl border border-slate-800 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-black text-amber-500 tracking-wide uppercase mb-1">Form Izin Pulang Cepat</h3>
            <p class="text-xs text-slate-400 mb-4">Data akan langsung dilaporkan ke Admin dan mencetak struk khusus.</p>
            
            <form action="{{ route('gate.izin_khusus') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama Siswa</label>
                    <input type="text" name="name" id="name-input" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500 transition" placeholder="Ketik nama depan/belakang siswa..." autocomplete="off">
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Alasan Izin Pulang</label>
                    <select name="alasan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500 transition">
                        <option value="Sakit / Masuk UKS">Sakit / Dirujuk Pulang</option>
                        <option value="Dispensasi Kegiatan">Dispensasi (Lomba / Acara Sekolah)</option>
                        <option value="Keperluan Keluarga">Urusan Keluarga Mendesak</option>
                    </select>
                </div>

                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeIzinModal()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl text-xs font-bold transition">BATAL</button>
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 px-5 py-2 rounded-xl text-xs font-bold transition">IZINKAN & CETAK STRUK</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const input = document.getElementById('uid-input');
        const nameInput = document.getElementById('name-input');
        let isInModalMode = false; // Pengunci otomatis fokus RFID
        
        // Paksa kursor selalu fokus ke input scanner (Hanya jika modal tidak terbuka)
        function keepFocus() { 
            if (!isInModalMode && input) {
                input.focus(); 
            }
        }
        setInterval(keepFocus, 100);
        document.addEventListener('click', function() {
            if (!isInModalMode) keepFocus();
        });

        // Jam Digital Realtime
        setInterval(() => {
            document.getElementById('clock').innerText = new Date().toLocaleTimeString('id-ID', { hour12: false });
        }, 1000);

        function openIzinModal() {
            isInModalMode = true; // Matikan paksa fokus RFID agar petugas bisa ngetik nama
            document.getElementById('izin-modal').classList.remove('hidden');
            setTimeout(() => { if(nameInput) nameInput.focus(); }, 200); 
        }

        function closeIzinModal() {
            document.getElementById('izin-modal').classList.add('hidden');
            if(nameInput) nameInput.value = ''; 
            isInModalMode = false; // Hidupkan kembali paksa fokus RFID
            if(input) input.focus();
        }
    </script>
</body>
</html>