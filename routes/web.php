<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\MetadataController;
use App\Http\Controllers\Admin\TinyMceController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\SecretLoginController;
use App\Http\Controllers\ClientLoginController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Webhook Routes (without CSRF protection)
Route::post('/webhook/github', [WebhookController::class, 'handleGithubWebhook'])->withoutMiddleware(['web', 'csrf']);
Route::post('/webhook/gitlab', [WebhookController::class, 'handleGitlabWebhook'])->withoutMiddleware(['web', 'csrf']);
Route::post('/webhook/bitbucket', [WebhookController::class, 'handleBitbucketWebhook'])->withoutMiddleware(['web', 'csrf']);
Route::post('/webhook', [WebhookController::class, 'handleWebhook'])->withoutMiddleware(['web', 'csrf']);

// Language Switcher
Route::get('lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Backward-compatible route: phục vụ ảnh tin tức từ hệ thống cũ (ASP.NET)
// URL dạng: /News/ViewImageMedia?imageId=XXX&ext=png
Route::get('/News/ViewImageMedia', [\App\Http\Controllers\SiteController::class, 'viewImageMedia'])->name('news.media.image');

// Client Routes
// Route::get('/', [ClientController::class, 'index'])->name('client.home');

// Authentication Links
Route::get('/login', [ClientLoginController::class, 'create'])->name('login');
Route::post('/login', [ClientLoginController::class, 'store'])->name('client.login.store');
Route::get('/verify', [ClientLoginController::class, 'verifyLoginByUsernameAndToken'])->name('client.verify');
Route::get('/api/get-user-info', [ClientLoginController::class, 'getStudyUserInfo']);

Route::get('/topsecret/login', [SecretLoginController::class, 'create'])->name('agent.login');
Route::post('/topsecret/store', [SecretLoginController::class, 'store'])->name('agent.login.store');

Route::post('/logout', [SecretLoginController::class, 'destroy'])->name('logout');

// Forced Password Change (First Login)
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'showForm'])->name('password.change.form');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');
});



