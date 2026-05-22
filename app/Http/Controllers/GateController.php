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
    // 1. Halaman Login Petugas
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

    // 2. Halaman Utama Kiosk Scan
    public function index() {
        if (!Auth::check() || (Auth::user()->role !== 'petugas' && Auth::user()->role !== 'admin')) {
            return redirect()->route('gate.login');
        }

        $latestLogs = AttendanceLog::with('student')->latest()->take(5)->get();
        return view('gate.monitor', compact('latestLogs'));
    }

    // 3. Logika Terima Tap RFID (Auto Submit dari Scanner)
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

            // Logika Printer Epson
            try {
                $connector = new CupsPrintConnector("EPSON_TM-T82X-S_A");
                $printer = new Printer($connector);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
                $printer->text("SMK NEGERI TA\n");
                $printer->selectPrintMode();
                $printer->text("Sistem Monitoring Gerbang Digital\n");
                $printer->text("================================\n");
    
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Tanggal : " . now()->format('d-m-Y') . "\n");
                $printer->text("Waktu   : " . now()->format('H:i:s') . " WIB\n");
                $printer->text("--------------------------------\n");
    
                //data siswa
                $printer->setEmphasis(true);
                $printer->text("NAMA   : " . strtoupper($student->name) . "\n");
                $printer->text("KELAS  : " . $student->grade . "\n"); 
                $printer->text("STATUS : " );
                $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
                $printer->text($status . "\n");
                $printer->selectPrintMode();
                $printer->setEmphasis(false);
                $printer->text("--------------------------------\n\n");
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

    // 4. Logout Petugas
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('gate.login');
    }
}