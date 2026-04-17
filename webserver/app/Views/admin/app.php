<!DOCTYPE html>
<html lang="de" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Transparent Laden</title>
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
        [x-cloak]{display:none!important}
        .fade-in{animation:fadeIn .2s ease-in}
        @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
        .badge{@apply px-2 py-0.5 rounded-full text-xs font-medium}
    </style>
</head>
<body class="h-full bg-gray-100" x-data="adminApp()" x-cloak>

<!-- ==================== AUTH SCREEN ==================== -->
<template x-if="!token">
<div class="min-h-full flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">⚡ Admin-Portal</h1>
            <p class="text-gray-500 mt-2">Transparent Laden Verwaltung</p>
        </div>
        <div x-show="authError" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm" x-text="authError"></div>

        <!-- Login -->
        <div x-show="authStep==='login'" class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold mb-4">Anmelden</h2>
            <form @submit.prevent="adminLogin()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-Mail</label>
                    <input type="email" x-model="authForm.email" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Passwort</label>
                    <input type="password" x-model="authForm.password" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">TOTP-Code (6-stellig)</label>
                    <input type="text" x-model="authForm.totp" pattern="[0-9]{6}" maxlength="6" placeholder="000000" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-center text-2xl tracking-widest font-mono focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-1">Leer lassen bei Erstanmeldung ohne TOTP</p>
                </div>
                <button type="submit" :disabled="authLoading" class="w-full bg-gray-900 text-white py-2 px-4 rounded-lg hover:bg-gray-800 disabled:opacity-50 font-medium">
                    <span x-show="!authLoading">Anmelden</span><span x-show="authLoading">…</span>
                </button>
            </form>
        </div>

        <!-- TOTP Setup (for new admins) -->
        <div x-show="authStep==='totp-setup'" class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold mb-4">TOTP einrichten</h2>
            <p class="text-sm text-gray-600 mb-4">Scannen Sie den QR-Code oder geben Sie den Schlüssel manuell in Ihrer Authenticator-App ein.</p>
            <div x-show="totpSetup.qr_url" class="text-center mb-4">
                <img :src="totpSetup.qr_url" class="inline-block w-48 h-48" alt="QR Code">
            </div>
            <div x-show="totpSetup.secret" class="bg-gray-50 p-3 rounded-lg text-center mb-4">
                <div class="text-xs text-gray-500 mb-1">Geheimer Schlüssel</div>
                <code class="text-sm font-mono" x-text="totpSetup.secret"></code>
            </div>
            <div x-show="totpSetup.recovery_codes" class="mb-4">
                <div class="text-xs text-gray-500 mb-1">Recovery Codes (sicher aufbewahren!)</div>
                <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                    <template x-for="code in (totpSetup.recovery_codes||[])" :key="code">
                        <code class="block text-sm font-mono" x-text="code"></code>
                    </template>
                </div>
            </div>
            <form @submit.prevent="confirmTotp()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bestätigungscode eingeben</label>
                    <input type="text" x-model="totpConfirmCode" required pattern="[0-9]{6}" maxlength="6" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-center text-2xl tracking-widest font-mono">
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2 px-4 rounded-lg hover:bg-gray-800 font-medium">TOTP bestätigen</button>
            </form>
        </div>
    </div>
</div>
</template>

