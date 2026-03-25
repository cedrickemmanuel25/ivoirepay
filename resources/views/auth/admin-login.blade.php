<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - IvoirePay</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .input-focus:focus { outline: none; border-color: #1B4332; box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.1); }
    </style>
</head>
<body class="min-h-screen bg-[#1B4332] flex items-center justify-center p-6 bg-[radial-gradient(circle_at_top_right,_#1A5C35,_#0F2419)]">
    
    <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 lg:p-10" data-aos="zoom-in">
        <!-- Logo IvoirePay -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="IvoirePay Logo" class="h-24 w-auto">
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Espace Administrateur</h1>
            <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                Connectez-vous pour accéder au tableau de bord et gérer les transactions.
            </p>
        </div>

        @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium">
                        {{ $errors->first() }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Formulaire -->
        <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Adresse Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                        </svg>
                    </div>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 text-sm font-medium transition-all input-focus" 
                        placeholder="admin@ivoirepay.ci">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" name="password" type="password" required
                        class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 text-sm font-medium transition-all input-focus"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition">
                        <svg id="eye-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-[#1B4332] focus:ring-[#1B4332] border-gray-300 rounded transition cursor-pointer">
                    <label for="remember" class="ml-2 block text-sm text-gray-600 font-medium cursor-pointer select-none">Se souvenir de moi</label>
                </div>
                <div>
                    <a href="{{ route('admin.password.request') }}" class="text-sm font-bold text-[#F59E0B] hover:text-[#d97706] transition">Oublié ?</a>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#1B4332] hover:bg-[#153427] text-white font-bold py-4 rounded-2xl shadow-lg shadow-green-900/20 transform active:scale-[0.98] transition-all">
                SE CONNECTER
            </button>
        </form>

        <p class="text-center mt-8 text-xs text-gray-400">
            &copy; 2026 IvoirePay. Tous droits réservés.
        </p>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</body>
</html>
