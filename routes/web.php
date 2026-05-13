<?php
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminCommentController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAdministratorController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AdminCourierController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\Courier\CourierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Tīmekļa maršruti
|--------------------------------------------------------------------------
*/

// ========== PUBLISKIE MARŠRUTI ==========

// Sākumlapa
Route::get('/', [HomeController::class, 'index'])->name('home');

// "Par mums" lapa
Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

// Kontaktlapa - publiski pieejams
Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Informatīvā biļetena abonēšana (publiska - var būt viesis)
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Veikala lapas
Route::prefix('shop')->group(function () {
    // Veikala sākumlapa
    Route::get('/', [ProductController::class, 'shopIndex'])->name('shop.index');

    // Produkta informācija
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('shop.product.show');

    // Kategorijas produkti
    Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('shop.category.show');

    // Veikala kontaktlapa
    Route::get('/contact', function () {
        return Inertia::render('Shop/Contact');
    })->name('shop.contact');

    // Veikala saziņas veidlapas iesniegšana
    Route::post('/contact', [ContactController::class, 'store'])->name('shop.contact.store');

    // FAQ, Shipping, Returns — publiski
    Route::get('/faq',      fn() => Inertia::render('Shop/FAQ'))->name('shop.faq');
    Route::get('/shipping', fn() => Inertia::render('Shop/Shipping'))->name('shop.shipping');
    Route::get('/returns',  fn() => Inertia::render('Shop/Returns'))->name('shop.returns');
});

// Satura lapas
Route::prefix('content')->group(function () {
    // Satura sākumlapa
    Route::get('/', [ContentController::class, 'index'])->name('content.index');

    // Komentāri ar noskaņojumiem - web maršruts, lai sesijas autentifikācija strādā (my_mood_score)
    Route::get('/{contentId}/comments', [CommentController::class, 'byContent'])->name('comments.byContent');

    // Satura detaļas
    Route::get('/{slug}', [ContentController::class, 'show'])->name('content.show');

    // Saturs pēc veida
    Route::get('/type/{type}', [ContentController::class, 'byType'])->name('content.type');
});

// ========== AUTENTIFICĒTI MARŠRUTI ==========

Route::middleware('auth')->group(function () {

    // Informācijas panelis
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Informatīvā biļetena preferences (lietotājiem, kas ir pieteikušies)
    Route::prefix('newsletter')->name('newsletter.')->group(function () {
        Route::get('/status', [NewsletterController::class, 'status'])->name('status');
        Route::put('/preferences', [NewsletterController::class, 'updatePreferences'])->name('preferences');
        Route::get('/offers', [NewsletterController::class, 'getOffers'])->name('offers');
    });

    // Profila pārvaldība
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Paroles maiņa
        Route::get('/password', [ProfileController::class, 'passwordEdit'])->name('profile.password.edit');
        Route::put('/password', [ProfileController::class, 'passwordUpdate'])->name('profile.password.update');

        // Profila bildes augšupielāde
        Route::post('/avatar', [ProfileController::class, 'avatarUpdate'])->name('profile.avatar.update');
        Route::delete('/avatar', [ProfileController::class, 'avatarDelete'])->name('profile.avatar.delete');

        // Adreses
        Route::get('/addresses', [ProfileController::class, 'addresses'])->name('profile.addresses');
        Route::get('/addresses/create', [ProfileController::class, 'addressCreate'])->name('profile.addresses.create');
        Route::post('/addresses', [ProfileController::class, 'addressStore'])->name('profile.addresses.store');
        Route::get('/addresses/{id}/edit', [ProfileController::class, 'addressEdit'])->name('profile.addresses.edit');
        Route::put('/addresses/{id}', [ProfileController::class, 'addressUpdate'])->name('profile.addresses.update');
        Route::delete('/addresses/{id}', [ProfileController::class, 'addressDelete'])->name('profile.addresses.delete');
    });

    // Profila lokālizācijas tulkošanai (domāts vairāk, lai iztulkotu "x.blade.php" failus)
    Route::put('/profile/locale', [ProfileController::class, 'updateLocale'])->name('profile.locale.update');

    // Privātuma iestatījumu atjauninājums (ārpus prefix grupas — URL: /profile/privacy)
    Route::put('/profile/privacy', [ProfileController::class, 'updatePrivacy'])
        ->name('profile.privacy.update');

    // Grozs
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/item/{cartItem}', [CartController::class, 'updateQuantity'])->name('update');
        Route::delete('/item/{cartItem}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::get('/data', [CartController::class, 'get'])->name('get');
    });

    // Kuponi
    Route::post('/coupons/validate', [CouponController::class, 'validate'])->name('coupons.validate');

    // Apmaksa
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

    // Pasūtījumi
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::put('/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // Rēķinu lejupielāde
    Route::get('/orders/{id}/invoice', [InvoiceController::class, 'download'])->name('orders.invoice.download');
    Route::get('/orders/{id}/invoice/view', [InvoiceController::class, 'view'])->name('orders.invoice.view');

    // Atsauksmes
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Komentāri
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
    Route::patch('/comments/{id}', [CommentController::class, 'update'])->name('comments.update.patch');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::patch('/comments/{id}/mood', [CommentController::class, 'updateMood'])->name('comments.mood');

    // Patīk saturs (WEB maršruts sesijas autentifikācijai)
    Route::post('/content/{id}/like', [ContentController::class, 'toggleLike'])->name('content.like');
});

