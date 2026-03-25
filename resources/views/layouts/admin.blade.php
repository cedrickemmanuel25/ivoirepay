<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - IvoirePay</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1B4332',
                        secondary: '#F59E0B',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .nav-link-active { color: #F59E0B !important; border-bottom: 2px solid #F59E0B; }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-gray-900">

    <div x-data="{ mobileMenuOpen: false }">
        <!-- HEADER NAVIGATION -->
        <header class="sticky top-0 z-50 bg-primary text-white shadow-xl">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                            <img src="{{ asset('images/logo.png') }}" alt="IvoirePay" class="h-10 w-auto">
                        </a>
                    </div>

                    <!-- Navigation Liens (Desktop) -->
                    <nav class="hidden lg:flex items-center space-x-8">
                        <a href="{{ route('admin.dashboard') }}" class="px-1 pt-1 text-sm font-semibold transition-colors hover:text-secondary {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
                        <a href="{{ route('admin.merchants.index') }}" class="px-1 pt-1 text-sm font-semibold transition-colors hover:text-secondary whitespace-nowrap {{ request()->routeIs('admin.merchants.*') ? 'nav-link-active' : '' }}">Commerçants</a>
                        <a href="{{ route('admin.transactions.index') }}" class="px-1 pt-1 text-sm font-semibold transition-colors hover:text-secondary whitespace-nowrap {{ request()->routeIs('admin.transactions.*') ? 'nav-link-active' : '' }}">Transactions</a>
                        <a href="{{ route('admin.kyc.index') }}" class="px-1 pt-1 text-sm font-semibold transition-colors hover:text-secondary whitespace-nowrap relative {{ request()->routeIs('admin.kyc.*') ? 'nav-link-active' : '' }}">
                            KYC
                            @if($pendingKycCount > 0)
                                <span class="absolute -top-2 -right-4 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-primary">
                                    {{ $pendingKycCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.withdrawals.index') }}" class="px-1 pt-1 text-sm font-semibold transition-colors hover:text-secondary whitespace-nowrap {{ request()->routeIs('admin.withdrawals.*') ? 'nav-link-active' : '' }}">Retraits</a>
                        <a href="{{ route('admin.users.index') }}" class="px-1 pt-1 text-sm font-semibold transition-colors hover:text-secondary whitespace-nowrap {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">Utilisateurs</a>
                        <a href="{{ route('admin.settings.index') }}" class="px-1 pt-1 text-sm font-semibold transition-colors hover:text-secondary whitespace-nowrap {{ request()->routeIs('admin.settings.*') ? 'nav-link-active' : '' }}">Paramètres</a>
                    </nav>

                    <!-- Zone Droite (Notifications + Profil) -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="p-2 rounded-full hover:bg-white/10 transition-colors relative">
                                <i class="fa-regular fa-bell text-xl"></i>
                                <span id="notif-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-primary" style="display: none;"></span>
                            </button>
                            
                            <!-- Dropdown -->
                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl py-2 z-50 text-gray-800 ring-1 ring-black ring-opacity-5">
                                <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                                    <span class="font-bold">Notifications</span>
                                    <form action="{{ route('admin.notifications.mark-read') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-primary hover:underline cursor-pointer">Tout marquer comme lu</button>
                                    </form>
                                </div>
                                <div class="max-h-96 overflow-y-auto" id="notifications-container">
                                    <div class="p-4 text-center text-gray-500 text-sm italic">
                                        Chargement des notifications...
                                    </div>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-100 text-center">
                                    <a href="{{ route('admin.notifications.index') }}" class="text-xs text-primary font-bold hover:underline">Voir toutes les notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- Profil Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-3 p-1.5 rounded-full hover:bg-white/10 transition-colors border border-white/10">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" id="header-avatar-img" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center text-primary font-bold text-sm">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="hidden md:block text-sm font-semibold pr-2">{{ auth()->user()->name }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-2xl py-2 z-50 text-gray-800 ring-1 ring-black ring-opacity-5">
                                <a href="{{ route('admin.profile.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-50 flex items-center space-x-2">
                                    <i class="fa-regular fa-user w-5"></i>
                                    <span>Mon Profil</span>
                                </a>
                                <div class="h-px bg-gray-100 my-1"></div>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center space-x-2">
                                        <i class="fa-solid fa-arrow-right-from-bracket w-5"></i>
                                        <span>Déconnexion</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Mobile Header Button -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 rounded-lg hover:bg-white/10 transition-colors">
                            <i class="fa-solid" :class="mobileMenuOpen ? 'fa-xmark' : 'fa-bars'"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MENU MOBILE -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform -translate-y-4 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-4 opacity-0"
            >
                <div x-show="mobileMenuOpen" x-cloak class="lg:hidden bg-primary border-t border-white/5 pb-4">
                    <div class="px-2 pt-2 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-4 rounded-xl text-base font-medium hover:bg-white/5 {{ request()->routeIs('admin.dashboard') ? 'text-secondary' : '' }}">Dashboard</a>
                        <a href="{{ route('admin.merchants.index') }}" class="block px-3 py-4 rounded-xl text-base font-medium hover:bg-white/5 {{ request()->routeIs('admin.merchants.*') ? 'text-secondary bg-white/5' : '' }}">Commerçants</a>
                        <a href="{{ route('admin.transactions.index') }}" class="block px-3 py-4 rounded-xl text-base font-medium hover:bg-white/5 {{ request()->routeIs('admin.transactions.*') ? 'text-secondary bg-white/5' : '' }}">Transactions</a>
                        <a href="{{ route('admin.kyc.index') }}" class="block px-3 py-4 rounded-xl text-base font-medium hover:bg-white/5 flex items-center justify-between {{ request()->routeIs('admin.kyc.*') ? 'text-secondary bg-white/5' : '' }}">
                            KYC
                            @if($pendingKycCount > 0)
                                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingKycCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.withdrawals.index') }}" class="block px-3 py-4 rounded-xl text-base font-medium hover:bg-white/5 {{ request()->routeIs('admin.withdrawals.*') ? 'text-secondary bg-white/5' : '' }}">Retraits</a>
                        <a href="{{ route('admin.users.index') }}" class="block px-3 py-4 rounded-xl text-base font-medium hover:bg-white/5 {{ request()->routeIs('admin.users.*') ? 'text-secondary bg-white/5' : '' }}">Utilisateurs</a>
                        <a href="{{ route('admin.settings.index') }}" class="block px-3 py-4 rounded-xl text-base font-medium hover:bg-white/5 border-b border-white/10 pb-4 mb-4 {{ request()->routeIs('admin.settings.*') ? 'text-secondary bg-white/5' : '' }}">Paramètres</a>
                    </div>
                </div>
            </transition>
        </header>

        <!-- MAIN CONTENT -->
        <main class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    
    <!-- AJAX Notifications Script -->
    <script>
        async function loadNotifications() {
            try {
                const res = await fetch('{{ route("admin.notifications.api.unread") }}');
                const { count, latest } = await res.json();
                
                const badge = document.getElementById('notif-badge');
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }

                const container = document.getElementById('notifications-container');
                if (container && latest) {
                    if (latest.length === 0) {
                        container.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Aucune notification</div>';
                    } else {
                        container.innerHTML = latest.map(n => `
                            <a href="{{ route('admin.notifications.index') }}" class="block px-4 py-3 hover:bg-gray-50 flex gap-3 transition-colors ${!n.read ? 'bg-green-50/50' : ''}">
                                <div class="shrink-0 mt-0.5">
                                    <i class="fa-solid ${n.icon}"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-800 font-medium ${!n.read ? 'font-bold' : ''}">${n.title}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">${n.message}</p>
                                    <p class="text-[10px] text-primary font-medium mt-1">${n.time}</p>
                                </div>
                            </a>
                        `).join('<div class="h-px bg-gray-100 mx-4"></div>');
                    }
                }
            } catch (err) {
                console.error('Error fetching notifications:', err);
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(loadNotifications, 30000);
        document.addEventListener('DOMContentLoaded', loadNotifications);
    </script>
</body>
</html>
