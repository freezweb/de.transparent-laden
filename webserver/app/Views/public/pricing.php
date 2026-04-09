<?php
$title = 'Preise & Abos';
ob_start();
?>

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Einfache & faire Preise</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Wählen Sie das passende Modell. Alle Preise sind transparent aufgeschlüsselt - ohne versteckte Kosten.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Free -->
            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-200 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Basis</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">0 &euro;<span class="text-lg font-normal text-gray-500">/Monat</span></div>
                <p class="text-gray-500 mb-6">Für Gelegenheitslader</p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Transparente Preise pro kWh</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Rechnungen mit Aufschlüsselung</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Ladepunkt-Karte</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Push-Benachrichtigungen</span></li>
                </ul>
                <a href="/portal#/register" class="block text-center bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">Kostenlos starten</a>
            </div>
            <!-- Pro -->
            <div class="bg-primary-800 rounded-2xl p-8 text-white relative shadow-xl scale-105">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-400 text-primary-900 text-xs font-bold px-3 py-1 rounded-full">BELIEBT</div>
                <h3 class="text-lg font-semibold mb-2">Pro</h3>
                <div class="text-4xl font-bold mb-1">4,99 &euro;<span class="text-lg font-normal text-primary-200">/Monat</span></div>
                <p class="text-primary-200 mb-6">Für regelmäßige Fahrer</p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-300 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Alles aus Basis</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-300 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Reduzierte kWh-Preise</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-300 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Keine Startgebühren</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-300 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Plug &amp; Charge</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-300 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Preis-Alarm</span></li>
                </ul>
                <a href="/portal#/register" class="block text-center bg-white text-primary-800 px-6 py-3 rounded-lg font-semibold hover:bg-primary-50 transition">Jetzt upgraden</a>
            </div>
            <!-- Business -->
            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-200 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Business</h3>
                <div class="text-4xl font-bold text-gray-900 mb-1">14,99 &euro;<span class="text-lg font-normal text-gray-500">/Monat</span></div>
                <p class="text-gray-500 mb-6">Für Flotten & Unternehmen</p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Alles aus Pro</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Mehrere Fahrzeuge</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Sammelrechnung</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Flottenauswertung</span></li>
                    <li class="flex items-start"><svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span class="text-gray-600">Dedizierter Support</span></li>
                </ul>
                <a href="/kontakt" class="block text-center bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">Kontakt aufnehmen</a>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'content' => $content]);
?>