<!-- ==================== ADMIN SHELL ==================== -->
<template x-if="token">
<div class="flex h-full">
    <!-- Sidebar -->
    <aside class="w-60 bg-gray-900 text-white flex-shrink-0 hidden lg:flex flex-col">
        <div class="p-4 border-b border-gray-700">
            <span class="text-lg font-bold">⚡ Admin</span>
            <span class="text-xs text-gray-400 block" x-text="adminUser?.email"></span>
        </div>
        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto text-sm">
            <template x-for="section in navSections" :key="section.title">
                <div class="mb-3">
                    <div class="text-[10px] uppercase tracking-wider text-gray-500 px-3 py-1" x-text="section.title"></div>
                    <template x-for="item in section.items" :key="item.id">
                        <button @click="navigate(item.id)" :class="page===item.id ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'" class="w-full text-left px-3 py-1.5 rounded-md flex items-center gap-2 transition-colors">
                            <span x-text="item.label"></span>
                            <span x-show="item.badge" x-text="item.badge" class="ml-auto bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full"></span>
                        </button>
                    </template>
                </div>
            </template>
        </nav>
        <div class="p-3 border-t border-gray-700">
            <button @click="adminLogout()" class="text-sm text-gray-400 hover:text-red-400">Abmelden</button>
        </div>
    </aside>

    <!-- Mobile header -->
    <div class="lg:hidden fixed top-0 left-0 right-0 bg-gray-900 text-white z-40 flex items-center justify-between px-4 py-3">
        <button @click="mobileNav=!mobileNav"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
        <span class="font-bold">⚡ Admin</span>
        <button @click="adminLogout()" class="text-sm text-red-400">Logout</button>
    </div>
    <div x-show="mobileNav" @click="mobileNav=false" class="lg:hidden fixed inset-0 bg-black/50 z-40"></div>
    <div x-show="mobileNav" x-transition class="lg:hidden fixed left-0 top-0 bottom-0 w-60 bg-gray-900 z-50 p-3 overflow-y-auto">
        <template x-for="section in navSections" :key="section.title">
            <div class="mb-3">
                <div class="text-[10px] uppercase tracking-wider text-gray-500 px-3 py-1" x-text="section.title"></div>
                <template x-for="item in section.items" :key="item.id">
                    <button @click="navigate(item.id); mobileNav=false" :class="page===item.id?'bg-gray-800 text-white':'text-gray-300'" class="w-full text-left px-3 py-1.5 rounded text-sm" x-text="item.label"></button>
                </template>
            </div>
        </template>
    </div>

    <!-- Main content -->
    <main class="flex-1 overflow-y-auto lg:pt-0 pt-14">
        <div class="p-6 max-w-7xl mx-auto">

<!-- ===== DASHBOARD ===== -->
<div x-show="page==='dashboard'" class="fade-in">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>
    <!-- Stats cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Benutzer gesamt</div>
            <div class="text-3xl font-bold mt-1" x-text="stats.total_users||0"></div>
            <div class="text-sm text-green-600 mt-1" x-text="(stats.active_users||0)+' aktiv'"></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Sessions gesamt</div>
            <div class="text-3xl font-bold mt-1" x-text="stats.total_sessions||0"></div>
            <div class="text-sm text-blue-600 mt-1" x-text="(stats.today_sessions||0)+' heute'"></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Umsatz</div>
            <div class="text-3xl font-bold mt-1" x-text="fmtCur(stats.total_revenue)"></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Offene Rechnungen</div>
            <div class="text-3xl font-bold mt-1" x-text="stats.pending_invoices||0"></div>
        </div>
    </div>
    <!-- Quick tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-4 py-3 border-b font-semibold text-sm">Letzte Sessions</div>
            <div class="divide-y">
                <template x-for="s in dashSessions.slice(0,8)" :key="s.id">
                    <div class="px-4 py-2 text-sm flex justify-between items-center hover:bg-gray-50 cursor-pointer" @click="showSessionDetail(s.id)">
                        <div>
                            <span class="font-medium" x-text="s.user_email||'—'"></span>
                            <span class="text-gray-500 ml-2" x-text="fmtDate(s.start_time)"></span>
                        </div>
                        <span :class="stClass(s.status)" class="badge" x-text="s.status"></span>
                    </div>
                </template>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-4 py-3 border-b font-semibold text-sm">Letzte Rechnungen</div>
            <div class="divide-y">
                <template x-for="inv in dashInvoices.slice(0,8)" :key="inv.id">
                    <div class="px-4 py-2 text-sm flex justify-between items-center hover:bg-gray-50">
                        <div>
                            <span class="font-mono" x-text="inv.invoice_number"></span>
                            <span class="text-gray-500 ml-2" x-text="inv.user_email||''"></span>
                        </div>
                        <span class="font-medium" x-text="fmtCur(inv.total_amount)"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- ===== USERS ===== -->
