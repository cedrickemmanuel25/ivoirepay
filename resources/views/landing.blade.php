<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Pay - Simplifiez vos paiements par QR Code</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="QR Pay est la solution de paiement par QR code la plus rapide et sécurisée en Afrique de l'Ouest. Compatible avec Wave, Djamo et Moov Money.">
    <meta name="keywords" content="QR Pay, paiement, QR code, Afrique, Wave, Djamo, Moov Money, fintech">
    <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/512/569/569501.png">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1B4332',     // Deep Green
                        secondary: '#F59E0B',   // Amber
                        'primary-light': '#2D6A4F',
                        'primary-dark': '#081C15',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    borderRadius: {
                        '3xl': '1.5rem',
                        '4xl': '2rem',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- AOS.js CDN -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .hero-gradient {
            background: radial-gradient(circle at top right, #2D6A4F 0%, #1B4332 100%);
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .marquee-container {
            display: flex;
            width: max-content;
            animation: marquee 30s linear infinite;
        }
        .marquee-container:hover {
            animation-play-state: paused;
        }
        .logo-blend {
            mix-blend-mode: multiply;
        }
    </style>
</head>
<body class="font-sans bg-white text-gray-900 overflow-x-hidden">

    <!-- NAVBAR -->
    <nav x-data="{ open: false }" class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="IvoirePay Logo" class="h-20 w-auto">
                </a>

                <!-- Desktop Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#how-it-works" class="text-primary/70 hover:text-primary font-medium transition-colors">Comment ça marche ?</a>
                    <a href="#features" class="text-primary/70 hover:text-primary font-medium transition-colors">Fonctionnalités</a>
                    <a href="#compatibility" class="text-primary/70 hover:text-primary font-medium transition-colors">Compatibilité</a>
                    <a href="#contact" class="text-primary/70 hover:text-primary font-medium transition-colors">Contact</a>
                    <a href="{{ route('admin.login') }}" class="bg-primary text-white px-6 py-2.5 rounded-full font-semibold hover:bg-primary-light transition-all shadow-lg shadow-primary/20">Se connecter</a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="open = !open" class="md:hidden text-primary p-2 focus:outline-none">
                    <i class="fa-solid text-2xl" :class="open ? 'fa-xmark' : 'fa-bars-staggered'"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden bg-white border-t border-gray-100 shadow-xl overflow-hidden">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="#how-it-works" @click="open = false" class="block px-4 py-3 text-primary/70 hover:text-primary hover:bg-gray-50 rounded-xl font-medium transition-all">Comment ça marche ?</a>
                <a href="#features" @click="open = false" class="block px-4 py-3 text-primary/70 hover:text-primary hover:bg-gray-50 rounded-xl font-medium transition-all">Fonctionnalités</a>
                <a href="#compatibility" @click="open = false" class="block px-4 py-3 text-primary/70 hover:text-primary hover:bg-gray-50 rounded-xl font-medium transition-all">Compatibilité</a>
                <a href="#contact" @click="open = false" class="block px-4 py-3 text-primary/70 hover:text-primary hover:bg-gray-50 rounded-xl font-medium transition-all">Contact</a>
                <div class="pt-4 px-4">
                    <a href="{{ route('admin.login') }}" class="block w-full text-center bg-primary text-white px-6 py-4 rounded-xl font-bold hover:bg-primary-light transition-all shadow-lg shadow-primary/20">Se connecter</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="relative pt-32 pb-20 md:pt-48 md:pb-32 overflow-hidden hero-gradient">
        <!-- Abstract Shapes -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-20">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-secondary rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-64 h-64 bg-primary-light rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="inline-flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full text-white/90 text-sm font-medium mb-6 border border-white/10">
                        <span class="w-2 h-2 bg-secondary rounded-full animate-pulse"></span>
                        Le futur du paiement est ici
                    </div>
                    <h1 class="text-5xl md:text-7xl font-extrabold text-white leading-tight mb-6">
                        Encaissez vos paiements en <span class="text-secondary">un scan.</span>
                    </h1>
                    <p class="text-xl text-white/80 mb-10 max-w-lg leading-relaxed">
                        QR Pay simplifie les transactions entre commerçants et clients. Plus besoin de monnaie, scannez et payez en 5 secondes avec vos applications préférées.
                    </p>
                    
                    <div class="flex flex-wrap gap-4 mb-12">
                        <a href="#" class="flex items-center gap-3 bg-white text-primary px-8 py-4 rounded-2xl font-bold hover:bg-white/90 transition-all transform hover:-translate-y-1 shadow-2xl">
                            <i class="fa-brands fa-apple text-2xl"></i>
                            <div class="text-left">
                                <div class="text-[10px] uppercase opacity-70">Télécharger sur</div>
                                <div class="text-lg leading-none">App Store</div>
                            </div>
                        </a>
                        <a href="#" class="flex items-center gap-3 bg-primary-dark text-white px-8 py-4 rounded-2xl font-bold hover:bg-black transition-all transform hover:-translate-y-1 shadow-2xl border border-white/5">
                            <i class="fa-brands fa-google-play text-2xl text-secondary"></i>
                            <div class="text-left">
                                <div class="text-[10px] uppercase opacity-70">Disponible sur</div>
                                <div class="text-lg leading-none">Google Play</div>
                            </div>
                        </a>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="flex -space-x-3">
                            <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=1" class="w-10 h-10 rounded-full border-2 border-primary" alt="User">
                            <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=2" class="w-10 h-10 rounded-full border-2 border-primary" alt="User">
                            <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=3" class="w-10 h-10 rounded-full border-2 border-primary" alt="User">
                        </div>
                        <p class="text-white/60 text-sm">Rejoignez +10,000 utilisateurs satisfaits</p>
                    </div>
                </div>

                <div class="relative flex justify-center w-full" data-aos="zoom-in" data-aos-duration="1200">
                    <div class="relative z-10 w-full max-w-[400px] md:max-w-[500px] lg:max-w-[650px]">
                        <img src="{{ asset('images/mockup_new.png') }}" alt="Phone Mockup" class="w-full h-auto mx-auto transition-transform hover:scale-105 duration-700 drop-shadow-2xl">
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave Divider -->
        <div class="absolute bottom-0 left-0 w-full leading-none">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-12 md:h-24 fill-white">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C57.86,110.39,118.9,122.51,180.3,121.3,243.32,120.1,281.41,72.48,321.39,56.44Z"></path>
            </svg>
        </div>
    </section>

    <!-- COMMENT ÇA MARCHE -->
    <section id="how-it-works" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-primary font-bold tracking-widest uppercase text-sm mb-4" data-aos="fade-up">Processus simple</h2>
                <h3 class="text-3xl md:text-5xl font-black text-gray-900" data-aos="fade-up" data-aos-delay="100">Comment ça marche ?</h3>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <!-- Step 1 -->
                <div class="text-center group" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-24 h-24 bg-primary/5 rounded-3xl flex items-center justify-center mx-auto mb-8 group-hover:bg-primary transition-all duration-500 overflow-hidden relative">
                        <span class="absolute top-2 right-4 text-primary group-hover:text-white font-black text-4xl opacity-10 leading-none">1</span>
                        <i class="fa-solid fa-store text-3xl text-primary group-hover:text-white transition-colors duration-500"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-4">Générer le QR</h4>
                    <p class="text-gray-600">Le commerçant génère un code QR statique ou dynamique via son application QR Pay.</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center group" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-24 h-24 bg-secondary/10 rounded-3xl flex items-center justify-center mx-auto mb-8 group-hover:bg-secondary transition-all duration-500 overflow-hidden relative">
                        <span class="absolute top-2 right-4 text-secondary group-hover:text-white font-black text-4xl opacity-10 leading-none">2</span>
                        <i class="fa-solid fa-qrcode text-3xl text-secondary group-hover:text-white transition-colors duration-500"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-4">Scanner le code</h4>
                    <p class="text-gray-600">Le client scanne le code QR avec son téléphone en utilisant QR Pay ou son scanner habituel.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center group" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-24 h-24 bg-primary/5 rounded-3xl flex items-center justify-center mx-auto mb-8 group-hover:bg-green-600 transition-all duration-500 overflow-hidden relative">
                        <span class="absolute top-2 right-4 text-green-600 group-hover:text-white font-black text-4xl opacity-10 leading-none">3</span>
                        <i class="fa-solid fa-bolt text-3xl text-primary group-hover:text-white transition-colors duration-500"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-4">Paiement Instantané</h4>
                    <p class="text-gray-600">Le paiement est traité en moins de 5 secondes. Transaction confirmée immédiatement pour les deux parties.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FONCTIONNALITÉS -->
    <section id="features" class="py-24 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h3 class="text-3xl md:text-5xl font-black text-gray-900" data-aos="fade-up">Fonctionnalités Clés</h3>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100 group" data-aos="zoom-in" data-aos-delay="100">
                    <div class="w-16 h-16 bg-primary/5 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-qrcode text-2xl text-primary"></i>
                    </div>
                    <h4 class="text-lg font-bold mb-3">QR Universel</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">Compatible avec toutes les banques et portefeuilles mobiles locaux.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100 group" data-aos="zoom-in" data-aos-delay="200">
                    <div class="w-16 h-16 bg-secondary/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-lock text-2xl text-secondary"></i>
                    </div>
                    <h4 class="text-lg font-bold mb-3">Sécurité Bancaire</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">Cryptage de bout en bout et authentification biométrique.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100 group" data-aos="zoom-in" data-aos-delay="300">
                    <div class="w-16 h-16 bg-primary/5 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chart-line text-2xl text-primary"></i>
                    </div>
                    <h4 class="text-lg font-bold mb-3">Suivi en temps réel</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">Tableau de bord complet pour suivre vos revenus minute par minute.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-100 group" data-aos="zoom-in" data-aos-delay="400">
                    <div class="w-16 h-16 bg-secondary/10 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-pdf text-2xl text-secondary"></i>
                    </div>
                    <h4 class="text-lg font-bold mb-3">Exports PDF/Excel</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">Générez vos relevés de compte et rapports comptables en un clic.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- COMPATIBILITÉ -->
    <section id="compatibility" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-2xl font-bold text-gray-500 uppercase tracking-widest mb-10" data-aos="fade-up">Compatible avec vos services favoris</h3>
            </div>
            
            <div class="relative overflow-hidden py-10">
                <div class="marquee-container flex gap-8 items-center">
                    <!-- Original Set -->
                    <div class="flex gap-8 items-center">
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/wave_logo.jpg') }}" alt="Wave" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/mtn.jpg') }}" alt="MTN" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/orange.jpg') }}" alt="Orange" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/djamo_logo.jpg') }}" alt="Djamo" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/moov_logo.png') }}" alt="Moov" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                    </div>
                    <!-- Duplicate Set for Seamless Loop -->
                    <div class="flex gap-8 items-center">
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/wave_logo.jpg') }}" alt="Wave" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/mtn.jpg') }}" alt="MTN" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/orange.jpg') }}" alt="Orange" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/djamo_logo.jpg') }}" alt="Djamo" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                        <div class="group transition-all duration-500 flex flex-col items-center justify-center min-w-[280px] min-h-[160px]">
                            <img src="{{ asset('images/moov_logo.png') }}" alt="Moov" class="h-24 w-auto object-contain transition-transform group-hover:scale-110 duration-500 logo-blend">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATISTIQUES -->
    <section class="py-24 bg-primary relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
                <div class="text-center" data-aos="fade-up">
                    <div class="text-4xl md:text-5xl font-black text-white mb-2">5,000+</div>
                    <div class="text-white/60 font-medium">Transactions / jour</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl md:text-5xl font-black text-secondary mb-2">99.9%</div>
                    <div class="text-white/60 font-medium">Uptime Serveur</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl md:text-5xl font-black text-white mb-2">2,500+</div>
                    <div class="text-white/60 font-medium">Commerçants ACTIFS</div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-4xl md:text-5xl font-black text-secondary mb-2">5s</div>
                    <div class="text-white/60 font-medium">Moyenne de paiement</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT FORM -->
    <section id="contact" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <div data-aos="fade-right">
                    <h3 class="text-4xl font-black text-primary mb-6">Contactez-nous</h3>
                    <p class="text-lg text-gray-600 mb-10 leading-relaxed">
                        Une question ? Un partenariat ? Notre équipe est à votre disposition pour vous accompagner dans la digitalisation de vos paiements.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary/5 rounded-full flex items-center justify-center text-primary">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 uppercase font-bold tracking-wider">Téléphone</div>
                                <div class="font-bold">+225 07 00 00 00 00</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary/5 rounded-full flex items-center justify-center text-primary">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 uppercase font-bold tracking-wider">Email</div>
                                <div class="font-bold">contact@qrpay.ci</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-primary/5 rounded-full flex items-center justify-center text-primary">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 uppercase font-bold tracking-wider">Adresse</div>
                                <div class="font-bold">Abidjan, Côte d'Ivoire</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-8 md:p-12 rounded-[2.5rem] shadow-2xl shadow-gray-200 border border-gray-100" data-aos="fade-left">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Nom Complet</label>
                                <input type="text" name="name" required class="w-full px-5 py-4 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="Jean Dupont">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" required class="w-full px-5 py-4 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="jean@email.com">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Téléphone</label>
                            <input type="tel" name="phone" required class="w-full px-5 py-4 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="+225 ...">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Message</label>
                            <textarea name="message" rows="4" required class="w-full px-5 py-4 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all" placeholder="Comment pouvons-nous vous aider ?"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white py-4 rounded-xl font-black text-lg hover:bg-primary-light transition-all shadow-xl shadow-primary/20 transform active:scale-95">
                            Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-primary-dark text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <!-- Brand -->
                <div class="col-span-1 lg:col-span-1">
                    <div class="flex items-center mb-6">
                        <img src="{{ asset('images/logo.png') }}" alt="IvoirePay Logo" class="h-14 w-auto">
                    </div>
                    <p class="text-white/60 text-sm leading-relaxed mb-8">
                        La solution de paiement par QR code leader en Afrique de l'Ouest. Transformez votre smartphone en terminal de paiement.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-secondary hover:text-primary transition-all"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-secondary hover:text-primary transition-all"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center hover:bg-secondary hover:text-primary transition-all"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Product -->
                <div>
                    <h5 class="font-bold mb-6">Produit</h5>
                    <ul class="space-y-4 text-white/60 text-sm">
                        <li><a href="#" class="hover:text-secondary transition-colors">Pour les Commerçants</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Pour les Particuliers</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Tarification</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Sécurité</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h5 class="font-bold mb-6">Support</h5>
                    <ul class="space-y-4 text-white/60 text-sm">
                        <li><a href="#" class="hover:text-secondary transition-colors">Centre d'aide</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Développeurs / API</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Status du service</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h5 class="font-bold mb-6">Légal</h5>
                    <ul class="space-y-4 text-white/60 text-sm">
                        <li><a href="#" class="hover:text-secondary transition-colors">Confidentialité</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Conditions Générales</a></li>
                        <li><a href="#" class="hover:text-secondary transition-colors">Mentions Légales</a></li>
                        <li class="pt-4 border-t border-white/5 flex items-center gap-2">
                            <i class="fa-solid fa-shield-check text-secondary"></i>
                            Conforme aux normes BCEAO
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-10 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6 text-white/40 text-xs text-center">
                <p>&copy; 2026 QR Pay. Tous droits réservés.</p>
                <p>Propulsé par la Tech de l'UEMOA</p>
            </div>
        </div>
    </footer>

    <!-- AOS.js Init -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            disable: 'mobile'
        });

        // Navbar Shadow logic
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-xl', 'bg-white/95');
                nav.classList.remove('bg-white/80');
            } else {
                nav.classList.remove('shadow-xl', 'bg-white/95');
                nav.classList.add('bg-white/80');
            }
        });
    </script>
</body>
</html>
