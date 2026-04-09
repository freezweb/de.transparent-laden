<?php
$title = 'Transparenz';
$description = 'So funktioniert Transparent Laden – fester Aufschlag von 0,01 €/kWh, sichtbare Zahlungsartenkosten, prozentuale Aufschlüsselung vor Ladebeginn.';
ob_start();
?>

<!-- Intro -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-6">So funktioniert Transparent Laden</h1>
        <p class="text-lg text-gray-600 mb-4">Andere Lade-Apps zeigen Ihnen einen Endpreis. Wir zeigen Ihnen, <strong>woraus dieser Preis besteht</strong> – aufgeschlüsselt nach den beteiligten Bereichen: Betreiber&nbsp;/ Infrastruktur, Roaming&nbsp;/ Betrieb, Zahlungsabwicklung und unsere Marge. Jeder Bereich wird mit seinem prozentualen Anteil sichtbar gemacht.</p>
        <p class="text-gray-600 mb-12">Unser Aufschlag ist dabei immer gleich. Das ist unser festes Preisversprechen. Die prozentuale Verteilung variiert von Ladestation zu Ladestation – und genau das machen wir transparent.</p>

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
                <p class="text-gray-600 mb-6">Vor Ladebeginn sehen Sie den Endpreis <strong>und</strong> die Aufschlüsselung – sowohl in Cent als auch in Prozent nach den beteiligten Bereichen:</p>

                <div class="bg-white rounded-lg p-6 border mb-4">
                    <h3 class="font-semibold text-gray-900 mb-4">Preisbeispiel: AC-Ladestation, 22 kW</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center"><span class="text-gray-600">Betreiber / Infrastruktur</span><span class="font-semibold">0,33 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 70 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Roaming / Betrieb</span><span class="font-semibold">0,12 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 26 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Zahlungsabwicklung</span><span class="font-semibold">0,01 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 2 %</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-600">Unsere Marge (fester Aufschlag)</span><span class="font-semibold text-primary-700">0,01 &euro;</span><span class="text-gray-400 text-xs w-16 text-right">~ 2 %</span></div>
                        <hr>
                        <div class="flex justify-between items-center font-bold text-primary-800"><span>Endpreis pro kWh</span><span>0,47 &euro;</span><span class="text-xs w-16 text-right">100 %</span></div>
                    </div>
                    <!-- Prozentbalken -->
                    <div class="mt-4 flex h-5 rounded-full overflow-hidden">
                        <div class="bg-primary-800" style="width:70%" title="Betreiber / Infrastruktur"></div>
                        <div class="bg-primary-500" style="width:26%" title="Roaming / Betrieb"></div>
                        <div class="bg-accent-500" style="width:2%" title="Zahlungsabwicklung"></div>
                        <div class="bg-green-500" style="width:2%" title="Unsere Marge"></div>
                    </div>
                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-1 text-xs text-gray-500">
                        <span><span class="inline-block w-2 h-2 rounded-full bg-primary-800 mr-1"></span>Betreiber</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-primary-500 mr-1"></span>Roaming</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-accent-500 mr-1"></span>Zahlung</span>
                        <span><span class="inline-block w-2 h-2 rounded-full bg-green-500 mr-1"></span>Marge</span>
                    </div>
                    <div class="mt-4 pt-4 border-t space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Startgebühr (Betreiber)</span><span class="font-medium">1,00 &euro;</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Zahlungsart-Fixgebühr (PayPal)</span><span class="font-medium">0,35 &euro;</span></div>
                    </div>
                </div>
                <p class="text-sm text-gray-500">* Beispielhafte Darstellung. Anteile variieren je nach Ladestation, Anbieter und Zahlungsart. Die tatsächliche Aufteilung wird ladespezifisch berechnet und vor Ladebeginn angezeigt.</p>
            </div>

            <!-- Was der Anbieter definiert + Roaming/Betrieb -->
            <div class="bg-gray-50 rounded-xl p-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Woher kommen die Preisbestandteile?</h2>
                <p class="text-gray-600 mb-6">Der Endpreis besteht aus mehreren Bereichen. Wir machen jeden einzelnen sichtbar:</p>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <span class="w-6 h-6 rounded-full bg-primary-800 flex-shrink-0 mt-0.5 mr-3"></span>
                        <div>
                            <strong class="text-gray-900">Betreiber / Infrastruktur</strong>
                            <p class="text-gray-600 text-sm mt-1">Der Anteil, den der Ladestationsbetreiber für Strom, Infrastruktur und Betrieb der Station erhält. Dieser variiert je nach Betreiber und Standort.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="w-6 h-6 rounded-full bg-primary-500 flex-shrink-0 mt-0.5 mr-3"></span>
                        <div>
                            <strong class="text-gray-900">Roaming / Betrieb</strong>
                            <p class="text-gray-600 text-sm mt-1">Kosten für die Roaming-Anbindung und den laufenden Betrieb des Ladenetzwerks. Ermöglicht Ihnen den Zugang zu Stationen verschiedener Betreiber über eine einzige App.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="w-6 h-6 rounded-full bg-accent-500 flex-shrink-0 mt-0.5 mr-3"></span>
                        <div>
                            <strong class="text-gray-900">Zahlungsabwicklung</strong>
                            <p class="text-gray-600 text-sm mt-1">Transaktionskosten Ihrer gewählten Zahlungsart (z.&nbsp;B. PayPal, Kreditkarte). Aufgeteilt in fixen und prozentualen Anteil – beides wird separat sichtbar gemacht.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <span class="w-6 h-6 rounded-full bg-green-500 flex-shrink-0 mt-0.5 mr-3"></span>
                        <div>
                            <strong class="text-gray-900">Unsere Marge</strong>
                            <p class="text-gray-600 text-sm mt-1">Fest 0,01&nbsp;&euro;/kWh. Bei Minutenpreisen 0,01&nbsp;&euro;/Minute. Bei Blockiergebühren 0,01&nbsp;&euro;/Minute. Unser festes Preisversprechen.</p>
                        </div>
                    </li>
                </ul>
                <p class="text-sm text-gray-500 mt-6">Zusätzlich zeigen wir providerseitige Zusatzkosten wie Startgebühren und Blockiergebühren als separate Positionen an.</p>
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
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Prozentuale Partner-Aufteilung</h2>
                <p class="text-gray-600 mb-4">Zusätzlich zum festen Aufschlag zeigen wir Ihnen die <strong>prozentuale Verteilung</strong> des Endpreises auf die beteiligten Bereiche. Sie sehen auf einen Blick:</p>
                <div class="grid sm:grid-cols-2 gap-3 mb-4">
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-primary-800 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Betreiber / Infrastruktur: wie viel % an den Stationsbetreiber gehen</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-primary-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Roaming / Betrieb: wie viel % für Netzanbindung und Betrieb anfallen</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-accent-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Zahlungsabwicklung: wie viel % Ihre Zahlungsart verursacht</span>
                    </div>
                    <div class="flex items-center bg-white rounded-lg p-3 border">
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-3 flex-shrink-0"></span>
                        <span class="text-gray-700 text-sm">Unsere Marge: wie viel % unser fester Aufschlag ausmacht</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Diese Verteilung ist <strong>dynamisch</strong> – sie wird für jeden Ladevorgang individuell berechnet und hängt vom jeweiligen Anbieter, der Ladestation und Ihrer Zahlungsart ab. So kennen Sie nicht nur den Endpreis, sondern verstehen auch, wer wie viel bekommt.</p>
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
