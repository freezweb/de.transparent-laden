<?php
$title = 'Preise';
$description = 'Transparent Laden – ein Tarif, 9,99 € pro Monat. Vollständige prozentuale Preisaufschlüsselung bei jedem Ladevorgang.';
ob_start();
?>

<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Ein Tarif. Volle Transparenz.</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Kein Freemium, keine Stufen. Ein Preis für die vollständige Preisaufschlüsselung bei jedem Ladevorgang.</p>
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
                        <span>Prozentuale Preisaufschlüsselung vor jedem Ladevorgang</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sichtbare Marge – was wir verdienen, liegt offen</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Transparente Anzeige aller Betreiber-Gebühren (Start, Blockierung, Minutenpreis)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Zahlungsart-Kosten sichtbar vor Ladebeginn</span>
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
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-primary-300 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Push-Benachrichtigungen zum Ladestatus</span>
                    </li>
                </ul>
                <a href="/portal#/register" class="block bg-white text-primary-800 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition shadow-lg text-lg">Jetzt registrieren</a>
            </div>
        </div>

        <!-- Additional info -->
        <div class="mt-16 space-y-8">
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Was kostet das Laden selbst?</h2>
                <p class="text-gray-600 mb-4">Der kWh-Preis variiert je nach Ladestation und Betreiber. Der Endpreis setzt sich aus folgenden Bestandteilen zusammen:</p>
                <div class="grid sm:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-primary-700 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700">Betreiber / Infrastruktur</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-primary-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700">Roaming / Betrieb</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-accent-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700">Zahlungsabwicklung (abhängig von Ihrer Zahlungsart)</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700">Unsere Marge (offen ausgewiesen)</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-gray-400 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700">Steuern & Abgaben</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">Dazu können je nach Betreiber Startgebühren, Blockiergebühren oder Minutenpreise anfallen. Alles wird Ihnen vor Ladebeginn angezeigt.</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Häufige Fragen zum Preis</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-800">Gibt es eine Mindestlaufzeit?</h3>
                        <p class="text-gray-600 text-sm mt-1">Nein. Das Abo ist monatlich kündbar.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Kommen weitere Kosten hinzu?</h3>
                        <p class="text-gray-600 text-sm mt-1">Neben der Monatsgebühr zahlen Sie nur die tatsächlichen Ladekosten. Der Endpreis pro kWh enthält alle Bestandteile – aufgeschlüsselt in Prozent, damit Sie genau sehen, wohin Ihr Geld fließt.</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Kann ich ohne Abo laden?</h3>
                        <p class="text-gray-600 text-sm mt-1">Nein. Da die prozentuale Preistransparenz das Kernprodukt ist, gibt es keinen kostenlosen Tarif.</p>
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