// Emergency Cache Clear
Route::get('/emergency-clear-cache', function() {
    try {
        Artisan::call('optimize:clear');
        return "All caches cleared successfully!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Footer Visitor Statistics Endpoint (Async AJAX)
Route::get('/footer-stats', function() {
    $onlineTotal = \Illuminate\Support\Facades\Cache::remember('stat_online_total', 30, function() {
        $c = \Illuminate\Support\Facades\DB::table('website_access_logs')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->distinct('ip_address')
            ->count('ip_address');
        return max($c, 1);
    });

    $onlineMembers = \Illuminate\Support\Facades\Cache::remember('stat_online_members', 30, function() {
        return \Illuminate\Support\Facades\DB::table('website_access_logs')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
    });

    $onlineGuests = max(0, $onlineTotal - $onlineMembers);

    $totalVisits = \Illuminate\Support\Facades\Cache::remember('stat_total_visits', 60, function() {
        return \Illuminate\Support\Facades\DB::table('website_access_logs')->count();
    });

    $today = \Illuminate\Support\Facades\Cache::remember('stat_today_visits', 60, function() {
        return \Illuminate\Support\Facades\DB::table('website_access_logs')
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->count();
    });

    $yesterday = \Illuminate\Support\Facades\Cache::remember('stat_yesterday_visits', 300, function() {
        return \Illuminate\Support\Facades\DB::table('website_access_logs')
            ->whereDate('created_at', \Carbon\Carbon::yesterday())
            ->count();
    });

    $month = \Illuminate\Support\Facades\Cache::remember('stat_month_visits', 300, function() {
        return \Illuminate\Support\Facades\DB::table('website_access_logs')
            ->whereMonth('created_at', \Carbon\Carbon::now()->month)
            ->whereYear('created_at', \Carbon\Carbon::now()->year)
            ->count();
    });

    $daysOperating = \Illuminate\Support\Facades\Cache::remember('stat_days_operating', 3600, function() {
        $firstLog = \Illuminate\Support\Facades\DB::table('website_access_logs')->min('created_at');
        if ($firstLog) {
            return max(1, (int) now()->diffInDays(\Carbon\Carbon::parse($firstLog)) + 1);
        }
        return 1;
    });

    return response()->json([
        'total_visits' => number_format($totalVisits),
        'online_total' => number_format($onlineTotal),
        'online_members' => number_format($onlineMembers),
        'online_guests' => number_format($onlineGuests),
        'today' => number_format($today),
        'yesterday' => number_format($yesterday),
        'month' => number_format($month),
        'days_operating' => number_format($daysOperating),
    ]);
});

// Barcode Generation (Public access for image display)
Route::get('/barcode/{code}', [\App\Http\Controllers\Admin\BarcodeController::class, 'show'])->name('admin.barcode.show');

// Digital Resource Routes
Route::get('/digital-resource/{id}', [\App\Http\Controllers\Site\DigitalResourceController::class, 'show'])->name('site.digital-resources.show');
Route::get('/digital-resource/{id}/view', [\App\Http\Controllers\Site\DigitalResourceController::class, 'viewPdf'])->name('site.digital-resources.view');
Route::get('/digital-resource/{id}/stream', [\App\Http\Controllers\Site\DigitalResourceController::class, 'streamPdf'])->name('site.digital-resources.stream');
Route::get('/digital-resource/{id}/stream-key', [\App\Http\Controllers\Site\DigitalResourceController::class, 'getStreamKey'])->name('site.digital-resources.stream-key');

Route::get('/digital-resource/{id}/download', [\App\Http\Controllers\Site\DigitalResourceController::class, 'download'])->name('site.digital-resources.download');


// OER Routes
Route::get('/oer', [\App\Http\Controllers\Site\OERController::class, 'landing'])->name('site.oer.landing');
Route::get('/oer/intro', [\App\Http\Controllers\Site\OERController::class, 'intro'])->name('site.oer.intro');
Route::get('/oer/contribute', [\App\Http\Controllers\Site\OERController::class, 'contribute'])->name('site.oer.contribute');
Route::post('/oer/contribute', [\App\Http\Controllers\Site\OERController::class, 'storeContribution'])->name('site.oer.contribute.store');
Route::get('/oer/{id}', [\App\Http\Controllers\Site\OERController::class, 'show'])->name('site.oer.show');
Route::get('/oer/{id}/download', [\App\Http\Controllers\Site\OERController::class, 'download'])->name('site.oer.download');

// Public Website Routes
Route::get('/', [\App\Http\Controllers\SiteController::class, 'home'])->name('home');
Route::get('/home', function() {
    return redirect('/');
});
Route::get('/opac', [\App\Http\Controllers\SiteController::class, 'opac'])->name('site.opac');
Route::get('/opac/search', function() {
    return redirect()->route('site.opac', request()->query());
})->name('opac.search');
Route::get('/opac/book/{record}', [\App\Http\Controllers\SiteController::class, 'bookDetail'])->name('opac.book.show');
Route::post('/opac/book/{record}/reserve', [\App\Http\Controllers\SiteController::class, 'reserveBook'])->name('opac.book.reserve')->middleware('auth');
Route::post('/de-nghi-bo-sung', [\App\Http\Controllers\SiteController::class, 'storeProposal'])->name('site.proposal.store');
Route::post('/khao-sat-y-kien', [\App\Http\Controllers\SiteController::class, 'storeSurvey'])->name('site.survey.store');

// Profile & My Loans
Route::middleware(['auth'])->group(function () {
    Route::get('/my-profile', [\App\Http\Controllers\SiteController::class, 'profile'])->name('profile');
    Route::post('/my-profile/change-password', [\App\Http\Controllers\SiteController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/my-profile/renew/{loan}', [\App\Http\Controllers\SiteController::class, 'renewLoan'])->name('profile.renew-loan');
});

// Admin Panel Redirect
Route::get('/topsecret', [AdminController::class, 'redirect'])->middleware(['auth', 'role:admin']);

// Admin Panel Routes (Consolidated for all staff levels)
Route::middleware(['auth', 'role:admin'])->prefix('topsecret')->group(function () {
    Route::get('/', [AdminController::class, 'redirect']);
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // ... (các route admin khác)
});

// Detail of Online Database (under Resources)
Route::get('/tai-nguyen/co-so-du-lieu-chi-tiet/{id?}', [\App\Http\Controllers\SiteController::class, 'onlineDatabaseDetail'])->name('site.online-database.detail');

// Public News Routes
Route::get('/tin-tuc-chi-tiet/{slug}', [\App\Http\Controllers\NewsController::class, 'show'])->name('news.show.custom');
Route::prefix('tin-tuc')->name('news.')->group(function () {
    Route::get('/', [\App\Http\Controllers\NewsController::class, 'index'])->name('index');
    Route::get('/{slug}', [\App\Http\Controllers\NewsController::class, 'show'])->name('show');
    Route::get('/chuyen-muc/{slug}', [\App\Http\Controllers\NewsController::class, 'category'])->name('category');
    Route::get('/chuyen-muc/{category_slug}/tag/{tag_slug}', [\App\Http\Controllers\NewsController::class, 'categoryTag'])->name('category.tag');
    Route::get('/tag/{slug}', [\App\Http\Controllers\NewsController::class, 'tag'])->name('tag');
    Route::get('/noi-bat', [\App\Http\Controllers\NewsController::class, 'featured'])->name('featured');
    Route::get('/tim-kiem', [\App\Http\Controllers\NewsController::class, 'search'])->name('search');
    Route::get('/rss', [\App\Http\Controllers\NewsController::class, 'rss'])->name('rss');
    Route::get('/sitemap.xml', [\App\Http\Controllers\NewsController::class, 'sitemap'])->name('sitemap');
    Route::get('/api', [\App\Http\Controllers\NewsController::class, 'api'])->name('api');
    Route::post('/{news}/like', [\App\Http\Controllers\NewsController::class, 'like'])->name('like');
});

Route::get('/sitemap', [\App\Http\Controllers\SiteController::class, 'sitemap'])->name('site.sitemap');
Route::get('/sitemap.xml', [\App\Http\Controllers\SiteController::class, 'xmlSitemap'])->name('site.sitemap.xml');
Route::get('/{code}', [\App\Http\Controllers\SiteController::class, 'page'])->name('site.page');

use App\Http\Controllers\SiteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PatronGroupController;
use App\Http\Controllers\Admin\ActivityLogController;

// Admin Panel Redirect
Route::get('/topsecret', [AdminController::class, 'redirect'])->middleware(['auth', 'role:admin']);

// Admin Panel Routes (Consolidated for all staff levels)
Route::middleware(['auth', 'role:admin'])->prefix('topsecret')->group(function () {
    Route::get('/', [AdminController::class, 'redirect']);
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Temporary fix for storage link
    Route::get('/storage-link', function () {
        try {
            if (file_exists(public_path('storage'))) {
                if (is_link(public_path('storage'))) {
                    app('files')->delete(public_path('storage'));
                } else {
                    return "Thư mục public/storage đã tồn tại. Vui lòng xoá thủ công thư mục này trước khi chạy lại.";
                }
            }
            app('files')->link(storage_path('app/public'), public_path('storage'));
            return "Đã tạo link storage thành công!.";
        } catch (\Exception $e) {
            return "Lỗi khi tạo link: " . $e->getMessage();
        }
    });

    // User Guides Management
    Route::get('/user-guides', function() {
        $parent = \App\Models\SiteNode::where('node_code', 'huong-dan')->first();
        $subNodes = $parent ? $parent->children()->where('is_active', true)->orderBy('sort_order')->get() : collect();
        return view('admin.user-guides.index', compact('parent', 'subNodes'));
    })->name('admin.user-guides.index');

    Route::post('/user-guides/{siteNode}', function(\Illuminate\Http\Request $request, \App\Models\SiteNode $siteNode) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'condition_title' => 'nullable|string|max:255',
            'condition_desc' => 'nullable|string|max:1000',
            'sections' => 'nullable|array',
            'sections.*.title' => 'nullable|string|max:255',
            'sections.*.steps' => 'nullable|array',
            'sections.*.steps.*.title' => 'nullable|string|max:255',
            'sections.*.steps.*.content' => 'nullable|string',
            'steps_title' => 'nullable|string|max:255',
            'steps' => 'nullable|array',
            'steps.*.title' => 'nullable|string|max:255',
            'steps.*.content' => 'nullable|string',
            'section2_title' => 'nullable|string|max:255',
            'section2_steps' => 'nullable|array',
            'section2_steps.*.title' => 'nullable|string|max:255',
            'section2_steps.*.content' => 'nullable|string',
            'video_title' => 'nullable|string|max:255',
            'video_source' => 'required|in:file,url,none',
            'embed_video_url' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg,mov,avi,mkv,pdf|max:102400',
        ]);

        $existingJson = json_decode($siteNode->content_json ?? '{}', true) ?: [];
        $uploadedVideoUrl = $existingJson['uploaded_video_url'] ?? '';
        $embedVideoUrl = $validated['embed_video_url'] ?? ($existingJson['embed_video_url'] ?? ($existingJson['video_url'] ?? ''));

        if ($request->hasFile('video_file')) {
            $path = $request->file('video_file')->store('user-guides', 'public');
            $uploadedVideoUrl = asset('storage/' . $path);
        }

        $videoSource = $validated['video_source'];
        if ($videoSource === 'none') {
            $activeVideoUrl = null;
        } else {
            $activeVideoUrl = ($videoSource === 'file' && !empty($uploadedVideoUrl)) ? $uploadedVideoUrl : $embedVideoUrl;
        }

        // Process dynamic sections array
        $sectionsList = [];
        if (!empty($validated['sections']) && is_array($validated['sections'])) {
            foreach ($validated['sections'] as $secIndex => $sec) {
                $cleanSteps = [];
                if (!empty($sec['steps']) && is_array($sec['steps'])) {
                    foreach ($sec['steps'] as $stepIndex => $step) {
                        if (!empty($step['title']) || !empty($step['content'])) {
                            $cleanSteps[] = [
                                'step_number' => count($cleanSteps) + 1,
                                'title' => $step['title'] ?? '',
                                'content' => $step['content'] ?? '',
                            ];
                        }
                    }
                }

                if (!empty($sec['title']) || count($cleanSteps) > 0) {
                    $sectionsList[] = [
                        'section_number' => count($sectionsList) + 1,
                        'title' => $sec['title'] ?? '',
                        'steps' => $cleanSteps,
                    ];
                }
            }
        }

        // Clean legacy steps array
        $stepsList = [];
        if (!empty($validated['steps']) && is_array($validated['steps'])) {
            foreach ($validated['steps'] as $idx => $step) {
                if (!empty($step['title']) || !empty($step['content'])) {
                    $stepsList[] = [
                        'step_number' => count($stepsList) + 1,
                        'title' => $step['title'] ?? '',
                        'content' => $step['content'] ?? '',
                    ];
                }
            }
        }

        // Clean legacy section 2 steps array
        $section2StepsList = [];
        if (!empty($validated['section2_steps']) && is_array($validated['section2_steps'])) {
            foreach ($validated['section2_steps'] as $idx => $step) {
                if (!empty($step['title']) || !empty($step['content'])) {
                    $section2StepsList[] = [
                        'step_number' => count($section2StepsList) + 1,
                        'title' => $step['title'] ?? '',
                        'content' => $step['content'] ?? '',
                    ];
                }
            }
        }

        // If legacy steps exist and sectionsList is empty, construct sectionsList automatically
        if (empty($sectionsList)) {
            if (!empty($stepsList)) {
                $sectionsList[] = [
                    'section_number' => 1,
                    'title' => $validated['steps_title'] ?? 'Các bước thực hiện:',
                    'steps' => $stepsList
                ];
            }
            if (!empty($section2StepsList)) {
                $sectionsList[] = [
                    'section_number' => 2,
                    'title' => $validated['section2_title'] ?? 'Các bước thực hiện Phần 2:',
                    'steps' => $section2StepsList
                ];
            }
        }

        $contentData = [
            'title' => $validated['title'],
            'condition_title' => $validated['condition_title'] ?? '',
            'condition_desc' => $validated['condition_desc'] ?? '',
            'sections' => $sectionsList,
            'steps_title' => $validated['steps_title'] ?? ($sectionsList[0]['title'] ?? 'Các bước thực hiện:'),
            'steps' => $stepsList,
            'section2_title' => $validated['section2_title'] ?? ($sectionsList[1]['title'] ?? ''),
            'section2_steps' => $section2StepsList,
            'video_title' => $validated['video_title'] ?? '',
            'video_source' => $videoSource,
            'uploaded_video_url' => $uploadedVideoUrl,
            'embed_video_url' => $embedVideoUrl,
            'video_url' => $activeVideoUrl,
        ];

        $siteNode->update([
            'display_name' => $validated['title'],
            'content_json' => json_encode($contentData, JSON_UNESCAPED_UNICODE)
        ]);

        $tab = $request->input('tab', $siteNode->node_code);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Đã cập nhật thông tin hướng dẫn thành công!'),
                'uploaded_video_url' => $uploadedVideoUrl,
                'video_url' => $activeVideoUrl
            ]);
        }

        return redirect()->to(route('admin.user-guides.index', ['tab' => $tab]))->with('success', __('Đã cập nhật thông tin hướng dẫn thành công!'));
    })->name('admin.user-guides.update');

    // Statistics
    Route::get('/statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('admin.statistics.index');

    // OER Management
    Route::prefix('oer')->name('admin.oer.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\OerController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\OerController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\Admin\OerController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\OerController::class, 'destroy'])->name('destroy');
    });

    // Site Management
    Route::prefix('site-nodes')->name('admin.site-nodes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SiteNodeController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\SiteNodeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\SiteNodeController::class, 'store'])->name('store');
        Route::get('/{siteNode}/edit', [\App\Http\Controllers\Admin\SiteNodeController::class, 'edit'])->name('edit');
        Route::get('/{siteNode}', function (\App\Models\SiteNode $siteNode) {
            return redirect()->route('admin.site-nodes.edit', $siteNode);
        })->name('show');
        Route::put('/{siteNode}', [\App\Http\Controllers\Admin\SiteNodeController::class, 'update'])->name('update');
        Route::delete('/{siteNode}', [\App\Http\Controllers\Admin\SiteNodeController::class, 'destroy'])->name('destroy');
        Route::post('/{siteNode}/toggle-status', [\App\Http\Controllers\Admin\SiteNodeController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/update-order', [\App\Http\Controllers\Admin\SiteNodeController::class, 'updateOrder'])->name('update-order');
        
        // Tree Structure Management Routes
        Route::get('/tree', [\App\Http\Controllers\Admin\SiteNodeController::class, 'tree'])->name('tree');
        Route::post('/tree/move', [\App\Http\Controllers\Admin\SiteNodeController::class, 'moveNode'])->name('tree.move');
        Route::post('/tree/rebuild', [\App\Http\Controllers\Admin\SiteNodeController::class, 'rebuildTree'])->name('tree.rebuild');
        Route::get('/tree/json', [\App\Http\Controllers\Admin\SiteNodeController::class, 'treeJson'])->name('tree.json');
        Route::post('/{siteNode}/duplicate', [\App\Http\Controllers\Admin\SiteNodeController::class, 'duplicate'])->name('duplicate');
        Route::post('/layout-settings', [\App\Http\Controllers\Admin\SiteNodeController::class, 'updateLayoutSettings'])->name('layout-settings');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\SiteNodeController::class, 'bulkAction'])->name('bulk-action');
        
        // Library Network Logos Routes
        Route::post('/network-logo/add', [\App\Http\Controllers\Admin\SiteNodeController::class, 'addNetworkLogo'])->name('add-network-logo');
        Route::get('/network-logo/{logo}/edit', [\App\Http\Controllers\Admin\SiteNodeController::class, 'editNetworkLogo'])->name('edit-network-logo');
        Route::post('/network-logo/{logo}', [\App\Http\Controllers\Admin\SiteNodeController::class, 'updateNetworkLogo'])->name('update-network-logo');
        Route::delete('/network-logo/{logo}', [\App\Http\Controllers\Admin\SiteNodeController::class, 'deleteNetworkLogo'])->name('delete-network-logo');
        
        // Banner Routes
        Route::post('/banner/add', [\App\Http\Controllers\Admin\SiteNodeController::class, 'addBanner'])->name('add-banner');
        Route::put('/banner/{banner}', [\App\Http\Controllers\Admin\SiteNodeController::class, 'updateBanner'])->name('update-banner');
        Route::post('/banner/{banner}/toggle-status', [\App\Http\Controllers\Admin\SiteNodeController::class, 'toggleBannerStatus'])->name('toggle-banner-status');
        Route::delete('/banner/{banner}', [\App\Http\Controllers\Admin\SiteNodeController::class, 'deleteBanner'])->name('delete-banner');
        
        // Page Builder Routes
        Route::get('/{siteNode}/page-builder', [\App\Http\Controllers\Admin\PageBuilderController::class, 'edit'])->name('page-builder');
        Route::put('/{siteNode}/page-builder', [\App\Http\Controllers\Admin\PageBuilderController::class, 'update'])->name('page-builder.update');
    });

    // Media Categories Management
    Route::resource('media-categories', \App\Http\Controllers\Admin\MediaCategoryController::class)->names('admin.media-categories');
    Route::resource('media-items', \App\Http\Controllers\Admin\MediaItemController::class)->names('admin.media-items');

    // Digital Documents Management
    Route::resource('digital-categories', \App\Http\Controllers\Admin\DigitalCategoryController::class)->names('admin.digital-categories')->except(['show']);
    Route::resource('digital-documents', \App\Http\Controllers\Admin\DigitalDocumentController::class)->names('admin.digital-documents')->except(['show']);

    // Digital Cataloging
    Route::get('/digital-cataloging', [\App\Http\Controllers\Admin\DigitalCatalogingController::class, 'index'])->name('admin.digital-cataloging.index');
    Route::get('/digital-cataloging/create', [\App\Http\Controllers\Admin\DigitalCatalogingController::class, 'create'])->name('admin.digital-cataloging.create');
    Route::get('/digital-cataloging/{id}/edit', [\App\Http\Controllers\Admin\DigitalCatalogingController::class, 'edit'])->name('admin.digital-cataloging.edit');
    Route::post('/digital-cataloging', [\App\Http\Controllers\Admin\DigitalCatalogingController::class, 'store'])->name('admin.digital-cataloging.store');
    Route::delete('/digital-cataloging/{id}', [\App\Http\Controllers\Admin\DigitalCatalogingController::class, 'destroy'])->name('admin.digital-cataloging.destroy');
    Route::post('/digital-cataloging/category', [\App\Http\Controllers\Admin\DigitalCatalogingController::class, 'storeCategory'])->name('admin.digital-cataloging.category.store');

    // OER Management
    Route::prefix('oer-management')->name('admin.oer.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\OERController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\OERController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\OERController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\OERController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\OERController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\OERController::class, 'destroy'])->name('destroy');
        Route::get('/contributions', [\App\Http\Controllers\Admin\OERController::class, 'contributions'])->name('contributions');
        Route::post('/contributions/{id}/approve', [\App\Http\Controllers\Admin\OERController::class, 'approveContribution'])->name('contributions.approve');
        Route::post('/contributions/{id}/reject', [\App\Http\Controllers\Admin\OERController::class, 'rejectContribution'])->name('contributions.reject');
    });

    // News Management
    Route::prefix('news')->name('admin.news.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NewsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\NewsController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\NewsController::class, 'store'])->name('store');
        Route::get('/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'show'])->name('show');
        Route::get('/{news}/edit', [\App\Http\Controllers\Admin\NewsController::class, 'edit'])->name('edit');
        Route::put('/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'update'])->name('update');
        Route::delete('/{news}', [\App\Http\Controllers\Admin\NewsController::class, 'destroy'])->name('destroy');
        
        // AJAX Routes
        Route::post('/{news}/publish', [\App\Http\Controllers\Admin\NewsController::class, 'publish'])->name('publish');
        Route::post('/{news}/archive', [\App\Http\Controllers\Admin\NewsController::class, 'archive'])->name('archive');
        Route::post('/{news}/toggle-featured', [\App\Http\Controllers\Admin\NewsController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\NewsController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/statistics', [\App\Http\Controllers\Admin\NewsController::class, 'statistics'])->name('statistics');
        Route::post('/auto-generate', [\App\Http\Controllers\Admin\NewsController::class, 'autoGenerate'])->name('auto-generate');
        Route::post('/reorder', [\App\Http\Controllers\Admin\NewsController::class, 'reorder'])->name('reorder');
    });

    // Online Database Management
    Route::resource('online-databases', \App\Http\Controllers\Admin\OnlineDatabaseController::class)->names('admin.online-databases');

    // Announcement Management (Independent from News)
    Route::prefix('announcements')->name('admin.announcements.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}/edit', [\App\Http\Controllers\Admin\AnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'update'])->name('update');
        Route::delete('/{announcement}', [\App\Http\Controllers\Admin\AnnouncementController::class, 'destroy'])->name('destroy');
        
        // AJAX Routes
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\AnnouncementController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/auto-generate', [\App\Http\Controllers\Admin\AnnouncementController::class, 'autoGenerate'])->name('auto-generate');
        Route::post('/reorder', [\App\Http\Controllers\Admin\AnnouncementController::class, 'reorder'])->name('reorder');
    });

    // News Categories Management
    Route::prefix('news-categories')->name('admin.news-categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'store'])->name('store');
        Route::get('/{newsCategory}', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'show'])->name('show');
        Route::get('/{newsCategory}/edit', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'edit'])->name('edit');
        Route::put('/{newsCategory}', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'update'])->name('update');
        Route::delete('/{newsCategory}', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'destroy'])->name('destroy');
        
        // AJAX Routes
        Route::post('/{newsCategory}/toggle-status', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/update-order', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'updateOrder'])->name('update-order');
        Route::get('/json', [\App\Http\Controllers\Admin\NewsCategoryController::class, 'json'])->name('json');
    });

    // News Tags Management
    Route::prefix('news-tags')->name('admin.news-tags.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NewsTagController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\NewsTagController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\NewsTagController::class, 'store'])->name('store');
        Route::get('/{newsTag}', [\App\Http\Controllers\Admin\NewsTagController::class, 'show'])->name('show');
        Route::get('/{newsTag}/edit', [\App\Http\Controllers\Admin\NewsTagController::class, 'edit'])->name('edit');
        Route::put('/{newsTag}', [\App\Http\Controllers\Admin\NewsTagController::class, 'update'])->name('update');
        Route::delete('/{newsTag}', [\App\Http\Controllers\Admin\NewsTagController::class, 'destroy'])->name('destroy');
        
        // AJAX Routes
        Route::post('/{newsTag}/toggle-status', [\App\Http\Controllers\Admin\NewsTagController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/json', [\App\Http\Controllers\Admin\NewsTagController::class, 'json'])->name('json');
        Route::get('/popular', [\App\Http\Controllers\Admin\NewsTagController::class, 'popular'])->name('popular');
        Route::post('/merge', [\App\Http\Controllers\Admin\NewsTagController::class, 'merge'])->name('merge');
        Route::post('/cleanup', [\App\Http\Controllers\Admin\NewsTagController::class, 'cleanup'])->name('cleanup');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\NewsTagController::class, 'bulkAction'])->name('bulk-action');
    });

    // Identity and Privilege Management
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/privileges', [UserController::class, 'privileges'])->name('admin.users.privileges');
        Route::get('/check-username', [UserController::class, 'checkUsername'])->name('admin.users.check');
        Route::post('/', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/roles', [UserController::class, 'storeRole'])->name('admin.users.roles.store');
        Route::delete('/roles/{id}', [UserController::class, 'removeRole'])->name('admin.users.roles.remove');
        Route::post('/roles/{id}/tabs', [UserController::class, 'assignTabs'])->name('admin.users.tabs');
        Route::post('/roles/{id}/sync-tabs', [UserController::class, 'syncTabs'])->name('admin.users.tabs.sync');
    });

    // Role Management (Security Clearance Templates)
    Route::resource('roles', RoleController::class)->names([
        'index' => 'admin.roles.index',
        'create' => 'admin.roles.create',
        'store' => 'admin.roles.store',
        'edit' => 'admin.roles.edit',
        'update' => 'admin.roles.update',
        'destroy' => 'admin.roles.destroy',
    ]);

    // Activity Monitoring
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
    Route::get('/activity-logs/{log}', [ActivityLogController::class, 'show'])->name('admin.activity-logs.show');
    Route::post('/activity-logs/clear-laravel-logs', [ActivityLogController::class, 'clearLaravelLogs'])->name('admin.activity-logs.clear-laravel-logs');

    // System Monitoring
    Route::get('/monitoring', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'index'])->name('admin.monitoring.index');
    Route::delete('/monitoring/kick/{id}', [\App\Http\Controllers\Admin\SystemMonitoringController::class, 'kickSession'])->name('admin.monitoring.kick');

    // Metadata Configuration (MARC Frameworks & Definitions)
    Route::get('/marc-definitions', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'index'])->name('admin.marc.index');
    Route::post('/marc-definitions/framework', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'storeFramework'])->name('admin.marc.framework.store');
    Route::put('/marc-definitions/framework/{framework}', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'updateFramework'])->name('admin.marc.framework.update');
    Route::delete('/marc-definitions/framework/{framework}', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'destroyFramework'])->name('admin.marc.framework.destroy');

    Route::post('/marc-definitions/tag', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'storeTag'])->name('admin.marc.tag.store');
    Route::post('/marc-definitions/subfield', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'storeSubfield'])->name('admin.marc.subfield.store');
    Route::put('/marc-definitions/tag/{tag}', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'updateTag'])->name('admin.marc.tag.update');
    Route::put('/marc-definitions/subfield/{subfield}', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'updateSubfield'])->name('admin.marc.subfield.update');
    Route::delete('/marc-definitions/tag/{tag}', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'destroyTag'])->name('admin.marc.tag.destroy');
    Route::delete('/marc-definitions/subfield/{subfield}', [\App\Http\Controllers\Admin\MarcDefinitionController::class, 'destroySubfield'])->name('admin.marc.subfield.destroy');

    Route::get('/marc-books', [\App\Http\Controllers\Admin\MarcBookController::class, 'index'])->name('admin.marc.book');
    Route::get('/marc-books/form/{record?}', [\App\Http\Controllers\Admin\MarcBookController::class, 'form'])
        ->whereNumber('record')
        ->name('admin.marc.book.form');
    Route::get('/marc-books/{record}', [\App\Http\Controllers\Admin\MarcBookController::class, 'show'])->name('admin.marc.book.show');
    Route::post('/marc-books', [\App\Http\Controllers\Admin\MarcBookController::class, 'store'])->name('admin.marc.book.store');
    Route::put('/marc-books/{record}', [\App\Http\Controllers\Admin\MarcBookController::class, 'update'])->name('admin.marc.book.update');
    Route::put('/marc-books/{record}/status', [\App\Http\Controllers\Admin\MarcBookController::class, 'updateStatus'])->name('admin.marc.book.status');
    Route::delete('/marc-books/{record}', [\App\Http\Controllers\Admin\MarcBookController::class, 'destroy'])->name('admin.marc.book.destroy');

    // MARC Import & Export
    Route::get('/marc-import', [\App\Http\Controllers\Admin\MarcImportController::class, 'index'])->name('admin.marc.import.index');
    Route::get('/marc-import/template', [\App\Http\Controllers\Admin\MarcImportController::class, 'downloadTemplate'])->name('admin.marc.import.template');
    Route::post('/marc-import/upload', [\App\Http\Controllers\Admin\MarcImportController::class, 'upload'])->name('admin.marc.import.upload');
    Route::post('/marc-import/upload-marc', [\App\Http\Controllers\Admin\MarcImportController::class, 'uploadMarcFile'])->name('admin.marc.import.upload-marc');
    Route::post('/marc-import/save-framework-marc', [\App\Http\Controllers\Admin\MarcImportController::class, 'saveFrameworkFromMarc'])->name('admin.marc.import.save-framework-marc');
    Route::post('/marc-import/process-marc', [\App\Http\Controllers\Admin\MarcImportController::class, 'processMarcFile'])->name('admin.marc.import.process-marc');
    
    // MARC Export & Reports
    Route::get('/marc-export', [\App\Http\Controllers\Admin\MarcReportController::class, 'index'])->name('admin.marc.export.index');
    Route::post('/marc-reports/generate', [\App\Http\Controllers\Admin\MarcReportController::class, 'generate'])->name('admin.marc.reports.generate');
    Route::get('/marc-export/download', [\App\Http\Controllers\Admin\MarcBookController::class, 'export'])->name('admin.marc.export.download');
    Route::post('/marc-import/process', [\App\Http\Controllers\Admin\MarcImportController::class, 'process'])->name('admin.marc.import.process');
    Route::post('/marc-import/create-framework', [\App\Http\Controllers\Admin\MarcImportController::class, 'createFrameworkFromFile'])->name('admin.marc.import.create-framework');

    Route::get('/marc-reports', [\App\Http\Controllers\Admin\MarcReportController::class, 'index'])->name('admin.marc.reports.index');
    Route::post('/marc-reports/generate', [\App\Http\Controllers\Admin\MarcReportController::class, 'generate'])->name('admin.marc.reports.generate');
    Route::post('/marc-reports/preview', [\App\Http\Controllers\Admin\MarcReportController::class, 'preview'])->name('admin.marc.reports.preview');

    // Export histories routes
    Route::get('/export-histories', [\App\Http\Controllers\Admin\MarcReportController::class, 'exportHistories'])->name('admin.export-histories.index');
    Route::get('/export-histories/list', [\App\Http\Controllers\Admin\MarcReportController::class, 'exportHistoriesList'])->name('admin.export-histories.list');
    Route::get('/export-histories/{id}/download', [\App\Http\Controllers\Admin\MarcReportController::class, 'exportHistoriesDownload'])->name('admin.export-histories.download');
    Route::post('/export-histories/mark-all-read', [\App\Http\Controllers\Admin\MarcReportController::class, 'exportHistoriesMarkAllRead'])->name('admin.export-histories.mark-all-read');
    Route::post('/export-histories/clear-completed', [\App\Http\Controllers\Admin\MarcReportController::class, 'exportHistoriesClearCompleted'])->name('admin.export-histories.clear-completed');
    Route::delete('/export-histories/{id}', [\App\Http\Controllers\Admin\MarcReportController::class, 'exportHistoriesDestroy'])->name('admin.export-histories.destroy');

    // Distribution & Inventory
    Route::get('/marc-books/{record}/distribution', [\App\Http\Controllers\Admin\BookDistributionController::class, 'index'])->name('admin.marc.book.distribution');
    Route::post('/marc-books/{record}/distribution', [\App\Http\Controllers\Admin\BookDistributionController::class, 'store'])->name('admin.marc.book.distribution.store');
    Route::get('/distribution/check-barcode', [\App\Http\Controllers\Admin\BookDistributionController::class, 'checkBarcode'])->name('admin.marc.book.distribution.check');
    Route::get('/distribution/check-accession', [\App\Http\Controllers\Admin\BookDistributionController::class, 'checkBarcode'])->name('admin.marc.book.distribution.check-accession');

    // Patron Management (Library Users)
    Route::get('/patrons', [\App\Http\Controllers\Admin\PatronController::class, 'index'])->name('admin.patrons.index');
    Route::get('/patrons/create', [\App\Http\Controllers\Admin\PatronController::class, 'create'])->name('admin.patrons.create');
    Route::get('/patrons/search-users', [\App\Http\Controllers\Admin\PatronController::class, 'searchUsers'])->name('admin.patrons.search-users');
    Route::post('/patrons', [\App\Http\Controllers\Admin\PatronController::class, 'store'])->name('admin.patrons.store');

    // Bulk Actions (must be declared BEFORE /patrons/{id} to avoid route param matching 'bulk-delete'/'bulk-update')
    Route::post('/patrons/bulk-update', [\App\Http\Controllers\Admin\PatronController::class, 'bulkUpdate'])->name('admin.patrons.bulk.update');
    Route::delete('/patrons/bulk-delete', [\App\Http\Controllers\Admin\PatronController::class, 'bulkDelete'])->name('admin.patrons.bulk.delete');

    Route::get('/patrons/{id}/edit', [\App\Http\Controllers\Admin\PatronController::class, 'edit'])->name('admin.patrons.edit');
    Route::patch('/patrons/{id}', [\App\Http\Controllers\Admin\PatronController::class, 'update'])->name('admin.patrons.update');
    Route::patch('/patrons/{id}/toggle-status', [\App\Http\Controllers\Admin\PatronController::class, 'toggleStatus'])->name('admin.patrons.toggle-status');
    Route::patch('/patrons/{id}/renew', [\App\Http\Controllers\Admin\PatronController::class, 'renew'])->name('admin.patrons.renew');
    Route::delete('/patrons/{id}', [\App\Http\Controllers\Admin\PatronController::class, 'destroy'])->name('admin.patrons.destroy');

    // Patron Import (Batch Import)
    Route::get('/patrons/import', [\App\Http\Controllers\Admin\PatronImportController::class, 'index'])->name('admin.patrons.import.index');
    Route::get('/patrons/import/template', [\App\Http\Controllers\Admin\PatronImportController::class, 'template'])->name('admin.patrons.import.template');
    Route::post('/patrons/import/upload', [\App\Http\Controllers\Admin\PatronImportController::class, 'upload'])->name('admin.patrons.import.upload');
    Route::get('/patrons/import/preview', [\App\Http\Controllers\Admin\PatronImportController::class, 'preview'])->name('admin.patrons.import.preview');
    Route::post('/patrons/import/process', [\App\Http\Controllers\Admin\PatronImportController::class, 'process'])->name('admin.patrons.import.process');
    Route::post('/patrons/import/images', [\App\Http\Controllers\Admin\PatronImportController::class, 'uploadImages'])->name('admin.patrons.import.images');

    // Patron Cards (ID Card Printing)
    Route::get('/patrons/cards', [\App\Http\Controllers\Admin\PatronCardController::class, 'index'])->name('admin.patrons.cards.index');
    Route::post('/patrons/cards/generate', [\App\Http\Controllers\Admin\PatronCardController::class, 'generateCards'])->name('admin.patrons.cards.generate');
    Route::get('/patrons/cards/{patron}/preview', [\App\Http\Controllers\Admin\PatronCardController::class, 'previewCard'])->name('admin.patrons.cards.preview');

    // Patron Management Features
    Route::patch('/patrons/{id}/lock', [\App\Http\Controllers\Admin\PatronController::class, 'lock'])->name('admin.patrons.lock');
    Route::patch('/patrons/{id}/unlock', [\App\Http\Controllers\Admin\PatronController::class, 'unlock'])->name('admin.patrons.unlock');
    
    // Financial Transactions
    Route::get('/patrons/{id}/transactions', [\App\Http\Controllers\Admin\PatronTransactionController::class, 'index'])->name('admin.patrons.transactions.index');
    Route::post('/patrons/{id}/transactions', [\App\Http\Controllers\Admin\PatronTransactionController::class, 'store'])->name('admin.patrons.transactions.store');
    
    // Print Queue Management
    Route::get('/patrons/print-queue', [\App\Http\Controllers\Admin\PrintQueueController::class, 'index'])->name('admin.patrons.print-queue.index');
    Route::post('/patrons/{id}/add-to-print-queue', [\App\Http\Controllers\Admin\PatronController::class, 'addToPrintQueue'])->name('admin.patrons.add-to-print-queue');
    Route::delete('/patrons/{id}/remove-from-print-queue', [\App\Http\Controllers\Admin\PatronController::class, 'removeFromPrintQueue'])->name('admin.patrons.remove-from-print-queue');
    Route::post('/patrons/print-queue/{id}/mark-printed', [\App\Http\Controllers\Admin\PrintQueueController::class, 'markPrinted'])->name('admin.patrons.print-queue.mark-printed');
    Route::delete('/patrons/print-queue/{id}', [\App\Http\Controllers\Admin\PrintQueueController::class, 'destroy'])->name('admin.patrons.print-queue.destroy');
    
    // Lock History
    Route::get('/patrons/{id}/lock-history', [\App\Http\Controllers\Admin\PatronController::class, 'lockHistory'])->name('admin.patrons.lock-history');
    Route::get('/patrons/lock-history', [\App\Http\Controllers\Admin\PatronController::class, 'allLockHistory'])->name('admin.patrons.lock-history.all');
    
    // System Logs
    Route::get('/patrons/system-logs', [\App\Http\Controllers\Admin\PatronController::class, 'systemLogs'])->name('admin.patrons.system-logs');

    // Patron Reports
    Route::get('/patron-reports', [\App\Http\Controllers\Admin\PatronReportController::class, 'index'])->name('admin.patrons.reports.index');
    Route::post('/patron-reports/generate', [\App\Http\Controllers\Admin\PatronReportController::class, 'generate'])->name('admin.patrons.reports.generate');

    // Book Proposals
    Route::get('/book-proposals', [\App\Http\Controllers\Admin\BookProposalController::class, 'index'])->name('admin.book-proposals.index');
    Route::patch('/book-proposals/{id}/status', [\App\Http\Controllers\Admin\BookProposalController::class, 'updateStatus'])->name('admin.book-proposals.update-status');

    // Patron Configuration
    Route::get('/patron-groups', [PatronGroupController::class, 'index'])->name('admin.patrons.groups.index');
    Route::post('/patron-groups', [PatronGroupController::class, 'store'])->name('admin.patrons.groups.store');
    Route::put('/patron-groups/{patronGroup}', [PatronGroupController::class, 'update'])->name('admin.patrons.groups.update');
    Route::delete('/patron-groups/{patronGroup}', [PatronGroupController::class, 'destroy'])->name('admin.patrons.groups.destroy');
    Route::patch('/patron-groups/reorder', [PatronGroupController::class, 'updateOrder'])->name('admin.patrons.groups.reorder');

    // System Infrastructure Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateLibraryInfo'])->name('admin.settings.update');
    
    // Sidebar Management
    Route::get('/sidebar-management', [\App\Http\Controllers\Admin\SidebarManagementController::class, 'index'])->name('admin.sidebar.index');
    Route::post('/sidebar-management', [\App\Http\Controllers\Admin\SidebarManagementController::class, 'store'])->name('admin.sidebar.store');
    Route::put('/sidebar-management/order', [\App\Http\Controllers\Admin\SidebarManagementController::class, 'updateOrder'])->name('admin.sidebar.order');
    Route::put('/sidebar-management/parent', [\App\Http\Controllers\Admin\SidebarManagementController::class, 'updateParent'])->name('admin.sidebar.parent');
    Route::put('/sidebar-management/toggle-active', [\App\Http\Controllers\Admin\SidebarManagementController::class, 'toggleActive'])->name('admin.sidebar.toggle-active');
    Route::post('/settings/policy', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updatePolicy'])->name('admin.settings.policy.update');
    Route::post('/settings/policy-digital', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateDigitalPolicy'])->name('admin.settings.policy.update_digital');

    Route::post('/settings/barcode', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'storeBarcodeConfig'])->name('admin.settings.barcode.store');
    Route::put('/settings/barcode/{config}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateBarcodeConfig'])->name('admin.settings.barcode.update');
    Route::delete('/settings/barcode/{config}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'deleteBarcodeConfig'])->name('admin.settings.barcode.destroy');

    Route::post('/settings/branches', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'storeBranch'])->name('admin.settings.branches.store');
    Route::put('/settings/branches/{branch}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateBranch'])->name('admin.settings.branches.update');
    Route::delete('/settings/branches/{branch}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'deleteBranch'])->name('admin.settings.branches.destroy');

    Route::post('/settings/locations', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'storeLocation'])->name('admin.settings.locations.store');
    Route::put('/settings/locations/{location}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateLocation'])->name('admin.settings.locations.update');
    Route::delete('/settings/locations/{location}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'deleteLocation'])->name('admin.settings.locations.destroy');

    Route::post('/settings/suppliers', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'storeSupplier'])->name('admin.settings.suppliers.store');
    Route::put('/settings/suppliers/{supplier}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'updateSupplier'])->name('admin.settings.suppliers.update');
    Route::delete('/settings/suppliers/{supplier}', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'deleteSupplier'])->name('admin.settings.suppliers.destroy');

    // Circulation & Fines
    Route::get('/circulation', [\App\Http\Controllers\Admin\CirculationController::class, 'index'])->name('admin.circulation.index');
    Route::post('/circulation/patron-groups', [\App\Http\Controllers\Admin\CirculationController::class, 'storePatronGroup'])->name('admin.circulation.patron-groups.store');
    Route::put('/circulation/patron-groups/{patronGroup}', [\App\Http\Controllers\Admin\CirculationController::class, 'updatePatronGroup'])->name('admin.circulation.patron-groups.update');
    Route::delete('/circulation/patron-groups/{patronGroup}', [\App\Http\Controllers\Admin\CirculationController::class, 'deletePatronGroup'])->name('admin.circulation.patron-groups.destroy');

    Route::post('/circulation/policies', [\App\Http\Controllers\Admin\CirculationController::class, 'storePolicy'])->name('admin.circulation.policies.store');
    Route::put('/circulation/policies/{policy}', [\App\Http\Controllers\Admin\CirculationController::class, 'updatePolicy'])->name('admin.circulation.policies.update');
    Route::delete('/circulation/policies/{policy}', [\App\Http\Controllers\Admin\CirculationController::class, 'deletePolicy'])->name('admin.circulation.policies.destroy');

    Route::get('/circulation/loan-desk', [\App\Http\Controllers\Admin\CirculationController::class, 'loanDesk'])->name('admin.circulation.loan-desk');
    Route::post('/circulation/checkout', [\App\Http\Controllers\Admin\CirculationController::class, 'checkout'])->name('admin.circulation.checkout');
    Route::post('/circulation/checkin', [\App\Http\Controllers\Admin\CirculationController::class, 'checkin'])->name('admin.circulation.checkin');
    Route::post('/circulation/renew/{loan}', [\App\Http\Controllers\Admin\CirculationController::class, 'renew'])->name('admin.circulation.renew');
    Route::post('/circulation/recall', [\App\Http\Controllers\Admin\CirculationController::class, 'recall'])->name('admin.circulation.recall');
    Route::post('/circulation/declare-lost', [\App\Http\Controllers\Admin\CirculationController::class, 'declareLost'])->name('admin.circulation.declare-lost');

    // AJAX Search Routes
    Route::get('/circulation/search-patron', [\App\Http\Controllers\Admin\CirculationController::class, 'searchPatron'])->name('admin.circulation.search-patron');
    Route::get('/circulation/search-book', [\App\Http\Controllers\Admin\CirculationController::class, 'searchBook'])->name('admin.circulation.search-book');

    Route::get('/circulation/fines', [\App\Http\Controllers\Admin\CirculationController::class, 'fines'])->name('admin.circulation.fines');
    Route::post('/circulation/fines/{fine}/pay', [\App\Http\Controllers\Admin\CirculationController::class, 'payFine'])->name('admin.circulation.fines.pay');
    Route::post('/circulation/fines/{fine}/waive', [\App\Http\Controllers\Admin\CirculationController::class, 'waiveFine'])->name('admin.circulation.fines.waive');
    
    // Circulation main operations
    Route::get('/circulation', [\App\Http\Controllers\Admin\CirculationController::class, 'loanDesk'])->name('admin.circulation.loan-desk');
    Route::get('/circulation/tab-content', [\App\Http\Controllers\Admin\CirculationController::class, 'tabContent'])->name('admin.circulation.tab-content');
    Route::get('/circulation/logs', [\App\Http\Controllers\Admin\CirculationController::class, 'activityLogs'])->name('admin.circulation.logs');
    Route::get('/circulation/book-management', [\App\Http\Controllers\Admin\CirculationController::class, 'bookManagement'])->name('admin.circulation.book-management');
    Route::get('/circulation/requests', function() {
        return redirect()->route('admin.circulation.loan-desk', ['tab' => 'requests']);
    })->name('admin.circulation.requests');
    Route::post('/circulation/requests/{reservation}/approve', [\App\Http\Controllers\Admin\CirculationController::class, 'approveRequest'])->name('admin.circulation.requests.approve');
    Route::post('/circulation/requests/{reservation}/reject', [\App\Http\Controllers\Admin\CirculationController::class, 'rejectRequest'])->name('admin.circulation.requests.reject');

    // Reading Room Operations
    Route::post('/circulation/reading-room/checkout', [\App\Http\Controllers\Admin\CirculationController::class, 'readingRoomCheckout'])->name('admin.circulation.reading-room.checkout');
    Route::post('/circulation/reading-room/checkin', [\App\Http\Controllers\Admin\CirculationController::class, 'readingRoomCheckin'])->name('admin.circulation.reading-room.checkin');
    Route::get('/circulation/reading-room/transactions', [\App\Http\Controllers\Admin\CirculationController::class, 'getReadingRoomTransactions'])->name('admin.circulation.reading-room.transactions');
    Route::get('/circulation/reading-room/active', [\App\Http\Controllers\Admin\CirculationController::class, 'getActiveReadingRoomTransactions'])->name('admin.circulation.reading-room.active');

    // Hold/Reserve Operations
    Route::post('/circulation/hold/place', [\App\Http\Controllers\Admin\CirculationController::class, 'placeHold'])->name('admin.circulation.hold.place');
    Route::post('/circulation/hold/cancel', [\App\Http\Controllers\Admin\CirculationController::class, 'cancelHold'])->name('admin.circulation.hold.cancel');
    Route::get('/circulation/hold/patron', [\App\Http\Controllers\Admin\CirculationController::class, 'getPatronReservations'])->name('admin.circulation.hold.patron');
    Route::get('/circulation/hold/all', [\App\Http\Controllers\Admin\CirculationController::class, 'getAllActiveReservations'])->name('admin.circulation.hold.all');
    Route::post('/circulation/hold/fulfill', [\App\Http\Controllers\Admin\CirculationController::class, 'fulfillReservation'])->name('admin.circulation.hold.fulfill');

    // Circulation Tools
    Route::get('/circulation/tools', [\App\Http\Controllers\Admin\CirculationController::class, 'tools'])->name('admin.circulation.tools');
    Route::post('/circulation/patron-history', [\App\Http\Controllers\Admin\CirculationController::class, 'getPatronHistory'])->name('admin.circulation.patron-history');
    Route::post('/circulation/advanced-search', [\App\Http\Controllers\Admin\CirculationController::class, 'advancedBookSearch'])->name('admin.circulation.advanced-search');

    // Circulation Policies Management - DEDICATED CONTROLLER
    Route::prefix('circulation/policies')->name('admin.circulation.policies.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'store'])->name('store');
        Route::get('/{policy}', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'show'])->name('show');
        Route::get('/{policy}/edit', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'edit'])->name('edit');
        Route::put('/{policy}', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'update'])->name('update');
        Route::delete('/{policy}', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'destroy'])->name('destroy');
        Route::delete('/{policy}/force', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'forceDelete'])->name('force-delete');
        Route::post('/{policy}/toggle', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{policy}/duplicate', [\App\Http\Controllers\Admin\CirculationPolicyController::class, 'duplicate'])->name('duplicate');
    });

    // Circulation Distribution (placeholder for sidebar)
    Route::get('/circulation/distribution', [\App\Http\Controllers\Admin\CirculationController::class, 'loanDesk'])->name('admin.circulation.distribution');

    // Circulation Reports
    Route::prefix('circulation/reports')->name('admin.circulation.reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CirculationReportController::class, 'index'])->name('index');
        Route::post('/export', [\App\Http\Controllers\Admin\CirculationReportController::class, 'export'])->name('export');
        Route::get('/currently-borrowed', [\App\Http\Controllers\Admin\CirculationReportController::class, 'currentlyBorrowed'])->name('currently_borrowed');
        Route::get('/patron-service', [\App\Http\Controllers\Admin\CirculationReportController::class, 'patronService'])->name('patron_service');
        Route::get('/overdue', [\App\Http\Controllers\Admin\CirculationReportController::class, 'overdue'])->name('overdue');
        Route::get('/top-patrons', [\App\Http\Controllers\Admin\CirculationReportController::class, 'topPatrons'])->name('top_patrons');
        Route::get('/transaction-history', [\App\Http\Controllers\Admin\CirculationReportController::class, 'transactionHistory'])->name('transaction_history');
        Route::get('/never-borrowed', [\App\Http\Controllers\Admin\CirculationReportController::class, 'neverBorrowed'])->name('never_borrowed');
        Route::get('/library-entries', [\App\Http\Controllers\Admin\CirculationReportController::class, 'libraryEntries'])->name('library_entries');
        Route::get('/website-access', [\App\Http\Controllers\Admin\CirculationReportController::class, 'websiteAccess'])->name('website_access');
    });

    // Metadata Configuration
    Route::get('/document-types', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'index'])->name('admin.document-types.index');
    Route::post('/document-types', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'store'])->name('admin.document-types.store');
    Route::put('/document-types/{documentType}', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'update'])->name('admin.document-types.update');
    Route::delete('/document-types/{documentType}', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'destroy'])->name('admin.document-types.destroy');
    Route::post('/document-types/order', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'updateOrder'])->name('admin.document-types.order');

    // Bibliographic Levels Management (MARC Bibliographic Level)
    Route::get('/bibliographic-levels', [\App\Http\Controllers\Admin\BibliographicLevelController::class, 'index'])->name('admin.bibliographic-levels.index');
    Route::get('/bibliographic-levels/create', [\App\Http\Controllers\Admin\BibliographicLevelController::class, 'create'])->name('admin.bibliographic-levels.create');
    Route::post('/bibliographic-levels', [\App\Http\Controllers\Admin\BibliographicLevelController::class, 'store'])->name('admin.bibliographic-levels.store');
    Route::get('/bibliographic-levels/{bibliographicLevel}/edit', [\App\Http\Controllers\Admin\BibliographicLevelController::class, 'edit'])->name('admin.bibliographic-levels.edit');
    Route::put('/bibliographic-levels/{bibliographicLevel}', [\App\Http\Controllers\Admin\BibliographicLevelController::class, 'update'])->name('admin.bibliographic-levels.update');
    Route::delete('/bibliographic-levels/{bibliographicLevel}', [\App\Http\Controllers\Admin\BibliographicLevelController::class, 'destroy'])->name('admin.bibliographic-levels.destroy');
    Route::post('/bibliographic-levels/order', [\App\Http\Controllers\Admin\BibliographicLevelController::class, 'updateOrder'])->name('admin.bibliographic-levels.order');

    // External Protocol Integration (Z39.50)
    Route::get('/z3950', [\App\Http\Controllers\Admin\Z3950Controller::class, 'index'])->name('admin.z3950.index');
    Route::post('/z3950', [\App\Http\Controllers\Admin\Z3950Controller::class, 'store'])->name('admin.z3950.store');
    Route::put('/z3950/{server}', [\App\Http\Controllers\Admin\Z3950Controller::class, 'update'])->name('admin.z3950.update');
    Route::delete('/z3950/{server}', [\App\Http\Controllers\Admin\Z3950Controller::class, 'destroy'])->name('admin.z3950.destroy');
    Route::post('/z3950/{server}/test', [\App\Http\Controllers\Admin\Z3950Controller::class, 'testConnection'])->name('admin.z3950.test');
    Route::get('/z3950/search', [\App\Http\Controllers\Admin\Z3950Controller::class, 'search'])->name('admin.z3950.search');
    Route::post('/z3950/search', [\App\Http\Controllers\Admin\Z3950Controller::class, 'doSearch'])->name('admin.z3950.doSearch');
    Route::post('/z3950/import', [\App\Http\Controllers\Admin\Z3950Controller::class, 'import'])->name('admin.z3950.import');

    // Digital Resources Management
    Route::get('digital-dashboard', [\App\Http\Controllers\Admin\DigitalResourceController::class, 'dashboard'])->name('admin.digital-resources.dashboard');
    Route::resource('digital-folders', \App\Http\Controllers\Admin\DigitalFolderController::class)->names([
        'index' => 'admin.digital-folders.index',
        'store' => 'admin.digital-folders.store',
        'update' => 'admin.digital-folders.update',
        'destroy' => 'admin.digital-folders.destroy',
    ]);
    Route::get('digital-folders-export', [\App\Http\Controllers\Admin\DigitalFolderController::class, 'export'])->name('admin.digital-folders.export');
    
    Route::get('digital-resources', [\App\Http\Controllers\Admin\DigitalResourceController::class, 'index'])->name('admin.digital-resources.index');
    Route::get('digital-resources/create', [\App\Http\Controllers\Admin\DigitalResourceController::class, 'create'])->name('admin.digital-resources.create');
    Route::post('digital-resources', [\App\Http\Controllers\Admin\DigitalResourceController::class, 'store'])->name('admin.digital-resources.store');
    Route::get('digital-resources/{resource}', [\App\Http\Controllers\Admin\DigitalResourceController::class, 'show'])->name('admin.digital-resources.show');
    Route::get('digital-resources/{resource}/download', [\App\Http\Controllers\Admin\DigitalResourceController::class, 'download'])->name('admin.digital-resources.download');

    // Digital Reports
    Route::get('/digital-reports', [\App\Http\Controllers\Admin\DigitalReportController::class, 'index'])->name('admin.digital.reports.index');
    Route::post('/digital-reports/generate', [\App\Http\Controllers\Admin\DigitalReportController::class, 'generate'])->name('admin.digital.reports.generate');
    Route::post('/digital-reports/preview', [\App\Http\Controllers\Admin\DigitalReportController::class, 'preview'])->name('admin.digital.reports.preview');
    Route::get('/digital-reports/history', [\App\Http\Controllers\Admin\DigitalReportController::class, 'history'])->name('admin.digital.reports.history');
    Route::get('/digital-reports/history/{id}/download', [\App\Http\Controllers\Admin\DigitalReportController::class, 'historyDownload'])->name('admin.digital.reports.history.download');
    Route::delete('/digital-reports/history/{id}', [\App\Http\Controllers\Admin\DigitalReportController::class, 'historyDelete'])->name('admin.digital.reports.history.delete');
    Route::post('/digital-reports/history/clear', [\App\Http\Controllers\Admin\DigitalReportController::class, 'clearHistory'])->name('admin.digital.reports.history.clear');

    // Patron Surveys List
    Route::get('/patron-surveys', [\App\Http\Controllers\Admin\PatronSurveyController::class, 'index'])->name('admin.patron-surveys.index');

    // Curriculum Management
    Route::get('/curriculum', [\App\Http\Controllers\Admin\CurriculumController::class, 'index'])->name('admin.curriculum.index');
    Route::post('/curriculum', [\App\Http\Controllers\Admin\CurriculumController::class, 'store'])->name('admin.curriculum.store');
    Route::put('/curriculum/{id}', [\App\Http\Controllers\Admin\CurriculumController::class, 'update'])->name('admin.curriculum.update');
    Route::delete('/curriculum/{id}', [\App\Http\Controllers\Admin\CurriculumController::class, 'destroy'])->name('admin.curriculum.destroy');

    // TinyMCE Token Management
    Route::get('/tinymce', [\App\Http\Controllers\Admin\TinyMceController::class, 'index'])->name('admin.tinymce.index');
    Route::post('/tinymce/update-token', [\App\Http\Controllers\Admin\TinyMceController::class, 'updateToken'])->name('admin.tinymce.update');

    // Video Management
    Route::resource('videos', \App\Http\Controllers\Admin\VideoController::class)->names('admin.videos');
    Route::post('/videos/{video}/toggle-status', [\App\Http\Controllers\Admin\VideoController::class, 'toggleStatus'])->name('admin.videos.toggle-status');
    Route::post('/videos/upload-video', [\App\Http\Controllers\Admin\VideoController::class, 'uploadVideo'])->name('admin.videos.upload-video');
    Route::post('/videos/upload-thumbnail', [\App\Http\Controllers\Admin\VideoController::class, 'uploadThumbnail'])->name('admin.videos.upload-thumbnail');

    // Mail Management
    Route::prefix('mails')->name('admin.mails.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MailManagementController::class, 'index'])->name('index');
        Route::post('/send', [\App\Http\Controllers\Admin\MailManagementController::class, 'send'])->name('send');
        Route::post('/send-mass', [\App\Http\Controllers\Admin\MailManagementController::class, 'sendMass'])->name('send-mass');
        Route::post('/preview', [\App\Http\Controllers\Admin\MailManagementController::class, 'preview'])->name('preview');
        Route::get('/{mail}', [\App\Http\Controllers\Admin\MailManagementController::class, 'show'])->name('show');
        Route::post('/{mail}/resend', [\App\Http\Controllers\Admin\MailManagementController::class, 'resend'])->name('resend');
        Route::delete('/{mail}', [\App\Http\Controllers\Admin\MailManagementController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\MailManagementController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/statistics', [\App\Http\Controllers\Admin\MailManagementController::class, 'statistics'])->name('statistics');
        Route::get('/export', [\App\Http\Controllers\Admin\MailManagementController::class, 'export'])->name('export');
        Route::post('/save-template', [\App\Http\Controllers\Admin\MailManagementController::class, 'saveTemplate'])->name('save-template');
        Route::post('/save-settings', [\App\Http\Controllers\Admin\MailManagementController::class, 'saveSettings'])->name('save-settings');
        Route::post('/send-overdue', [\App\Http\Controllers\Admin\MailManagementController::class, 'sendOverdueMails'])->name('send-overdue');
        Route::post('/send-due-soon', [\App\Http\Controllers\Admin\MailManagementController::class, 'sendDueSoonMails'])->name('send-due-soon');
    });
});

// Visitor Routes
Route::middleware(['auth', 'role:visitor'])->group(function () {
    // Visitor-specific functionality
});


