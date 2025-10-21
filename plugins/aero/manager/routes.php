<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Aero\Manager\Classes\ApiDispatcher;

// Manager API (secundaria) - Movida a /api/manager
// El endpoint principal /api ahora es manejado por aero/clouds
Route::any('/api/manager', function (Request $request) {
    $dispatcher = new ApiDispatcher();
    return $dispatcher->handle($request);
});

// Rutas para integración WHMCS (October CMS → WHMCS)
Route::group(['prefix' => 'api-whmcs', 'middleware' => 'aero.manager.api_token'], function() {
    
    // Test y dashboard
    Route::get('test', 'WhmcsController@testConnection');
    Route::get('dashboard', 'WhmcsController@dashboard');
    
    // Gestión de clientes
    Route::post('clients', 'WhmcsController@createClient');
    Route::get('clients/search', 'WhmcsController@searchClientByEmail');
    Route::get('clients/{id}', 'WhmcsController@getClient');
    Route::put('clients/{id}', 'WhmcsController@updateClient');
    Route::get('clients/{id}/invoices', 'WhmcsController@getClientInvoices');
    
    // Gestión de facturas
    Route::post('invoices', 'WhmcsController@createInvoice');
    Route::post('invoices/create-from-pricing', 'WhmcsController@createInvoiceFromPricing');
    Route::get('invoices/{id}/details', 'WhmcsController@getInvoiceDetails');
    Route::post('invoices/{id}/mark-paid', 'WhmcsController@markInvoicePaid');
    
    // Gestión de carrito
    Route::post('cart/process', 'WhmcsController@processCart');
    
    // Gestión de tickets
    Route::post('tickets', 'WhmcsController@createTicket');
    Route::post('tickets/{id}/reply', 'WhmcsController@replyTicket');
    
    // Sincronización de precios
    Route::post('sync-pricing', 'WhmcsController@syncPricing');
    Route::get('products', 'WhmcsController@getProducts');
});

// Rutas para recepción desde WHMCS (WHMCS → October CMS)
Route::group(['prefix' => 'whmcs-api'], function() {
    Route::post('receive', 'WhmcsApi@receive');
    Route::get('test-connection', 'WhmcsApi@test');
    Route::post('whmcs-webhook', 'WhmcsApi@webhook');
    Route::get('status', 'WhmcsApi@status');
});