@extends('layouts.admin')

@section('title', 'Paramètres du Site')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Paramètres du Site</h1>
        <p class="text-sm text-gray-500 mt-1">Configurez les aspects globaux, les tarifs et la sécurité de IvoirePay.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
@endif

<div x-data="{ activeTab: 'general' }" class="grid grid-cols-1 xl:grid-cols-4 gap-8">
    
    <!-- Left Sidebar: Tabs Navigation -->
    <div class="xl:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
            <nav class="p-2 flex flex-col gap-1">
                <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-primary text-white shadow-md' : 'text-gray-600 hover:bg-gray-50'" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-semibold text-left">
                    <i class="fa-solid fa-gears w-5"></i>
                    Général
                </button>
                <button @click="activeTab = 'pricing'" :class="activeTab === 'pricing' ? 'bg-primary text-white shadow-md' : 'text-gray-600 hover:bg-gray-50'" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-semibold text-left">
                    <i class="fa-solid fa-percent w-5"></i>
                    Tarification & Commissions
                </button>
                <button @click="activeTab = 'api'" :class="activeTab === 'api' ? 'bg-primary text-white shadow-md' : 'text-gray-600 hover:bg-gray-50'" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-semibold text-left">
                    <i class="fa-solid fa-code w-5"></i>
                    Intégrations API
                </button>
                <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-primary text-white shadow-md' : 'text-gray-600 hover:bg-gray-50'" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm font-semibold text-left">
                    <i class="fa-solid fa-shield-halved w-5"></i>
                    Sécurité
                </button>
            </nav>
        </div>
    </div>

    <!-- Right Content Area -->
    <div class="xl:col-span-3">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Tab 1: General -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-bold text-gray-900">Informations Générales</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Logo de l'application</label>
                                <div class="flex items-center gap-6">
                                    <div class="w-16 h-16 rounded-xl bg-gray-50 flex items-center justify-center border border-gray-200 overflow-hidden">
                                        <img src="{{ $settings['site_logo'] ?? '/images/logo.png' }}" alt="Logo" class="max-w-full h-auto object-contain p-2">
                                    </div>
                                    <div>
                                        <input type="file" name="site_logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all">
                                        <p class="text-[10px] text-gray-400 mt-1 italic">PNG, JPG ou SVG. Max 2Mo.</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de l'application</label>
                                <input type="text" name="settings[site_name]" value="{{ $settings['site_name'] ?? 'IvoirePay' }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Slogan / Description</label>
                                <input type="text" name="settings[site_description]" value="{{ $settings['site_description'] ?? 'La solution de paiement sécurisée de Côte d\'Ivoire' }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email de Support</label>
                                <input type="email" name="settings[support_email]" value="{{ $settings['support_email'] ?? 'support@ivoirepay.ci' }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone Support</label>
                                <input type="text" name="settings[support_phone]" value="{{ $settings['support_phone'] ?? '+225 0102030405' }}" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Fuseau Horaire</label>
                                <select name="settings[timezone]" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                                    <option value="Africa/Abidjan" {{ ($settings['timezone'] ?? 'Africa/Abidjan') === 'Africa/Abidjan' ? 'selected' : '' }}>Africa/Abidjan (UTC+0)</option>
                                    <option value="Europe/Paris" {{ ($settings['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (UTC+1)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Devise</label>
                                <input type="text" value="XOF Franc CFA" disabled class="w-full px-4 py-2.5 border border-gray-100 rounded-xl bg-gray-50 text-gray-400 text-sm outline-none cursor-not-allowed">
                                <p class="text-[10px] text-gray-400 mt-1 italic">La devise par défaut est verrouillée sur le Franc CFA (XOF).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Pricing -->
            <div x-show="activeTab === 'pricing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" style="display: none;" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                        <i class="fa-solid fa-coins text-secondary"></i>
                        <h3 class="text-lg font-bold text-gray-900">Tarification & Commissions</h3>
                    </div>
                    <div class="p-6 space-y-8">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-semibold text-gray-700">Taux commission transaction (%)</label>
                                <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-lg text-xs font-bold" x-text="$refs.commRange.value + '%'"></span>
                            </div>
                            <input type="range" name="settings[commission_rate]" x-ref="commRange" min="0.01" max="5.00" step="0.01" value="{{ $settings['commission_rate'] ?? '1.50' }}" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-primary">
                            <div class="flex justify-between mt-1 text-[10px] text-gray-400 font-medium">
                                <span>0.01%</span>
                                <span>5.00%</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Montant minimum retrait (XOF)</label>
                                <div class="relative">
                                    <input type="number" name="settings[min_withdrawal]" value="{{ $settings['min_withdrawal'] ?? '1000' }}" min="0" class="w-full pl-4 pr-12 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                                    <span class="absolute right-3 top-3.5 text-xs font-bold text-gray-400">XOF</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Maximum transaction / jour (XOF)</label>
                                <div class="relative">
                                    <input type="number" name="settings[max_daily_transaction]" value="{{ $settings['max_daily_transaction'] ?? '500000' }}" min="0" class="w-full pl-4 pr-12 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                                    <span class="absolute right-3 top-3.5 text-xs font-bold text-gray-400">XOF</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Taux commission retrait (%)</label>
                                <div class="relative">
                                    <input type="number" name="settings[withdrawal_fee_rate]" step="0.01" value="{{ $settings['withdrawal_fee_rate'] ?? '0.50' }}" class="w-full pl-4 pr-12 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                                    <span class="absolute right-3 top-3.5 text-xs font-bold text-gray-400">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: API -->
            <div x-show="activeTab === 'api'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" style="display: none;" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ showKeys: false }">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-plug text-blue-500"></i>
                            <h3 class="text-lg font-bold text-gray-900">Intégrations API</h3>
                        </div>
                        <button type="button" @click="showKeys = !showKeys" class="text-xs font-bold text-primary hover:underline">
                            <i class="fa-solid" :class="showKeys ? 'fa-eye-slash' : 'fa-eye'"></i>
                            <span x-text="showKeys ? 'Masquer les clés' : 'Afficher les clés'"></span>
                        </button>
                    </div>
                    <div class="p-6 space-y-8">
                        <!-- Yengapay -->
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50/30">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-xs">Y</div>
                                    <span class="font-bold text-sm">Yengapay Gateway</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Sandbox</span>
                                        <div x-data="{ enabled: {{ ($settings['sandbox_mode'] ?? '1') == '1' ? 'true' : 'false' }} }">
                                            <input type="hidden" name="sandbox_mode_enabled" :value="enabled ? '1' : '0'">
                                            <button type="button" @click="enabled = !enabled" :class="enabled ? 'bg-orange-500' : 'bg-gray-200'" class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out">
                                                <span :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">ACTIF</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Clé API (Secret)</label>
                                    <input :type="showKeys ? 'text' : 'password'" name="settings[yengapay_api_key]" value="{{ $settings['yengapay_api_key'] ?? 'pk_test_xxxxxxxxxxxxxxxx' }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs font-mono bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">URL Webhook</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" readonly value="{{ url('/api/webhooks/yengapay') }}" class="flex-1 px-3 py-2 border border-gray-100 rounded-lg text-xs font-mono bg-gray-50 text-gray-400 cursor-default">
                                        <button type="button" @click="navigator.clipboard.writeText('{{ url('/api/webhooks/yengapay') }}')" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-primary transition-colors">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <button type="button" class="w-full py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-satellite-dish text-primary"></i> Tester la connexion
                                </button>
                            </div>
                        </div>

                        <!-- Africa's Talking -->
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50/30">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs">AT</div>
                                    <span class="font-bold text-sm">Africa's Talking (SMS)</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">ACTIF</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Username</label>
                                    <input type="text" name="settings[at_username]" value="{{ $settings['at_username'] ?? 'sandbox' }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs font-mono bg-white">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1 uppercase">Clé API (Secret)</label>
                                    <input :type="showKeys ? 'text' : 'password'" name="settings[at_api_key]" value="{{ $settings['at_api_key'] ?? 'sk_xxxxxxxxxxxxxxxxx' }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs font-mono bg-white">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Security -->
            <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" style="display: none;" class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                        <i class="fa-solid fa-user-shield text-red-500"></i>
                        <h3 class="text-lg font-bold text-gray-900">Paramètres de Sécurité</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Durée de validité OTP</label>
                                <select name="settings[otp_validity_minutes]" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                                    <option value="3" {{ ($settings['otp_validity_minutes'] ?? '5') == '3' ? 'selected' : '' }}>3 minutes</option>
                                    <option value="5" {{ ($settings['otp_validity_minutes'] ?? '5') == '5' ? 'selected' : '' }}>5 minutes (Défaut)</option>
                                    <option value="10" {{ ($settings['otp_validity_minutes'] ?? '5') == '10' ? 'selected' : '' }}>10 minutes</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tentatives OTP maximales</label>
                                <select name="settings[otp_max_attempts]" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                                    <option value="3" {{ ($settings['otp_max_attempts'] ?? '3') == '3' ? 'selected' : '' }}>3 tentatives</option>
                                    <option value="5" {{ ($settings['otp_max_attempts'] ?? '3') == '5' ? 'selected' : '' }}>5 tentatives</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Blocage PIN (Tentatives échouées)</label>
                                <select name="settings[pin_lockout_attempts]" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                                    <option value="3" {{ ($settings['pin_lockout_attempts'] ?? '5') == '3' ? 'selected' : '' }}>3 tentatives</option>
                                    <option value="5" {{ ($settings['pin_lockout_attempts'] ?? '5') == '5' ? 'selected' : '' }}>5 tentatives (Défaut)</option>
                                    <option value="10" {{ ($settings['pin_lockout_attempts'] ?? '5') == '10' ? 'selected' : '' }}>10 tentatives</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Durée de session Admin (Minutes)</label>
                                <select name="settings[admin_session_lifetime]" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                                    <option value="30" {{ ($settings['admin_session_lifetime'] ?? '60') == '30' ? 'selected' : '' }}>30 minutes</option>
                                    <option value="60" {{ ($settings['admin_session_lifetime'] ?? '60') == '60' ? 'selected' : '' }}>60 minutes</option>
                                    <option value="120" {{ ($settings['admin_session_lifetime'] ?? '60') == '120' ? 'selected' : '' }}>120 minutes</option>
                                    <option value="240" {{ ($settings['admin_session_lifetime'] ?? '60') == '240' ? 'selected' : '' }}>240 minutes</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Adresses IPs autorisées (Whitelist Admin)</label>
                                <textarea name="settings[admin_ip_whitelist]" rows="2" placeholder="127.0.0.1, 192.168.1.1, ..." class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none resize-none font-mono">{{ $settings['admin_ip_whitelist'] ?? '' }}</textarea>
                                <p class="text-[10px] text-gray-400 mt-1.5 italic"><i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1"></i> Séparez les adresses par des virgules. Laissez vide pour autoriser toutes les IPs.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Save Bar -->
            <div class="mt-8 bg-white border border-gray-100 rounded-2xl p-4 shadow-lg flex items-center justify-between sticky bottom-4 z-40">
                <div class="hidden md:block">
                    <p class="text-xs text-gray-500 font-medium">Certaines modifications peuvent affecter le comportement des transactions en cours.</p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <button type="reset" class="px-6 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors flex-1 md:flex-none">
                        Réinitialiser
                    </button>
                    <button type="submit" class="px-8 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-opacity-90 transition-all flex items-center justify-center gap-2 shadow-sm flex-1 md:flex-none">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Sauvegarder tout
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
