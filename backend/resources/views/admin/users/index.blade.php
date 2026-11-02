@extends('layouts.admin')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Utilisateurs</h1>
        <p class="text-sm text-gray-500 mt-1">Gérez l'ensemble des utilisateurs de la plateforme</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 p-4">
    <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full relative">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Recherche</label>
            <div class="absolute inset-y-0 left-0 top-5 pl-3 flex items-center pointer-events-none">
                <i class="fa-solid fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, téléphone..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none">
        </div>
        
        <div class="w-full md:w-48">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Rôle</label>
            <select name="role" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous les rôles</option>
                <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Clients</option>
                <option value="merchant" {{ request('role') === 'merchant' ? 'selected' : '' }}>Commerçants</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
            </select>
        </div>

        <div class="w-full md:w-48">
            <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Statut</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white">
                <option value="">Tous</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>
        </div>

        <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0">
            <button type="submit" class="flex-1 md:flex-none px-6 py-2 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-colors font-medium text-sm text-center border border-primary">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm flex items-center justify-center border border-gray-200">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="userManager()">
    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition.opacity class="fixed bottom-4 right-4 z-50 rounded-lg shadow-xl p-4 flex items-center gap-3 border" :class="toast.type === 'success' ? 'bg-green-50 justify-between border border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'" style="display: none;">
        <div>
            <i class="fa-solid" :class="toast.type === 'success' ? 'fa-check-circle text-green-500' : 'fa-triangle-exclamation text-red-500'"></i>
            <span class="font-medium text-sm ml-2" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <!-- Confirm Modal -->
    <div x-show="modal.show" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 relative overflow-hidden" @click.away="closeModal()">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0" :class="modal.isDisabling ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'">
                    <i class="fa-solid text-xl" :class="modal.isDisabling ? 'fa-user-lock' : 'fa-user-check'"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="modal.title"></h3>
                    <p class="text-sm text-gray-500" x-text="modal.description"></p>
                </div>
            </div>

            <div class="flex gap-3 justify-end mt-6">
                <button @click="closeModal()" class="px-5 py-2 rounded-lg font-semibold text-sm transition-colors text-gray-600 hover:bg-gray-100" :disabled="isLoading">Annuler</button>
                <button @click="confirmAction()" class="px-5 py-2 rounded-lg font-semibold text-sm transition-colors text-white flex items-center gap-2" :class="modal.isDisabling ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'" :disabled="isLoading">
                    <i class="fa-solid fa-spinner fa-spin" x-show="isLoading" style="display: none;"></i>
                    <span x-text="modal.confirmText"></span>
                </button>
            </div>
        </div>
    </div>


    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Téléphone</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Rôle</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Inscription</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors" id="row-{{ $user->id }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="ml-3">
                                    <span class="block text-sm font-semibold text-gray-900">{{ $user->name }}</span>
                                    @if($user->email)
                                        <span class="block text-xs text-gray-500">{{ $user->email }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="block text-sm text-gray-900 font-mono">{{ $user->phone ?? 'Non spécifié' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->role === 'client')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200 font-bold">
                                    <i class="fa-solid fa-user"></i> Client
                                </span>
                            @elseif($user->role === 'merchant')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#1B4332]/10 text-[#1B4332] text-xs font-medium border border-[#1B4332]/20 font-bold">
                                    <i class="fa-solid fa-store"></i> Commerçant
                                </span>
                            @elseif($user->role === 'admin')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#F59E0B]/10 text-[#F59E0B] text-xs font-medium border border-[#F59E0B]/20 font-bold">
                                    <i class="fa-solid fa-shield-halved"></i> Admin
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="block text-sm text-gray-900">{{ $user->created_at->format('d/m/Y') }}</span>
                            <span class="block text-xs text-gray-500">{{ $user->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <!-- Toggle Button -->
                            @if($user->id !== auth()->id())
                                <button type="button" 
                                    @click="openModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->is_active ? 'true' : 'false' }})"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                    id="toggle-btn-{{ $user->id }}"
                                    :class="getToggleClass({{ $user->id }}, {{ $user->is_active ? 'true' : 'false' }})">
                                    <span class="sr-only">Toggle status</span>
                                    <span 
                                        class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="getTranslateClass({{ $user->id }}, {{ $user->is_active ? 'true' : 'false' }})">
                                        <span
                                            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                                            :class="getIconActiveOpacity({{ $user->id }}, {{ $user->is_active ? 'true' : 'false' }})"
                                            aria-hidden="true">
                                            <i class="fa-solid fa-check text-green-500 text-[10px]"></i>
                                        </span>
                                        <span
                                            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
                                            :class="getIconInactiveOpacity({{ $user->id }}, {{ $user->is_active ? 'true' : 'false' }})"
                                            aria-hidden="true">
                                            <i class="fa-solid fa-xmark text-gray-400 text-[10px]"></i>
                                        </span>
                                    </span>
                                </button>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Moi-même</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex items-center justify-center p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors tooltip relative group" title="Voir le profil">
                                <i class="fa-regular fa-eye text-lg"></i>
                                <span class="absolute right-full mr-2 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">Voir Profil</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-users-slash text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-sm font-medium">Aucun utilisateur trouvé</p>
                                <p class="text-xs text-gray-400 mt-1">Modifiez vos filtres de recherche.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $users->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userManager', () => ({
            toast: { show: false, message: '', type: 'success' },
            modal: { show: false, id: null, title: '', description: '', confirmText: '', isDisabling: false },
            isLoading: false,
            // Track local states to update UI instantly without full page reload
            userStates: {},

            getToggleClass(id, defaultState) {
                const isActive = this.userStates[id] !== undefined ? this.userStates[id] : defaultState;
                return isActive ? 'bg-green-500' : 'bg-gray-200';
            },

            getTranslateClass(id, defaultState) {
                const isActive = this.userStates[id] !== undefined ? this.userStates[id] : defaultState;
                return isActive ? 'translate-x-5' : 'translate-x-0';
            },

            getIconActiveOpacity(id, defaultState) {
                const isActive = this.userStates[id] !== undefined ? this.userStates[id] : defaultState;
                return isActive ? 'opacity-100 duration-200 ease-in' : 'opacity-0 duration-100 ease-out';
            },

            getIconInactiveOpacity(id, defaultState) {
                const isActive = this.userStates[id] !== undefined ? this.userStates[id] : defaultState;
                return isActive ? 'opacity-0 duration-100 ease-out' : 'opacity-100 duration-200 ease-in';
            },

            openModal(id, userName, currentState) {
                const isActive = this.userStates[id] !== undefined ? this.userStates[id] : currentState;
                this.modal.id = id;
                this.modal.isDisabling = isActive;
                
                if (isActive) {
                    this.modal.title = "Suspendre l'utilisateur";
                    this.modal.description = `Voulez-vous vraiment suspendre le compte de ${userName} ? Il ne pourra plus se connecter ou effectuer des transactions.`;
                    this.modal.confirmText = "Suspendre le compte";
                } else {
                    this.modal.title = "Réactiver l'utilisateur";
                    this.modal.description = `Voulez-vous réactiver le compte de ${userName} ?`;
                    this.modal.confirmText = "Réactiver le compte";
                }
                
                this.modal.show = true;
            },

            closeModal() {
                if(this.isLoading) return;
                this.modal.show = false;
            },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 4000);
            },

            async confirmAction() {
                this.isLoading = true;
                const id = this.modal.id;

                try {
                    const response = await fetch(`/admin/users/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Une erreur est survenue');
                    }

                    this.showToast(data.message, 'success');
                    
                    // Update state locally
                    this.userStates[id] = data.is_active;

                } catch (error) {
                    this.showToast(error.message, 'error');
                } finally {
                    this.isLoading = false;
                    this.closeModal();
                }
            }
        }));
    });
</script>
@endpush
