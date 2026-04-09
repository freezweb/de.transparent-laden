<?php
$title = 'Startseite';
$description = 'Transparent Laden - Faire und nachvollziehbare Preise beim Laden von Elektrofahrzeugen.';
ob_start();
?>

<!-- Hero -->
<section class="relative bg-gradient-to-br from-primary-800 via-primary-700 to-primary-900 text-white overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(#grid)"/></svg>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 relative">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="fade-in">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                    Laden mit <span class="text-primary-200">voller Transparenz</span>
                </h1>
                <p class="text-lg md:text-xl text-primary-100 mb-8 leading-relaxed">
                    Wissen, was Sie zahlen - bevor Sie laden. Faire Preise, nachvollziehbare Kosten, keine versteckten Gebühren.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/portal#/register" class="bg-white text-primary-800 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition text-center shadow-lg">
                        Jetzt starten
                    </a>
                    <a href="/transparenz" class="border-2 border-white/30 text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition text-center">
                        So funktioniert's
                    </a>
                </div>
            </div>
            <div class="hidden md:flex justify-center">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 shadow-2xl max-w-sm w-full">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-primary-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <p class="text-primary-200 text-sm">Aktuelle Ladung</p>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between"><span class="text-primary-200">Energie</span><span class="font-mono font-bold">23.4 kWh</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">Preis/kWh</span><span class="font-mono font-bold">0.39 &euro;</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">Blockiergebühr</span><span class="font-mono font-bold">0.00 &euro;</span></div>
                        <hr class="border-white/20">
                        <div class="flex justify-between text-lg"><span class="font-semibold">Gesamt</span><span class="font-mono font-bold">9.13 &euro;</span></div>
                    </div>
                    <div class="mt-4 bg-primary-500/30 rounded-lg p-3 text-xs text-primary-100 text-center">
                        Alle Preisbestandteile jederzeit einsehbar
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Warum Transparent Laden?</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Wir glauben, dass faire Preise und Nachvollziehbarkeit die Grundlage für Vertrauen sind.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Volle Preistransparenz</h3>
                <p class="text-gray-600">Jeder Preisbestandteil wird offen dargestellt - Energiekosten, Netzentgelte, Steuern und Marge. Keine versteckten Aufschläge.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-accent-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Faire Abrechnung</h3>
                <p class="text-gray-600">Bezahlen Sie nur, was Sie verbrauchen. Kein Minutentakt-Trick, keine überhöhten Startgebühren. Echte kWh-basierte Abrechnung.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Europaweites Netz</h3>
                <p class="text-gray-600">Zugang zu tausenden Ladepunkten verschiedener Anbieter - immer mit dem gleichen transparenten Preismodell.</p>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">In 3 Schritten laden</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">1</div>
                <h3 class="text-xl font-semibold mb-3">App laden & registrieren</h3>
                <p class="text-gray-600">Erstellen Sie Ihr Konto in Sekunden. Keine versteckten Pflichtangaben.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">2</div>
                <h3 class="text-xl font-semibold mb-3">Ladepunkt finden</h3>
                <p class="text-gray-600">Sehen Sie vorab den exakten Preis pro kWh - aufgeschlüsselt und verständlich.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">3</div>
                <h3 class="text-xl font-semibold mb-3">Laden & fair bezahlen</h3>
                <p class="text-gray-600">Laden Sie Ihr Fahrzeug und erhalten Sie eine transparente Rechnung mit allen Details.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 bg-primary-800 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Bereit für transparentes Laden?</h2>
        <p class="text-lg text-primary-100 mb-8">Starten Sie jetzt und erleben Sie, wie einfach und fair E-Mobilität sein kann.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/portal#/register" class="bg-white text-primary-800 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition shadow-lg">Kostenlos registrieren</a>
            <a href="/preise" class="border-2 border-white/30 px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition">Preise ansehen</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'description' => $description, 'content' => $content]);
?>
