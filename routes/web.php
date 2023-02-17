<?php

use Illuminate\Support\Facades\Route;
use Areaseb\Core\Http\Controllers\CalendarController;
use Areaseb\Core\Http\Controllers\CompanyController;
use Areaseb\Core\Http\Controllers\ContactController;
use Areaseb\Core\Http\Controllers\CostController;
use Areaseb\Core\Http\Controllers\CostPaymentController;
use Areaseb\Core\Http\Controllers\EditorController;
use Areaseb\Core\Http\Controllers\EventController;
use Areaseb\Core\Http\Controllers\ExemptionController;
use Areaseb\Core\Http\Controllers\ExpenseController;
use Areaseb\Core\Http\Controllers\ExportController;
use Areaseb\Core\Http\Controllers\GeneralController;
use Areaseb\Core\Http\Controllers\ImportController;
use Areaseb\Core\Http\Controllers\InvoiceController;
use Areaseb\Core\Http\Controllers\InvoicePaymentController;
use Areaseb\Core\Http\Controllers\MediaController;
use Areaseb\Core\Http\Controllers\NewsletterController;
use Areaseb\Core\Http\Controllers\NewsletterListController;
use Areaseb\Core\Http\Controllers\NotificationController;
use Areaseb\Core\Http\Controllers\NoteController;
use Areaseb\Core\Http\Controllers\PagesController;
use Areaseb\Core\Http\Controllers\PdfController;
use Areaseb\Core\Http\Controllers\ProductController;
use Areaseb\Core\Http\Controllers\ReportController;
use Areaseb\Core\Http\Controllers\RoleController;
use Areaseb\Core\Http\Controllers\StatController;
use Areaseb\Core\Http\Controllers\SettingController;
use Areaseb\Core\Http\Controllers\UserController;
use Areaseb\Core\Http\Controllers\TemplateController;


Route::get('calendars', [CalendarController::class, 'index'])->name('calendars.index');
Route::post('calendars', [CalendarController::class, 'store'])->name('calendars.store');
Route::get('calendars/bind', [CalendarController::class, 'bind'])->name('calendars.bind');
Route::get('calendars/overlayed', [CalendarController::class, 'overlayed'])->name('calendars.overlayed');
Route::get('calendars/create', [CalendarController::class, 'create'])->name('calendars.create');
Route::get('calendars/{calendar}', [CalendarController::class, 'show'])->name('calendars.show');
Route::patch('calendars/{calendar}', [CalendarController::class, 'update'])->name('calendars.update');
Route::delete('calendars/{calendar}', [CalendarController::class, 'destroy'])->name('calendars.destroy');
Route::get('calendars/{calendar}/edit', [CalendarController::class, 'edit'])->name('calendars.edit');
Route::get('api/calendars/{calendar_id}/events', [EventController::class, 'calendarEvent'])->name('api.calendar.events');

Route::resource('companies', CompanyController::class);
Route::post('api/companies/create-contacts', [CompanyController::class, 'createContactsFromCompanies'])->name('api.company.createContacts');
Route::get('api/companies/{company}', [CompanyController::class, 'checkNation'])->name('api.company.checkNation');
Route::get('api/companies/{company}/discount-exemption', [CompanyController::class, 'discountExemption'])->name('api.company.checkNation');
Route::get('api/companies/{company}/payment', [CompanyController::class, 'payment'])->name('api.company.payment');
Route::get('api/companies/{company}/notes', [CompanyController::class, 'getNote'])->name('api.company.notes');
Route::post('api/companies/{company}/notes/add', [CompanyController::class, 'addNote'])->name('api.company.addNotes');
Route::post('api/companies/sede/add', [CompanyController::class, 'addSede'])->name('api.company.addSede');
Route::post('api/companies/sede/update', [CompanyController::class, 'updateSede'])->name('api.company.updateSede');
Route::delete('companies-sede/{id}', [CompanyController::class, 'deleteSede'])->name('companies.sede.delete');

Route::post('api/companies/{company}/check-vies', [CompanyController::class, 'checkVies'])->name('api.company.vies');
Route::get('api/companies/{company}/contact', [CompanyController::class, 'firstContact'])->name('api.company.contact');
Route::get('api/ta/companies', [CompanyController::class, 'taindex'])->name('api.ta.companies');

