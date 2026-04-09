<?php
$title = 'Preise';
$description = 'Transparent Laden – ein Tarif (9,99 €/Monat), fester Aufschlag von 0,01 €/kWh. Alle Kosten sichtbar vor Ladebeginn.';
ob_start();
?>

<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Ein Tarif. Ein fester Aufschlag. Volle Transparenz.</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Kein Freemium, keine Stufen. Sie zahlen die Grundgebühr und sehen bei jedem Ladevorgang die prozentuale Aufteilung auf Betreiber, Roaming, Zahlungsabwicklung und unsere Marge.</p>
        </div>

        <!-- Single plan -->
        <div class="max-w-lg mx-auto">
            <div class="bg-primary-800 rounded-2xl p-10 text-white shadow-xl text-center">
                <h2 class="text-2xl font-semibold mb-2">Transparent Laden</h2>
                <div class="text-5xl font-bold mb-2">9,99 &euro;<span class="text-xl font-normal text-primary-200">/Monat</span></div>
                <p class="text-primary-200 mb-8">Monatlich kündbar</p>
                <ul class="space-y-3 mb-10 text-left max-w-sm mx-auto">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Fester Aufschlag: 0,01&nbsp;&euro;/kWh, 0,01&nbsp;&euro;/Minute, 0,01&nbsp;&euro;/Minute Blockiergebühr</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Prozentuale Partner-Aufteilung: Betreiber, Roaming, Zahlung, Marge</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Anbieter-Gebühren transparent weitergereicht</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Zahlungsartenkosten sichtbar vor Ladebeginn</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Zugang zum europaweiten Ladenetzwerk</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Rechnungen mit vollständiger Kostenaufschlüsselung</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Ladepunkt-Karte mit Echtzeitverfügbarkeit</span>
                    </li>
                </ul>
                <a href="/portal#/register" class="block bg-white text-primary-800 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition shadow-lg text-lg">Jetzt registrieren</a>
            </div>
        </div>

        <!-- Preisbeispiel -->
        <div class="mt-16 space-y-8">
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Was kostet das Laden selbst?</h2>
                <p class="text-gray-600 mb-6">Der Endpreis setzt sich aus mehreren Bereichen zusammen. Wir zeigen jeden einzelnen – in Cent und in Prozent:</p>

                <div class="bg-white rounded-lg p-6 border mb-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Preisbeispiel</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center"><span class="text-gray-600">Betreiber / Infrastruktur</span><span class="font-semibold">0,33 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 70 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Roaming / Betrieb</span><span class="font-semibold">0,12 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 26 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Zahlungsabwicklung</span><span class="font-semibold">0,01 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 2 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Unsere Marge (fest)</span><span class="font-semibold text-primary-700">0,01 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 2 %</span></div>
                        <hr>
                        <div class="flex justify-between items-center font-bold text-primary-800"><span>Endpreis pro kWh</span><span>0,47 &euro;</span><span class="text-xs w-16 text-right">100 %</span></div>
                    </div>
                    <div class="mt-4 pt-4 border-t space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Startgebühr (Betreiber)</span><span class="font-medium">1,00 &euro;</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Zahlungsart-Fixgebühr (PayPal)</span><span class="font-medium">0,35 &euro;</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Minutenpreis</span><span class="font-medium">keiner an dieser Station</span></div>
                    </div>
                </div>
                <p class="text-sm text-gray-500">Anteile variieren je nach Ladestation und Anbieter. Die tatsächliche Aufteilung wird ladespezifisch berechnet und vor Ladebeginn angezeigt. Unser Aufschlag bleibt immer 0,01&nbsp;&euro;.</p>
            </div>

            <!-- FAQ -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Häufige Fragen zum Preis</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">Gibt es eine Mindestlaufzeit?</h3>
                        <p class="text-gray-600 text-sm mt-1">Nein. Das Abo ist monatlich kündbar.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Was genau verdient ihr an einem Ladevorgang?</h3>
                        <p class="text-gray-600 text-sm mt-1">Genau 0,01&nbsp;&euro; pro kWh. Falls der Anbieter einen Minutenpreis hat, zusätzlich 0,01&nbsp;&euro; pro Minute. Falls der Anbieter Blockiergebühren hat, zusätzlich 0,01&nbsp;&euro; pro Minute Blockiergebühr. Das ist unser festes Preisversprechen.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Kommen weitere Kosten hinzu?</h3>
                        <p class="text-gray-600 text-sm mt-1">Neben der Grundgebühr (9,99&nbsp;&euro;/Monat) zahlen Sie den ladespezifischen Preis, der sich aus Betreiber-/Infrastrukturkosten, Roaming-/Betriebskosten, Zahlungsabwicklung und unserem festen Aufschlag zusammensetzt. Alle Anteile werden in Prozent und Cent vor Ladebeginn angezeigt.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Kann ich ohne Abo laden?</h3>
                        <p class="text-gray-600 text-sm mt-1">Nein. Die Preistransparenz mit festem Aufschlag ist das Kernprodukt und nur mit Abo verfügbar.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'description' => $description, 'content' => $content]);
?>
