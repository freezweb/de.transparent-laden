<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Transparent Laden' ?> | Transparent Laden</title>
    <meta name="description" content="<?= $description ?? 'Transparentes Laden für Elektrofahrzeuge - fair, nachvollziehbar, digital.' ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: { 50:'#e8f5e9',100:'#c8e6c9',200:'#a5d6a7',300:'#81c784',400:'#66bb6a',500:'#4caf50',600:'#43a047',700:'#388e3c',800:'#2e7d32',900:'#1b5e20' },
                    accent: { 50:'#e3f2fd',100:'#bbdefb',200:'#90caf9',300:'#64b5f6',400:'#42a5f5',500:'#2196f3',600:'#1e88e5',700:'#1565c0',800:'#0d47a1',900:'#0a3d91' }
                }
            }
        }
    }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <?= $head ?? '' ?>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 flex flex-col">
    <!-- Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-50" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-primary-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-xl font-bold text-primary-800">Transparent Laden</span>
                </a>
                <nav class="hidden md:flex space-x-8">
                    <a href="/" class="text-gray-600 hover:text-primary-700 font-medium transition">Start</a>
                    <a href="/transparenz" class="text-gray-600 hover:text-primary-700 font-medium transition">Transparenz</a>
                    <a href="/preise" class="text-gray-600 hover:text-primary-700 font-medium transition">Preise</a>
                    <a href="/faq" class="text-gray-600 hover:text-primary-700 font-medium transition">FAQ</a>
                    <a href="/kontakt" class="text-gray-600 hover:text-primary-700 font-medium transition">Kontakt</a>
                </nav>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="/portal" class="text-primary-700 hover:text-primary-800 font-medium transition">Anmelden</a>
                    <a href="/portal#/register" class="bg-primary-700 text-white px-4 py-2 rounded-lg hover:bg-primary-800 transition font-medium">Registrieren</a>
                </div>
                <button @click="open = !open" class="md:hidden p-2 rounded-md text-gray-600 hover:text-primary-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
        <div x-show="open" x-cloak class="md:hidden border-t bg-white px-4 py-4 space-y-3">
            <a href="/" class="block text-gray-600 hover:text-primary-700">Start</a>
            <a href="/transparenz" class="block text-gray-600 hover:text-primary-700">Transparenz</a>
            <a href="/preise" class="block text-gray-600 hover:text-primary-700">Preise</a>
            <a href="/faq" class="block text-gray-600 hover:text-primary-700">FAQ</a>
            <a href="/kontakt" class="block text-gray-600 hover:text-primary-700">Kontakt</a>
            <hr>
            <a href="/portal" class="block text-primary-700 font-medium">Anmelden</a>
            <a href="/portal#/register" class="block bg-primary-700 text-white text-center px-4 py-2 rounded-lg">Registrieren</a>
        </div>
    </header>

    <main class="flex-1">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span class="text-lg font-bold text-white">Transparent Laden</span>
                    </div>
                    <p class="text-sm">Vollständige Preistransparenz beim Laden von Elektrofahrzeugen – in Prozent, vor Ladebeginn.</p>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-3">Produkt</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/transparenz" class="hover:text-white transition">Transparenz</a></li>
                        <li><a href="/preise" class="hover:text-white transition">Preise</a></li>
                        <li><a href="/faq" class="hover:text-white transition">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-3">Konto</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/portal" class="hover:text-white transition">Kundenportal</a></li>
                        <li><a href="/portal#/register" class="hover:text-white transition">Registrierung</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-semibold mb-3">Rechtliches</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/impressum" class="hover:text-white transition">Impressum</a></li>
                        <li><a href="/datenschutz" class="hover:text-white transition">Datenschutz</a></li>
                        <li><a href="/kontakt" class="hover:text-white transition">Kontakt</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-sm text-center">
                &copy; <?= date('Y') ?> Transparent Laden. Alle Rechte vorbehalten.
            </div>
        </div>
    </footer>
</body>
</html>