Route::post('contacts/make-company', [ContactController::class, 'makeCompany'])->name('contacts.makeCompany');
Route::post('contacts/make-user', [ContactController::class, 'makeUser'])->name('contacts.makeUser');
Route::post('contacts-validate-file', [ContactController::class, 'validateFile'])->name('csv.contacts.validate');
Route::resource('contacts', ContactController::class);
Route::get('api/ta/contacts', [ContactController::class, 'taindex'])->name('api.ta.contacts');

Route::patch('costs/{cost}/update-saldo', [CostController::class, 'updateSaldoForm'])->name('costs.updateSaldo');
Route::resource('costs', CostController::class);
Route::get('costs/{cost}/media', [CostController::class, 'media'])->name('costs.media');

Route::get('cost-payments/{cost}', [CostPaymentController::class, 'show'])->name('costs.payments.show');
Route::post('cost-payments/{cost}', [CostPaymentController::class, 'store'])->name('costs.payments.store');
Route::delete('cost-payments/{payment}', [CostPaymentController::class, 'destroy'])->name('costs.payments.delete');

Route::get('api/costs/import', [CostController::class, 'import'])->name('api.costs.importForm');
Route::post('api/costs/import', [CostController::class, 'importProcess'])->name('api.costs.import');

Route::post('api/costs/saldato', [CostController::class, 'toggleSaldato'])->name('api.costs.toggleSaldato');
Route::get('api/ta/costs', [CostController::class, 'taindex'])->name('api.ta.costs');

Route::get('template-builder', [EditorController::class, 'editor']);
Route::get('template-builder/{id}', [EditorController::class, 'editorWithTemplate']);
Route::get('create-template-builder', [EditorController::class, 'createTemplateBuilder'] );
Route::get('edit-template-builder/{id}', [EditorController::class, 'editTemplateBuilder'] );
Route::get('editor/elements/{slug}', [EditorController::class, 'show']);
Route::get('editor/images', [EditorController::class, 'images'])->name('editor.images');
Route::post('editor/upload', [EditorController::class, 'upload'])->name('editor.upload');
Route::post('editor/delete', [EditorController::class, 'delete'])->name('editor.delete');

Route::post('expiring-events', [EventController::class, 'expiring']);
Route::resource('events', EventController::class)->except('index');
Route::delete('event/delete-for-me/{event}', [EventController::class, 'deleteEventForMe'])->name('event.delete');


Route::post('set-cookie', [CalendarController::class, 'setCookie']);

Route::get('addevent/{token}', [EventController::class, 'createFromToken']);
Route::post('addevent/{token}', [EventController::class, 'storeFromToken']);

Route::get('api/events', [EventController::class, 'defaultUserEvent'])->name('api.user.defaultEvents');
Route::get('api-events/{event}', [EventController::class, 'apiEvent'])->name('api.events.show');
Route::get('api/events/{user_id}', [EventController::class, 'userEvent'])->name('api.user.events');
Route::post('api/events/{event}/done', [EventController::class, 'markAsDone'])->name('api.events.done');

Route::get('expenses/modify', [ExpenseController::class, 'modifyCategories'])->name('expenses.categories.edit');
Route::resource('expenses', ExpenseController::class);


Route::get('exports/{model}', [ExportController::class, 'export'])->name('csv.export');
Route::post('imports/peek', [ImportController::class, 'peek'])->name('csv.import.peek');
Route::get('imports/{model}', [ImportController::class, 'importForm'])->name('csv.import.form');
Route::post('imports/{model}', [ImportController::class, 'importUpload'])->name('csv.import.upload');

Route::get('payments/{invoice}', [InvoicePaymentController::class, 'show'])->name('invoices.payments.show');
Route::post('payments/{invoice}', [InvoicePaymentController::class, 'store'])->name('invoices.payments.store');
Route::delete('payments/{payment}', [InvoicePaymentController::class, 'destroy'])->name('invoices.payments.delete');

Route::post('notices/{invoice}', [InvoicePaymentController::class, 'storeNotice'])->name('invoices.notices.store');
Route::delete('notices/{notice}', [InvoicePaymentController::class, 'destroyNotice'])->name('invoices.notices.delete');

Route::get('insoluti', [InvoiceController::class, 'insoluti'])->name('invoices.insoluti');

Route::get('invoices/{invoice}/export', [InvoiceController::class, 'export'])->name('invoices.export');
Route::post('invoices/{invoice}/export', [InvoiceController::class, 'exportPost'])->name('invoices.export.post');

