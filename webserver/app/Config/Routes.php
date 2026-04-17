<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('preise', 'Home::pricing');
$routes->get('transparenz', 'Home::transparency');
$routes->get('faq', 'Home::faq');
$routes->get('kontakt', 'Home::contact');
$routes->get('impressum', 'Home::imprint');
$routes->get('datenschutz', 'Home::privacy');

// Kundenportal SPA
$routes->get('portal', 'PortalController::index');
$routes->get('portal/(:any)', 'PortalController::index');

// Admin/Support SPA
$routes->get('admin', 'AdminWebController::index');
$routes->get('admin/(:any)', 'AdminWebController::index');

// ──────────────────────────────────────────────
// API v1 Routes
// ──────────────────────────────────────────────
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], static function ($routes) {

    // ── Health (public) ──
    $routes->get('health', 'HealthController::index');

    // ── Auth (public) ──
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/refresh', 'AuthController::refresh');

    // ── Auth (authenticated) ──
    $routes->group('auth', ['filter' => 'jwt'], static function ($routes) {
        $routes->post('logout', 'AuthController::logout');
    });

    // ── User ──
    $routes->group('user', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('profile', 'UserController::profile');
        $routes->put('profile', 'UserController::update');
        $routes->put('password', 'UserController::changePassword');
        $routes->delete('account', 'UserController::delete');
    });

    // ── Subscriptions ──
    $routes->group('subscriptions', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('plans', 'SubscriptionController::plans');
        $routes->get('current', 'SubscriptionController::current');
        $routes->post('subscribe', 'SubscriptionController::subscribe');
        $routes->post('cancel', 'SubscriptionController::cancel');
    });

    // ── Charge Points ──
    $routes->group('charge-points', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('nearby', 'ChargePointController::nearby');
        $routes->get('status', 'ChargePointController::status');
        $routes->get('(:num)', 'ChargePointController::show/$1');
        $routes->get('(:num)/pricing', 'ChargePointController::pricing/$1');
        $routes->get('(:num)/reviews', 'ReviewController::index/$1');
        $routes->post('(:num)/reviews', 'ReviewController::store/$1');
    });

    // ── Reviews ──
    $routes->group('reviews', ['filter' => 'jwt'], static function ($routes) {
        $routes->post('(:num)/images', 'ReviewController::uploadImage/$1');
    });

    // ── QR Scans ──
    $routes->post('qr-scans', 'QrScanController::store', ['filter' => 'jwt']);

    // ── Content Reports ──
    $routes->post('reports', 'ReportController::store', ['filter' => 'jwt']);

    // ── Charging Sessions ──
    $routes->group('charging', ['filter' => 'jwt'], static function ($routes) {
        $routes->post('start', 'ChargingController::start');
        $routes->post('(:num)/stop', 'ChargingController::stop/$1');
        $routes->get('(:num)/status', 'ChargingController::status/$1');
        $routes->get('(:num)/live', 'ChargingController::liveStatus/$1');
        $routes->get('active', 'ChargingController::active');
        $routes->get('history', 'ChargingController::history');
    });

    // ── Invoices ──
    $routes->group('invoices', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('/', 'InvoiceController::index');
        $routes->get('(:num)', 'InvoiceController::show/$1');
        $routes->get('(:num)/pdf', 'InvoiceController::downloadPdf/$1');
    });

    // ── Contract & Withdrawal ──
    $routes->group('contract', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('status', 'ContractController::status');
        $routes->get('terms', 'ContractController::terms');
        $routes->post('accept', 'ContractController::accept');
        $routes->post('waive-withdrawal', 'ContractController::waiveWithdrawal');
    });

    // ── Payment Methods ──
    $routes->group('payment-methods', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('/', 'PaymentMethodController::index');
        $routes->get('config', 'PaymentMethodController::config');
        $routes->post('setup-intent', 'PaymentMethodController::createSetupIntent');
        $routes->post('confirm-stripe', 'PaymentMethodController::confirmStripe');
        $routes->post('paypal/setup', 'PaymentMethodController::paypalSetup');
        $routes->post('paypal/confirm', 'PaymentMethodController::paypalConfirm');
        $routes->get('(:num)', 'PaymentMethodController::show/$1');
        $routes->put('(:num)/default', 'PaymentMethodController::setDefault/$1');
        $routes->delete('(:num)', 'PaymentMethodController::delete/$1');
    });

    // ── Devices ──
    $routes->group('devices', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('/', 'DeviceController::index');
        $routes->post('/', 'DeviceController::register');
        $routes->delete('(:num)', 'DeviceController::delete/$1');
    });

    // ── Notification Preferences ──
    $routes->group('notifications', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('preferences', 'NotificationPreferenceController::index');
        $routes->put('preferences', 'NotificationPreferenceController::update');
    });
});

// ──────────────────────────────────────────────
// Admin API Routes
// ──────────────────────────────────────────────
$routes->group('api/v1/admin', ['namespace' => 'App\Controllers\Api\Admin'], static function ($routes) {

    // ── Admin Auth (public) ──
    $routes->post('auth/login', 'AdminAuthController::login');
    $routes->post('auth/setup-totp', 'AdminAuthController::setupTotp');
    $routes->post('auth/confirm-totp', 'AdminAuthController::confirmTotp');

    // ── Admin Auth (admin only) ──
    $routes->group('', ['filter' => 'admin'], static function ($routes) {
        $routes->post('auth/invite', 'AdminAuthController::invite');

        // ── Dashboard ──
        $routes->get('dashboard/stats', 'AdminDashboardController::stats');
        $routes->get('dashboard/users', 'AdminDashboardController::users');
        $routes->get('dashboard/users/(:num)', 'AdminDashboardController::userDetail/$1');
        $routes->put('dashboard/users/(:num)/block', 'AdminDashboardController::blockUser/$1');
        $routes->put('dashboard/users/(:num)/unblock', 'AdminDashboardController::unblockUser/$1');
        $routes->get('dashboard/sessions', 'AdminDashboardController::sessions');
        $routes->get('dashboard/invoices', 'AdminDashboardController::invoices');

        // ── Providers ──
        $routes->get('providers', 'AdminProviderController::index');
        $routes->get('providers/(:num)', 'AdminProviderController::show/$1');
        $routes->post('providers', 'AdminProviderController::store');
        $routes->put('providers/(:num)', 'AdminProviderController::update/$1');
        $routes->post('providers/(:num)/sync', 'AdminProviderController::sync/$1');

        // ── Config ──
        $routes->get('config', 'AdminConfigController::index');
        $routes->get('config/(:segment)', 'AdminConfigController::show/$1');
        $routes->put('config', 'AdminConfigController::update');

        // ── Reviews Moderation ──
        $routes->get('reviews', 'AdminReviewController::index');
        $routes->put('reviews/(:num)/moderate', 'AdminReviewController::moderate/$1');
        $routes->delete('reviews/(:num)', 'AdminReviewController::destroy/$1');

        // ── QR Scan Logs ──
        $routes->get('qr-scans', 'AdminQrScanController::index');

        // ── Content Reports ──
        $routes->get('reports', 'AdminReportController::index');
        $routes->put('reports/(:num)/moderate', 'AdminReportController::moderate/$1');
    });
});
