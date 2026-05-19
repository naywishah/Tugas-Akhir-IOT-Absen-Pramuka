<?php

namespace App\Filament\Petugas\Pages;

use Filament\Pages\Page;
use App\Models\Student;
use App\Models\AttendanceLog;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Log;
use BackedEnum;

class ScanMonitor extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $title = 'Area Tap Kartu (Gerbang)';
    protected static string $routePath = '/';
    protected string $view = 'filament.petugas.pages.scan-monitor';
    
    // Ini agar navigasi di panel petugas cuma muncul halaman ini saja
    public static function canAccess(): bool { return true; }

    public $uid = ''; 

    public function updatedUid()
    {
        if (empty($this->uid)) return;

        $student = Student::where('uid', $this->uid)->first();

        if ($student) {
            $status = ($student->grade >= 11) ? 'DIIZINKAN' : 'DITOLAK';
            AttendanceLog::create(['student_id' => $student->id, 'status' => $status]);

            // LOGIKA PRINTER (Tetap di sini agar langsung cetak dari backend petugas)
            try {
                $connector = new CupsPrintConnector("EPSON_TM-T82X-S_A");
                $printer = new Printer($connector);
    
                // Header Struk
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setTextSize(2, 2); // Judul lebih besar
                $printer->text("GERAKAN PRAMUKA\n");
                $printer->setTextSize(1, 1);
                $printer->text("SMK NEGERI 1 SURABAYA\n");
                $printer->text("--------------------------------\n");

                // Isi Data
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Nama   : " . strtoupper($student->name) . "\n");
                $printer->text("Kelas  : " . $student->grade . "\n");
                $printer->text("Waktu  : " . now()->format('d/m/Y H:i:s') . "\n");
                $printer->text("--------------------------------\n");

                // Status dengan penekanan
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setTextSize(2, 2);
                $printer->text($status . "\n\n");
                $printer->setTextSize(1, 1);
    
                // Footer
                $printer->text("Simpan struk ini sebagai bukti.\n");
                $printer->text("Salam Pramuka!\n");
                $printer->feed(4);
                $printer->cut();
                $printer->close();
            } catch (\Exception $e) {
                Log::error("Printer Error: " . $e->getMessage());
            }

            session()->flash('success', "Data tercatat: {$student->name}");
        }
        $this->uid = '';
    }
}