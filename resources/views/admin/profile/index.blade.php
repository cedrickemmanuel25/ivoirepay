@extends('layouts.admin')

@section('title', 'Mon Profil')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Mon Profil</h1>
        <p class="text-sm text-gray-500 mt-1">Gérez vos informations personnelles et paramètres de sécurité.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 shadow-sm">
        <div class="flex items-center gap-3 mb-2">
            <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg"></i>
            <span class="font-bold text-sm">Des erreurs sont survenues :</span>
        </div>
        <ul class="list-disc list-inside text-sm ml-7 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Left Column: Avatar & Personnal Info -->
    <div class="lg:col-span-1 space-y-8">
        
        <!-- Section 1: Avatar -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="avatarUpload()">
            <div class="p-6 border-b border-gray-100 text-center relative">
                <div class="relative inline-block group cursor-pointer" @click="$refs.avatarInput.click()">
                    <!-- Avatar Image -->
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Avatar Preview" class="w-[120px] h-[120px] rounded-full object-cover ring-4 ring-gray-50 shadow-sm mx-auto">
                    </template>
                    <template x-if="!previewUrl">
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" alt="Avatar" class="w-[120px] h-[120px] rounded-full object-cover ring-4 ring-gray-50 shadow-sm mx-auto">
                        @else
                            <div class="w-[120px] h-[120px] rounded-full bg-primary/10 text-primary flex items-center justify-center text-4xl font-bold ring-4 ring-gray-50 shadow-sm mx-auto">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </template>

                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-black/50 rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fa-solid fa-camera text-white text-xl mb-1 mt-2"></i>
                        <span class="text-white text-xs font-semibold">Modifier</span>
                    </div>

                    <!-- Process indicator overlay -->
                    <div x-show="uploading" class="absolute inset-0 bg-white/80 rounded-full flex items-center justify-center" style="display: none;">
                        <i class="fa-solid fa-spinner fa-spin text-primary text-2xl"></i>
                    </div>
                </div>

                <input type="file" x-ref="avatarInput" @change="fileSelected" accept="image/png, image/jpeg, image/jpg" class="hidden">
                
                <h2 class="text-xl font-bold text-gray-900 mt-4">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 mt-2">
                    Administrateur
                </span>
            </div>
            
            <!-- Toast UI logic for Avatar -->
            <div x-show="toastMessage" x-transition class="p-4 bg-gray-50 border-t border-gray-100 text-sm font-medium text-center" :class="toastType === 'success' ? 'text-green-600' : 'text-red-600'" x-text="toastMessage" style="display: none;"></div>
        </div>

        <!-- Section 2: Personal Info Edit -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900">Informations Personnelles</h3>
            </div>
            <form action="{{ route('admin.profile.info') }}" method="POST" class="p-5">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Adresse Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                </div>
                <!-- Phone isn't strictly requested to be editable, but often is. Leaving it out as per explicit requirements: "Section 2: Champs Nom + Email" -->

                <button type="submit" class="w-full py-2.5 px-4 bg-gray-900 hover:bg-black text-white rounded-xl font-semibold transition-colors shadow-sm">
                    Sauvegarder les modifications
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Password & Activity -->
    <div class="lg:col-span-2 space-y-8">
        
        <!-- Section 3: Password Update -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center gap-3 bg-gray-50/50">
                <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Paramètres de Sécurité</h3>
                    <p class="text-xs text-gray-500">Mettez à jour votre mot de passe pour sécuriser votre compte.</p>
                </div>
            </div>
            
            <form action="{{ route('admin.profile.password') }}" method="POST" class="p-6">
                @csrf
                <div class="max-w-md">
                    <div class="mb-4 relative" x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mot de passe actuel</label>
                        <input :type="show ? 'text' : 'password'" name="current_password" required class="w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                        <button type="button" @click="show = !show" class="absolute right-3 top-[34px] text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fa-regular text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>

                    <div class="mb-4 relative" x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nouveau mot de passe</label>
                        <input :type="show ? 'text' : 'password'" name="password" required class="w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                        <button type="button" @click="show = !show" class="absolute right-3 top-[34px] text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fa-regular text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                        <p class="text-[10px] text-gray-500 mt-1.5 font-medium flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-primary"></i> L'ancien doit contenir minimum 8 caractères, 1 majuscule, 1 chiffre et 1 caractère spécial.
                        </p>
                    </div>

                    <div class="mb-6 relative" x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmer le nouveau mot de passe</label>
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" required class="w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
                        <button type="button" @click="show = !show" class="absolute right-3 top-[34px] text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fa-regular text-sm" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>

                    <button type="submit" class="py-2.5 px-6 bg-primary hover:bg-opacity-90 text-white rounded-xl font-semibold transition-colors shadow-sm inline-flex items-center gap-2">
                        <i class="fa-solid fa-lock text-sm"></i> Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
        </div>

        <!-- Section 4: Activity Log -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Journal d'activités</h3>
                        <p class="text-xs text-gray-500">Historique de vos 20 dernières actions (Données fictives d'exemple).</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100">
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Cible</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Adresse IP</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($activities as $log)
                            <tr class="hover:bg-gray-50/30 transition-colors">
                                <td class="px-5 py-3">
                                    <span class="block text-sm font-semibold text-gray-900">{{ $log->action }}</span>
                                    <span class="block text-[10px] sm:text-xs text-gray-400 whitespace-nowrap overflow-hidden text-ellipsis max-w-[150px] sm:max-w-none break-all" title="{{ $log->browser }}"><i class="fa-brands fa-chrome mr-1"></i> {{ \Illuminate\Support\Str::limit($log->browser, 30) }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="block text-sm text-gray-600 font-medium">{{ $log->target }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-mono text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $log->ip }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="block text-sm text-gray-900">{{ $log->date->format('d/m/Y') }}</span>
                                    <span class="block text-xs text-gray-500">{{ $log->date->format('H:i:s') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50/50 text-center">
                <span class="text-xs text-gray-500 italic">Affiche uniquement les 20 entrées récentes.</span>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('avatarUpload', () => ({
        previewUrl: null,
        uploading: false,
        toastMessage: null,
        toastType: 'success',

        fileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            // JS Preview
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Trigger Upload
                this.uploadAvatar(file);
            } else {
                this.showToast('Veuillez sélectionner une image valide (JPG, PNG).', 'error');
            }
        },

        async uploadAvatar(file) {
            this.uploading = true;
            this.toastMessage = null;

            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const response = await fetch('{{ route("admin.profile.avatar") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erreur lors de l\'upload de l\'image.');
                }

                this.previewUrl = data.avatar_url; // Set final confirmed URL from server
                this.showToast('Photo de profil mise à jour avec succès.', 'success');
                
                // Optionally update header avatar without reloading
                // document.getElementById('header-avatar-img').src = data.avatar_url; 

            } catch (error) {
                console.error('Avatar upload error:', error);
                this.showToast(error.message, 'error');
            } finally {
                this.uploading = false;
            }
        },

        showToast(message, type) {
            this.toastMessage = message;
            this.toastType = type;
            setTimeout(() => { this.toastMessage = null; }, 4000);
        }
    }));
});
</script>
@endpush
@endsection