// ========== KURJERU MARŠRUTI ==========
Route::middleware(['auth', 'courier'])->prefix('courier')->name('courier.')->group(function () {

    // Informācijas panelis
    Route::get('/dashboard', [CourierController::class, 'dashboard'])->name('dashboard');

    // Pasūtījumu saraksts ar filtriem
    Route::get('/orders', [CourierController::class, 'orders'])->name('orders');

    // Pasūtījuma detaļas
    Route::get('/orders/{orderId}', [CourierController::class, 'showOrder'])->name('orders.show');

    // Statusa maiņa (packed → shipped → in_transit → delivered)
    Route::put('/orders/{orderId}/status', [CourierController::class, 'updateStatus'])->name('orders.status');

    // Piezīmju saglabāšana
    Route::put('/orders/{orderId}/notes', [CourierController::class, 'updateNotes'])->name('orders.notes');

    // Kurjera profils
    Route::get('/profile', [CourierController::class, 'profile'])->name('profile');

    // Kurjera profila rediģēšana
    Route::put('/profile', [CourierController::class, 'updateProfile'])->name('profile.update');

    // Problēmas ziņojums administratoram
    Route::post('/report', [CourierController::class, 'reportProblem'])->name('report');

    // Kurjera iesūtne (nosūtītie ziņojumi + atbildes)
    Route::get('/inbox', [CourierController::class, 'inbox'])->name('inbox');
});

