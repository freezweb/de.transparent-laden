<?php
$title = 'Startseite';
$description = 'Transparent Laden – Festes Preisversprechen: 0,01 €/kWh Aufschlag, alles sichtbar vor Ladebeginn. Ein Tarif: 9,99 €/Monat.';
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
                    Immer nur <span class="text-primary-200">1&nbsp;Cent</span> Aufschlag pro&nbsp;kWh
                </h1>
                <p class="text-lg md:text-xl text-primary-100 mb-8 leading-relaxed">
                    Unser Preisversprechen ist einfach: Wir schlagen auf den Anbieterpreis genau <strong>0,01&nbsp;&euro; pro&nbsp;kWh</strong> auf. Bei Minutenpreisen und Blockiergebühren jeweils <strong>0,01&nbsp;&euro; pro&nbsp;Minute</strong>. Dazu sehen Sie vor Ladebeginn die prozentuale Zusammensetzung und die Zahlungsartenkosten. Alles offen, alles nachvollziehbar.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/portal#/register" class="bg-white text-primary-800 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition text-center shadow-lg">
                        Jetzt starten – 9,99&nbsp;&euro;/Monat
                    </a>
                    <a href="/transparenz" class="border-2 border-white/30 text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition text-center">
                        So funktioniert's
                    </a>
                </div>
            </div>
            <div class="hidden md:flex justify-center">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 shadow-2xl max-w-sm w-full">
                    <div class="text-center mb-5">
                        <div class="w-16 h-16 bg-primary-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <p class="text-primary-200 text-sm font-medium">Preisbeispiel vor Ladebeginn</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-primary-200">Anbieter-kWh-Preis</span><span class="font-mono font-bold">0,45 &euro;</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">+ Unser Aufschlag</span><span class="font-mono font-bold text-primary-300">0,01 &euro;</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">+ Zahlungsart (PayPal)</span><span class="font-mono font-bold">0,01 &euro;</span></div>
                        <hr class="border-white/20">
                        <div class="flex justify-between text-base"><span class="font-semibold">Endpreis</span><span class="font-mono font-bold">0,47 &euro;/kWh</span></div>
                        <div class="flex justify-between text-xs text-primary-300"><span>zzgl. Startgebühr (Betreiber)</span><span>1,00 &euro;</span></div>
                        <div class="flex justify-between text-xs text-primary-300"><span>zzgl. Zahlungsart-Fixgebühr</span><span>0,35 &euro;</span></div>
                    </div>
                    <div class="mt-4 bg-primary-500/30 rounded-lg p-3 text-xs text-primary-100 text-center">
                        Prozentuale Aufschlüsselung + fester Aufschlag – vor Ladebeginn
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Preisversprechen -->
<section class="py-16 bg-white border-b">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Unser festes Preisversprechen</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Keine versteckten Margen, keine undurchsichtigen Zuschläge. Unser Aufschlag ist immer gleich – egal an welcher Ladestation.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div class="bg-primary-50 rounded-xl p-6 border-2 border-primary-200 text-center">
                <div class="text-4xl font-bold text-primary-800 mb-2">0,01&nbsp;&euro;</div>
                <div class="text-sm font-semibold text-primary-700 mb-2">pro kWh</div>
                <p class="text-sm text-gray-600">Unser fester Aufschlag auf den kWh-Preis des Anbieters. Immer. Überall.</p>
            </div>
            <div class="bg-primary-50 rounded-xl p-6 border-2 border-primary-200 text-center">
                <div class="text-4xl font-bold text-primary-800 mb-2">0,01&nbsp;&euro;</div>
                <div class="text-sm font-semibold text-primary-700 mb-2">pro Minute</div>
                <p class="text-sm text-gray-600">Wenn der Anbieter minutenbasiert abrechnet, schlagen wir 0,01&nbsp;&euro;/Minute auf.</p>
            </div>
            <div class="bg-primary-50 rounded-xl p-6 border-2 border-primary-200 text-center">
                <div class="text-4xl font-bold text-primary-800 mb-2">0,01&nbsp;&euro;</div>
                <div class="text-sm font-semibold text-primary-700 mb-2">pro Min. Blockiergebühr</div>
                <p class="text-sm text-gray-600">Hat der Anbieter eine Blockiergebühr, schlagen wir 0,01&nbsp;&euro;/Minute darauf auf.</p>
            </div>
        </div>
        <p class="text-center text-gray-500 text-sm mt-6">Dazu: 9,99&nbsp;&euro;/Monat Grundgebühr + sichtbare Zahlungsartenkosten. Das ist unser ganzes Modell.</p>
    </div>
</section>

