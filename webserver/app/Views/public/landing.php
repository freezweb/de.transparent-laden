<?php
$title = 'Startseite';
$description = 'Transparent Laden – Sehen Sie vor jedem Ladevorgang, wie sich der Preis prozentual zusammensetzt. Vollständige Preistransparenz für Elektrofahrzeuge.';
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
                    Wissen, <span class="text-primary-200">woraus</span> sich Ihr Ladepreis zusammensetzt
                </h1>
                <p class="text-lg md:text-xl text-primary-100 mb-8 leading-relaxed">
                    Andere Apps zeigen Ihnen einen Endpreis. Wir zeigen Ihnen zusätzlich, wie sich dieser Preis prozentual zusammensetzt – inklusive Betreiberkosten, Roaming, Zahlungsabwicklung und unserer Marge. Alles sichtbar vor Ladebeginn.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/portal#/register" class="bg-white text-primary-800 px-8 py-3 rounded-lg font-semibold hover:bg-primary-50 transition text-center shadow-lg">
                        Jetzt starten – 9,99 €/Monat
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
                        <p class="text-primary-200 text-sm font-medium">Preisaufschlüsselung vor Ladebeginn</p>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-primary-200">Betreiber / Infrastruktur</span><span class="font-mono font-bold">62 %</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">Roaming / Betrieb</span><span class="font-mono font-bold">18 %</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">Zahlungsabwicklung</span><span class="font-mono font-bold">4 %</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">Unsere Marge</span><span class="font-mono font-bold">3 %</span></div>
                        <div class="flex justify-between"><span class="text-primary-200">Steuern & Abgaben</span><span class="font-mono font-bold">13 %</span></div>
                        <hr class="border-white/20">
                        <div class="flex justify-between text-base"><span class="font-semibold">Endpreis</span><span class="font-mono font-bold">0,49 &euro;/kWh</span></div>
                        <div class="flex justify-between text-xs text-primary-300"><span>zzgl. Startgebühr (Betreiber)</span><span>0,35 &euro;</span></div>
                    </div>
                    <div class="mt-4 bg-primary-500/30 rounded-lg p-3 text-xs text-primary-100 text-center">
                        Alle Bestandteile in % sichtbar – vor Ladebeginn
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- USP / Differenzierung -->
<section class="py-16 bg-white border-b">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Der Unterschied zu anderen Lade‑Apps</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Fast jede App zeigt Ihnen einen Endpreis pro kWh. Aber nur Transparent Laden zeigt Ihnen, <strong>woraus</strong> dieser Preis besteht.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <div class="bg-gray-100 rounded-xl p-6 border-2 border-gray-200">
                <h3 class="text-lg font-semibold text-gray-500 mb-4">Andere Lade-Apps</h3>
                <div class="bg-white rounded-lg p-4 border text-center">
                    <div class="text-3xl font-bold text-gray-800">0,49 &euro;/kWh</div>
                    <div class="text-sm text-gray-400 mt-2">Das war's. Keine weiteren Informationen.</div>
                </div>
            </div>
            <div class="bg-primary-50 rounded-xl p-6 border-2 border-primary-300">
                <h3 class="text-lg font-semibold text-primary-800 mb-4">Transparent Laden</h3>
                <div class="bg-white rounded-lg p-4 border border-primary-200 space-y-1.5 text-sm">
                    <div class="flex justify-between"><span class="text-gray-600">Betreiber / Infrastruktur</span><span class="font-semibold">62 %</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Roaming / Betrieb</span><span class="font-semibold">18 %</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Zahlungsabwicklung (PayPal)</span><span class="font-semibold">4 %</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Unsere Marge</span><span class="font-semibold">3 %</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Steuern & Abgaben</span><span class="font-semibold">13 %</span></div>
                    <hr>
                    <div class="flex justify-between font-bold text-primary-800"><span>Endpreis</span><span>0,49 &euro;/kWh</span></div>
                    <div class="flex justify-between text-xs text-gray-500"><span>Startgebühr (Betreiber)</span><span>0,35 &euro;</span></div>
                    <div class="flex justify-between text-xs text-gray-500"><span>davon Zahlungsart-Transaktionsgebühr</span><span>0,35 &euro;</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Was uns ausmacht</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Preistransparenz ist kein Feature – es ist unser Geschäftsmodell.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Prozentuale Aufschlüsselung</h3>
                <p class="text-gray-600">Sehen Sie vor Ladebeginn, welcher Anteil an den Betreiber, ans Roaming, an die Zahlungsabwicklung und an uns geht – in Prozent.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-accent-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-accent-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Alle Kosten sichtbar</h3>
                <p class="text-gray-600">Startgebühren, Minutenpreise, Blockiergebühren – wenn der Betreiber sie erhebt, zeigen wir sie Ihnen klar an, bevor Sie laden.</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-8 hover:shadow-lg transition">
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <h3 class="text-xl font-semibold mb-3">Zahlungsart-Transparenz</h3>
                <p class="text-gray-600">Ihre Zahlungsart beeinflusst den Preis. Wir zeigen Ihnen vor Ladebeginn, welche Kosten Ihre gewählte Zahlungsart verursacht.</p>
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
                <h3 class="text-xl font-semibold mb-3">Registrieren</h3>
                <p class="text-gray-600">Konto erstellen und Zahlungsart hinterlegen. Ein Tarif, klar und einfach: 9,99 €/Monat.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">2</div>
                <h3 class="text-xl font-semibold mb-3">Ladestation auswählen</h3>
                <p class="text-gray-600">Sehen Sie vorab die vollständige Preiszusammensetzung in Prozent – inklusive aller Gebühren des Betreibers und Ihrer Zahlungsart.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-primary-700 text-white rounded-full mx-auto mb-6 flex items-center justify-center text-2xl font-bold">3</div>
                <h3 class="text-xl font-semibold mb-3">Laden & nachvollziehen</h3>
                <p class="text-gray-600">Laden Sie Ihr Fahrzeug. Die Kostenentwicklung ist live nachvollziehbar, die Rechnung vollständig aufgeschlüsselt.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 bg-primary-800 text-white">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Preistransparenz, die es so noch nicht gab</h2>
        <p class="text-lg text-primary-100 mb-8">Ein Tarif. 9,99 €/Monat. Dafür sehen Sie bei jedem Ladevorgang, wie sich der Preis zusammensetzt – in Prozent, vor Ladebeginn.</p>
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
