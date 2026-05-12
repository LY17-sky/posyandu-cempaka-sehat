<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Posyandu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Quicksand:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .font-brand { font-family: 'Quicksand', sans-serif; }
    </style>
     <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body class="bg-gradient-to-br from-blue-100 via-pink-50 to-blue-100 min-h-screen flex items-center justify-center p-4">
     <!-- Background decoration -->
     <div class="absolute inset-0 overflow-hidden pointer-events-none">
         <div class="absolute top-0 left-0 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
         <div class="absolute top-0 right-0 w-96 h-96 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
         <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
     </div>

    <!-- Login Container -->
    <div class="w-full max-w-md relative z-10">
        <div class="bg-white backdrop-blur-sm p-8 md:p-10 rounded-2xl shadow-2xl border border-white/20">
            <!-- Header -->
            <div class="text-center mb-8 animate-fade-in relative">
                <!-- Decorative background glow for logo -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-32 bg-gradient-to-tr from-blue-300 to-pink-300 rounded-full blur-2xl opacity-30"></div>
                
                <!-- Logo Container with Glassmorphism ring -->
                <div class="relative inline-flex items-center justify-center w-32 h-32 mb-4 p-4 bg-gradient-to-br from-white/90 to-white/50 backdrop-blur-xl rounded-[2.5rem] shadow-[inset_0_2px_4px_rgba(255,255,255,0.6),0_8px_16px_rgba(0,0,0,0.05)] border border-white/60 ring-8 ring-white/30 hover:ring-pink-400/40 hover:from-pink-50 hover:to-blue-50 hover:shadow-[0_20px_50px_rgba(236,72,153,0.3)] hover:-translate-y-1.5 transition-all duration-500 group cursor-pointer">
                    <img src="assets/img/logo_anak.png" alt="Logo Cempaka Sehat" class="w-full h-full object-contain drop-shadow-xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 ease-out z-10">
                </div>
                
                 <h1 class="font-brand text-4xl md:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-transparent pb-1 drop-shadow-sm">
                    Cempaka Sehat
                </h1>
                <p class="text-gray-500 font-semibold mt-2 text-xs md:text-sm tracking-[0.15em] uppercase">Sistem Manajemen Data Posyandu</p>
            </div>
            
            <!-- Form -->
            <form id="loginForm" class="space-y-5">
                <!-- Username Input -->
                <div class="animate-slide-in-left" style="animation-delay: 0.1s;">
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        Username / NIK
                    </label>
                    <input type="text" id="username" name="username" required placeholder="admin atau NIK Ibu"
                           class="form-input">
                </div>
                
                <!-- Password Input -->
                <div class="animate-slide-in-left" style="animation-delay: 0.2s;">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 2l7 3.11v5.89c0 4.34-2.84 8.36-7 9.64-4.16-1.28-7-5.3-7-9.64V6.11L12 3z"/><path d="M12 5c-3.31 0-6 2.69-6 6 0 2.97 2.16 5.44 5 5.92V19h2v-2.08c2.84-.48 5-2.95 5-5.92 0-3.31-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required placeholder="Masukkan password"
                           class="form-input">
                </div>

                <!-- Help Text -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs md:text-sm text-blue-700">
                    <strong>💡 Petunjuk:</strong>
                    <ul class="mt-2 space-y-1 list-disc list-inside">
                        <li><strong>Super Admin:</strong> username: admin, password: password</li>
                        <li><strong>Admin Pos:</strong> username: cempaka1-5, password: pos123</li>
                        <li><strong>User View:</strong> NIK Ibu (cth: 1234567890123456)</li>
                    </ul>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-base font-bold rounded-xl text-white bg-gradient-to-r from-blue-500 to-pink-500 hover:from-blue-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-pink-500/30 transform transition-all duration-300 hover:-translate-y-1 overflow-hidden animate-slide-in-right" style="animation-delay: 0.3s;">
                    <span class="absolute right-0 w-8 h-32 -mt-12 transition-all duration-1000 transform translate-x-12 bg-white opacity-20 rotate-12 group-hover:-translate-x-96 ease"></span>
                    <span id="loginText" class="flex items-center gap-2 relative z-10">
                        <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Masuk Sekarang
                    </span>
                    <div id="loadingSpinner" class="hidden spinner relative z-10"></div>
                </button>
            </form>
            
            <!-- Dark Mode Toggle -->
            <div class="mt-6 flex justify-center">
                <button id="darkModeToggle" class="text-gray-600 hover:text-gray-800 transition" title="Dark Mode">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-600">
            <p>© 2026 Sistem Informasi Posyandu. Semua hak dilindungi.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Dark mode toggle - default to light mode
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        
        // Check for saved theme preference - default to light mode
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
        
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                html.classList.toggle('dark');
                const isDark = html.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }

        // Login form handler
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Username dan password harus diisi'
                });
                return;
            }
            
            const loginText = document.getElementById('loginText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            loginText.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let redirectUrl = 'index.php';
                    if (data.role === 'super_admin' || data.need_pos) {
                        redirectUrl = 'choose_pos.php';
                    }
                    await Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil',
                        text: 'Redirecting...',
                        timer: 1500,
                        didClose: () => {
                            window.location.href = redirectUrl;
                        }
                    });
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        text: data.message || 'Username atau password salah'
                    });
                }
            } catch (error) {
                console.error('Login error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal menghubungi server. Pastikan koneksi internet Anda stabil.'
                });
            } finally {
                loginText.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>