<?php
$title = 'Impressum';
ob_start();
?>
<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 prose prose-lg">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Impressum</h1>
        <div class="space-y-6 text-gray-700">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Angaben gemäß § 5 TMG</h2>
                <p>Transparent Laden GmbH<br>Musterstraße 1<br>80331 München<br>Deutschland</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Kontakt</h2>
                <p>E-Mail: info@transparent-laden.de<br>Telefon: +49 89 123 456 0</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Registereintrag</h2>
                <p>Registergericht: Amtsgericht München<br>Registernummer: HRB XXXXXX</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Umsatzsteuer-ID</h2>
                <p>Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz:<br>DE XXXXXXXXX</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Geschäftsführung</h2>
                <p>[Name des Geschäftsführers]</p>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Streitschlichtung</h2>
                <p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: <a href="https://ec.europa.eu/consumers/odr/" class="text-primary-700 hover:underline" target="_blank">https://ec.europa.eu/consumers/odr/</a>. Wir sind nicht verpflichtet und nicht bereit, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'content' => $content]);
?>