<div x-show="page==='users'" class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Benutzer</h1>
        <div class="flex gap-2">
            <input type="text" x-model="userSearch" @keyup.enter="searchUsers()" placeholder="Suche (E-Mail, Name)…" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm w-64">
            <button @click="searchUsers()" class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-sm">Suchen</button>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">E-Mail</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Erstellt</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Aktionen</th>
            </tr></thead>
            <tbody class="divide-y">
                <template x-for="u in users" :key="u.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-xs" x-text="u.id"></td>
                        <td class="px-4 py-2" x-text="(u.first_name||'')+' '+(u.last_name||'')"></td>
                        <td class="px-4 py-2" x-text="u.email"></td>
                        <td class="px-4 py-2"><span :class="u.status==='active'?'bg-green-100 text-green-800':'bg-red-100 text-red-800'" class="badge" x-text="u.status"></span></td>
                        <td class="px-4 py-2 text-gray-500" x-text="fmtDate(u.created_at)"></td>
                        <td class="px-4 py-2 space-x-2">
                            <button @click="showUserDetail(u.id)" class="text-accent-600 hover:underline">Detail</button>
                            <button x-show="u.status==='active'" @click="blockUser(u.id)" class="text-red-600 hover:underline">Sperren</button>
                            <button x-show="u.status==='blocked'" @click="unblockUser(u.id)" class="text-green-600 hover:underline">Freigeben</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
        <span x-text="'Seite '+usersPage"></span>
        <div class="space-x-2">
            <button @click="usersPage=Math.max(1,usersPage-1); searchUsers()" :disabled="usersPage<=1" class="px-3 py-1 border rounded disabled:opacity-30">← Zurück</button>
            <button @click="usersPage++; searchUsers()" class="px-3 py-1 border rounded">Weiter →</button>
        </div>
    </div>
</div>

<!-- ===== USER DETAIL ===== -->
<div x-show="page==='user-detail'" class="fade-in">
    <button @click="page='users'" class="text-sm text-gray-500 hover:text-gray-700 mb-4 flex items-center gap-1">← Zurück zur Liste</button>
    <template x-if="userDetail">
        <div>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg" x-text="(userDetail.first_name||'?')[0]+(userDetail.last_name||'?')[0]"></div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900" x-text="(userDetail.first_name||'')+' '+(userDetail.last_name||'')"></h1>
                    <p class="text-gray-500" x-text="userDetail.email"></p>
                </div>
                <span :class="userDetail.status==='active'?'bg-green-100 text-green-800':'bg-red-100 text-red-800'" class="badge ml-4" x-text="userDetail.status"></span>
            </div>
            <!-- Profile data -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h3 class="font-semibold text-sm text-gray-500 mb-3">Profil</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Telefon</dt><dd x-text="userDetail.phone||'—'"></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Adresse</dt><dd x-text="userDetail.address||'—'"></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">PLZ/Stadt</dt><dd x-text="(userDetail.postal_code||'')+' '+(userDetail.city||'')"></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Land</dt><dd x-text="userDetail.country||'—'"></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Erstellt</dt><dd x-text="fmtDate(userDetail.created_at)"></dd></div>
                    </dl>
                </div>
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h3 class="font-semibold text-sm text-gray-500 mb-3">Aktionen</h3>
                    <div class="space-y-2">
                        <button x-show="userDetail.status==='active'" @click="blockUser(userDetail.id); userDetail.status='blocked'" class="w-full bg-red-600 text-white py-2 rounded-lg text-sm hover:bg-red-700">Benutzer sperren</button>
                        <button x-show="userDetail.status==='blocked'" @click="unblockUser(userDetail.id); userDetail.status='active'" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700">Benutzer freigeben</button>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h3 class="font-semibold text-sm text-gray-500 mb-3">Statistik</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Sessions</dt><dd x-text="(userDetail.recent_sessions||[]).length"></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Rechnungen</dt><dd x-text="(userDetail.recent_invoices||[]).length"></dd></div>
                    </dl>
                </div>
            </div>
            <!-- Recent sessions & invoices -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="px-4 py-3 border-b font-semibold text-sm">Letzte Sessions</div>
                    <div class="divide-y text-sm">
                        <template x-for="s in (userDetail.recent_sessions||[])" :key="s.id">
                            <div class="px-4 py-2 flex justify-between">
                                <span x-text="fmtDate(s.start_time)"></span>
                                <span x-text="fmtCur(s.total_cost)"></span>
                                <span :class="stClass(s.status)" class="badge" x-text="s.status"></span>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="px-4 py-3 border-b font-semibold text-sm">Letzte Rechnungen</div>
                    <div class="divide-y text-sm">
                        <template x-for="inv in (userDetail.recent_invoices||[])" :key="inv.id">
                            <div class="px-4 py-2 flex justify-between">
                                <span class="font-mono" x-text="inv.invoice_number"></span>
                                <span x-text="fmtCur(inv.total_amount)"></span>
                                <span :class="invClass(inv.status)" class="badge" x-text="inv.status"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<!-- ===== SESSIONS ===== -->
