<?php
$title = 'Datenschutzerklärung';
ob_start();
?>
<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 prose prose-lg">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Datenschutzerklärung</h1>
        <div class="space-y-8 text-gray-700">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">1. Datenschutz auf einen Blick</h2>
                <h3 class="text-lg font-medium mt-4">Allgemeine Hinweise</h3>
                <p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie persönlich identifiziert werden können.</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">2. Verantwortliche Stelle</h2>
                <p>Transparent Laden GmbH<br>Musterstraße 1<br>80331 München<br>E-Mail: datenschutz@transparent-laden.de</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">3. Datenerfassung auf dieser Website</h2>
                <h3 class="text-lg font-medium mt-4">Server-Log-Dateien</h3>
                <p>Der Provider der Seiten erhebt und speichert automatisch Informationen in sogenannten Server-Log-Dateien, die Ihr Browser automatisch an uns übermittelt. Dies sind: Browsertyp und Browserversion, verwendetes Betriebssystem, Referrer URL, Hostname des zugreifenden Rechners, Uhrzeit der Serveranfrage, IP-Adresse.</p>
                <h3 class="text-lg font-medium mt-4">Kontaktformular</h3>
                <p>Wenn Sie uns per Kontaktformular Anfragen zukommen lassen, werden Ihre Angaben aus dem Anfrageformular inklusive der dort angegebenen Kontaktdaten zwecks Bearbeitung der Anfrage und für den Fall von Anschlussfragen bei uns gespeichert. Diese Daten geben wir nicht ohne Ihre Einwilligung weiter.</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">4. Nutzerkonto und App-Nutzung</h2>
                <p>Für die Nutzung unserer Dienste ist ein Nutzerkonto erforderlich. Dabei werden folgende Daten erhoben:</p>
                <ul class="list-disc ml-6 space-y-1">
                    <li>Name, E-Mail-Adresse</li>
                    <li>Fahrzeugdaten (Marke, Modell, Kennzeichen, Plug&Charge-IDs)</li>
                    <li>Ladehistorie und Sitzungsdaten</li>
                    <li>Rechnungsdaten und Zahlungsinformationen</li>
                    <li>Standortdaten (bei Nutzung der Ladestellensuche)</li>
                    <li>Geräte-Push-Tokens für Benachrichtigungen</li>
                </ul>
                <p class="mt-2">Diese Daten werden ausschließlich zur Erbringung unserer Ladedienste, zur Abrechnung und zur Verbesserung unseres Services verarbeitet.</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">5. Zahlungsabwicklung</h2>
                <p>Zahlungsdaten werden über sichere Zahlungsdienstleister verarbeitet. Wir speichern keine vollständigen Kreditkartennummern oder Bankverbindungen auf unseren Servern. Die Zahlungsabwicklung unterliegt den Datenschutzbestimmungen des jeweiligen Zahlungsdienstleisters.</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">6. Ihre Rechte</h2>
                <p>Sie haben jederzeit das Recht:</p>
                <ul class="list-disc ml-6 space-y-1">
                    <li>Auskunft über Ihre gespeicherten personenbezogenen Daten zu erhalten (Art. 15 DSGVO)</li>
                    <li>Berichtigung unrichtiger Daten zu verlangen (Art. 16 DSGVO)</li>
                    <li>Löschung Ihrer Daten zu verlangen (Art. 17 DSGVO)</li>
                    <li>Einschränkung der Verarbeitung zu verlangen (Art. 18 DSGVO)</li>
                    <li>Datenübertragbarkeit zu verlangen (Art. 20 DSGVO)</li>
                    <li>Widerspruch gegen die Verarbeitung einzulegen (Art. 21 DSGVO)</li>
                </ul>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">7. Cookies</h2>
                <p>Diese Website verwendet technisch notwendige Cookies, die für den Betrieb der Seite erforderlich sind. An analytics oder Tracking-Cookies werden nicht eingesetzt. Ein Session-Cookie wird für die Anmeldung im Kundenportal und Admin-Bereich verwendet.</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">8. SSL/TLS-Verschlüsselung</h2>
                <p>Diese Seite nutzt aus Sicherheitsgründen und zum Schutz der Übertragung vertraulicher Inhalte eine SSL- bzw. TLS-Verschlüsselung. Eine verschlüsselte Verbindung erkennen Sie daran, dass die Adresszeile des Browsers von „http://" auf „https://" wechselt.</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">9. Änderungen dieser Datenschutzerklärung</h2>
                <p>Wir behalten uns vor, diese Datenschutzerklärung anzupassen, damit sie stets den aktuellen rechtlichen Anforderungen entspricht oder um Änderungen unserer Leistungen umzusetzen. Für Ihren erneuten Besuch gilt dann die neue Datenschutzerklärung.</p>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'content' => $content]);
?>
