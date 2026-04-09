<?php
$title = 'FAQ';
$description = 'Häufig gestellte Fragen zu Transparent Laden – Tarif, Preisaufschlüsselung, Zahlungsarten und mehr.';
ob_start();
?>
<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4 text-center">Häufig gestellte Fragen</h1>
        <p class="text-lg text-gray-600 mb-12 text-center">Haben Sie Fragen? Hier finden Sie Antworten.</p>
        <div class="space-y-4" x-data="{ open: null }">
            <?php
            $faqs = [
                ['Wie funktioniert die Preistransparenz?', 'Vor jedem Ladevorgang sehen Sie, wie sich der kWh-Preis prozentual zusammensetzt: Anteil Betreiber/Infrastruktur, Anteil Roaming/Betrieb, Anteil Zahlungsabwicklung, Steuern und unsere Marge – alles in Prozent. Zusätzlich werden Startgebühren, Blockiergebühren und Minutenpreise des Betreibers separat ausgewiesen.'],
                ['Was unterscheidet Transparent Laden von anderen Lade-Apps?', 'Andere Apps zeigen Ihnen einen Endpreis pro kWh. Wir zeigen Ihnen zusätzlich, woraus dieser Preis besteht – welcher Anteil an den Betreiber, ans Roaming, an die Zahlungsabwicklung und an uns geht. Das gibt es so bei keiner anderen App.'],
                ['Was kostet Transparent Laden?', 'Es gibt einen Tarif: 9,99 € pro Monat, monatlich kündbar. Dazu kommen die Ladekosten, die je nach Ladestation und Betreiber variieren. Es gibt keinen kostenlosen Tarif – die prozentuale Preistransparenz ist das Kernprodukt.'],
                ['Brauche ich eine Ladekarte?', 'Nein. Die Authentifizierung erfolgt über die App oder über Plug & Charge (wenn Ihr Fahrzeug das unterstützt). Eine physische Karte ist nicht erforderlich.'],
                ['Welche Ladepunkte kann ich nutzen?', 'Sie haben Zugang zu tausenden Ladepunkten in Deutschland und Europa über unsere Roaming-Partner. Die Verfügbarkeit sehen Sie in Echtzeit in der App.'],
                ['Wie wird abgerechnet?', 'Nach dem Ladevorgang erhalten Sie eine Rechnung mit vollständiger Kostenaufschlüsselung. Die Zahlung erfolgt über Ihre hinterlegte Zahlungsart.'],
                ['Beeinflusst meine Zahlungsart den Preis?', 'Ja. Verschiedene Zahlungsarten haben unterschiedliche Transaktionsgebühren – z. B. PayPal: 0,35 € fix + 1,2 % variabel. Diese Kosten werden Ihnen vor Ladebeginn als Teil der Preisaufschlüsselung angezeigt, damit Sie bewusst wählen können.'],
                ['Was sind Startgebühren und Blockiergebühren?', 'Manche Ladestationsbetreiber erheben eine einmalige Startgebühr pro Ladevorgang oder Blockiergebühren bei längerem Stehen nach Ladeschluss. Diese Gebühren werden vom Betreiber festgelegt – nicht von uns. Wir zeigen sie Ihnen transparent vor Ladebeginn an.'],
                ['Was ist Plug & Charge?', 'Mit Plug & Charge stecken Sie nur das Ladekabel ein – Ihr Fahrzeug authentifiziert sich automatisch. Keine App, kein QR-Code nötig. Die volle Preistransparenz bleibt erhalten.'],
                ['Was passiert bei einer Ladestörung?', 'Unsere App erkennt Störungen automatisch und benachrichtigt Sie. Nicht geladene Energie wird nicht berechnet. Im Problemfall hilft unser Support.'],
                ['Wie sicher sind meine Daten?', 'Wir verwenden Ende-zu-Ende-Verschlüsselung, speichern Daten ausschließlich auf europäischen Servern und halten alle DSGVO-Vorgaben ein.'],
            ];
            foreach ($faqs as $i => $faq): ?>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="open = open === <?= $i ?> ? null : <?= $i ?>" class="w-full flex justify-between items-center px-6 py-4 text-left bg-white hover:bg-gray-50 transition">
                    <span class="font-medium text-gray-900"><?= $faq[0] ?></span>
                    <svg :class="open === <?= $i ?> ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === <?= $i ?>" x-cloak class="px-6 pb-4 text-gray-600"><?= $faq[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-12 text-center bg-gray-50 rounded-xl p-8">
            <h2 class="text-xl font-semibold mb-3">Frage nicht dabei?</h2>
            <p class="text-gray-600 mb-4">Schreiben Sie uns und wir helfen Ihnen gerne weiter.</p>
            <a href="/kontakt" class="inline-block bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-800 transition">Kontakt aufnehmen</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'description' => $description, 'content' => $content]);
?>
