<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Petugas Gerbang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 flex items-center justify-center min-h-screen text-white">
    <div class="bg-slate-900 p-8 rounded-3xl border border-slate-800 w-full max-w-md shadow-2xl">
        <h2 class="text-2xl font-black text-amber-500 text-center mb-2">GATE SYSTEM</h2>
        <p class="text-sm text-slate-400 text-center mb-6">Silakan masuk menggunakan akun petugas gerbang</p>
        
        <form action="{{ route('gate.login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 mb-2">Email Petugas</label>
                <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-amber-500">
            </div>
            <button type="submit" class="w-full bg-amber-500 text-slate-950 font-bold py-3 rounded-xl hover:bg-amber-400 transition tracking-wide uppercase text-sm">Masuk Sistem</button>
        </form>
    </div>
</body>
</html>