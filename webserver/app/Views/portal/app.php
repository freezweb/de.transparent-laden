<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kundenportal – Transparent Laden</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d'},
                    accent: {50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a'}
                }
            }
        }
    }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .fade-in { animation: fadeIn .2s ease-in; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
    </style>
</head>
<body class="h-full bg-gray-50" x-data="portalApp()" x-cloak>

<!-- Auth Screen -->
<template x-if="!token">
<div class="min-h-full flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <a href="/" class="text-3xl font-bold text-primary-700">⚡ Transparent Laden</a>
            <h2 class="mt-6 text-2xl font-bold text-gray-900" x-text="authMode==='login'?'Anmelden':'Registrieren'"></h2>
        </div>
        <div x-show="authError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm" x-text="authError"></div>

        <!-- Login -->
        <form x-show="authMode==='login'" @submit.prevent="login()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">E-Mail</label>
                <input type="email" x-model="authForm.email" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Passwort</label>
                <input type="password" x-model="authForm.password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <button type="submit" :disabled="authLoading" class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-primary-700 disabled:opacity-50 font-medium">
                <span x-show="!authLoading">Anmelden</span>
                <span x-show="authLoading">Wird geladen…</span>
            </button>
            <p class="text-center text-sm text-gray-600">Noch kein Konto? <button type="button" @click="authMode='register'" class="text-primary-600 hover:underline font-medium">Registrieren</button></p>
        </form>

        <!-- Register -->
        <form x-show="authMode==='register'" @submit.prevent="register()" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vorname</label>
                    <input type="text" x-model="authForm.first_name" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nachname</label>
                    <input type="text" x-model="authForm.last_name" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">E-Mail</label>
                <input type="email" x-model="authForm.email" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Passwort (min. 8 Zeichen)</label>
                <input type="password" x-model="authForm.password" required minlength="8" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Passwort bestätigen</label>
                <input type="password" x-model="authForm.password_confirmation" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <button type="submit" :disabled="authLoading" class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg hover:bg-primary-700 disabled:opacity-50 font-medium">
                <span x-show="!authLoading">Registrieren</span>
                <span x-show="authLoading">Wird geladen…</span>
            </button>
            <p class="text-center text-sm text-gray-600">Bereits ein Konto? <button type="button" @click="authMode='login'" class="text-primary-600 hover:underline font-medium">Anmelden</button></p>
        </form>
    </div>
</div>
</template>

<!-- Portal Shell -->
<template x-if="token">
<div class="flex h-full">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 hidden lg:flex flex-col">
        <div class="p-4 border-b border-gray-200">
            <a href="/" class="text-xl font-bold text-primary-700">⚡ Transparent Laden</a>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <template x-for="item in navItems" :key="item.id">
                <button @click="page=item.id; if(item.load) item.load()" :class="page===item.id ? 'bg-primary-50 text-primary-700 border-primary-500' : 'text-gray-600 hover:bg-gray-50 border-transparent'" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-3 border-l-2 transition-colors">
                    <span x-html="item.icon" class="w-5 h-5 flex-shrink-0"></span>
                    <span x-text="item.label"></span>
                </button>
            </template>
        </nav>
        <div class="p-4 border-t border-gray-200">
            <div class="text-sm text-gray-600 mb-2" x-text="user?.first_name+' '+user?.last_name"></div>
            <button @click="logout()" class="text-sm text-red-600 hover:underline">Abmelden</button>
        </div>
    </aside>

    <!-- Mobile header -->
    <div class="lg:hidden fixed top-0 left-0 right-0 bg-white border-b z-40 flex items-center justify-between px-4 py-3">
        <button @click="mobileNav=!mobileNav" class="text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="font-bold text-primary-700">⚡ Transparent Laden</span>
        <button @click="logout()" class="text-sm text-red-600">Abmelden</button>
    </div>

    <!-- Mobile nav overlay -->
    <div x-show="mobileNav" @click="mobileNav=false" class="lg:hidden fixed inset-0 bg-black/30 z-40"></div>
    <div x-show="mobileNav" x-transition class="lg:hidden fixed left-0 top-0 bottom-0 w-64 bg-white z-50 p-4 space-y-1 overflow-y-auto">
        <template x-for="item in navItems" :key="item.id">
            <button @click="page=item.id; mobileNav=false; if(item.load) item.load()" :class="page===item.id ? 'bg-primary-50 text-primary-700' : 'text-gray-600'" class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium">
                <span x-text="item.label"></span>
            </button>
        </template>
    </div>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto lg:pt-0 pt-14">
        <div class="max-w-6xl mx-auto p-6">

            <!-- Dashboard -->
            <div x-show="page==='dashboard'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <div class="text-sm text-gray-500">Aktive Session</div>
                        <div class="text-2xl font-bold mt-1" x-text="activeSession ? 'Lädt…' : 'Keine'"></div>
                        <template x-if="activeSession">
                            <div class="mt-2 text-sm text-primary-600">
                                <span x-text="activeSession.charge_point_name || 'Station'"></span>
                                – <span x-text="((activeSession.energy_kwh||0)).toFixed(2)"></span> kWh
                            </div>
                        </template>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <div class="text-sm text-gray-500">Aktives Abo</div>
                        <div class="text-2xl font-bold mt-1" x-text="subscription?.plan_name || 'Kein Abo'"></div>
                        <template x-if="subscription">
                            <div class="mt-2 text-sm text-gray-500" x-text="'Gültig bis ' + formatDate(subscription.end_date)"></div>
                        </template>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <div class="text-sm text-gray-500">Ladevorgänge</div>
                        <div class="text-2xl font-bold mt-1" x-text="sessions.length + (sessionsPager?.total ? ' / '+sessionsPager.total : '')"></div>
                    </div>
                </div>
                <!-- Recent sessions -->
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Letzte Ladevorgänge</h2>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Station</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Energie</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kosten</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="s in sessions.slice(0,5)" :key="s.id">
                                <tr class="hover:bg-gray-50 cursor-pointer" @click="page='sessions'; selectedSession=s">
                                    <td class="px-4 py-3 text-sm" x-text="formatDate(s.start_time)"></td>
                                    <td class="px-4 py-3 text-sm" x-text="s.charge_point_name||'—'"></td>
                                    <td class="px-4 py-3 text-sm" x-text="((s.energy_kwh||0)).toFixed(2)+' kWh'"></td>
                                    <td class="px-4 py-3 text-sm font-medium" x-text="formatCurrency(s.total_cost)"></td>
                                    <td class="px-4 py-3 text-sm"><span :class="statusClass(s.status)" class="px-2 py-1 rounded-full text-xs font-medium" x-text="s.status"></span></td>
                                </tr>
                            </template>
                            <template x-if="sessions.length===0">
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Keine Ladevorgänge vorhanden</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sessions / Ladevorgänge -->
            <div x-show="page==='sessions'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Ladevorgänge</h1>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Station</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dauer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Energie</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kosten</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="s in sessions" :key="s.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm" x-text="formatDate(s.start_time)"></td>
                                    <td class="px-4 py-3 text-sm" x-text="s.charge_point_name||'—'"></td>
                                    <td class="px-4 py-3 text-sm" x-text="formatDuration(s.start_time,s.end_time)"></td>
                                    <td class="px-4 py-3 text-sm" x-text="((s.energy_kwh||0)).toFixed(2)+' kWh'"></td>
                                    <td class="px-4 py-3 text-sm font-medium" x-text="formatCurrency(s.total_cost)"></td>
                                    <td class="px-4 py-3 text-sm"><span :class="statusClass(s.status)" class="px-2 py-1 rounded-full text-xs font-medium" x-text="s.status"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="sessionsPager?.hasMore" class="mt-4 text-center">
                    <button @click="loadMoreSessions()" class="text-primary-600 hover:underline text-sm">Weitere laden…</button>
                </div>
            </div>

            <!-- Active Session / Live -->
            <div x-show="page==='live'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Live-Status</h1>
                <template x-if="activeSession">
                    <div class="bg-white rounded-xl shadow-sm border p-6 max-w-lg">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold" x-text="activeSession.charge_point_name||'Ladestation'"></h2>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Aktiv</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div><div class="text-sm text-gray-500">Energie</div><div class="text-xl font-bold" x-text="((liveData?.energy_kwh||activeSession.energy_kwh||0)).toFixed(2)+' kWh'"></div></div>
                            <div><div class="text-sm text-gray-500">Aktuelle Kosten</div><div class="text-xl font-bold text-primary-700" x-text="formatCurrency(liveData?.estimated_cost||activeSession.total_cost||0)"></div></div>
                            <div><div class="text-sm text-gray-500">Leistung</div><div class="text-xl font-bold" x-text="((liveData?.power_kw||0)).toFixed(1)+' kW'"></div></div>
                            <div><div class="text-sm text-gray-500">Dauer</div><div class="text-xl font-bold" x-text="formatDuration(activeSession.start_time)"></div></div>
                        </div>
                        <button @click="stopCharging()" :disabled="stopping" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 disabled:opacity-50 font-medium">
                            <span x-show="!stopping">Laden beenden</span>
                            <span x-show="stopping">Wird beendet…</span>
                        </button>
                    </div>
                </template>
                <template x-if="!activeSession">
                    <div class="bg-white rounded-xl shadow-sm border p-12 text-center text-gray-400 max-w-lg">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <p>Keine aktive Ladesitzung</p>
                    </div>
                </template>
            </div>

            <!-- Invoices / Rechnungen -->
            <div x-show="page==='invoices'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Rechnungen</h1>
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nr.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Betrag</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PDF</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="inv in invoices" :key="inv.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-mono" x-text="inv.invoice_number"></td>
                                    <td class="px-4 py-3 text-sm" x-text="formatDate(inv.created_at)"></td>
                                    <td class="px-4 py-3 text-sm font-medium" x-text="formatCurrency(inv.total_amount)"></td>
                                    <td class="px-4 py-3 text-sm"><span :class="invoiceStatusClass(inv.status)" class="px-2 py-1 rounded-full text-xs font-medium" x-text="inv.status"></span></td>
                                    <td class="px-4 py-3 text-sm">
                                        <button @click="downloadPdf(inv.id)" class="text-primary-600 hover:underline text-sm">PDF</button>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="invoices.length===0">
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Keine Rechnungen vorhanden</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Methods / Zahlungsarten -->
            <div x-show="page==='payments'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Zahlungsarten</h1>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <template x-for="pm in paymentMethods" :key="pm.id">
                        <div class="bg-white rounded-xl shadow-sm border p-4 flex items-center justify-between">
                            <div>
                                <div class="font-medium" x-text="pm.type.toUpperCase()"></div>
                                <div class="text-sm text-gray-500" x-text="pm.display_name || pm.last_four ? '****'+pm.last_four : ''"></div>
                                <span x-show="pm.is_default" class="text-xs text-primary-600 font-medium">Standard</span>
                            </div>
                            <div class="flex gap-2">
                                <button x-show="!pm.is_default" @click="setDefaultPayment(pm.id)" class="text-xs text-primary-600 hover:underline">Standard</button>
                                <button x-show="!pm.is_default" @click="deletePayment(pm.id)" class="text-xs text-red-600 hover:underline">Löschen</button>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- Add payment -->
                <div class="bg-white rounded-xl shadow-sm border p-6 max-w-md">
                    <h2 class="text-lg font-semibold mb-4">Zahlungsart hinzufügen</h2>
                    <form @submit.prevent="addPayment()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Typ</label>
                            <select x-model="newPayment.type" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                                <option value="credit_card">Kreditkarte</option>
                                <option value="debit_card">Debitkarte</option>
                                <option value="paypal">PayPal</option>
                                <option value="sepa">SEPA-Lastschrift</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Token / Referenz</label>
                            <input type="text" x-model="newPayment.token" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                        <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 text-sm font-medium">Hinzufügen</button>
                    </form>
                </div>
            </div>

            <!-- Subscription / Abo -->
            <div x-show="page==='subscription'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Abonnement</h1>
                <!-- Current sub -->
                <template x-if="subscription">
                    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6 max-w-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-xl font-bold">Transparent Laden</h2>
                                <p class="text-sm text-gray-500 mt-1">9,99 &euro;/Monat – monatlich kündbar</p>
                            </div>
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium" x-text="subscription.status"></span>
                        </div>
                        <div class="mt-4 text-sm text-gray-600">
                            Gültig bis: <span class="font-medium" x-text="formatDate(subscription.end_date)"></span>
                        </div>
                        <button @click="cancelSubscription()" class="mt-4 text-red-600 text-sm hover:underline">Abo kündigen</button>
                    </div>
                </template>
                <!-- Single plan info -->
                <template x-if="!subscription">
                    <div class="max-w-lg">
                        <div class="bg-white rounded-xl shadow-sm border p-6">
                            <h2 class="text-xl font-bold mb-2">Transparent Laden</h2>
                            <div class="text-3xl font-bold">9,99 &euro;<span class="text-sm font-normal text-gray-500">/Monat</span></div>
                            <p class="text-sm text-gray-500 mt-1 mb-4">Monatlich kündbar</p>
                            <ul class="space-y-2 mb-6">
                                <li class="text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Prozentuale Preisaufschlüsselung vor jedem Ladevorgang
                                </li>
                                <li class="text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Marge offen ausgewiesen
                                </li>
                                <li class="text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Betreiber-Gebühren transparent angezeigt
                                </li>
                                <li class="text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Zahlungsart-Kosten sichtbar
                                </li>
                                <li class="text-sm text-gray-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Zugang zum europaweiten Ladenetzwerk
                                </li>
                            </ul>
                            <button @click="subscribe(plans[0]?.id||1,'monthly')" class="w-full bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 text-sm font-medium">Jetzt abonnieren</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Devices / Push-Geräte -->
            <div x-show="page==='devices'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Geräte & Benachrichtigungen</h1>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Devices -->
                    <div>
                        <h2 class="text-lg font-semibold mb-3">Registrierte Geräte</h2>
                        <div class="space-y-3">
                            <template x-for="d in devices" :key="d.id">
                                <div class="bg-white rounded-lg shadow-sm border p-4 flex justify-between items-center">
                                    <div>
                                        <div class="font-medium text-sm" x-text="d.device_name||d.platform"></div>
                                        <div class="text-xs text-gray-500" x-text="d.platform+' · '+formatDate(d.last_used_at||d.created_at)"></div>
                                    </div>
                                    <button @click="deleteDevice(d.id)" class="text-red-600 text-xs hover:underline">Entfernen</button>
                                </div>
                            </template>
                            <div x-show="devices.length===0" class="text-gray-400 text-sm">Keine Geräte registriert</div>
                        </div>
                    </div>
                    <!-- Notification prefs -->
                    <div>
                        <h2 class="text-lg font-semibold mb-3">Benachrichtigungen</h2>
                        <div class="bg-white rounded-lg shadow-sm border p-4 space-y-3">
                            <template x-for="pref in notifPrefs" :key="pref.event_type">
                                <label class="flex items-center justify-between">
                                    <span class="text-sm" x-text="notifLabel(pref.event_type)"></span>
                                    <input type="checkbox" :checked="pref.push_enabled" @change="toggleNotif(pref.event_type, $event.target.checked)" class="rounded text-primary-600 focus:ring-primary-500">
                                </label>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile / Einstellungen -->
            <div x-show="page==='profile'" class="fade-in">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Profil & Einstellungen</h1>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Profile form -->
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h2 class="text-lg font-semibold mb-4">Profil bearbeiten</h2>
                        <div x-show="profileMsg" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-sm" x-text="profileMsg"></div>
                        <form @submit.prevent="updateProfile()" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vorname</label>
                                    <input type="text" x-model="profileForm.first_name" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nachname</label>
                                    <input type="text" x-model="profileForm.last_name" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefon</label>
                                <input type="tel" x-model="profileForm.phone" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adresse</label>
                                <input type="text" x-model="profileForm.address" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">PLZ</label>
                                    <input type="text" x-model="profileForm.postal_code" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stadt</label>
                                    <input type="text" x-model="profileForm.city" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                                </div>
                            </div>
                            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 text-sm font-medium">Speichern</button>
                        </form>
                    </div>
                    <!-- Password -->
                    <div class="bg-white rounded-xl shadow-sm border p-6">
                        <h2 class="text-lg font-semibold mb-4">Passwort ändern</h2>
                        <div x-show="pwMsg" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-sm" x-text="pwMsg"></div>
                        <div x-show="pwError" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm" x-text="pwError"></div>
                        <form @submit.prevent="changePassword()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Aktuelles Passwort</label>
                                <input type="password" x-model="pwForm.current_password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Neues Passwort</label>
                                <input type="password" x-model="pwForm.new_password" required minlength="8" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Neues Passwort bestätigen</label>
                                <input type="password" x-model="pwForm.new_password_confirmation" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>
                            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 text-sm font-medium">Passwort ändern</button>
                        </form>
                    </div>
                </div>
                <!-- Danger zone -->
                <div class="mt-6 bg-white rounded-xl shadow-sm border border-red-200 p-6 max-w-md">
                    <h2 class="text-lg font-semibold text-red-600 mb-2">Gefahrenzone</h2>
                    <p class="text-sm text-gray-600 mb-4">Account unwiderruflich löschen. Alle Daten gehen verloren.</p>
                    <button @click="if(confirm('Account wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) deleteAccount()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm font-medium">Account löschen</button>
                </div>
            </div>

        </div>
    </main>
</div>
</template>

<script>
const API = '/api/v1';

function portalApp() {
    return {
        // Auth
        token: localStorage.getItem('portal_token') || '',
        refreshToken: localStorage.getItem('portal_refresh') || '',
        authMode: 'login',
        authForm: { email:'', password:'', first_name:'', last_name:'', password_confirmation:'' },
        authError: '',
        authLoading: false,

        // Nav
        page: 'dashboard',
        mobileNav: false,

        // Data
        user: null,
        activeSession: null,
        liveData: null,
        sessions: [],
        sessionsPager: null,
        sessionsPage: 1,
        invoices: [],
        paymentMethods: [],
        subscription: null,
        plans: [],
        devices: [],
        notifPrefs: [],
        stopping: false,

        // Forms
        profileForm: {},
        profileMsg: '',
        pwForm: { current_password:'', new_password:'', new_password_confirmation:'' },
        pwMsg: '',
        pwError: '',
        newPayment: { type:'credit_card', token:'' },

        // Live poll
        liveInterval: null,

        get navItems() {
            return [
                { id:'dashboard', label:'Dashboard', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4"/></svg>', load:()=>this.loadDashboard() },
                { id:'live', label:'Live-Status', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>', load:()=>this.loadLive() },
                { id:'sessions', label:'Ladevorgänge', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', load:()=>this.loadSessions() },
                { id:'invoices', label:'Rechnungen', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', load:()=>this.loadInvoices() },
                { id:'payments', label:'Zahlungsarten', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>', load:()=>this.loadPayments() },
                { id:'subscription', label:'Abonnement', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>', load:()=>this.loadSubscription() },
                { id:'devices', label:'Geräte & Push', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>', load:()=>this.loadDevices() },
                { id:'profile', label:'Einstellungen', icon:'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', load:()=>this.loadProfile() },
            ];
        },

        async init() {
            if (this.token) {
                await this.loadDashboard();
            }
        },

        // Auth
        async login() {
            this.authLoading = true; this.authError = '';
            try {
                const r = await this.api('POST', '/auth/login', { email: this.authForm.email, password: this.authForm.password });
                this.setAuth(r.data);
                await this.loadDashboard();
            } catch(e) { this.authError = e.message || 'Anmeldung fehlgeschlagen'; }
            this.authLoading = false;
        },
        async register() {
            this.authLoading = true; this.authError = '';
            if (this.authForm.password !== this.authForm.password_confirmation) {
                this.authError = 'Passwörter stimmen nicht überein';
                this.authLoading = false; return;
            }
            try {
                const r = await this.api('POST', '/auth/register', {
                    email: this.authForm.email, password: this.authForm.password,
                    password_confirmation: this.authForm.password_confirmation,
                    first_name: this.authForm.first_name, last_name: this.authForm.last_name
                });
                this.setAuth(r.data);
                await this.loadDashboard();
            } catch(e) { this.authError = e.message || 'Registrierung fehlgeschlagen'; }
            this.authLoading = false;
        },
        setAuth(data) {
            this.token = data.token || data.access_token || '';
            this.refreshToken = data.refresh_token || '';
            localStorage.setItem('portal_token', this.token);
            localStorage.setItem('portal_refresh', this.refreshToken);
        },
        async logout() {
            try { await this.api('POST', '/auth/logout'); } catch(e) {}
            this.token = ''; this.refreshToken = '';
            localStorage.removeItem('portal_token');
            localStorage.removeItem('portal_refresh');
            if (this.liveInterval) clearInterval(this.liveInterval);
        },

        // Data loading
        async loadDashboard() {
            const [profile, active, sessions, sub] = await Promise.allSettled([
                this.api('GET', '/user/profile'),
                this.api('GET', '/charging/active'),
                this.api('GET', '/charging/history?per_page=5'),
                this.api('GET', '/subscriptions/current'),
            ]);
            if (profile.status==='fulfilled') { this.user = profile.value.data; this.profileForm = {...this.user}; }
            if (active.status==='fulfilled') this.activeSession = active.value.data;
            if (sessions.status==='fulfilled') { this.sessions = sessions.value.data?.data || sessions.value.data || []; this.sessionsPager = sessions.value.data?.pager; }
            if (sub.status==='fulfilled') this.subscription = sub.value.data;
        },
        async loadSessions() {
            this.sessionsPage = 1;
            const r = await this.api('GET', '/charging/history?per_page=20&page=1');
            this.sessions = r.data?.data || r.data || [];
            this.sessionsPager = r.data?.pager;
        },
        async loadMoreSessions() {
            this.sessionsPage++;
            const r = await this.api('GET', `/charging/history?per_page=20&page=${this.sessionsPage}`);
            const more = r.data?.data || r.data || [];
            this.sessions = [...this.sessions, ...more];
            this.sessionsPager = r.data?.pager;
        },
        async loadLive() {
            if (this.liveInterval) clearInterval(this.liveInterval);
            try {
                const r = await this.api('GET', '/charging/active');
                this.activeSession = r.data;
                if (this.activeSession) {
                    this.liveInterval = setInterval(async () => {
                        try {
                            const lr = await this.api('GET', `/charging/${this.activeSession.id}/live`);
                            this.liveData = lr.data;
                        } catch(e) {}
                    }, 5000);
                }
            } catch(e) { this.activeSession = null; }
        },
        async stopCharging() {
            if (!this.activeSession) return;
            this.stopping = true;
            try {
                await this.api('POST', `/charging/${this.activeSession.id}/stop`);
                this.activeSession = null; this.liveData = null;
                if (this.liveInterval) clearInterval(this.liveInterval);
            } catch(e) { alert(e.message || 'Fehler beim Beenden'); }
            this.stopping = false;
        },
        async loadInvoices() {
            const r = await this.api('GET', '/invoices?per_page=50');
            this.invoices = r.data?.data || r.data || [];
        },
        async downloadPdf(id) {
            window.open(`${API}/invoices/${id}/pdf?token=${this.token}`, '_blank');
        },
        async loadPayments() {
            const r = await this.api('GET', '/payment-methods');
            this.paymentMethods = r.data || [];
        },
        async addPayment() {
            await this.api('POST', '/payment-methods', this.newPayment);
            this.newPayment = { type:'credit_card', token:'' };
            await this.loadPayments();
        },
        async setDefaultPayment(id) {
            await this.api('PUT', `/payment-methods/${id}/default`);
            await this.loadPayments();
        },
        async deletePayment(id) {
            if (!confirm('Zahlungsart wirklich löschen?')) return;
            await this.api('DELETE', `/payment-methods/${id}`);
            await this.loadPayments();
        },
        async loadSubscription() {
            const [sub, plans] = await Promise.allSettled([
                this.api('GET', '/subscriptions/current'),
                this.api('GET', '/subscriptions/plans'),
            ]);
            if (sub.status==='fulfilled') this.subscription = sub.value.data;
            if (plans.status==='fulfilled') this.plans = plans.value.data || [];
        },
        async subscribe(planId, cycle) {
            await this.api('POST', '/subscriptions/subscribe', { plan_id: planId, billing_cycle: cycle });
            await this.loadSubscription();
        },
        async cancelSubscription() {
            if (!confirm('Abo wirklich kündigen?')) return;
            await this.api('POST', '/subscriptions/cancel');
            this.subscription = null;
            await this.loadSubscription();
        },
        async loadDevices() {
            const [dev, notif] = await Promise.allSettled([
                this.api('GET', '/devices'),
                this.api('GET', '/notifications/preferences'),
            ]);
            if (dev.status==='fulfilled') this.devices = dev.value.data || [];
            if (notif.status==='fulfilled') this.notifPrefs = notif.value.data || [];
        },
        async deleteDevice(id) {
            await this.api('DELETE', `/devices/${id}`);
            await this.loadDevices();
        },
        async toggleNotif(eventType, enabled) {
            await this.api('PUT', '/notifications/preferences', { event_type: eventType, push_enabled: enabled });
        },
        async loadProfile() {
            const r = await this.api('GET', '/user/profile');
            this.user = r.data;
            this.profileForm = {...this.user};
        },
        async updateProfile() {
            this.profileMsg = '';
            await this.api('PUT', '/user/profile', this.profileForm);
            this.profileMsg = 'Profil gespeichert';
            setTimeout(()=> this.profileMsg = '', 3000);
        },
        async changePassword() {
            this.pwMsg = ''; this.pwError = '';
            if (this.pwForm.new_password !== this.pwForm.new_password_confirmation) {
                this.pwError = 'Passwörter stimmen nicht überein'; return;
            }
            try {
                await this.api('PUT', '/user/password', this.pwForm);
                this.pwMsg = 'Passwort geändert';
                this.pwForm = { current_password:'', new_password:'', new_password_confirmation:'' };
                setTimeout(()=> this.pwMsg = '', 3000);
            } catch(e) { this.pwError = e.message || 'Fehler'; }
        },
        async deleteAccount() {
            await this.api('DELETE', '/user/account');
            this.logout();
        },

        // API helper
        async api(method, path, body) {
            const opts = { method, headers: { 'Content-Type':'application/json', 'Accept':'application/json' } };
            if (this.token) opts.headers['Authorization'] = `Bearer ${this.token}`;
            if (body) opts.body = JSON.stringify(body);
            const res = await fetch(API + path, opts);
            if (res.status === 401 && this.refreshToken) {
                const ref = await fetch(API + '/auth/refresh', {
                    method:'POST', headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ refresh_token: this.refreshToken })
                });
                if (ref.ok) {
                    const rd = await ref.json();
                    this.setAuth(rd.data || rd);
                    opts.headers['Authorization'] = `Bearer ${this.token}`;
                    const retry = await fetch(API + path, opts);
                    if (!retry.ok) throw new Error((await retry.json()).message || 'Fehler');
                    return retry.json();
                }
                this.logout(); throw new Error('Sitzung abgelaufen');
            }
            if (!res.ok) {
                const err = await res.json().catch(()=>({}));
                throw new Error(err.message || err.messages?.error || `Fehler ${res.status}`);
            }
            return res.json();
        },

        // Helpers
        formatDate(d) { if(!d) return '—'; return new Date(d).toLocaleDateString('de-DE',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}); },
        formatCurrency(v) { return new Intl.NumberFormat('de-DE',{style:'currency',currency:'EUR'}).format(v||0); },
        formatDuration(start, end) {
            if (!start) return '—';
            const s = new Date(start), e = end ? new Date(end) : new Date();
            const mins = Math.round((e-s)/60000);
            if (mins < 60) return mins + ' Min';
            return Math.floor(mins/60) + 'h ' + (mins%60) + 'min';
        },
        statusClass(s) {
            const map = { active:'bg-green-100 text-green-800', completed:'bg-blue-100 text-blue-800', failed:'bg-red-100 text-red-800', pending:'bg-yellow-100 text-yellow-800' };
            return map[s] || 'bg-gray-100 text-gray-800';
        },
        invoiceStatusClass(s) {
            const map = { paid:'bg-green-100 text-green-800', pending:'bg-yellow-100 text-yellow-800', overdue:'bg-red-100 text-red-800', draft:'bg-gray-100 text-gray-800' };
            return map[s] || 'bg-gray-100 text-gray-800';
        },
        notifLabel(t) {
            const map = {
                session_started:'Ladevorgang gestartet', session_completed:'Ladevorgang beendet',
                session_failed:'Ladevorgang fehlgeschlagen', cost_threshold:'Kostenschwelle erreicht',
                invoice_created:'Neue Rechnung', subscription_expiring:'Abo läuft ab', subscription_cancelled:'Abo gekündigt'
            };
            return map[t] || t;
        },
    };
}
</script>
</body>
</html>
