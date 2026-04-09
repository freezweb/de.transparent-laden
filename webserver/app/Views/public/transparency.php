<?php
$title = 'Transparenz';
$description = 'So funktioniert Transparent Laden – prozentuale Preisaufschlüsselung, Betreiberkosten, Roaming, Zahlungsabwicklung und Marge offen dargestellt.';
ob_start();
?>

<!-- Intro -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-6">So funktioniert Transparent Laden</h1>
        <p class="text-lg text-gray-600 mb-4">Jede Lade-App zeigt Ihnen den Endpreis pro kWh. Nur bei uns sehen Sie zusätzlich, <strong>wie sich dieser Preis zusammensetzt</strong> – in Prozent, vor Ladebeginn.</p>
        <p class="text-gray-600 mb-12">Wir erfinden keine Preisbestandteile. Wir zeigen Ihnen die realen Kostenblöcke, die den Endpreis ausmachen.</p>

        <div class="space-y-12">
            <!-- Preisaufschlüsselung -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Preisaufschlüsselung in Prozent</h2>
                <p class="text-gray-600 mb-6">Vor Ladebeginn zeigen wir Ihnen, welcher Anteil des kWh-Preises wohin fließt:</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 border">
                        <span class="text-sm text-gray-500">Betreiber / Infrastruktur</span>
                        <div class="text-2xl font-bold text-gray-900 mt-1">62 %</div>
                        <p class="text-xs text-gray-400 mt-1">Anteil, der für Betrieb und Wartung der Ladestation anfällt</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border">
                        <span class="text-sm text-gray-500">Roaming / Betrieb</span>
                        <div class="text-2xl font-bold text-gray-900 mt-1">18 %</div>
                        <p class="text-xs text-gray-400 mt-1">Kosten für Roaming-Zugang und technischen Betrieb</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border">
                        <span class="text-sm text-gray-500">Zahlungsabwicklung</span>
                        <div class="text-2xl font-bold text-gray-900 mt-1">4 %</div>
                        <p class="text-xs text-gray-400 mt-1">Abhängig von Ihrer gewählten Zahlungsart</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border">
                        <span class="text-sm text-gray-500">Unsere Marge</span>
                        <div class="text-2xl font-bold text-gray-900 mt-1">3 %</div>
                        <p class="text-xs text-gray-400 mt-1">Das verdienen wir – offen ausgewiesen</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border">
                        <span class="text-sm text-gray-500">Steuern & Abgaben</span>
                        <div class="text-2xl font-bold text-gray-900 mt-1">13 %</div>
                        <p class="text-xs text-gray-400 mt-1">Gesetzliche Steuern und Abgaben</p>
                    </div>
                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                        <span class="text-sm text-primary-700">Endpreis für Sie</span>
                        <div class="text-2xl font-bold text-primary-800 mt-1">0,49 &euro;/kWh</div>
                        <p class="text-xs text-primary-600 mt-1">Alle Anteile zusammen = 100 %</p>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">* Beispielhafte Darstellung. Tatsächliche Anteile variieren je nach Ladestation, Betreiber und Zahlungsart.</p>
            </div>

            <!-- Startgebühren, Blockiergebühren, Minutenpreise -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Zusätzliche Gebühren vom Betreiber</h2>
                <p class="text-gray-600 mb-6">Viele Ladestationsbetreiber erheben neben dem kWh-Preis weitere Gebühren. Wir definieren diese Gebühren nicht – aber wir zeigen sie Ihnen <strong>vor Ladebeginn</strong> transparent an.</p>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="text-gray-900">Startgebühren</strong>
                            <p class="text-gray-600 text-sm mt-1">Manche Betreiber berechnen eine einmalige Gebühr pro Ladevorgang. Falls vorhanden, wird sie Ihnen in der Preisübersicht angezeigt.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="text-gray-900">Blockiergebühren</strong>
                            <p class="text-gray-600 text-sm mt-1">Einige Betreiber erheben Gebühren, wenn ein Fahrzeug nach Ende des Ladevorgangs den Ladepunkt weiter belegt. Betrag und Bedingungen werden vom Betreiber festgelegt und vor Ladebeginn angezeigt.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="text-gray-900">Minutenpreise</strong>
                            <p class="text-gray-600 text-sm mt-1">An manchen Ladestationen gibt es zusätzlich Gebühren pro Minute Ladezeit. Auch diese werden Ihnen klar dargestellt.</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Zahlungsart -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Einfluss der Zahlungsart</h2>
                <p class="text-gray-600 mb-6">Ihre gewählte Zahlungsart verursacht unterschiedliche Transaktionsgebühren. Wir machen das sichtbar.</p>
                <div class="bg-white rounded-lg p-6 border mb-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Beispiel: PayPal</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Fixe Transaktionsgebühr</span><span class="font-semibold">0,35 &euro; pro Vorgang</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Variable Transaktionsgebühr</span><span class="font-semibold">1,2 % des Betrags</span></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-6 border">
                    <h3 class="font-semibold text-gray-900 mb-3">Beispiel: Kreditkarte</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Fixe Transaktionsgebühr</span><span class="font-semibold">0,25 &euro; pro Vorgang</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Variable Transaktionsgebühr</span><span class="font-semibold">0,9 % des Betrags</span></div>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">Die tatsächlichen Gebühren Ihrer Zahlungsart werden Ihnen als Teil der Preisaufschlüsselung vor Ladebeginn angezeigt.</p>
            </div>

            <!-- Unsere Marge -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Was verdienen wir?</h2>
                <p class="text-gray-600 mb-4">Unsere Marge ist offen ausgewiesen – als prozentualer Anteil in der Preisaufschlüsselung. Wir verdienen an jedem Ladevorgang z. B. 0,01 &euro;/kWh. Ob das fair ist, sehen Sie selbst.</p>
                <p class="text-gray-600">Das ist der Kern von Transparent Laden: <strong>Nicht nur den Endpreis kennen, sondern wissen, wer wie viel bekommt.</strong></p>
            </div>

            <!-- Plug & Charge -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Plug & Charge</h2>
                <p class="text-gray-600 mb-4">Mit Plug & Charge stecken Sie einfach das Kabel ein – die Authentifizierung und Abrechnung erfolgen automatisch über Ihr Fahrzeug.</p>
                <ul class="space-y-2 text-gray-600">
                    <li>&#x2022; Kein QR-Code scannen nötig</li>
                    <li>&#x2022; Kein App-Start erforderlich</li>
                    <li>&#x2022; Automatische Zuordnung zu Ihrem Konto</li>
                    <li>&#x2022; Volle Preistransparenz wie gewohnt</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'description' => $description, 'content' => $content]);
?>
