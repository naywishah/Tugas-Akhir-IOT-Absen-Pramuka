<x-filament-panels::page>
    <div x-data="{}" x-init="$nextTick(() => { 
        const input = document.getElementById('uid-scanner');
        input.focus();
        document.addEventListener('click', () => input.focus());
    })">
        <input 
            wire:model.live.debounce.100ms="uid" 
            type="text" 
            id="uid-scanner"
            class="opacity-0 fixed top-0" 
            autofocus 
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-filament::section>
            <x-slot name="heading">Total Scan Hari Ini</x-slot>
            <p class="text-4xl font-bold text-amber-600">{{ \App\Models\AttendanceLog::whereDate('created_at', today())->count() }}</p>
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">Status Sistem</x-slot>
            <div class="flex items-center gap-2 text-success-600 font-bold">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-success-500"></span>
                </span>
                <span>ONLINE</span>
            </div>
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">Riwayat Absensi Terakhir</x-slot>
        
        <div class="fi-ta-ctn overflow-hidden">
            <table class="fi-ta-table w-full border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-2 text-left text-sm font-bold text-gray-500">Waktu</th>
                        <th class="px-4 py-2 text-left text-sm font-bold text-gray-500">Siswa</th>
                        <th class="px-4 py-2 text-right text-sm font-bold text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach(\App\Models\AttendanceLog::with('student')->latest()->take(5)->get() as $log)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $log->created_at->format('H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ $log->student->name ?? '?' }}</td>
                            <td class="px-4 py-3 text-right">
                                <x-filament::badge :color="$log->status === 'DIIZINKAN' ? 'success' : 'danger'">
                                    {{ $log->status }}
                                </x-filament::badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>