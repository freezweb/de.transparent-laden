<?php
$title = 'Transparenz';
ob_start();
?>
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-6">So funktioniert Transparent Laden</h1>
        <p class="text-lg text-gray-600 mb-12">Bei uns sehen Sie immer, wofür Sie zahlen. Wir schlüsseln jeden Preisbestandteil offen auf.</p>

        <div class="space-y-12">
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Preisaufschlüsselung</h2>
                <p class="text-gray-600 mb-6">Jeder Ladevorgang zeigt Ihnen transparent:</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 border"><span class="text-sm text-gray-500">Energiekosten (Börsenpreis)</span><div class="text-lg font-semibold mt-1">0,12 &euro;/kWh</div></div>
                    <div class="bg-white rounded-lg p-4 border"><span class="text-sm text-gray-500">Netzentgelte</span><div class="text-lg font-semibold mt-1">0,08 &euro;/kWh</div></div>
                    <div class="bg-white rounded-lg p-4 border"><span class="text-sm text-gray-500">Steuern & Abgaben</span><div class="text-lg font-semibold mt-1">0,07 &euro;/kWh</div></div>
                    <div class="bg-white rounded-lg p-4 border"><span class="text-sm text-gray-500">CPO-Entgelt</span><div class="text-lg font-semibold mt-1">0,08 &euro;/kWh</div></div>
                    <div class="bg-white rounded-lg p-4 border"><span class="text-sm text-gray-500">Unsere Marge</span><div class="text-lg font-semibold mt-1">0,04 &euro;/kWh</div></div>
                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-200"><span class="text-sm text-primary-700">Endpreis für Sie</span><div class="text-lg font-bold text-primary-800 mt-1">0,39 &euro;/kWh</div></div>
                </div>
                <p class="text-sm text-gray-500 mt-4">* Beispielhafte Darstellung. Tatsächliche Preise variieren je nach Standort und Anbieter.</p>
            </div>

            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Keine versteckten Kosten</h2>
                <ul class="space-y-4">
                    <li class="flex items-start"><svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><div><strong class="text-gray-900">Keine Startgebühren</strong><p class="text-gray-600 text-sm mt-1">Sie zahlen nur für die tatsächlich geladene Energie.</p></div></li>
                    <li class="flex items-start"><svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><div><strong class="text-gray-900">Faire Blockiergebühren</strong><p class="text-gray-600 text-sm mt-1">Nur nach 4 Stunden Standzeit, klar kommuniziert, pro Minute berechnet.</p></div></li>
                    <li class="flex items-start"><svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><div><strong class="text-gray-900">Echte kWh-Abrechnung</strong><p class="text-gray-600 text-sm mt-1">Physikalisch gemessene Energie am Ladepunkt, keine Schätzwerte.</p></div></li>
                </ul>
            </div>

            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Plug & Charge</h2>
                <p class="text-gray-600 mb-4">Mit Plug & Charge stecken Sie einfach das Kabel ein - die Authentifizierung und Abrechnung erfolgen automatisch über Ihr Fahrzeug.</p>
                <ul class="space-y-2 text-gray-600">
                    <li>&#x2022; Kein QR-Code scannen nötig</li>
                    <li>&#x2022; Kein App-Start erforderlich</li>
                    <li>&#x2022; Automatische Zuordnung zu Ihrem Konto</li>
                    <li>&#x2022; Verfügbar mit Pro- und Business-Abo</li>
                </ul>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'content' => $content]);
?>
