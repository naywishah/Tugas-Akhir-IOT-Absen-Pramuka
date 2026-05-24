<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Auth;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Log;

class GateController extends Controller
{
    public function showLogin() {
        if (Auth::check() && Auth::user()->role === 'petugas') {
            return redirect()->route('gate.monitor');
        }
        return view('gate.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->role === 'petugas' || Auth::user()->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->route('gate.monitor');
            }
            Auth::logout();
        }

        return back()->withErrors(['email' => 'Akun petugas tidak ditemukan.']);
    }

    public function index() {
        if (!Auth::check() || (Auth::user()->role !== 'petugas' && Auth::user()->role !== 'admin')) {
            return redirect()->route('gate.login');
        }

        $latestLogs = AttendanceLog::with('student')->latest()->take(5)->get();
        return view('gate.monitor', compact('latestLogs'));
    }

    // 1. SCAN RFID REGULER
    public function scan(Request $request) {
        $uid = $request->input('uid');
        if (empty($uid)) return redirect()->back();

        $student = Student::where('uid', $uid)->first();

        if ($student) {
            $status = ($student->grade >= 11) ? 'DIIZINKAN' : 'DITOLAK';
            AttendanceLog::create([
                'student_id' => $student->id,
                'status' => $status
            ]);

            try {
                $connector = new CupsPrintConnector("EPSON_TM-T82X-S_A");
                $printer = new Printer($connector);
                
                //header
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
                $printer->text("SMK NEGERI 1 SURABAYA\n");
                $printer->selectPrintMode();
                $printer->text("Sistem Absensi Jam Pulang Pramuka\n");
                $printer->text("================================\n");
                //data siswa
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Tanggal : " . now()->format('d-m-Y') . "\n");
                $printer->text("Waktu   : " . now()->format('H:i:s') . " WIB\n");
                $printer->text("--------------------------------\n");
                $printer->setEmphasis(true);
                $printer->text("NAMA   : " . strtoupper($student->name) . "\n");
                $printer->text("KELAS  : " . $student->grade . "\n"); 
                $printer->text("STATUS : " );
                $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
                $printer->text($status . "\n");
                $printer->selectPrintMode();
                $printer->setEmphasis(false);
                $printer->text("--------------------------------\n");
                //footer
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                if ($status === 'DIIZINKAN') {
                    $printer->setEmphasis(true);
                    $printer->text("*** HATI-HATI DI JALAN ***\n");
                    $printer->setEmphasis(false);
                    $printer->text("Simpan struk ini sebagai bukti\nsah meninggalkan sekolah.\n");
                } else {
                    $printer->setEmphasis(true);
                    $printer->text("!!! PERINGATAN !!!\n");
                    $printer->setEmphasis(false);
                    $printer->text("Jam kepulangan belum sesuai.\nHarap segera kembali ke kelas!\n");
                }
                $printer->text("--------------------------------\n");
                $printer->text("[ PRAMUKA SMKN 1 SURABAYA ]\n"); // Watermark Text
                $printer->text("================================\n");
                
                $printer->feed(3);
                $printer->cut();
                $printer->close();

            } catch (\Exception $e) {
                Log::error("Printer Error: " . $e->getMessage());
            }

            if ($status === 'DIIZINKAN') {
                return redirect()->back()->with('success', "Akses Diterima: {$student->name} ({$status})");
            } else {
                return redirect()->back()->with('error', "Akses Ditolak: {$student->name} ({$status})");
            }
        }

        return redirect()->back()->with('error', "Kartu RFID Tidak Dikenal!");
    }

    // 2. FORM IZIN KHUSUS
    public function izinKhusus(Request $request) {
        $name = $request->input('name');
        $alasan = $request->input('alasan');

        if (empty($name) || empty($alasan)) {
            return redirect()->back()->with('error', 'Nama dan Alasan Izin tidak boleh kosong!');
        }

        $student = Student::where('name', 'like', '%' . $name . '%')->first();

        if ($student) {
            $statusUntukAdmin = "IZIN PRAMUKA (" . strtoupper($alasan) . ")";
            
            AttendanceLog::create([
                'student_id' => $student->id,
                'status' => $statusUntukAdmin
            ]);

            try {
                $connector = new CupsPrintConnector("EPSON_TM-T82X-S_A");
                $printer = new Printer($connector);
                
                //header
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
                $printer->text("SMK NEGERI 1 SURABAYA\n");
                $printer->selectPrintMode();
                $printer->text("STRUK IZIN PULANG KELUAR\n");
                $printer->text("================================\n");
                //data siswa
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Tanggal : " . now()->format('d-m-Y') . "\n");
                $printer->text("Waktu   : " . now()->format('H:i:s') . " WIB\n");
                $printer->text("--------------------------------\n");
                $printer->setEmphasis(true);
                $printer->text("NAMA    : " . strtoupper($student->name) . "\n");
                $printer->text("KELAS   : " . $student->grade . "\n");
                $printer->text("KETE.   : IZIN KHUSUS\n");
                $printer->text("ALASAN  : " . strtoupper($alasan) . "\n"); 
                $printer->text("STATUS  : ");
                $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
                $printer->text("DIIZINKAN\n");
                $printer->selectPrintMode();
                $printer->setEmphasis(false);
                //footer
                $printer->text("--------------------------------\n");
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("Struk ini sah dikeluarkan oleh\nsistem atas persetujuan petugas.\n");
                $printer->text("--------------------------------\n");
                $printer->text("[ PRAMUKA SMKN 1 SURABAYA ]\n"); // Watermark Text
                $printer->text("================================\n");
                
                $printer->feed(3);
                $printer->cut();
                $printer->close();
                
            } catch (\Exception $e) {
                Log::error("Printer Error: " . $e->getMessage());
            }

            return redirect()->back()->with('success', "Izin Khusus Diproses: {$student->name} ({$alasan})");
        }

        return redirect()->back()->with('error', "Siswa bernama '{$name}' tidak ditemukan!");
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('gate.login');
    }
}