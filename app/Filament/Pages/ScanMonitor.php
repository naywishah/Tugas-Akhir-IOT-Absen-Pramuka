<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Student;
use App\Models\AttendanceLog;
use BackedEnum;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Log;

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

            // ==================== LOGIKA AUTO PRINT BACKEND ====================
            try {
                // Sesuaikan nama ini dengan yang terdaftar di CUPS Linux kamu
                $connector = new CupsPrintConnector("EPSON_TM-T82X-S_A");
                $printer = new Printer($connector);

                // Header Struk
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("LOG ABSENSI MAHASISWA\n");
                $printer->text("--------------------------------\n");

                // Isi Struk
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Nama   : " . $student->name . "\n");
                $printer->text("Kelas  : " . $student->grade . "\n");
                $printer->text("Waktu  : " . now()->format('H:i:s d-m-Y') . "\n");
                $printer->text("Status : " . $status . "\n");
                $printer->text("--------------------------------\n");

                // Potong Kertas
                $printer->feed(3);
                $printer->cut();
                $printer->close();

            } catch (\Exception $e) {
                // Jika printer mati, aplikasi Filament-mu gak bakal crash
                Log::error("Gagal cetak thermal: " . $e->getMessage());
            }
            // ===================================================================

            session()->flash('success', "Data tercatat: {$student->name}");
        } else {
            session()->flash('error', "Kartu '{$this->uid}' belum terdaftar!");
        }

        $this->uid = ''; 
    }
}