<div x-show="page==='sessions'" class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Ladevorgänge</h1>
        <div class="flex gap-2">
            <select x-model="sessionFilter" @change="loadAdminSessions()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">Alle Status</option>
                <option value="active">Aktiv</option>
                <option value="completed">Abgeschlossen</option>
                <option value="failed">Fehlgeschlagen</option>
                <option value="pending">Ausstehend</option>
            </select>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500">ID</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Benutzer</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Start</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Energie</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Kosten</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
            </tr></thead>
            <tbody class="divide-y">
                <template x-for="s in adminSessions" :key="s.id">
                    <tr class="hover:bg-gray-50 cursor-pointer" @click="showSessionDetail(s.id)">
                        <td class="px-4 py-2 font-mono text-xs" x-text="s.id"></td>
                        <td class="px-4 py-2" x-text="s.user_email||'—'"></td>
                        <td class="px-4 py-2 text-gray-500" x-text="fmtDate(s.start_time)"></td>
                        <td class="px-4 py-2" x-text="((s.energy_kwh||0)).toFixed(2)+' kWh'"></td>
                        <td class="px-4 py-2 font-medium" x-text="fmtCur(s.total_cost)"></td>
                        <td class="px-4 py-2"><span :class="stClass(s.status)" class="badge" x-text="s.status"></span></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
        <span x-text="'Seite '+sessionsPage"></span>
        <div class="space-x-2">
            <button @click="sessionsPage=Math.max(1,sessionsPage-1); loadAdminSessions()" :disabled="sessionsPage<=1" class="px-3 py-1 border rounded disabled:opacity-30">← Zurück</button>
            <button @click="sessionsPage++; loadAdminSessions()" class="px-3 py-1 border rounded">Weiter →</button>
        </div>
    </div>
</div>

<!-- ===== INVOICES ===== -->
<div x-show="page==='invoices'" class="fade-in">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Rechnungen</h1>
    <div class="bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Nr.</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Benutzer</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Datum</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Betrag</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Lexware</th>
            </tr></thead>
            <tbody class="divide-y">
                <template x-for="inv in adminInvoices" :key="inv.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-xs" x-text="inv.invoice_number"></td>
                        <td class="px-4 py-2" x-text="inv.user_email||'—'"></td>
                        <td class="px-4 py-2 text-gray-500" x-text="fmtDate(inv.created_at)"></td>
                        <td class="px-4 py-2 font-medium" x-text="fmtCur(inv.total_amount)"></td>
                        <td class="px-4 py-2"><span :class="invClass(inv.status)" class="badge" x-text="inv.status"></span></td>
                        <td class="px-4 py-2"><span :class="inv.lexware_synced?'text-green-600':'text-yellow-600'" x-text="inv.lexware_synced?'✓ Sync':'Ausstehend'"></span></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
        <span x-text="'Seite '+invoicesPage"></span>
        <div class="space-x-2">
            <button @click="invoicesPage=Math.max(1,invoicesPage-1); loadAdminInvoices()" :disabled="invoicesPage<=1" class="px-3 py-1 border rounded disabled:opacity-30">←</button>
            <button @click="invoicesPage++; loadAdminInvoices()" class="px-3 py-1 border rounded">→</button>
        </div>
    </div>
