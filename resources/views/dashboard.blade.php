<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilotage Réseau Mobile — Supervision des Antennes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        telecom: {
                            50: '#fdf2f2',
                            100: '#fde8e8',
                            500: '#e02424',
                            600: '#c81e1e',
                            700: '#9b1c1c',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased">

    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-md w-full pointer-events-none"></div>

    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-red-600 flex items-center justify-center text-white font-black text-xl shadow">
                    F
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 leading-tight">Supervision Réseau Mobile</h1>
                    <p class="text-xs text-slate-500 font-medium">Console de pilotage des antennes & interventions</p>
                </div>
            </div>

            <div class="flex items-center gap-4 text-xs">
                <a href="/docs" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition border border-slate-200">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Documentation Swagger
                </a>

                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200">
                    <span id="api-status-dot" class="h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span id="api-status-text" class="text-slate-600 font-semibold">API en attente</span>
                </div>

                <div class="flex items-center gap-2">
                    <label for="api-key-input" class="text-slate-500 hidden md:inline font-medium">Clé API :</label>
                    <input type="password" id="api-key-input" value="{{ $defaultApiKey }}"
                        class="px-2.5 py-1 text-xs border border-slate-300 rounded-md font-mono bg-slate-50 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500 w-32 md:w-48"
                        placeholder="Bearer token...">
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total affiché</p>
                    <p id="stat-total" class="text-2xl font-black text-slate-800 mt-1">-</p>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-100 text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Antennes UP</p>
                    <p id="stat-up" class="text-2xl font-black text-emerald-700 mt-1">-</p>
                </div>
                <div class="p-2.5 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">Antennes DOWN</p>
                    <p id="stat-down" class="text-2xl font-black text-rose-700 mt-1">-</p>
                </div>
                <div class="p-2.5 rounded-lg bg-rose-50 text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Interventions actives</p>
                    <p id="stat-active" class="text-2xl font-black text-amber-700 mt-1">-</p>
                </div>
                <div class="p-2.5 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="filter-city" class="text-xs font-semibold text-slate-600">Ville :</label>
                    <select id="filter-city" class="px-3 py-1.5 text-xs font-medium bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">Toutes les villes</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label for="filter-status" class="text-xs font-semibold text-slate-600">Statut :</label>
                    <select id="filter-status" class="px-3 py-1.5 text-xs font-medium bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <option value="">Tous les statuts</option>
                        <option value="UP">UP (Opérationnelle)</option>
                        <option value="DOWN">DOWN (En panne / En cours)</option>
                    </select>
                </div>

                <button id="btn-reset-filters" class="text-xs text-slate-500 hover:text-slate-800 underline font-medium">
                    Réinitialiser
                </button>
            </div>

            <div class="flex items-center gap-2.5">
                <button id="btn-refresh" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition inline-flex items-center gap-1.5">
                    <svg id="icon-refresh" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Actualiser
                </button>

                <button id="btn-open-create-modal" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Déclarer une intervention
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3.5">Antenne</th>
                            <th scope="col" class="px-5 py-3.5">Ville</th>
                            <th scope="col" class="px-5 py-3.5">Statut Antenne</th>
                            <th scope="col" class="px-5 py-3.5">Dernière Intervention</th>
                            <th scope="col" class="px-5 py-3.5">Priorité</th>
                            <th scope="col" class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="antennas-tbody" class="divide-y divide-slate-100 bg-white font-medium text-slate-700">
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <div class="inline-flex items-center gap-2">
                                    <svg class="w-5 h-5 animate-spin text-red-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Chargement des données...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <div class="text-xs text-slate-500" id="pagination-info">
                    Affichage de 0 sur 0 antennes
                </div>
                <div class="flex items-center gap-2">
                    <button id="btn-prev-page" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        &larr; Précédent
                    </button>
                    <span id="pagination-current-page" class="text-xs font-bold text-slate-700 px-2">Page 1</span>
                    <button id="btn-next-page" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        Suivant &rarr;
                    </button>
                </div>
            </div>
        </div>

    </main>

    <div id="modal-create" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 transform transition-all">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="p-1.5 rounded bg-red-100 text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </span>
                    Déclarer une nouvelle intervention
                </h3>
                <button id="btn-close-modal" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="form-create-intervention" class="mt-4 space-y-4">
                <div>
                    <label for="create-antenna-id" class="block text-xs font-semibold text-slate-700 mb-1">Antenne ciblée</label>
                    <select id="create-antenna-id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none">
                        <option value="">Sélectionner une antenne...</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="create-technician" class="block text-xs font-semibold text-slate-700 mb-1">Technicien référent</label>
                        <input type="text" id="create-technician" required placeholder="ex: Jean Dupont"
                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="create-priority" class="block text-xs font-semibold text-slate-700 mb-1">Priorité</label>
                        <select id="create-priority" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none">
                            <option value="LOW">Faible (LOW)</option>
                            <option value="MEDIUM" selected>Moyenne (MEDIUM)</option>
                            <option value="HIGH">Haute (HIGH)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="create-description" class="block text-xs font-semibold text-slate-700 mb-1">Description de la panne / motif</label>
                    <textarea id="create-description" rows="3" required placeholder="Détaillez le problème détecté sur le panneau ou contrôleur..."
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>L'ouverture de l'intervention basculera automatiquement l'antenne au statut <strong>DOWN</strong>.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" id="btn-cancel-modal" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit" id="btn-submit-intervention" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition inline-flex items-center gap-1.5">
                        <span>Créer l'intervention</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            'use strict';

            let currentPage = 1;
            let currentTotalPages = 1;
            let antennasCache = [];

            const tbody = document.getElementById('antennas-tbody');
            const cityFilter = document.getElementById('filter-city');
            const statusFilter = document.getElementById('filter-status');
            const btnReset = document.getElementById('btn-reset-filters');
            const btnRefresh = document.getElementById('btn-refresh');
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');
            const paginationInfo = document.getElementById('pagination-info');
            const paginationCurrentPage = document.getElementById('pagination-current-page');
            const apiKeyInput = document.getElementById('api-key-input');
            const apiStatusDot = document.getElementById('api-status-dot');
            const apiStatusText = document.getElementById('api-status-text');

            const statTotal = document.getElementById('stat-total');
            const statUp = document.getElementById('stat-up');
            const statDown = document.getElementById('stat-down');
            const statActive = document.getElementById('stat-active');

            const modalCreate = document.getElementById('modal-create');
            const btnOpenModal = document.getElementById('btn-open-create-modal');
            const btnCloseModal = document.getElementById('btn-close-modal');
            const btnCancelModal = document.getElementById('btn-cancel-modal');
            const formCreate = document.getElementById('form-create-intervention');
            const selectAntennaModal = document.getElementById('create-antenna-id');
            const btnSubmit = document.getElementById('btn-submit-intervention');

            function getApiKey() {
                return (apiKeyInput.value || '').trim();
            }

            function showToast(message, type = 'success') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `p-4 rounded-xl shadow-lg border text-xs font-medium flex items-center justify-between gap-3 pointer-events-auto transition-all transform duration-300 translate-y-2 opacity-0 ${
                    type === 'success'
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-900'
                        : 'bg-rose-50 border-rose-200 text-rose-900'
                }`;

                const icon = type === 'success'
                    ? '<svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                    : '<svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';

                toast.innerHTML = `
                    <div class="flex items-center gap-2">
                        ${icon}
                        <span>${message}</span>
                    </div>
                    <button class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
                `;

                toast.querySelector('button').onclick = () => {
                    toast.remove();
                };

                container.appendChild(toast);
                setTimeout(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                }, 10);

                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }

            async function checkApiHealth() {
                try {
                    const res = await fetch('/api/health');
                    if (res.ok) {
                        apiStatusDot.className = 'h-2.5 w-2.5 rounded-full bg-emerald-500';
                        apiStatusText.textContent = 'API connectée';
                    } else {
                        throw new Error('API non saine');
                    }
                } catch (e) {
                    apiStatusDot.className = 'h-2.5 w-2.5 rounded-full bg-rose-500';
                    apiStatusText.textContent = 'API déconnectée';
                }
            }

            async function fetchAntennas(page = 1) {
                currentPage = page;
                const city = cityFilter.value;
                const status = statusFilter.value;

                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                            <div class="inline-flex items-center gap-2">
                                <svg class="w-5 h-5 animate-spin text-red-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Chargement des données...
                            </div>
                        </td>
                    </tr>
                `;

                const params = new URLSearchParams();
                params.append('page', page);
                params.append('per_page', 15);
                if (city) params.append('city', city);
                if (status) params.append('status', status);

                try {
                    const res = await fetch(`/api/v1/antennas?${params.toString()}`);
                    if (!res.ok) {
                        throw new Error(`Erreur HTTP: ${res.status}`);
                    }

                    const json = await res.json();
                    antennasCache = json.data || [];
                    const meta = json.meta || { current_page: 1, total: 0, last_page: 1 };
                    currentTotalPages = meta.last_page || 1;

                    renderTable(antennasCache);
                    renderPagination(meta);
                    updateStats(antennasCache, meta.total);
                    populateModalSelect(antennasCache);
                } catch (err) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-rose-600 font-semibold">
                                Impossible de charger la liste des antennes (${err.message}).
                            </td>
                        </tr>
                    `;
                    showToast('Erreur lors du chargement des antennes', 'error');
                }
            }

            function renderTable(antennas) {
                if (!antennas.length) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                Aucune antenne ne correspond aux critères sélectionnés.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = antennas.map(antenna => {
                    const isUp = antenna.status === 'UP';
                    const hasActive = antenna.has_active_intervention;
                    const latest = antenna.latest_intervention;

                    const statusBadge = isUp
                        ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                               <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> UP
                           </span>`
                        : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                               <span class="h-1.5 w-1.5 rounded-full bg-rose-600 animate-pulse"></span> DOWN
                           </span>`;

                    let priorityBadge = '<span class="text-slate-400 italic">Aucune</span>';
                    if (latest) {
                        const prio = latest.priority;
                        const prioColors = {
                            LOW: 'bg-slate-100 text-slate-700 border-slate-200',
                            MEDIUM: 'bg-amber-100 text-amber-800 border-amber-200',
                            HIGH: 'bg-rose-100 text-rose-800 border-rose-200',
                        };
                        priorityBadge = `<span class="px-2 py-0.5 rounded border text-[10px] font-bold ${prioColors[prio] || 'bg-slate-100'}">${prio}</span>`;
                    }

                    let interventionHtml = '<span class="text-slate-400 italic">Aucune intervention</span>';
                    if (latest) {
                        const dateFormatted = new Date(latest.created_at).toLocaleDateString('fr-FR', {
                            day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
                        });

                        const stateNotice = latest.is_active
                            ? `<span class="text-rose-600 font-bold inline-flex items-center gap-1">
                                   <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-ping"></span> En cours
                               </span>`
                            : `<span class="text-slate-400">Terminée</span>`;

                        interventionHtml = `
                            <div>
                                <div class="font-semibold text-slate-800 line-clamp-1" title="${escapeHtml(latest.description)}">
                                    ${escapeHtml(latest.description)}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Tech: <span class="font-medium text-slate-700">${escapeHtml(latest.technician_identity)}</span>
                                    &bull; ${dateFormatted} &bull; ${stateNotice}
                                </div>
                            </div>
                        `;
                    }

                    let actionsHtml = '';
                    if (hasActive && latest) {
                        actionsHtml = `
                            <button onclick="window.handleCloseIntervention(${latest.id}, ${antenna.id})"
                                id="btn-close-${latest.id}"
                                class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Clôturer
                            </button>
                        `;
                    } else {
                        actionsHtml = `
                            <button onclick="window.handlePreSelectAntenna(${antenna.id})"
                                class="px-2.5 py-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded text-xs font-medium transition">
                                + Intervenir
                            </button>
                        `;
                    }

                    return `
                        <tr class="hover:bg-slate-50/80 transition" id="row-antenna-${antenna.id}">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">${escapeHtml(antenna.name)}</div>
                                <div class="text-[11px] text-slate-400">ID: #${antenna.id}</div>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-700">
                                ${escapeHtml(antenna.city)}
                            </td>
                            <td class="px-5 py-3.5">
                                ${statusBadge}
                            </td>
                            <td class="px-5 py-3.5 max-w-xs">
                                ${interventionHtml}
                            </td>
                            <td class="px-5 py-3.5">
                                ${priorityBadge}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                ${actionsHtml}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            function renderPagination(meta) {
                paginationInfo.textContent = `Affichage de ${meta.from || 0} à ${meta.to || 0} sur ${meta.total} antennes`;
                paginationCurrentPage.textContent = `Page ${meta.current_page} sur ${meta.last_page || 1}`;

                btnPrev.disabled = meta.current_page <= 1;
                btnNext.disabled = meta.current_page >= meta.last_page;
            }

            function updateStats(antennas, totalCount) {
                statTotal.textContent = totalCount.toLocaleString('fr-FR');
                const upCount = antennas.filter(a => a.status === 'UP').length;
                const downCount = antennas.filter(a => a.status === 'DOWN').length;
                const activeCount = antennas.filter(a => a.has_active_intervention).length;

                statUp.textContent = upCount;
                statDown.textContent = downCount;
                statActive.textContent = activeCount;
            }

            function populateModalSelect(antennas) {
                const currentVal = selectAntennaModal.value;
                selectAntennaModal.innerHTML = '<option value="">Sélectionner une antenne...</option>' +
                    antennas.map(a => `<option value="${a.id}">${escapeHtml(a.name)} (${escapeHtml(a.city)}) - [${a.status}]</option>`).join('');
                if (currentVal) selectAntennaModal.value = currentVal;
            }

            window.handleCloseIntervention = async function(interventionId, antennaId) {
                const btn = document.getElementById(`btn-close-${interventionId}`);
                const apiKey = getApiKey();

                if (!apiKey) {
                    showToast('Veuillez renseigner une clé API dans l\'en-tête.', 'error');
                    apiKeyInput.focus();
                    return;
                }

                if (!confirm(`Confirmez-vous la clôture de l'intervention #${interventionId} ? L'antenne repassera au statut UP.`)) {
                    return;
                }

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Clôture...`;
                }

                try {
                    const res = await fetch(`/api/v1/interventions/${interventionId}/close`, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${apiKey}`,
                        },
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        throw new Error(data.message || `Erreur ${res.status}`);
                    }

                    showToast(`Intervention #${interventionId} clôturée avec succès. Antenne rétablie à UP.`);
                    fetchAntennas(currentPage);
                } catch (err) {
                    showToast(err.message, 'error');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Clôturer`;
                    }
                }
            };

            window.handlePreSelectAntenna = function(antennaId) {
                selectAntennaModal.value = antennaId;
                modalCreate.classList.remove('hidden');
            };

            formCreate.addEventListener('submit', async function(e) {
                e.preventDefault();
                const apiKey = getApiKey();

                if (!apiKey) {
                    showToast('Veuillez renseigner une clé API valide.', 'error');
                    apiKeyInput.focus();
                    return;
                }

                const payload = {
                    antenna_id: parseInt(document.getElementById('create-antenna-id').value, 10),
                    technician_identity: document.getElementById('create-technician').value.trim(),
                    priority: document.getElementById('create-priority').value,
                    description: document.getElementById('create-description').value.trim(),
                };

                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Enregistrement...`;

                try {
                    const res = await fetch('/api/v1/interventions', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${apiKey}`,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        let errMsg = data.message || 'Impossible de créer l\'intervention.';
                        if (data.errors) {
                            errMsg = Object.values(data.errors).flat().join(' ');
                        }
                        throw new Error(errMsg);
                    }

                    showToast(`Intervention créée avec succès sur l'antenne #${payload.antenna_id}.`);
                    formCreate.reset();
                    modalCreate.classList.add('hidden');
                    fetchAntennas(currentPage);
                } catch (err) {
                    showToast(err.message, 'error');
                } finally {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = `<span>Créer l'intervention</span>`;
                }
            });

            btnOpenModal.addEventListener('click', () => modalCreate.classList.remove('hidden'));
            btnCloseModal.addEventListener('click', () => modalCreate.classList.add('hidden'));
            btnCancelModal.addEventListener('click', () => modalCreate.classList.add('hidden'));

            cityFilter.addEventListener('change', () => fetchAntennas(1));
            statusFilter.addEventListener('change', () => fetchAntennas(1));
            btnReset.addEventListener('click', () => {
                cityFilter.value = '';
                statusFilter.value = '';
                fetchAntennas(1);
            });

            btnRefresh.addEventListener('click', () => fetchAntennas(currentPage));

            btnPrev.addEventListener('click', () => {
                if (currentPage > 1) fetchAntennas(currentPage - 1);
            });

            btnNext.addEventListener('click', () => {
                if (currentPage < currentTotalPages) fetchAntennas(currentPage + 1);
            });

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            checkApiHealth();
            fetchAntennas(1);

        })();
    </script>
</body>
</html>
