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
                ['Wie funktioniert die Preistransparenz?', 'Vor jedem Ladevorgang sehen Sie die vollst&auml;ndige Preiszusammensetzung: Betreiber&nbsp;/ Infrastruktur, Roaming&nbsp;/ Betrieb, Zahlungsabwicklung und unsere Marge &ndash; jeweils mit prozentualen Anteilen. Zus&auml;tzlich werden Startgeb&uuml;hren, Minutenpreise und Blockiergeb&uuml;hren als separate Positionen angezeigt. Die Verteilung ist dynamisch und wird f&uuml;r jeden Ladevorgang individuell berechnet.'],
                ['Was unterscheidet Transparent Laden von anderen Lade-Apps?', 'Andere Apps zeigen einen Endpreis. Wir zeigen Ihnen zus&auml;tzlich, wie sich dieser Preis auf die beteiligten Bereiche verteilt: Betreiber&nbsp;/ Infrastruktur, Roaming&nbsp;/ Betrieb, Zahlungsabwicklung und unsere Marge &ndash; in Prozent und Cent. Dazu sind providerseitige Zusatzkosten (Startgeb&uuml;hren, Blockiergeb&uuml;hren, Minutenpreise) und die Zahlungsartenkosten einzeln sichtbar.'],
                ['Was kostet Transparent Laden?', 'Es gibt einen Tarif: 9,99&nbsp;&euro; pro Monat, monatlich k&uuml;ndbar. Dazu kommen die Ladekosten, die sich aus Betreiber-/Infrastrukturkosten, Roaming-/Betriebskosten, Zahlungsabwicklung und unserem festen Aufschlag (0,01&nbsp;&euro;/kWh) zusammensetzen. Alle Anteile werden vor Ladebeginn aufgeschl&uuml;sselt angezeigt.'],
                ['Was genau verdient ihr an einem Ladevorgang?', 'Genau 0,01&nbsp;&euro; pro kWh. Falls der Anbieter einen Minutenpreis erhebt, zus&auml;tzlich 0,01&nbsp;&euro; pro Minute. Falls der Anbieter Blockiergeb&uuml;hren hat, zus&auml;tzlich 0,01&nbsp;&euro; pro Minute Blockiergeb&uuml;hr. Das ist unser festes, verbindliches Preisversprechen. In der prozentualen Darstellung sehen Sie, dass unsere Marge typischerweise nur ca.&nbsp;2&nbsp;% des Endpreises ausmacht.'],
                ['Was bedeutet Roaming / Betrieb?', 'Roaming&nbsp;/ Betrieb umfasst die Kosten, die f&uuml;r die Anbindung an das &uuml;bergreifende Ladenetzwerk und dessen laufenden Betrieb anfallen. Dadurch k&ouml;nnen Sie mit einer einzigen App Ladestationen verschiedener Betreiber nutzen. Dieser Anteil wird in der prozentualen Aufteilung als eigener Bereich sichtbar gemacht.'],
                ['Brauche ich eine Ladekarte?', 'Nein. Die Authentifizierung erfolgt &uuml;ber die App oder &uuml;ber Plug &amp; Charge (wenn Ihr Fahrzeug das unterst&uuml;tzt). Eine physische Karte ist nicht erforderlich.'],
                ['Welche Ladepunkte kann ich nutzen?', 'Sie haben Zugang zu tausenden Ladepunkten in Deutschland und Europa &uuml;ber unsere Roaming-Partner. Die Verf&uuml;gbarkeit sehen Sie in Echtzeit in der App.'],
                ['Wie wird abgerechnet?', 'Nach dem Ladevorgang erhalten Sie eine Rechnung mit vollst&auml;ndiger Kostenaufschl&uuml;sselung &ndash; Betreiber-/Infrastrukturanteil, Roaming-/Betriebsanteil, Zahlungsabwicklung und unser Aufschlag einzeln ausgewiesen. Die Zahlung erfolgt &uuml;ber Ihre hinterlegte Zahlungsart.'],
                ['Beeinflusst meine Zahlungsart den Preis?', 'Ja. Zahlungsarten verursachen zwei Arten von Kosten: einen Fixbetrag pro Transaktion (z.&nbsp;B. PayPal 0,35&nbsp;&euro;) und einen prozentualen Anteil (z.&nbsp;B. 1,2&nbsp;%). Der Fixbetrag erscheint als separate Position, der prozentuale Anteil flie&szlig;t sichtbar in den kWh-Preis ein. In der prozentualen Aufteilung ist die Zahlungsabwicklung als eigener Bereich sichtbar. Beides wird vor Ladebeginn angezeigt.'],
                ['Was sind Startgeb&uuml;hren und Blockiergeb&uuml;hren?', 'Startgeb&uuml;hren sind einmalige Kosten pro Ladevorgang, die der Betreiber erhebt. Blockiergeb&uuml;hren fallen an, wenn das Fahrzeug nach Ladeschluss am Ladepunkt steht. Beide werden vom Betreiber festgelegt und als separate Positionen angezeigt. Auf Blockiergeb&uuml;hren erheben wir 0,01&nbsp;&euro;/Minute Aufschlag. Alles wird vor Ladebeginn angezeigt.'],
                ['Was ist Plug &amp; Charge?', 'Mit Plug &amp; Charge stecken Sie nur das Ladekabel ein &ndash; Ihr Fahrzeug authentifiziert sich automatisch. Keine App, kein QR-Code n&ouml;tig. Die volle Preistransparenz mit prozentualer Partner-Aufteilung und festem Aufschlag bleibt erhalten.'],
                ['Was passiert bei einer Ladest&ouml;rung?', 'Unsere App erkennt St&ouml;rungen automatisch und benachrichtigt Sie. Nicht geladene Energie wird nicht berechnet. Im Problemfall hilft unser Support.'],
                ['Wie sicher sind meine Daten?', 'Wir verwenden Ende-zu-Ende-Verschl&uuml;sselung, speichern Daten ausschlie&szlig;lich auf europ&auml;ischen Servern und halten alle DSGVO-Vorgaben ein.'],
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
