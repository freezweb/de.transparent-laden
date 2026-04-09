<?php
$title = 'FAQ';
ob_start();
?>
<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4 text-center">Häufig gestellte Fragen</h1>
        <p class="text-lg text-gray-600 mb-12 text-center">Haben Sie Fragen? Hier finden Sie Antworten.</p>
        <div class="space-y-4" x-data="{ open: null }">
            <?php
            $faqs = [
                ['Wie funktioniert die Preistransparenz?', 'Bei jedem Ladevorgang sehen Sie vorab und während der Ladung die vollständige Preisaufschlüsselung: Energiekosten, Netzentgelte, Steuern, CPO-Entgelt und unsere Marge. Nichts ist versteckt.'],
                ['Brauche ich eine Ladekarte?', 'Nein. Die Authentifizierung erfolgt über die App oder - mit Pro-/Business-Abo - automatisch über Plug & Charge. Eine physische Karte ist nicht erforderlich.'],
                ['Welche Ladepunkte kann ich nutzen?', 'Sie haben Zugang zu tausenden Ladepunkten in Deutschland und Europa über unsere Roaming-Partner. Die Verfügbarkeit sehen Sie in der App-Karte.'],
                ['Wie wird abgerechnet?', 'Direkt nach dem Ladevorgang erhalten Sie eine detaillierte Rechnung mit allen Kosten. Die Zahlung erfolgt über Ihre hinterlegte Zahlungsart.'],
                ['Was ist Plug & Charge?', 'Mit Plug & Charge stecken Sie nur das Ladekabel ein - Ihr Fahrzeug authentifiziert sich automatisch. Keine App, kein QR-Code nötig. Verfügbar im Pro- und Business-Abo.'],
                ['Kann ich mehrere Fahrzeuge registrieren?', 'Ja. Im Business-Abo können Sie beliebig viele Fahrzeuge verwalten und erhalten eine Sammelrechnung.'],
                ['Was passiert bei einer Ladestörung?', 'Unsere App erkennt Störungen automatisch und benachrichtigt Sie. Nicht geladene Energie wird nicht berechnet. Im Problemfall hilft unser Support.'],
                ['Gibt es eine Mindestlaufzeit?', 'Nein. Sie können das Abo jederzeit monatlich kündigen. Das Basis-Konto ist dauerhaft kostenlos.'],
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
echo view('layouts/public', ['title' => $title, 'content' => $content]);
?>
