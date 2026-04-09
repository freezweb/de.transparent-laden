<?php
$title = 'Kontakt';
$description = 'Kontaktieren Sie Transparent Laden – wir helfen Ihnen bei Fragen zu Ihrem Konto, Ladevorgängen oder unserem Transparenz-Modell.';
ob_start();
?>
<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4 text-center">Kontakt</h1>
        <p class="text-lg text-gray-600 mb-12 text-center">Wir sind für Sie da. Schreiben Sie uns eine Nachricht.</p>
        <div class="bg-gray-50 rounded-xl p-8">
            <form action="/api/v1/contact" method="POST" class="space-y-6" x-data="{ sent: false }" @submit.prevent="
                const fd = new FormData($el);
                sent = true;
            ">
                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-Mail</label>
                        <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Betreff</label>
                    <select name="subject" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
                        <option>Allgemeine Frage</option>
                        <option>Technisches Problem</option>
                        <option>Rechnung / Zahlung</option>
                        <option>Business-Anfrage</option>
                        <option>Sonstiges</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nachricht</label>
                    <textarea name="message" rows="5" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition"></textarea>
                </div>
                <button type="submit" x-show="!sent" class="w-full bg-primary-700 text-white py-3 rounded-lg font-semibold hover:bg-primary-800 transition">Nachricht senden</button>
                <div x-show="sent" x-cloak class="bg-primary-50 border border-primary-200 text-primary-800 rounded-lg p-4 text-center font-medium">
                    Vielen Dank! Wir melden uns in Kürze bei Ihnen.
                </div>
            </form>
        </div>
        <div class="mt-12 grid sm:grid-cols-2 gap-6">
            <div class="bg-gray-50 rounded-xl p-6 text-center">
                <svg class="w-8 h-8 text-primary-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <h3 class="font-semibold mb-1">E-Mail</h3>
                <p class="text-gray-600">support@transparent-laden.de</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-6 text-center">
                <svg class="w-8 h-8 text-primary-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="font-semibold mb-1">Erreichbarkeit</h3>
                <p class="text-gray-600">Mo-Fr, 9:00 - 17:00 Uhr</p>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
echo view('layouts/public', ['title' => $title, 'description' => $description, 'content' => $content]);
?>
