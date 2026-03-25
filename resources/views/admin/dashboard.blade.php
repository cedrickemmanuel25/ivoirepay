@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('content')
<div class="mb-8" data-aos="fade-right">
    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Tableau de Bord</h2>
    <p class="mt-2 text-gray-600">Bienvenue dans votre espace d'administration IvoirePay, {{ Auth::guard('admin')->user()->name }}.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Card 1 — Transactions Aujourd'hui -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border-l-4 border-primary hover:shadow-xl transition-all duration-300" data-aos="zoom-in" data-aos-delay="100">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-primary">
                <i class="fa-solid fa-chart-bar text-xl"></i>
            </div>
            <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">{{ $todayTransactionCount }} Trans.</span>
        </div>
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Aujourd'hui</h3>
        <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($todayTransactionAmount, 0, ',', ' ') }} FCFA</p>
    </div>

    <!-- Card 2 — Commerçants Actifs -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300" data-aos="zoom-in" data-aos-delay="200">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-secondary">
                <i class="fa-solid fa-store text-xl"></i>
            </div>
        </div>
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Commerçants Actifs</h3>
        <p class="text-2xl font-black text-gray-900 mt-1">{{ $activeMerchantsCount }}</p>
    </div>

    <!-- Card 3 — KYC en Attente -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 relative group" data-aos="zoom-in" data-aos-delay="300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-red-600">
                <i class="fa-solid fa-clock text-xl"></i>
            </div>
            @if($pendingKycCount > 0)
                <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded-full animate-pulse">{{ $pendingKycCount }} Urgents</span>
            @endif
        </div>
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">KYC en Attente</h3>
        <p class="text-2xl font-black text-gray-900 mt-1">{{ $pendingKycCount }}</p>
        <a href="#" class="absolute inset-0 z-10"></a>
    </div>

    <!-- Card 4 — Revenus du Mois -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300" data-aos="zoom-in" data-aos-delay="400">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-sack-dollar text-xl"></i>
            </div>
        </div>
        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Revenus/Commissions (Mois)</h3>
        <p class="text-2xl font-black text-gray-900 mt-1">{{ number_format($monthlyRevenue, 0, ',', ' ') }} FCFA</p>
    </div>
</div>

<div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Graphique 7 jours -->
    <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100" data-aos="fade-up">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex justify-between items-center">
            Transactions sur 7 jours
            <span class="text-xs text-gray-400 font-normal">Volume en FCFA</span>
        </h3>
        <div class="h-80">
            <canvas id="weeklyTrends"></canvas>
        </div>
    </div>

    <!-- Répartition Wallets -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100" data-aos="fade-up" data-aos-delay="100">
        <h3 class="text-xl font-bold text-gray-900 mb-6">Répartition Wallets</h3>
        <div class="h-80 flex items-center">
            <canvas id="walletDistributionChart"></canvas>
        </div>
    </div>
</div>

<div class="mt-10 bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden" data-aos="fade-up">
    <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center">
        <h3 class="text-xl font-bold text-gray-900">Transactions Récentes</h3>
        <button class="text-primary font-bold text-sm hover:underline">Voir tout</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-8 py-4 font-bold">Référence</th>
                    <th class="px-8 py-4 font-bold">Commerçant</th>
                    <th class="px-8 py-4 font-bold">Client</th>
                    <th class="px-8 py-4 font-bold">Montant</th>
                    <th class="px-8 py-4 font-bold">Méthode</th>
                    <th class="px-8 py-4 font-bold">Statut</th>
                    <th class="px-8 py-4 font-bold">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentTransactions as $tx)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-8 py-4 font-mono text-xs">{{ $tx->reference }}</td>
                    <td class="px-8 py-4">
                        <div class="text-sm font-bold text-gray-900">{{ $tx->merchant->business_name }}</div>
                        <div class="text-xs text-gray-500">{{ $tx->merchant->user->name }}</div>
                    </td>
                    <td class="px-8 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $tx->client->name }}</div>
                        <div class="text-xs text-gray-500">{{ $tx->client->phone }}</div>
                    </td>
                    <td class="px-8 py-4 font-bold text-sm">{{ number_format($tx->amount, 0, ',', ' ') }} FCFA</td>
                    <td class="px-8 py-4">
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-bold uppercase tracking-wider">
                            {{ $tx->wallet_type }}
                        </span>
                    </td>
                    <td class="px-8 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                            {{ $tx->status == 'success' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $tx->status }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-xs text-gray-500">{{ $tx->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-8 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-inbox text-5xl mb-4 opacity-20"></i>
                        <p>Aucune transaction récente pour le moment.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Courbe transactions 7 jours
    const ctx1 = document.getElementById('weeklyTrends');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: @json($weeklyData->pluck('date')),
                datasets: [{
                    label: 'Montant (XOF)',
                    data: @json($weeklyData->pluck('total')),
                    borderColor: '#1B4332',
                    backgroundColor: '#1B433215',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1B4332',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5] },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Doughnut répartition wallets
    const ctx2 = document.getElementById('walletDistributionChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: @json($walletDistribution->pluck('wallet_type')),
                datasets: [{
                    data: @json($walletDistribution->pluck('count')),
                    backgroundColor: ['#1B4332', '#F59E0B', '#3B82F6', '#10B981', '#6366F1'],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12, weight: 'bold' }
                        }
                    }
                }
            }
        });
    }
</script>
@endpush