</div>

<!-- ===== PROVIDERS ===== -->
<div x-show="page==='providers'" class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Provider / Ladeanbieter</h1>
        <button @click="providerForm={name:'',slug:'',adapter_class:'',api_url:'',roaming_fee_percent:0,is_active:true}; providerEditing=null; showProviderModal=true" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800">+ Neuer Provider</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="p in providers" :key="p.id">
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-semibold" x-text="p.name"></h3>
                        <span class="text-xs text-gray-500 font-mono" x-text="p.slug"></span>
                    </div>
                    <span :class="p.is_active?'bg-green-100 text-green-800':'bg-gray-100 text-gray-800'" class="badge" x-text="p.is_active?'Aktiv':'Inaktiv'"></span>
                </div>
                <dl class="text-sm space-y-1 mb-4">
                    <div class="flex justify-between"><dt class="text-gray-500">Adapter</dt><dd class="font-mono text-xs" x-text="p.adapter_class||'—'"></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Roaming-Gebühr</dt><dd x-text="(p.roaming_fee_percent||0)+'%'"></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Letzter Sync</dt><dd x-text="fmtDate(p.last_sync_at)"></dd></div>
                </dl>
                <div class="flex gap-2">
                    <button @click="editProvider(p)" class="text-accent-600 hover:underline text-sm">Bearbeiten</button>
                    <button @click="syncProvider(p.id)" class="text-primary-600 hover:underline text-sm">Sync</button>
                </div>
            </div>
        </template>
    </div>
    <!-- Provider Modal -->
    <div x-show="showProviderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showProviderModal=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 m-4">
            <h2 class="text-lg font-semibold mb-4" x-text="providerEditing?'Provider bearbeiten':'Neuer Provider'"></h2>
            <form @submit.prevent="saveProvider()" class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" x-model="providerForm.name" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
                <div><label class="block text-sm font-medium text-gray-700">Slug</label><input type="text" x-model="providerForm.slug" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 font-mono"></div>
                <div><label class="block text-sm font-medium text-gray-700">Adapter-Klasse</label><input type="text" x-model="providerForm.adapter_class" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 font-mono"></div>
                <div><label class="block text-sm font-medium text-gray-700">API-URL</label><input type="url" x-model="providerForm.api_url" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
                <div><label class="block text-sm font-medium text-gray-700">Roaming-Gebühr (%)</label><input type="number" step="0.01" x-model="providerForm.roaming_fee_percent" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
                <div class="flex items-center gap-2"><input type="checkbox" x-model="providerForm.is_active" class="rounded"><label class="text-sm">Aktiv</label></div>
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="showProviderModal=false" class="px-4 py-2 border rounded-lg text-sm">Abbrechen</button>
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm hover:bg-gray-800">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== SYSTEM CONFIG ===== -->
<div x-show="page==='config'" class="fade-in">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Systemkonfiguration</h1>
    <div x-show="configMsg" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-sm" x-text="configMsg"></div>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Schlüssel</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Wert</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Beschreibung</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 w-24">Aktion</th>
            </tr></thead>
            <tbody class="divide-y">
                <template x-for="cfg in configs" :key="cfg.key">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-xs" x-text="cfg.key"></td>
                        <td class="px-4 py-2"><input type="text" x-model="cfg.value" class="w-full border border-gray-200 rounded px-2 py-1 text-sm"></td>
                        <td class="px-4 py-2 text-gray-500 text-xs" x-text="cfg.description||''"></td>
                        <td class="px-4 py-2"><button @click="updateConfig(cfg.key, cfg.value)" class="text-primary-600 hover:underline text-xs">Speichern</button></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== ADMIN USERS ===== -->
