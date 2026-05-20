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
                $printer->text("STRUK IZIN PULANG\n-----------------------\n");
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Nama   : " . $student->name . "\n");
                $printer->text("Status : " . $status . "\n");
                $printer->feed(3);
                $printer->cut();
                $printer->close();
            } catch (\Exception $e) {
                Log::error("Printer Error: " . $e->getMessage());
            }

            return redirect()->back()->with('success', "Akses Diterima: {$student->name} ({$status})");
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