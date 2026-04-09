<?php
$title = 'Transparenz';
$description = 'So funktioniert Transparent Laden – fester Aufschlag von 0,01 €/kWh, sichtbare Zahlungsartenkosten, prozentuale Aufschlüsselung vor Ladebeginn.';
ob_start();
?>

<!-- Intro -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-6">So funktioniert Transparent Laden</h1>
        <p class="text-lg text-gray-600 mb-4">Andere Lade-Apps zeigen Ihnen einen Endpreis. Wir zeigen Ihnen, <strong>woraus dieser Preis besteht</strong>: was der Anbieter verlangt, was wir aufschlagen und was Ihre Zahlungsart kostet.</p>
        <p class="text-gray-600 mb-12">Unser Aufschlag ist dabei immer gleich. Das ist unser festes Preisversprechen.</p>

        <div class="space-y-12">
            <!-- Unser festes Aufschlagsmodell -->
            <div class="bg-primary-50 rounded-xl p-8 border-2 border-primary-200">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Unser festes Transparenzversprechen</h2>
                <p class="text-gray-600 mb-6">Egal welche Ladestation, egal welcher Anbieter – unser Aufschlag ist immer gleich:</p>
                <div class="grid sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg p-5 border border-primary-200 text-center">
                        <div class="text-3xl font-bold text-primary-800">0,01&nbsp;&euro;</div>
                        <div class="text-sm font-semibold text-primary-700 mt-1">pro kWh</div>
                        <p class="text-xs text-gray-500 mt-2">Auf den kWh-Preis des Anbieters</p>
                    </div>
                    <div class="bg-white rounded-lg p-5 border border-primary-200 text-center">
                        <div class="text-3xl font-bold text-primary-800">0,01&nbsp;&euro;</div>
                        <div class="text-sm font-semibold text-primary-700 mt-1">pro Minute</div>
                        <p class="text-xs text-gray-500 mt-2">Wenn der Anbieter einen Minutenpreis hat</p>
                    </div>
                    <div class="bg-white rounded-lg p-5 border border-primary-200 text-center">
                        <div class="text-3xl font-bold text-primary-800">0,01&nbsp;&euro;</div>
                        <div class="text-sm font-semibold text-primary-700 mt-1">pro Min. Blockiergebühr</div>
                        <p class="text-xs text-gray-500 mt-2">Wenn der Anbieter Blockiergebühren erhebt</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Diese Aufschläge sind konstant und gelten für jeden Ladevorgang. Der Rest des Preises kommt vom Anbieter und von Ihrer gewählten Zahlungsart.</p>
            </div>

            <!-- Preis-Zusammensetzung Beispiel -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">So setzt sich Ihr Ladepreis zusammen</h2>
                <p class="text-gray-600 mb-6">Vor Ladebeginn sehen Sie den Endpreis <strong>und</strong> die Aufschlüsselung – sowohl in Cent als auch in Prozent:</p>

                <div class="bg-white rounded-lg p-6 border mb-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Preisbeispiel: AC-Ladestation, 22 kW</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center"><span class="text-gray-600">Anbieter-kWh-Preis</span><span class="font-semibold">0,45 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 90 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Unser Aufschlag (fest)</span><span class="font-semibold text-primary-700">0,01 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 2 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Zahlungsart-Anteil (variabel)</span><span class="font-semibold">0,01 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 2 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">MwSt. (im Anbieterpreis enthalten)</span><span class="font-semibold">incl.</span><span class="text-gray-400 text-xs w-16 text-right"></span></div>
                        <hr>
                        <div class="flex justify-between items-center font-bold text-primary-800"><span>Endpreis pro kWh</span><span>0,47 &euro;</span><span class="text-xs w-16 text-right">100 %</span></div>
                    </div>
                    <div class="mt-4 pt-4 border-t space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Startgebühr (Betreiber)</span><span class="font-medium">1,00 &euro;</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Zahlungsart-Fixgebühr (PayPal)</span><span class="font-medium">0,35 &euro;</span></div>
                    </div>
                </div>
                <p class="text-sm text-gray-500">* Beispielhafte Darstellung. Anbieterpreise, Gebühren und Zahlungsartenkosten variieren je nach Ladestation und Zahlungsart.</p>
            </div>

            <!-- Was der Anbieter definiert -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Was der Anbieter definiert</h2>
                <p class="text-gray-600 mb-6">Der Ladestationsbetreiber legt die Basispreise fest. Wir reichen diese transparent an Sie weiter:</p>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="text-gray-900">kWh-Preis</strong>
                            <p class="text-gray-600 text-sm mt-1">Der Grundpreis pro Kilowattstunde. Darauf kommt unser fester Aufschlag von 0,01&nbsp;&euro;.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="text-gray-900">Minutenpreis (falls vorhanden)</strong>
                            <p class="text-gray-600 text-sm mt-1">Einige Anbieter berechnen einen Preis pro Minute Ladezeit. Darauf kommt unser Aufschlag von 0,01&nbsp;&euro;/Minute.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="text-gray-900">Startgebühr (falls vorhanden)</strong>
                            <p class="text-gray-600 text-sm mt-1">Eine einmalige Gebühr pro Ladevorgang, vom Betreiber festgelegt. Wird Ihnen separat angezeigt und 1:1 weitergereicht.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-primary-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="text-gray-900">Blockiergebühr (falls vorhanden)</strong>
                            <p class="text-gray-600 text-sm mt-1">Kosten pro Minute, wenn das Fahrzeug nach Ladeschluss den Platz belegt. Darauf kommt unser Aufschlag von 0,01&nbsp;&euro;/Minute.</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Zahlungsart -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Einfluss der Zahlungsart</h2>
                <p class="text-gray-600 mb-6">Verschiedene Zahlungsarten verursachen unterschiedliche Kosten. Wir zeigen Ihnen vor Ladebeginn, was Ihre gewählte Zahlungsart kostet – aufgesplittet in fixen und prozentualen Anteil.</p>
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div class="bg-white rounded-lg p-6 border">
                        <h3 class="font-semibold text-gray-900 mb-3">Beispiel: PayPal</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600">Fixe Transaktionsgebühr</span><span class="font-semibold">0,35 &euro;</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Variable Transaktionsgebühr</span><span class="font-semibold">1,2 %</span></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-3">Die 0,35&nbsp;&euro; erscheinen als eigene Startkosten. Die 1,2&nbsp;% fließen sichtbar in den kWh-Preis ein.</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 border">
                        <h3 class="font-semibold text-gray-900 mb-3">Beispiel: Kreditkarte</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600">Fixe Transaktionsgebühr</span><span class="font-semibold">0,25 &euro;</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Variable Transaktionsgebühr</span><span class="font-semibold">0,9 %</span></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-3">Die 0,25&nbsp;&euro; erscheinen als eigene Startkosten. Die 0,9&nbsp;% fließen sichtbar in den kWh-Preis ein.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-500">Die tatsächlichen Kosten Ihrer Zahlungsart sehen Sie vor Ladebeginn als Teil der Preisaufschlüsselung.</p>
            </div>

            <!-- Prozentuale Aufschlüsselung -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Prozentuale Transparenz</h2>
                <p class="text-gray-600 mb-4">Zusätzlich zum festen Aufschlag zeigen wir Ihnen die <strong>prozentuale Zusammensetzung</strong> des Endpreises. Sie sehen auf einen Blick:</p>
                <div class="grid sm:grid-cols-2 gap-3 mb-4">
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-primary-700 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Wie viel % an den Anbieter gehen</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-primary-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Wie viel % unser Aufschlag ausmacht</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-accent-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Wie viel % die Zahlungsart kostet</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-gray-400 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Wie viel % Steuern enthalten sind</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600">So kennen Sie nicht nur den Endpreis, sondern verstehen auch, wer wie viel bekommt.</p>
            </div>

            <!-- Grundgebühr / wirtschaftliches Modell -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Wie verdienen wir?</h2>
                <p class="text-gray-600 mb-4">Wir finanzieren uns aus zwei Quellen:</p>
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div class="bg-white rounded-lg p-4 border">
                        <div class="font-semibold text-gray-900 mb-1">Grundgebühr</div>
                        <div class="text-2xl font-bold text-primary-800">9,99&nbsp;&euro;/Monat</div>
                        <p class="text-xs text-gray-500 mt-2">Unser wirtschaftlicher Haupthebel. Monatlich kündbar.</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border">
                        <div class="font-semibold text-gray-900 mb-1">Fester variabler Aufschlag</div>
                        <div class="text-2xl font-bold text-primary-800">0,01&nbsp;&euro;</div>
                        <p class="text-xs text-gray-500 mt-2">Pro kWh, pro Minute, pro Minute Blockiergebühr – wenn vorhanden.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Das ist unser gesamtes Erlösmodell. Keine versteckten Gebühren, keine dynamischen Margen. Die Grundgebühr ermöglicht es uns, den variablen Aufschlag so niedrig zu halten.</p>
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