Route::get('invoices/{invoice}/edit-saldo', [InvoiceController::class, 'editSaldoForm'])->name('invoices.editSaldo');
Route::post('invoices/{invoice}/notice', [InvoiceController::class, 'sendNotice'])->name('invoices.sendNotice');
Route::patch('invoices/{invoice}/update-saldo', [InvoiceController::class, 'updateSaldoForm'])->name('invoices.updateSaldo');
Route::post('invoices/{invoice}/mark-as-unpaid', [InvoiceController::class, 'markAsUnpaid'])->name('invoices.mark-as-unpaid');
Route::delete('invoices-item/{item}', [InvoiceController::class, 'deleteItem'])->name('invoices.item.delete');
Route::resource('invoices', InvoiceController::class);
Route::delete('invoices-item/{item}', [InvoiceController::class, 'deleteItem'])->name('invoices.item.delete');
Route::get('api/invoices/export', [InvoiceController::class, 'exportXmlInZip'])->name('api.invoices.export');
Route::get('api/invoices/import', [InvoiceController::class, 'import'])->name('api.invoices.importForm');
Route::post('api/invoices/import', [InvoiceController::class, 'importProcess'])->name('api.invoices.import');
Route::post('api/invoices/saldato', [InvoiceController::class, 'toggleSaldato'])->name('api.invoices.toggleSaldato');