<div x-show="page==='admins'" class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Admin-Verwaltung</h1>
        <button @click="showInviteModal=true" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800">+ Admin einladen</button>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <p class="text-gray-500 text-sm">Admin-Benutzer werden über die Einladungsfunktion erstellt. Jeder Admin muss TOTP einrichten.</p>
        <div class="mt-4 text-sm text-gray-600">
            <div>Angemeldet als: <span class="font-medium" x-text="adminUser?.email"></span></div>
            <div>Rolle: <span class="font-medium" x-text="adminUser?.role || 'admin'"></span></div>
        </div>
    </div>
    <!-- Invite Modal -->
    <div x-show="showInviteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showInviteModal=false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 m-4">
            <h2 class="text-lg font-semibold mb-4">Admin einladen</h2>
            <div x-show="inviteMsg" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded text-sm" x-text="inviteMsg"></div>
            <div x-show="inviteError" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm" x-text="inviteError"></div>
            <form @submit.prevent="inviteAdmin()" class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">E-Mail</label><input type="email" x-model="inviteForm.email" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" x-model="inviteForm.name" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rolle</label>
                    <select x-model="inviteForm.role" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="admin">Admin</option>
                        <option value="support">Support</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="showInviteModal=false" class="px-4 py-2 border rounded-lg text-sm">Abbrechen</button>
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm hover:bg-gray-800">Einladen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== HEALTH / SYSTEM ===== -->
<div x-show="page==='health'" class="fade-in">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">System-Health</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="font-semibold mb-4">API Health Check</h2>
            <button @click="checkHealth()" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800 mb-4">Prüfen</button>
            <template x-if="healthResult">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span :class="healthResult.status==='ok'||healthResult.status===200?'bg-green-500':'bg-red-500'" class="w-3 h-3 rounded-full"></span>
                        <span class="font-medium" x-text="healthResult.status==='ok'||healthResult.status===200?'Gesund':'Problem'"></span>
                    </div>
                    <pre class="bg-gray-50 rounded p-3 text-xs font-mono overflow-auto max-h-64" x-text="JSON.stringify(healthResult,null,2)"></pre>
                </div>
            </template>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="font-semibold mb-4">Systeminfo</h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-500">API-Server</dt><dd class="font-mono text-xs"><?= base_url() ?></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">PHP-Version</dt><dd class="font-mono text-xs"><?= PHP_VERSION ?></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">CI4-Version</dt><dd class="font-mono text-xs"><?= \CodeIgniter\CodeIgniter::CI_VERSION ?></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Umgebung</dt><dd class="font-mono text-xs"><?= ENVIRONMENT ?></dd></div>
            </dl>
        </div>
    </div>
</div>

<!-- ===== AUDIT LOG ===== -->
<div x-show="page==='audit'" class="fade-in">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Audit-Log</h1>
    <p class="text-gray-500 text-sm mb-4">Audit-Einträge werden vom Backend automatisch erstellt bei Konfigurations-, Abo- und Statusänderungen.</p>
    <div class="bg-white rounded-xl shadow-sm border p-6 text-center text-gray-400">
        <p>Die Audit-Log-API-Erweiterung wird in einer nächsten Version bereitgestellt.</p>
        <p class="text-sm mt-2">Aktuelle Audit-Logs sind in der Datenbanktabelle <code class="bg-gray-100 px-1 rounded">audit_log</code> verfügbar.</p>
    </div>
</div>

        </div>
    </main>
</div>
</template>

<script>
const AAPI = '/api/v1/admin';

