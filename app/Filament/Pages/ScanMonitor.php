<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Student;
use App\Models\AttendanceLog;
use BackedEnum;

class ScanMonitor extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $title = 'Area Tap Kartu';

    protected string $view = 'filament.pages.scan-monitor';
    public $uid = ''; 

    public function updatedUid()
    {
        if (empty($this->uid)) return;

        $student = Student::where('uid', $this->uid)->first();

        if ($student) {
            $status = ($student->grade >= 11) ? 'DIIZINKAN' : 'DITOLAK';

            AttendanceLog::create([
                'student_id' => $student->id,
                'status' => $status
            ]);

            // Dispatch event ke Alpine.js/Browser untuk print
            $this->dispatch('print-now', 
                nama: $student->name,
                kelas: $student->grade,
                status: $status,
                waktu: now()->format('H:i:s d-m-Y')
            );

            session()->flash('success', "Data tercatat: {$student->name}");
        } else {
            session()->flash('error', "Kartu '{$this->uid}' belum terdaftar!");
        }

        $this->uid = ''; // Kosongkan input untuk scan berikutnya
    }
}