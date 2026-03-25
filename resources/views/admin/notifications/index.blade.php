@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Centre de Notifications</h1>
        <p class="text-sm text-gray-500 mt-1">Gérez vos alertes et envoyez des messages en masse aux utilisateurs.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 flex items-center gap-3 shadow-sm">
        <i class="fa-solid fa-check-circle text-green-500 text-lg"></i>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 shadow-sm">
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

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <!-- Left Column: Send Manual Notification -->
    <div class="xl:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
            <div class="p-5 border-b border-gray-100 flex items-center gap-3 bg-gray-50/50">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Envoi Manuel (Masse)</h2>
            </div>
            
            <form action="{{ route('admin.notifications.send-bulk') }}" method="POST" class="p-5" x-data="{ message: '', chars: 0 }">
                @csrf
                
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Cible des destinataires</label>
                    <div class="relative">
                        <select name="target" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none bg-white appearance-none pr-10">
                            <option value="all">Tous les utilisateurs actifs</option>
                            <option value="clients">Clients uniquement</option>
                            <option value="merchants">Commerçants uniquement</option>
                            <!-- Optional: For a specific user, we'd add an autocomplete logic. To keep it simple and robust here, we'll focus on groups, or you can switch to "specific" if integrated with an autocomplete JS. We omitted the autocomplete input from this basic implementation, but the backend supports 'target=specific'. -->
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-3 top-3.5 text-gray-400 text-[10px] pointer-events-none"></i>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wider">Canaux de diffusion</label>
                    <div class="flex flex-col gap-3">
                        <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer select-none">
                            <input type="checkbox" name="channels[inapp]" value="1" checked class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-bell text-gray-400 w-4"></i>
                                <span class="text-sm font-medium text-gray-700">Notification In-App</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors cursor-pointer select-none">
                            <input type="checkbox" name="channels[sms]" value="1" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary focus:ring-2">
                            <div class="flex items-center gap-2 relative group">
                                <i class="fa-solid fa-comment-sms text-gray-400 w-4"></i>
                                <span class="text-sm font-medium text-gray-700">Envoi par SMS <span class="text-xs text-gray-400 font-normal">(Africa's Talking)</span></span>
                                <div class="absolute bottom-full left-0 mb-2 w-48 p-2 bg-gray-900/90 text-[10px] text-white rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Des frais SMS de votre opérateur seront appliqués pour chaque destinataire.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Message</label>
                        <span class="text-xs font-medium" :class="chars > 160 ? 'text-red-500' : 'text-gray-400'">
                            <span x-text="chars"></span> / 160
                        </span>
                    </div>
                    <textarea 
                        name="message" 
                        x-model="message" 
                        @input="chars = message.length"
                        rows="4" 
                        maxlength="160" 
                        required 
                        placeholder="Tapez le contenu de la notification ici..." 
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm outline-none resize-none"
                    ></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-primary hover:bg-opacity-90 text-white rounded-xl font-semibold transition-colors flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-paper-plane text-sm"></i> Envoyer la campagne
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Notifications List -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Notifications Reçues</h2>
                </div>

                <form action="{{ route('admin.notifications.mark-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl font-semibold text-sm transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-check-double text-primary"></i> 
                        <span class="hidden sm:inline">Tout marquer comme lu</span>
                    </button>
                </form>
            </div>

            <div class="divide-y divide-gray-50">
                @forelse($notifications as $notif)
                    @php
                        $isUnread = $notif->read_at === null;
                        
                        // Default Icon
                        $iconClass = 'fa-bell text-gray-400';
                        $iconBg = 'bg-gray-100';

                        if ($notif->type === 'kyc_submitted') {
                            $iconClass = 'fa-id-card text-blue-600';
                            $iconBg = 'bg-blue-100';
                        } elseif ($notif->type === 'withdrawal_requested') {
                            $iconClass = 'fa-money-bill-transfer text-orange-600';
                            $iconBg = 'bg-orange-100';
                        } elseif ($notif->type === 'payment_received') {
                            $iconClass = 'fa-wallet text-green-600';
                            $iconBg = 'bg-green-100';
                        }
                    @endphp

                    <div class="p-5 hover:bg-gray-50 transition-colors {{ $isUnread ? 'bg-[#D1FAE5]/30 border-l-4 border-l-green-500' : 'bg-white border-l-4 border-l-transparent' }}">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0 mt-1">
                                <i class="fa-solid {{ $iconClass }}"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900">{{ $notif->title }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">{{ $notif->message }}</p>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-400 whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="mt-2 text-xs font-medium">
                                    @if($isUnread)
                                        <span class="text-green-600"><i class="fa-solid fa-circle text-[8px] mr-1"></i> Non lue</span>
                                    @else
                                        <span class="text-gray-400"><i class="fa-regular fa-circle-check mr-1"></i> Lue le {{ $notif->read_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-500">
                        <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4 border border-gray-100">
                            <i class="fa-regular fa-bell-slash text-2xl text-gray-400"></i>
                        </div>
                        <p class="font-medium text-gray-600">Aucune notification reçue</p>
                        <p class="text-sm mt-1">Vous n'avez pas de notifications dans votre historique.</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $notifications->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