<!-- Vergleich -->
<section class="py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Der Unterschied zu anderen Lade-Apps</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Andere zeigen einen Endpreis. Wir zeigen Ihnen, <strong>was der Anbieter verlangt</strong>, <strong>was wir aufschlagen</strong> und <strong>was Ihre Zahlungsart kostet</strong>.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <div class="bg-gray-100 rounded-xl p-6 border-2 border-gray-200">
                <h3 class="text-lg font-semibold text-gray-500 mb-4">Andere Lade-Apps</h3>
                <div class="bg-white rounded-lg p-4 border text-center">
                    <div class="text-3xl font-bold text-gray-800">0,49&nbsp;&euro;/kWh</div>
                    <div class="text-sm text-gray-400 mt-2">Fertig. Keine Erklärung, keine Aufschlüsselung.</div>
                </div>
            </div>
            <div class="bg-primary-50 rounded-xl p-6 border-2 border-primary-300">
                <h3 class="text-lg font-semibold text-primary-800 mb-4">Transparent Laden</h3>
                <div class="bg-white rounded-lg p-4 border border-primary-200 space-y-1.5 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Anbieter-kWh-Preis</span><span class="font-semibold">0,45 &euro;</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Unser Aufschlag (fest)</span><span class="font-semibold text-primary-700">+ 0,01 &euro;</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Zahlungsart (anteilig)</span><span class="font-semibold">+ 0,01 &euro;</span></div>
                    <hr>
                    <div class="flex justify-between font-bold text-primary-800"><span>Endpreis</span><span>0,47 &euro;/kWh</span></div>
                    <div class="flex justify-between text-xs text-gray-500"><span>Startgebühr (Betreiber)</span><span>1,00 &euro;</span></div>
                    <div class="flex justify-between text-xs text-gray-500"><span>Zahlungsart-Fixgebühr (PayPal)</span><span>0,35 &euro;</span></div>
                </div>
                <p class="text-xs text-gray-500 mt-3">+ prozentuale Aufteilung aller Bestandteile sichtbar</p>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Was uns ausmacht</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Ein fester Aufschlag, volle Transparenz und keine Tricks.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Fester Aufschlag</h3>
                <p class="text-gray-600">Immer 0,01&nbsp;&euro;/kWh. Immer 0,01&nbsp;&euro;/Minute bei Minutenpreis. Immer 0,01&nbsp;&euro;/Minute bei Blockiergebühr. Kein Rätselraten.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-accent-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Alle Kosten sichtbar</h3>
                <p class="text-gray-600">Anbieterpreis, unser Aufschlag, Startgebühren, Minutenpreise, Blockiergebühren, Zahlungsartenkosten – alles vor Ladebeginn, alles aufgeschlüsselt.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Zahlungsart-Transparenz</h3>
                <p class="text-gray-600">Verschiedene Zahlungsarten kosten unterschiedlich. Wir zeigen Ihnen vor Ladebeginn, welche Fixgebühr und welchen prozentualen Anteil Ihre Zahlungsart verursacht.</p>
            </div>
        </div>
    </div>
</section>

<!-- In 3 Schritten -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">In 3 Schritten laden</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">1</div>
                <h3 class="text-xl font-semibold mb-3">Registrieren</h3>
                <p class="text-gray-600">Konto erstellen und Zahlungsart hinterlegen. Ein Tarif: 9,99&nbsp;&euro;/Monat, monatlich kündbar.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">2</div>
                <h3 class="text-xl font-semibold mb-3">Ladestation auswählen</h3>
                <p class="text-gray-600">Sehen Sie vorab den Anbieterpreis, unseren festen 1-Cent-Aufschlag, Ihre Zahlungsartenkosten und alle Betreiber-Gebühren – aufgeschlüsselt in Prozent und Cent.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">3</div>
                <h3 class="text-xl font-semibold mb-3">Laden & nachvollziehen</h3>
                <p class="text-gray-600">Laden Sie Ihr Fahrzeug. Die Rechnung zeigt die vollständige Aufschlüsselung – Anbieter, unser Aufschlag, Zahlungsart, Gebühren.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 bg-primary-800 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">1 Cent pro kWh. So einfach ist unser Modell.</h2>
        <p class="text-lg text-primary-100 mb-8">Ein fester Aufschlag. Sichtbare Zahlungsartenkosten. Prozentuale Aufschlüsselung. 9,99&nbsp;&euro;/Monat. Alles vor Ladebeginn.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/portal#/register" class="bg-white text-primary-800 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition shadow-lg">Jetzt registrieren</a>
            <a href="/transparenz" class="border-2 border-white/30 px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition">Transparenz-Modell ansehen</a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'description' => $description, 'content' => $content]);
?>