Route::get('api/invoices/{invoice}/check', [InvoiceController::class, 'checkBeforeFe'])->name('api.invoices.checkBeforeFe');
Route::post('api/invoices/{invoice}/send-fe', [InvoiceController::class, 'sendFe'])->name('api.invoices.sendFe');
Route::post('api/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('api.invoices.duplicate');
Route::get('api/invoices/{company}/list', [InvoiceController::class, 'getOfCompany'])->name('api.invoices.ofCompany');
Route::get('api/invoices/{type}', [InvoiceController::class, 'getNumberFromType'])->name('api.invoices.getNumber');
Route::get('api/exemptions/{exemption}', [ExemptionController::class, 'getIva'])->name('api.exemption.getIva');

Route::resource('newsletters', NewsletterController::class);
Route::post('newsletters-duplicate/{newsletter}', [NewsletterController::class, 'duplicate'])->name('newsletters.duplicate');
Route::get('newsletters/{newsletter}/send-test', [NewsletterController::class, 'test'])->name('newsletters.formTest');
Route::post('newsletters/{newsletter}/send-test', [NewsletterController::class, 'sendTest'])->name('newsletters.sendTest');
Route::get('newsletters/{newsletter}/send', [NewsletterController::class, 'send'])->name('newsletters.form');
Route::post('newsletters/{newsletter}/send', [NewsletterController::class, 'sendOfficial'])->name('newsletters.send');

Route::get('newsletters/{newsletter}/reports', [ReportController::class, 'index'])->name('reports.newsletter.index');
Route::get('newsletters/{newsletter}/reports/aperte', [ReportController::class, 'showOpen'])->name('reports.newsletter.opened');
Route::get('newsletters/{newsletter}/reports/errore', [ReportController::class, 'showErrore'])->name('reports.newsletter.failed');
Route::get('newsletters/{newsletter}/reports/unsubscribed', [ReportController::class, 'showUnsubscribed'])->name('reports.newsletter.unsubscribed');
Route::get('newsletters/{newsletter}/reports/clicked', [ReportController::class, 'showClicked'])->name('reports.newsletter.clicked');
Route::get('newsletters/{newsletter}/reports/{report}', [ReportController::class, 'show'])->name('reports.newsletter.show');

Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
Route::post('notifications/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

Route::delete('notes-destroy/{note}', [NoteController::class, 'destroyAjax'])->name('notes.destroyAjax');
Route::resource('notes', NoteController::class);

Route::get('/', [PagesController::class, 'home'])->name('home');
Route::post('logout', [PagesController::class, 'logout'])->name('logout');

Route::get('lists', [NewsletterListController::class, 'index'])->name('lists.index');
Route::post('lists', [NewsletterListController::class, 'store'])->name('lists.store');
Route::get('lists/create', [NewsletterListController::class, 'create'])->name('lists.create');
Route::get('lists/{list}', [NewsletterListController::class, 'show'])->name('lists.show');
Route::delete('lists/{list}', [NewsletterListController::class, 'destroy'])->name('lists.destroy');
Route::post('add-contact-to-list', [NewsletterListController::class, 'addContact'])->name('lists.addContact');
Route::post('duplicate-lists', [NewsletterListController::class, 'duplicate'])->name('lists.duplicate');
Route::delete('lists/{list}/contact/{contact}', [NewsletterListController::class, 'removeContactFromList'])->name('lists.removeContact');
Route::post('lists/{list}/update', [NewsletterListController::class, 'updateContacts'])->name('lists.updateContacts');
Route::get('create-list', [NewsletterListController::class, 'CreateList'])->name('lists.createForm');
Route::post('create-list', [NewsletterListController::class, 'CreateListPost'])->name('lists.createStore');

Route::post('pdf/send/{id}', [PdfController::class, 'sendInvoiceCortesia'])->name('pdf.send');
Route::get('pdf/{model}/{id}', [PdfController::class, 'generate'])->name('pdf.create');

Route::get('products/{product}/media', [ProductController::class, 'media'])->name('products.media');
Route::resource('products', ProductController::class);
Route::get('api/products/{product}', [ProductController::class, 'apiShow'])->name('api.products.show');
Route::get('api/products/{product}/{locale}', [ProductController::class, 'apiShowLocale'])->name('api.products.show.locale');
Route::get('api/products/{product}/children/{company_id}', [ProductController::class, 'apiShowChildren'])->name('api.products.showChildren');

Route::resource('roles', RoleController::class);

Route::resource('settings', SettingController::class)->only(['index', 'edit', 'update']);

Route::get('stats/aziende', [StatController::class, 'companies'])->name('stats.companies');
Route::get('stats/categorie', [StatController::class, 'categories'])->name('stats.categories');
Route::get('stats/categorie/{id}', [StatController::class, 'category'])->name('stats.category');
Route::get('stats/balance', [StatController::class, 'balance'])->name('stats.balance');
Route::get('stats/export', [StatController::class, 'export'])->name('stats.export');
Route::get('stats/expenses/{category}', [StatController::class, 'expense'])->name('stats.expense');

Route::get('users', [UserController::class, 'index'])->name('users.index');
Route::post('users', [UserController::class, 'store'])->name('users.store');
Route::get('users/create', [UserController::class, 'create'])->name('users.create');
Route::get('users/{id}/permissions', [UserController::class, 'permissions'])->name('user.permissions');
Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::post('api/direct-permissions/{user_id}', [UserController::class, 'permissionUpdate'])->name('api.permissions.update');

Route::get('change-password/{user}', [UserController::class, 'editPassword'])->name('users.edit.password');
Route::post('change-password/{user}', [UserController::class, 'updatePassword'])->name('users.update.password');

Route::get('templates/iframe', [TemplateController::class, 'iframe'])->name('templates.iframe');
Route::get('templates/html/{template}', [TemplateController::class, 'html'])->name('templates.html');
Route::post('templates/{template}', [TemplateController::class, 'update'])->name('templates.update');
Route::post('templates/{template}/duplicate', [TemplateController::class, 'duplicate'])->name('templates.duplicate');
Route::resource('templates', TemplateController::class)->except(['create', 'edit', 'update']);

Route::post('api/countries', [GeneralController::class, 'prefix'])->name('api.countries.prefix');
Route::post('api/city', [GeneralController::class, 'zip'])->name('api.city.zip');
Route::post('api/cities/{province}/province', [GeneralController::class, 'citiesOfProvince'])->name('api.city.province');
Route::post('api/clear-cache', [GeneralController::class, 'clearCache'])->name('api.cache.clear');
Route::post('update-field', [GeneralController::class, 'updateField'])->name('global.updateField');

Route::get('faqs', [PagesController::class, 'faqs'])->name('faqs.index');
Route::get('faqs/{faq}', [PagesController::class, 'faq'])->name('faqs.show');

Route::group(['prefix' => 'api/media'], function () {
    Route::post('upload', [MediaController::class, 'add'])->name('media.add');
    Route::post('update', [MediaController::class, 'update'])->name('media.update');
    Route::post('order', [MediaController::class, 'sort'])->name('media.sort');
    Route::post('type', [MediaController::class, 'type'])->name('media.type');
    Route::delete('delete',[MediaController::class, 'delete'])->name('media.delete');
});