function adminApp() {
    return {
        // Auth
        token: localStorage.getItem('admin_token') || '',
        authStep: 'login',
        authForm: { email:'', password:'', totp:'' },
        authError: '',
        authLoading: false,
        totpSetup: {},
        totpConfirmCode: '',
        adminUser: JSON.parse(localStorage.getItem('admin_user')||'null'),

        // Nav
        page: 'dashboard',
        mobileNav: false,

        // Data
        stats: {},
        dashSessions: [],
        dashInvoices: [],
        users: [],
        userSearch: '',
        usersPage: 1,
        userDetail: null,
        adminSessions: [],
        sessionsPage: 1,
        sessionFilter: '',
        adminInvoices: [],
        invoicesPage: 1,
        providers: [],
        providerForm: {},
        providerEditing: null,
        showProviderModal: false,
        configs: [],
        configMsg: '',
        showInviteModal: false,
        inviteForm: { email:'', name:'', role:'admin' },
        inviteMsg: '',
        inviteError: '',
        healthResult: null,

        get navSections() {
            return [
                { title:'Übersicht', items:[
                    { id:'dashboard', label:'Dashboard' },
                    { id:'health', label:'System-Health' },
                ]},
                { title:'Verwaltung', items:[
                    { id:'users', label:'Benutzer' },
                    { id:'sessions', label:'Ladevorgänge' },
                    { id:'invoices', label:'Rechnungen' },
                ]},
                { title:'System', items:[
                    { id:'providers', label:'Provider / Tarife' },
                    { id:'config', label:'Konfiguration' },
                    { id:'audit', label:'Audit-Log' },
                ]},
                { title:'Admin', items:[
                    { id:'admins', label:'Admin-Verwaltung' },
                ]},
            ];
        },

        async init() {
            if (this.token) await this.loadDashboard();
        },

        navigate(id) {
            this.page = id;
            const loaders = {
                dashboard: ()=>this.loadDashboard(),
                users: ()=>this.searchUsers(),
                sessions: ()=>this.loadAdminSessions(),
                invoices: ()=>this.loadAdminInvoices(),
                providers: ()=>this.loadProviders(),
                config: ()=>this.loadConfig(),
            };
            if (loaders[id]) loaders[id]();
        },

        // Auth
        async adminLogin() {
            this.authLoading = true; this.authError = '';
            try {
                const r = await this.api('POST', '/auth/login', this.authForm, true);
                if (r.data?.requires_totp_setup) {
                    this.authStep = 'totp-setup';
                    await this.setupTotp();
                } else {
                    this.setAdminAuth(r.data);
                    await this.loadDashboard();
                }
            } catch(e) { this.authError = e.message || 'Anmeldung fehlgeschlagen'; }
            this.authLoading = false;
        },
        async setupTotp() {
            try {
                const r = await this.api('POST', '/auth/setup-totp', { email: this.authForm.email, password: this.authForm.password }, true);
                this.totpSetup = r.data || r;
            } catch(e) { this.authError = e.message; }
        },
        async confirmTotp() {
            this.authError = '';
            try {
                const r = await this.api('POST', '/auth/confirm-totp', { email: this.authForm.email, totp: this.totpConfirmCode }, true);
                if (r.access_token || r.data?.access_token) {
                    this.setAdminAuth(r.data || r);
                    await this.loadDashboard();
                } else {
                    this.authError = r.message || 'TOTP bestätigt, bitte erneut anmelden.';
                    this.authStep = 'login';
                }
            } catch(e) { this.authError = e.message; }
        },
        setAdminAuth(data) {
            this.token = data.token || data.access_token || '';
            this.adminUser = data.user || data.admin || data;
            localStorage.setItem('admin_token', this.token);
            localStorage.setItem('admin_user', JSON.stringify(this.adminUser));
            this.authStep = 'login';
        },
        adminLogout() {
            this.token = '';
            this.adminUser = null;
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
        },

        // Data loading
        async loadDashboard() {
            const [st, se, inv] = await Promise.allSettled([
                this.api('GET', '/dashboard/stats'),
                this.api('GET', '/dashboard/sessions?page=1'),
                this.api('GET', '/dashboard/invoices?page=1'),
            ]);
            if (st.status==='fulfilled') this.stats = st.value.data || {};
            if (se.status==='fulfilled') this.dashSessions = se.value.data?.data || se.value.data || [];
            if (inv.status==='fulfilled') this.dashInvoices = inv.value.data?.data || inv.value.data || [];
        },
        async searchUsers() {
            const q = this.userSearch ? `&search=${encodeURIComponent(this.userSearch)}` : '';
            const r = await this.api('GET', `/dashboard/users?page=${this.usersPage}${q}`);
            this.users = r.data?.data || r.data || [];
        },
        async showUserDetail(id) {
            const r = await this.api('GET', `/dashboard/users/${id}`);
            this.userDetail = r.data;
            this.page = 'user-detail';
        },
        async blockUser(id) {
            await this.api('PUT', `/dashboard/users/${id}/block`);
            if (this.page==='users') this.searchUsers();
        },
        async unblockUser(id) {
            await this.api('PUT', `/dashboard/users/${id}/unblock`);
            if (this.page==='users') this.searchUsers();
        },
        showSessionDetail(id) {
            // In a full implementation, this would load session detail with events
            // For now, navigate to sessions view
            if (this.page !== 'sessions') { this.page = 'sessions'; this.loadAdminSessions(); }
        },
        async loadAdminSessions() {
            const f = this.sessionFilter ? `&status=${this.sessionFilter}` : '';
            const r = await this.api('GET', `/dashboard/sessions?page=${this.sessionsPage}${f}`);
            this.adminSessions = r.data?.data || r.data || [];
        },
        async loadAdminInvoices() {
            const r = await this.api('GET', `/dashboard/invoices?page=${this.invoicesPage}`);
            this.adminInvoices = r.data?.data || r.data || [];
        },
        async loadProviders() {
            const r = await this.api('GET', '/providers');
            this.providers = r.data || [];
        },
        editProvider(p) {
            this.providerEditing = p.id;
            this.providerForm = { ...p };
            this.showProviderModal = true;
        },
        async saveProvider() {
            if (this.providerEditing) {
                await this.api('PUT', `/providers/${this.providerEditing}`, this.providerForm);
            } else {
                await this.api('POST', '/providers', this.providerForm);
            }
            this.showProviderModal = false;
            await this.loadProviders();
        },
        async syncProvider(id) {
            try {
                await this.api('POST', `/providers/${id}/sync`);
                alert('Sync gestartet');
                await this.loadProviders();
            } catch(e) { alert('Sync-Fehler: '+(e.message||'Unbekannt')); }
        },
        async loadConfig() {
            const r = await this.api('GET', '/config');
            this.configs = r.data || [];
        },
        async updateConfig(key, value) {
            await this.api('PUT', '/config', { configs: [{ key, value }] });
            this.configMsg = `"${key}" gespeichert`;
            setTimeout(()=> this.configMsg='', 3000);
        },
        async inviteAdmin() {
            this.inviteMsg=''; this.inviteError='';
            try {
                await this.api('POST', '/auth/invite', this.inviteForm);
                this.inviteMsg = 'Einladung gesendet an ' + this.inviteForm.email;
                this.inviteForm = { email:'', name:'', role:'admin' };
            } catch(e) { this.inviteError = e.message; }
        },
        async checkHealth() {
            try {
                const r = await fetch('/api/v1/health');
                this.healthResult = await r.json();
            } catch(e) { this.healthResult = { error: e.message }; }
        },

        // API helper
        async api(method, path, body, noAuth) {
            const opts = { method, headers: { 'Content-Type':'application/json', 'Accept':'application/json' } };
            if (!noAuth && this.token) opts.headers['Authorization'] = `Bearer ${this.token}`;
            if (body) opts.body = JSON.stringify(body);
            const res = await fetch(AAPI + path, opts);
            if (res.status === 401) { this.adminLogout(); throw new Error('Sitzung abgelaufen'); }
            if (!res.ok) {
                const err = await res.json().catch(()=>({}));
                throw new Error(err.message || err.messages?.error || `Fehler ${res.status}`);
            }
            return res.json();
        },

        // Helpers
        fmtDate(d) { if(!d) return '—'; return new Date(d).toLocaleDateString('de-DE',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}); },
        fmtCur(v) { return new Intl.NumberFormat('de-DE',{style:'currency',currency:'EUR'}).format(v||0); },
        stClass(s) { return { active:'bg-green-100 text-green-800', completed:'bg-blue-100 text-blue-800', failed:'bg-red-100 text-red-800', pending:'bg-yellow-100 text-yellow-800' }[s] || 'bg-gray-100 text-gray-800'; },
        invClass(s) { return { paid:'bg-green-100 text-green-800', pending:'bg-yellow-100 text-yellow-800', overdue:'bg-red-100 text-red-800', draft:'bg-gray-100 text-gray-800' }[s] || 'bg-gray-100 text-gray-800'; },
    };
}
</script>
</body>
</html>
