<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center p-10 bg-white shadow rounded-xl">
        <h1 class="text-2xl font-bold mb-4">AREA SCAN KARTU</h1>
        
        <!-- Input ini tersembunyi tapi selalu aktif (fokus) -->
        <input type="text" wire:model.live="uid" id="rfid_input" autofocus 
               class="opacity-0 absolute" onblur="this.focus()">

        <div class="text-center">
            @if(session('message'))
                <div class="p-4 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-100 text-red-700 rounded-lg">{{ session('error') }}</div>
            @endif
        </div>
    </div>

    <!-- AREA STRUK (Tersembunyi di layar, muncul saat print) -->
    <div id="struk-area" class="hidden-print" style="width: 58mm; font-family: monospace; font-size: 10pt;">
        <center>
            <h3>IZIN KELUAR SEKOLAH</h3>
            <p>========================</p>
        </center>
        <p>NAMA  : <span id="p-nama"></span></p>
        <p>KELAS : <span id="p-kelas"></span></p>
        <p>STATUS: <span id="p-status"></span></p>
        <p>WAKTU : <span id="p-waktu"></span></p>
        <center>
            <p>========================</p>
            <p>Simpan sebagai bukti</p>
        </center>
    </div>

    <style>
        /* CSS agar struk pas di kertas thermal 58mm */
        @media print {
            body * { visibility: hidden; }
            #struk-area, #struk-area * { visibility: visible; }
            #struk-area { position: absolute; left: 0; top: 0; width: 58mm; }
            @page { size: 58mm auto; margin: 0; }
        }
    </style>

    <script>
        // Mendengarkan sinyal dari Laravel untuk mencetak
        window.addEventListener('print-struk', event => {
            document.getElementById('p-nama').innerText = event.detail.nama;
            document.getElementById('p-kelas').innerText = event.detail.kelas;
            document.getElementById('p-status').innerText = event.detail.status;
            document.getElementById('p-waktu').innerText = event.detail.waktu;

            if(event.detail.status === 'DIIZINKAN') {
                setTimeout(() => { window.print(); }, 500);
            }
        });

        // Klik di mana saja, input RFID langsung fokus lagi
        document.addEventListener('click', () => { document.getElementById('rfid_input').focus(); });
    </script>
</x-filament-panels::page>