// ========== ADMINISTRĀCIJAS MARŠRUTI ==========
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Informācijas panelis
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Administratora pārvaldība (tikai galvenais administrators)
    Route::prefix('administrators')->name('administrators.')->group(function () {
        Route::get('/', [AdminAdministratorController::class, 'index'])->name('index');
        Route::post('/', [AdminAdministratorController::class, 'store'])->name('store');
        Route::put('/{id}/permissions', [AdminAdministratorController::class, 'updatePermissions'])->name('permissions');
        Route::delete('/{id}', [AdminAdministratorController::class, 'destroy'])->name('destroy');
    });

    // Produkti
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [AdminProductController::class, 'index'])->name('index');
        Route::get('/create', [AdminProductController::class, 'create'])->name('create');
        Route::post('/', [AdminProductController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminProductController::class, 'update'])->name('update');
        Route::put('/{id}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [AdminProductController::class, 'destroy'])->name('destroy');
    });

    // Kategorijas
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'index'])->name('index');
        Route::post('/', [AdminCategoryController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminCategoryController::class, 'destroy'])->name('destroy');
    });

    // Pasūtījumi
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('status');
        Route::get('/{order}/invoice/pdf', [AdminOrderController::class, 'downloadInvoicePdf'])->name('invoice.pdf');
        Route::get('/{order}/invoice/print', [AdminOrderController::class, 'printInvoice'])->name('invoice.print');
    });

    // Saturs
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [AdminContentController::class, 'index'])->name('index');
        Route::get('/create', [AdminContentController::class, 'create'])->name('create');
        Route::post('/', [AdminContentController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [AdminContentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminContentController::class, 'update'])->name('update');
        Route::post('/{id}/quick-update', [AdminContentController::class, 'quickUpdate'])->name('quick-update');
        Route::delete('/{id}', [AdminContentController::class, 'destroy'])->name('destroy');
    });

    // Atsauksmes
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index'])->name('index');
        Route::put('/{id}/approve', [AdminReviewController::class, 'approve'])->name('approve');
        Route::put('/{id}/reject', [AdminReviewController::class, 'reject'])->name('reject');
    });

    // Komentāri
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [AdminCommentController::class, 'index'])->name('index');
        Route::put('/{id}/approve', [AdminCommentController::class, 'approve'])->name('approve');
        Route::put('/{id}/reject', [AdminCommentController::class, 'reject'])->name('reject');
    });

    // Kontaktziņojumi
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [AdminContactController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminContactController::class, 'show'])->name('show');
        Route::put('/{id}/read', [AdminContactController::class, 'markAsRead'])->name('read');
        Route::put('/{id}/reply', [AdminContactController::class, 'reply'])->name('reply');
        Route::delete('/{id}', [AdminContactController::class, 'destroy'])->name('destroy');
    });

    // Lietotāji
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminUserController::class, 'update'])->name('update');
        Route::put('/{id}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('toggle-active');
        Route::put('/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/send-email', [AdminUserController::class, 'sendEmail'])->name('send-email');
        Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('destroy');
    });

    // Iestatījumi (nepieciešama settings.view atļauja vai galvenā administratora tiesības)
    Route::middleware(['can:settings.view'])->group(function () {
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingsController::class, 'store'])->name('settings.store')->middleware('can:settings.edit');
        Route::post('/settings/test-email', [AdminSettingsController::class, 'testEmail'])->name('settings.test-email')->middleware('can:settings.edit');
        Route::post('/settings/clear-cache', [AdminSettingsController::class, 'clearCache'])->name('settings.clear-cache')->middleware('can:settings.edit');
    });

    // Aktivitāšu žurnāli (nepieciešama logs.view atļauja vai galvenā administratora tiesības)
    Route::middleware(['can:logs.view'])->group(function () {
        Route::get('/logs', [AdminActivityLogController::class, 'index'])->name('logs.index');
        Route::get('/logs/export', [AdminActivityLogController::class, 'export'])->name('logs.export');
    });

    Route::prefix('couriers')->name('couriers.')->group(function () {
        Route::get('/',                   [AdminCourierController::class, 'index'])->name('index');
        Route::post('/',                  [AdminCourierController::class, 'store'])->name('store');
        // Statiskie maršruti PIRMS /{id} — citādi Laravel uzskata 'assign' par {id}
        Route::post('/assign',            [AdminCourierController::class, 'assignOrder'])->name('assign');
        Route::delete('/assignments/{assignmentId}', [AdminCourierController::class, 'unassignOrder'])->name('unassign');
        // Dinamiskie maršruti ar {id} — vienmēr pēdējie
        Route::get('/{id}',               [AdminCourierController::class, 'show'])->name('show');
        Route::put('/{id}',               [AdminCourierController::class, 'update'])->name('update');
        Route::put('/{id}/toggle-active', [AdminCourierController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{id}',            [AdminCourierController::class, 'destroy'])->name('destroy');
    });
});

// Privātuma politikas lapa
Route::get('/privacy', function () {
    return Inertia::render('Privacy');
})->name('privacy');

// Lietošanas noteikumu lapa
Route::get('/terms', function () {
    return Inertia::render('Terms');
})->name('terms');

// ========== AUTORIZĀCIJAS UN ADMINISTRĀCIJAS MARŠRUTI (BREEZE) ==========
require __DIR__.'/auth.php';
