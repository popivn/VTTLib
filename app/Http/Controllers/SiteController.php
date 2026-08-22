<?php

namespace App\Http\Controllers;

use App\Models\SiteNode;
use App\Models\BibliographicRecord;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display the home page.
     */
    public function home(Request $request)
    {
        // 1. Lấy dữ liệu cơ bản cho Menu/Footer (cached)
        $menuItems = \Cache::remember('site.menu_items', 600, function () {
            return SiteNode::getMenuItems('menu');
        });
        $footerItems = \Cache::remember('site.footer_items', 600, function () {
            return SiteNode::getMenuItems('footer');
        });

        // 2. Xử lý AJAX nạp tab sidebar (Mới | Nổi bật)
        if ($request->ajax() && $request->has('resource_type')) {
            $sidebarType = $request->query('resource_type', 'new');
            $sidebarQuery = \App\Models\BibliographicRecord::with(['fields.subfields'])
                ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED);

            if ($sidebarType === 'featured') {
                $sidebarQuery->where('is_featured', 1);
            } else {
                $sidebarQuery->where('record_type', 'book');
            }

            $sidebarBooks = $sidebarQuery->latest()->take(10)->get();

            return view('site.pages.partials.sidebar-books', compact('sidebarBooks'));
        }

        // 3. Lấy dữ liệu cho các Section trang chủ (cached)
        $newResources = \Cache::remember('site.new_resources', 300, function () {
            return \App\Models\DigitalResource::with('folder')
                ->where('status', 'published')
                ->latest()
                ->take(8)
                ->get();
        });

        // Xử lý lọc cho Section 1 tabs
        $type = $request->query('type', 'book');
        $query = \App\Models\BibliographicRecord::with(['fields.subfields', 'items'])
            ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED);
        
        if ($type === 'journal') {
            $query->where('record_type', 'resource');
        } elseif ($type === 'folder') {
            $query->where('record_type', 'collection');
        } else {
            $query->where('record_type', 'book')->has('items');
        }

        if ($request->has('offset')) {
            $offset = intval($request->query('offset'));
            $perPage = intval($request->query('limit', 4));
        } else {
            $page = intval($request->query('page', 1));
            if ($page === 1) {
                $perPage = 12;
                $offset = 0;
            } else {
                $perPage = 4;
                $offset = 12 + (($page - 2) * 4);
            }
        }

        $newBooks = $query->where('status', 'approved')
            ->orderBy(
                \App\Models\BookItem::select('accession_number')
                    ->whereColumn('bibliographic_record_id', 'bibliographic_records.id')
                    ->orderBy('accession_number', 'desc')
                    ->limit(1),
                'desc'
            )
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Check if AJAX requesting only slides for lazy loading
        if ($request->ajax() && $request->has('only_slides')) {
            return view('site.pages.partials.home-books-slides', compact('newBooks'));
        }

        // Kiểm tra AJAX cho Section 1 tabs (Sách mới | Tạp chí | Thư mục)
        if ($request->ajax() && $request->has('type')) {
            return view('site.pages.partials.home-books', compact('newBooks'));
        }

        // Kiểm tra AJAX cho Section 3 tabs (Tin mới | Video)
        if ($request->ajax() && $request->has('news_type')) {
            $newsType = $request->query('news_type', 'news');
            if ($newsType === 'video') {
                $tabNews = \App\Models\News::published()
                    ->whereHas('category', function($q) {
                        $q->where('slug', 'video');
                    })
                    ->latest()
                    ->take(12)
                    ->get();
            } else {
                $tabNews = \App\Models\News::published()->latest()->take(12)->get();
            }
            return view('site.pages.partials.home-news', compact('tabNews', 'newsType'));
        }

        // Kiểm tra AJAX cho Section 5 tabs (Sản khoa | Nhi khoa | Nội khoa)
        if ($request->ajax() && $request->has('medical_type')) {
            $medicalType = $request->query('medical_type', 'Sản khoa');
            
            // Tìm topic tương ứng trong portal_topics
            $topic = \Illuminate\Support\Facades\DB::table('portal_topics')
                ->where('description', 'LIKE', '%' . $medicalType . '%')
                ->first();

            if ($topic) {
                // Lấy các bib_id liên kết với topic này trong portal_articles
                $bibIds = \Illuminate\Support\Facades\DB::table('portal_articles')
                    ->where('topic_id', $topic->id)
                    ->whereNotNull('bib_id')
                    ->orderBy('sort_order')
                    ->pluck('bib_id')
                    ->toArray();

                if (!empty($bibIds)) {
                    $newBooks = \App\Models\BibliographicRecord::with(['fields.subfields', 'items'])
                        ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                        ->whereIn('id', $bibIds)
                        // Giữ nguyên thứ tự sắp xếp theo bibIds
                        ->orderByRaw('FIELD(id, ' . implode(',', $bibIds) . ')')
                        ->take(12)
                        ->get();
                } else {
                    $newBooks = collect();
                }
            } else {
                $newBooks = collect();
            }

            return view('site.pages.partials.home-medical', compact('newBooks'));
        }

        // 4. Lấy dữ liệu Tin tức & Thông báo (cached)
        $homeNews = \Cache::remember('site.home_news', 300, function () {
            return \App\Models\News::published()
                ->whereHas('category', function($q) {
                    $q->where('slug', 'tin-tuc-su-kien');
                })
                ->latest('published_at')
                ->take(5)
                ->get();
        });

        $homeAnnouncements = \Cache::remember('site.home_announcements', 300, function () {
            return \App\Models\News::published()
                ->whereHas('category', function($q) {
                    $q->where('slug', 'thong-bao');
                })
                ->latest()
                ->take(5)
                ->get();
        });

        // Dữ liệu cho tab Tin Mới (Section 3) (cached)
        $tabNews = \Cache::remember('site.tab_news', 300, function () {
            return \App\Models\News::published()
                ->latest()
                ->take(12)
                ->get();
        });

        // Dữ liệu cho section Giới Thiệu Sách Hàng Tháng (cached)
        $bookIntroductionNews = \Cache::remember('site.book_intro_news', 300, function () {
            return \App\Models\News::published()
                ->whereHas('category', function($q) {
                    $q->where('slug', 'gioi-thieu-sach');
                })
                ->latest('published_at')
                ->get();
        });

        // Lấy sidebarBooks mặc định (tab Mới) (cached)
        $sidebarBooks = \Cache::remember('site.sidebar_books', 300, function () {
            return \App\Models\BibliographicRecord::with(['fields.subfields'])
                ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                ->where('record_type', 'book')
                ->latest()
                ->take(10)
                ->get();
        });

        // Lấy dữ liệu Network Logos cho slide (cached)
        $networkLogos = \Cache::remember('site.network_logos', 600, function () {
            return \App\Models\LibraryNetworkLogo::where('is_active', 1)
                ->orderBy('sort_order')
                ->get();
        });

        // Lấy Videos cho sidebar bên phải (tab VIDEO) (cached)
        $sidebarVideos = \Cache::remember('site.sidebar_videos', 300, function () {
            return \App\Models\News::published()
                ->whereHas('category', function($q) {
                    $q->where('slug', 'video');
                })
                ->latest()
                ->take(5)
                ->get();
        });

        // Lấy Banners cho Hero Section (cached)
        $banners = \Cache::remember('site.banners.' . session('locale', app()->getLocale()), 300, function () {
            return \App\Models\Banner::currentlyActive()
                ->byLanguage(session('locale', app()->getLocale()))
                ->where(function($q) {
                    $q->where('position', 'home_hero')
                      ->orWhereNull('position');
                })
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        return view('site.pages.home', compact(
            'menuItems', 'footerItems', 'newResources',
            'newBooks', 'homeNews', 'sidebarBooks', 'homeAnnouncements', 'tabNews', 'bookIntroductionNews', 'networkLogos', 'sidebarVideos', 'banners'
        ));
    }

    /**
     * Display the OPAC search page.
     */
    public function opac(Request $request)
    {
        $query = $request->query('q');
        $type = $request->query('type', 'all');
        $locationId = $request->query('location');
        $ddcCode = $request->query('ddc');

        $booksQuery = \App\Models\BibliographicRecord::with(['fields.subfields', 'items'])
            ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
            ->where(function($q) {
                $q->where('record_type', 'resource') // Tài liệu số / Tạp chí Online không bắt buộc có bản ấn
                  ->orWhereHas('items'); // Sách giấy bắt buộc phải có ít nhất 1 bản ấn
            });

        if ($query) {
            $booksQuery->where(function($mainQ) use ($query, $type) {
                if ($type !== 'accession') {
                    $mainQ->whereHas('fields.subfields', function ($q) use ($query, $type) {
                        if ($type !== 'all') {
                            $tags = [
                                'title' => ['245'],
                                'author' => ['100', '700'],
                                'subject' => ['650', '651'],
                            ];
                            if (isset($tags[$type])) {
                                $q->whereIn('tag', $tags[$type]);
                            }
                        }
                        $q->where('value', 'LIKE', "%{$query}%");
                    });
                }
                
                if ($type === 'all' || $type === 'accession') {
                    $itemSearch = function($q) use ($query) {
                        $q->where('accession_number', 'LIKE', "%{$query}%")
                          ->orWhere('barcode', 'LIKE', "%{$query}%");
                    };
                    if ($type === 'accession') {
                        $mainQ->whereHas('items', $itemSearch);
                    } else {
                        $mainQ->orWhereHas('items', $itemSearch);
                    }
                }
            });
        }

        if ($locationId) {
            $booksQuery->whereHas('items', function($q) use ($locationId) {
                $q->where('storage_location_id', $locationId);
            });
        }

        if ($ddcCode) {
            $booksQuery->whereHas('fields.subfields', function($q) use ($ddcCode) {
                $q->where('tag', '082')->where('code', 'a')->where('value', 'LIKE', $ddcCode . '%');
            });
        }

        $sort = $request->query('sort', 'accession_desc');
        if ($sort === 'newest') {
            $booksQuery->orderBy('updated_at', 'desc');
        } elseif ($sort === 'oldest') {
            $booksQuery->orderBy('updated_at', 'asc');
        } elseif ($sort === 'accession_asc') {
            $booksQuery->orderBy(
                \App\Models\BookItem::select('accession_number')
                    ->whereColumn('bibliographic_record_id', 'bibliographic_records.id')
                    ->orderBy('accession_number', 'asc')
                    ->limit(1),
                'asc'
            );
        } elseif ($sort === 'title_az') {
            $titleSubquery = \App\Models\MarcSubfield::select('value')
                ->join('marc_fields', 'marc_subfields.marc_field_id', '=', 'marc_fields.id')
                ->whereColumn('marc_fields.record_id', 'bibliographic_records.id')
                ->where('marc_fields.tag', '245')
                ->where('marc_subfields.code', 'a')
                ->limit(1);
            $booksQuery->orderBy($titleSubquery, 'asc');
        } elseif ($sort === 'title_za') {
            $titleSubquery = \App\Models\MarcSubfield::select('value')
                ->join('marc_fields', 'marc_subfields.marc_field_id', '=', 'marc_fields.id')
                ->whereColumn('marc_fields.record_id', 'bibliographic_records.id')
                ->where('marc_fields.tag', '245')
                ->where('marc_subfields.code', 'a')
                ->limit(1);
            $booksQuery->orderBy($titleSubquery, 'desc');
        } else {
            // Mặc định là Số đăng ký: Giảm dần (accession_desc)
            $booksQuery->orderBy(
                \App\Models\BookItem::select('accession_number')
                    ->whereColumn('bibliographic_record_id', 'bibliographic_records.id')
                    ->orderBy('accession_number', 'desc')
                    ->limit(1),
                'desc'
            );
        }

        $books = $booksQuery->paginate(12)->withQueryString();
        $totalRecords = \Cache::remember('site.opac.total_records', 300, function () {
            return \App\Models\BibliographicRecord::where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                ->where(function($q) {
                    $q->where('record_type', 'resource')
                      ->orWhereHas('items');
                })
                ->count();
        });

        // Prepare Sidebar Data (cached)
        $sidebar = \Cache::remember('site.opac.sidebar', 300, function () {
            return [
                'locations' => \App\Models\StorageLocation::withCount(['bookItems' => function($q) {
                    $q->whereHas('bibliographicRecord', function($rq) {
                        $rq->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED);
                    });
                }])->get(),
                'ddc' => $this->getDdcStats(),
                'mostBorrowed' => \App\Models\BibliographicRecord::with(['fields.subfields'])
                    ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                    ->take(5)
                    ->get(),
                'hotKeywords' => \App\Models\MarcSubfield::whereHas('field', function($q) {
                        $q->whereIn('tag', ['650', '651']);
                    })
                    ->whereNotNull('value')
                    ->groupBy('value')
                    ->orderByRaw('COUNT(*) DESC')
                    ->take(10)
                    ->pluck('value')
                    ->toArray()
            ];
        });

        $menuItems = \Cache::remember('site.menu_items', 600, function () {
            return SiteNode::getMenuItems('menu');
        });
        $footerItems = \Cache::remember('site.footer_items', 600, function () {
            return SiteNode::getMenuItems('footer');
        });

        return view('site.pages.opac', compact('books', 'totalRecords', 'menuItems', 'footerItems', 'sidebar'));
    }

    /**
     * Get DDC Statistics for Sidebar
     */
    private function getDdcStats()
    {
        $ddcs = [
            ['code' => '000', 'name' => 'Tin học, Kiến thức chung'],
            ['code' => '100', 'name' => 'Triết học & Tâm lý học'],
            ['code' => '200', 'name' => 'Tôn giáo'],
            ['code' => '300', 'name' => 'Khoa học xã hội'],
            ['code' => '400', 'name' => 'Ngôn ngữ'],
            ['code' => '500', 'name' => 'Khoa học tự nhiên'],
            ['code' => '600', 'name' => 'Công nghệ (Khoa học ứng dụng)'],
            ['code' => '700', 'name' => 'Nghệ thuật & Giải trí'],
            ['code' => '800', 'name' => 'Văn học'],
            ['code' => '900', 'name' => 'Địa lý & Lịch sử'],
        ];

        foreach ($ddcs as &$ddc) {
            $ddc['count'] = \App\Models\BibliographicRecord::where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                ->whereHas('fields.subfields', function($q) use ($ddc) {
                    $q->where('tag', '082')->where('code', 'a')->where('value', 'LIKE', substr($ddc['code'], 0, 1) . '%');
                })->count();
        }

        return array_filter($ddcs, fn($d) => $d['count'] > 0);
    }

    /**
     * Display the book detail page.
     */
    public function bookDetail(\App\Models\BibliographicRecord $record)
    {
        $record->load(['fields.subfields', 'items']);
        
        // Tăng lượt xem
        $record->increment('view_count');
        
        $menuItems = SiteNode::getMenuItems('menu');
        $footerItems = SiteNode::getMenuItems('footer');

        return view('site.pages.book-detail', compact('record', 'menuItems', 'footerItems'));
    }

    /**
     * Reserve a book.
     */
    public function reserveBook(Request $request, \App\Models\BibliographicRecord $record)
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vui lòng đăng nhập để mượn sách.')
                ], 401);
            }

            $patron = \App\Models\PatronDetail::where('user_id', $user->id)->first();
            if (!$patron) {
                return response()->json([
                    'success' => false,
                    'message' => __('Tài khoản của bạn chưa kích hoạt thông tin độc giả. Vui lòng liên hệ thủ thư.')
                ], 400);
            }

            // Check if patron already has active reservation for this book
            $existingReservation = \App\Models\Reservation::where('patron_detail_id', $patron->id)
                ->where('bibliographic_record_id', $record->id)
                ->whereIn('status', ['pending', 'ready'])
                ->first();

            if ($existingReservation) {
                return response()->json([
                    'success' => false,
                    'message' => __('Bạn đã đăng ký mượn tài liệu này rồi.')
                ], 400);
            }

            // Check if patron is currently borrowing this book
            $activeLoan = \App\Models\LoanTransaction::whereHas('bookItem', function($q) use ($record) {
                    $q->where('bibliographic_record_id', $record->id);
                })
                ->where('patron_detail_id', $patron->id)
                ->where('status', 'borrowed')
                ->first();

            if ($activeLoan) {
                return response()->json([
                    'success' => false,
                    'message' => __('Bạn đang mượn tài liệu này, không thể đăng ký mượn thêm.')
                ], 400);
            }

            // Check patron's hold policy
            $policy = $patron->patronGroup?->activePolicy;
            if (!$policy || !$policy->canPlaceHolds()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Bạn đọc không được phép đặt giữ lại sách.')
                ], 400);
            }

            // Check patron's hold limit
            $activeHolds = \App\Models\Reservation::where('patron_detail_id', $patron->id)
                ->whereIn('status', ['pending', 'ready'])
                ->count();

            if ($activeHolds >= $policy->max_holds) {
                return response()->json([
                    'success' => false,
                    'message' => __('Bạn đã đạt giới hạn đặt giữ tối đa (:max)', ['max' => $policy->max_holds])
                ], 400);
            }

            // Always initialize reservation as pending. A librarian must manually approve it.
            $reservationStatus = 'pending';

            // Create reservation
            $reservation = \App\Models\Reservation::create([
                'patron_detail_id' => $patron->id,
                'bibliographic_record_id' => $record->id,
                'book_item_id' => null,
                'reservation_date' => \Carbon\Carbon::now(),
                'expiry_date' => null, // Will be set when approved by librarian
                'pickup_branch_id' => $patron->branch_id,
                'status' => $reservationStatus,
                'notified' => false,
                'notes' => __('Đăng ký mượn tự động từ OPAC')
            ]);

            \Illuminate\Support\Facades\DB::commit();

            $statusText = $reservationStatus === 'ready' ? __('Sẵn sàng nhận sách') : __('Trong danh sách chờ');

            return response()->json([
                'success' => true,
                'message' => __('Đăng ký mượn sách thành công! Trạng thái: :status', ['status' => $statusText]),
                'data' => [
                    'reservation_id' => $reservation->id,
                    'status' => $reservationStatus,
                    'status_display' => $statusText,
                    'expiry_date' => $reservation->expiry_date ? $reservation->expiry_date->format('d/m/Y') : null
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store book proposal from patrons.
     */
    public function storeProposal(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email_phone' => 'required|string|max:255',
            'book_title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'publisher_year' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ], [
            'fullname.required' => 'Vui lòng nhập họ và tên của bạn.',
            'email_phone.required' => 'Vui lòng nhập email hoặc số điện thoại liên hệ.',
            'book_title.required' => 'Vui lòng nhập nhan đề tài liệu đề xuất.',
            'quantity.required' => 'Vui lòng nhập số lượng đề xuất.',
            'quantity.integer' => 'Số lượng đề xuất phải là số nguyên.',
            'quantity.min' => 'Số lượng đề xuất tối thiểu là 1.',
        ]);

        \App\Models\BookProposal::create([
            'user_id' => auth()->id(),
            'fullname' => $request->fullname,
            'email_phone' => $request->email_phone,
            'book_title' => $request->book_title,
            'author' => $request->author,
            'publisher_year' => $request->publisher_year,
            'quantity' => $request->quantity,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Đề xuất bổ sung tài liệu của bạn đã được gửi thành công.');
    }

    /**
     * Store patron survey response.
     */
    public function storeSurvey(Request $request)
    {
        $request->validate([
            'full_name' => 'nullable|string|max:255',
            'card_number' => 'nullable|string|max:100',
            'email_phone' => 'required|string|max:255',
            'patron_group' => 'required|string|max:100',
            'rating_overall' => 'required|integer|min:1|max:5',
            'survey_category' => 'required|string|max:100',
            'content' => 'required|string|min:5',
            'ratings' => 'nullable|array',
            'ratings.*' => 'nullable|integer|min:1|max:5',
        ], [
            'email_phone.required' => 'Vui lòng nhập Email hoặc Số điện thoại liên hệ.',
            'patron_group.required' => 'Vui lòng chọn nhóm bạn đọc.',
            'rating_overall.required' => 'Vui lòng đánh giá mức độ hài lòng chung.',
            'survey_category.required' => 'Vui lòng chọn chủ đề đóng góp ý kiến.',
            'content.required' => 'Vui lòng nhập nội dung ý kiến đóng góp.',
            'content.min' => 'Nội dung đóng góp phải có ít nhất 5 ký tự.',
        ]);

        $survey = \App\Models\PatronSurvey::create([
            'full_name' => $request->full_name ?: (auth()->check() ? auth()->user()->name : 'Bạn đọc ẩn danh'),
            'card_number' => $request->card_number ?: (auth()->check() ? (auth()->user()->username ?? '') : ''),
            'email_phone' => $request->email_phone,
            'patron_group' => $request->patron_group,
            'rating_overall' => $request->rating_overall,
            'survey_category' => $request->survey_category,
            'content' => $request->content,
            'status' => 'pending',
        ]);

        // Lưu chi tiết từng tiêu chí khảo sát động vào bảng patron_survey_ratings
        $criteria = \App\Models\SurveyCriterion::active()->get();
        foreach ($criteria as $criterion) {
            $ratingVal = $request->input("ratings.{$criterion->id}") ?? $request->input("ratings.{$criterion->code}") ?? $request->input("rating_{$criterion->code}") ?? 5;
            \App\Models\PatronSurveyRating::create([
                'patron_survey_id' => $survey->id,
                'survey_criterion_id' => $criterion->id,
                'rating' => (int) $ratingVal,
            ]);
        }

        return back()->with('success', 'Cảm ơn bạn đã gửi ý kiến khảo sát! Đóng góp của bạn giúp Thư viện nâng cao chất lượng phục vụ.');
    }

    /**
     * Display the user profile page.
     */
    public function profile()
    {
        $user = auth()->user();
        $patron = \App\Models\PatronDetail::where('user_id', $user->id)->with('patronGroup')->first();
        
        $activeLoans = collect();
        $returnedLoans = collect();
        $reservations = collect();
        $stats = [
            'total_borrowed' => 0,
            'active_loans' => 0,
            'overdue_loans' => 0,
            'total_fines' => 0,
        ];

        if ($patron) {
            $activeLoans = \App\Models\LoanTransaction::where('patron_detail_id', $patron->id)
                ->where('status', 'borrowed')
                ->with(['bookItem.bibliographicRecord.fields.subfields', 'policy'])
                ->latest('loan_date')
                ->get();

            $returnedLoans = \App\Models\LoanTransaction::where('patron_detail_id', $patron->id)
                ->where('status', 'returned')
                ->with(['bookItem.bibliographicRecord.fields.subfields'])
                ->latest('return_date')
                ->take(10)
                ->get();

            $reservations = \App\Models\Reservation::where('patron_detail_id', $patron->id)
                ->with(['bibliographicRecord.fields.subfields', 'bookItem'])
                ->latest('reservation_date')
                ->take(10)
                ->get();

            $stats = [
                'total_borrowed' => \App\Models\LoanTransaction::where('patron_detail_id', $patron->id)->count(),
                'active_loans' => $activeLoans->count(),
                'overdue_loans' => $activeLoans->filter(fn($loan) => $loan->isOverdue())->count(),
                'total_fines' => $patron->total_outstanding_fine ?? 0,
            ];
        }

        // Nạp lịch sử khảo sát ý kiến của tài khoản này
        $mySurveys = \App\Models\PatronSurvey::where(function($q) use ($user) {
            if (!empty($user->username)) {
                $q->where('card_number', $user->username)
                  ->orWhere('email_phone', $user->username);
            }
            if (!empty($user->email)) {
                $q->orWhere('email_phone', $user->email);
            }
            $q->orWhere('full_name', $user->name);
        })
        ->with(['ratings.criterion'])
        ->latest()
        ->get();

        $menuItems = SiteNode::getMenuItems('menu');
        $footerItems = SiteNode::getMenuItems('footer');

        return view('site.pages.profile', compact(
            'user', 'patron', 'menuItems', 'footerItems', 
            'stats', 'activeLoans', 'returnedLoans', 'reservations', 'mySurveys'
        ));
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->is_first_login = false;
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công.');
    }

    /**
     * Renew a loan by the student/user.
     */
    public function renewLoan($loanId)
    {
        $user = auth()->user();
        $patron = \App\Models\PatronDetail::where('user_id', $user->id)->first();
        if (!$patron) {
            return response()->json(['success' => false, 'message' => __('Không tìm thấy thông tin độc giả.')], 403);
        }

        $loan = \App\Models\LoanTransaction::where('id', $loanId)
            ->where('patron_detail_id', $patron->id)
            ->first();

        if (!$loan) {
            return response()->json(['success' => false, 'message' => __('Không tìm thấy giao dịch mượn sách.')], 404);
        }

        if (!$loan->canRenew()) {
            return response()->json(['success' => false, 'message' => __('Không thể gia hạn cuốn sách này. Vui lòng kiểm tra lại giới hạn lượt gia hạn hoặc liên hệ thủ thư.')], 400);
        }

        try {
            \DB::beginTransaction();

            $renewalDays = $loan->policy?->renewal_days ?? 7;
            $loan->update([
                'due_date' => \Carbon\Carbon::parse($loan->due_date)->addDays($renewalDays),
                'renewal_count' => $loan->renewal_count + 1,
                'last_renewal_date' => \Carbon\Carbon::now(),
                'notes' => trim(($loan->notes ?? '') . "\n" . __('Độc giả tự gia hạn lúc :time', ['time' => \Carbon\Carbon::now()->format('H:i d/m/Y')]))
            ]);

            \DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('Gia hạn sách thành công!'),
                'due_date' => $loan->due_date->format('d/m/Y'),
                'renewal_count' => $loan->renewal_count,
                'max_renewals' => $loan->policy?->max_renewals ?? 0
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified page.
     */
    public function page($code = 'home')
    {
        // 1. Tìm node duy nhất theo mã (không lọc theo cột language)
        $siteNode = SiteNode::where('node_code', $code)
            ->where('is_active', 1)
            ->first();

        // Xử lý alias đường dẫn chương trình đào tạo
        if (!$siteNode && in_array($code, ['chuong-trinh-dao-tao-vttu', 'chuong-trinh-dao-tao'])) {
            $siteNode = SiteNode::where('node_code', 'khung-chuong-trinh-dao-tao')
                ->where('is_active', 1)
                ->first();
        }

        if (!$siteNode) {
            $builtinPages = [
                'khao-sat-y-kien' => 'Khảo sát ý kiến bạn đọc',
                'khao-sat' => 'Khảo sát ý kiến bạn đọc',
                'sb-khao-sat' => 'Khảo sát ý kiến bạn đọc',
                'de-nghi-bo-sung' => 'Đề nghị bổ sung tài liệu',
                'sb-de-nghi-bo-sung' => 'Đề nghị bổ sung tài liệu',
                'co-so-du-lieu' => 'Cơ sở dữ liệu',
                'sb-co-so-du-lieu' => 'Cơ sở dữ liệu',
                'tai-lieu-so' => 'Tài liệu số',
                'tra-cuu-tai-lieu-so' => 'Tài liệu số',
                'tra-cuu-tai-lieu-giay' => 'Tra cứu OPAC',
                'dang-nhap-tai-khoan' => 'Đăng nhập tài khoản',
                'ban-do-website-thu-vien' => 'Sơ đồ trang',
                'huong-dan' => 'Cẩm nang hướng dẫn',
                'tai-app-mobile' => 'Tải ứng dụng trên điện thoại',
            ];

            if ($code === 'ban-do-website-thu-vien') {
                return $this->sitemap();
            }

            if (isset($builtinPages[$code])) {
                $siteNode = SiteNode::firstOrCreate(
                    ['node_code' => $code],
                    [
                        'node_name' => $builtinPages[$code],
                        'display_name' => $builtinPages[$code],
                        'is_active' => 1,
                        'allow_guest' => 1,
                        'access_type' => 'public',
                        'display_type' => 'none',
                        'language' => 'vi',
                    ]
                );

                if ($siteNode->display_type === 'menu') {
                    $siteNode->update(['display_type' => 'none']);
                }
            } else {
                if ($code === 'home') {
                    return redirect('/');
                }
                abort(404);
            }
        }

        // Tự động chuyển hướng nếu có thiết lập redirect_to (tránh vòng lặp chuyển hướng)
        if (!empty($siteNode->redirect_to) && trim($siteNode->redirect_to, '/') !== $code && trim($siteNode->redirect_to, '/') !== request()->path()) {
            return redirect($siteNode->getUrl());
        }

        // Check access permissions
        if (!$siteNode->canAccess(auth()->user())) {
            if (auth()->guest()) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem trang này');
            }
            abort(403, 'Bạn không có quyền truy cập trang này');
        }

        // Đảm bảo các node khảo sát không hiển thị trên thanh menu chính
        \App\Models\SiteNode::where(function($q) {
            $q->where('node_code', 'LIKE', '%khao-sat%')
              ->orWhere('display_name', 'LIKE', '%Khảo sát%');
        })->where('display_type', 'menu')->update(['display_type' => 'none']);

        // Nạp menu với eager loading children
        $menuItems = SiteNode::getMenuItems('menu');
        $footerItems = SiteNode::getMenuItems('footer');

        // Tạo breadcrumb
        $breadcrumb = [];
        $tempNode = $siteNode;
        while ($tempNode) {
            array_unshift($breadcrumb, [
                'name' => $tempNode->display_name,
                'url' => route('site.page', $tempNode->node_code)
            ]);
            $tempNode = $tempNode->parent;
        }

        // Khởi tạo extraData để tránh lỗi undefined
        $extraData = [];

        // Xử lý nạp dữ liệu cho trang chủ hoặc Ajax nạp tab sidebar
        $isHome = ($code === 'home' || $siteNode->masterpage === 'home' || request()->query('preview_template') === 'home');
        $isAjaxSidebar = request()->ajax() && request()->has('resource_type');

        if ($isHome || $isAjaxSidebar) {
            // Nạp các dữ liệu chung cho Home
            $extraData['homeNews'] = \App\Models\News::published()
                ->whereHas('category', function($q) {
                    $q->where('slug', 'tin-tuc');
                })
                ->featured()
                ->latest()
                ->take(5)
                ->get();
                
            $extraData['homeAnnouncements'] = \App\Models\News::published()
                ->whereHas('category', function($q) {
                    $q->where('slug', 'thong-bao');
                })
                ->latest()
                ->take(5)
                ->get();

            $extraData['tabNews'] = \App\Models\News::published()
                ->latest()
                ->take(6)
                ->get();
            $extraData['newResources'] = \App\Models\DigitalResource::with('folder')
                ->where('status', 'published')
                ->latest()
                ->take(8)
                ->get();

            $extraData['medicalResources'] = \App\Models\DigitalResource::whereHas('folder', function($q) {
                    $q->where('folder_name', 'LIKE', '%Y khoa%');
                })
                ->where('status', 'published')
                ->take(4)
                ->get();

            $extraData['newBooks'] = \App\Models\BibliographicRecord::with(['fields.subfields', 'items'])
                ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                ->latest()
                ->take(8)
                ->get();

            // Logic Sidebar (Mới | Nổi bật)
            $sidebarType = request('resource_type', 'new');
            $sidebarQuery = \App\Models\BibliographicRecord::with(['fields.subfields'])
                ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED);

            if ($sidebarType === 'featured') {
                $sidebarQuery->where('is_featured', 1);
            } else {
                $sidebarQuery->where('record_type', 'book');
            }

            $extraData['sidebarBooks'] = $sidebarQuery->latest()
                ->take(10)
                ->get();
            $extraData['activeResourceType'] = $sidebarType;

            // Nếu là AJAX gọi sidebar, trả về ngay partial view
            if ($isAjaxSidebar) {
                return view('site.pages.partials.sidebar-books', [
                    'sidebarBooks' => $extraData['sidebarBooks']
                ]);
            }
        }

        // Nạp dữ liệu sách nếu dùng template opac
        if ($siteNode->masterpage === 'opac' || request()->query('preview_template') === 'opac') {
            $extraData['newBooks'] = \App\Models\BibliographicRecord::with(['fields.subfields', 'items'])
                ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                ->latest()
                ->take(8)
                ->get();
                
            $extraData['books'] = \App\Models\BibliographicRecord::with(['fields.subfields', 'items'])
                ->where('status', \App\Models\BibliographicRecord::STATUS_APPROVED)
                ->latest()
                ->paginate(12);
        }

        // Nạp dữ liệu tin tức nếu dùng template news
        if ($siteNode->masterpage === 'news' || request()->query('preview_template') === 'news' || $code === 'tin-tuc') {
            $extraData['news'] = \App\Models\News::where('status', 'published')
                ->with(['category', 'author', 'tags'])
                ->orderBy('sort_order', 'asc')
                ->orderBy('published_at', 'desc')
                ->paginate(12);
            
            // Lấy tổng số bạn đọc (User) cho template news
            $extraData['totalUsers'] = \App\Models\User::count();
            
            // Lấy 3 avatar người dùng mới nhất (từ PatronDetail nếu có profile_image)
            $extraData['latestPatrons'] = \App\Models\PatronDetail::whereNotNull('profile_image')
                ->latest()
                ->take(3)
                ->get();
        }

        // Nạp dữ liệu tài liệu số nếu truy cập trang tài liệu số
        if ($code === 'tai-lieu-so' || $siteNode->masterpage === 'digital-resources') {
            $sort = request()->query('sort', 'oldest_updated');
            $field = request()->query('field', 'title');
            $keyword = request()->query('q');
            $folderId = request()->query('folder_id');

            $query = \App\Models\DigitalResource::with('folder')->where('status', 'published');
            
            if ($folderId) {
                $query->where('folder_id', $folderId);
            }

            if ($keyword) {
                $query->where(function($q) use ($field, $keyword) {
                    if ($field === 'author') {
                        $q->where('authors', 'like', '%' . $keyword . '%')
                          ->orWhere('secondary_authors', 'like', '%' . $keyword . '%');
                    } elseif ($field === 'subject') {
                        $q->where('subjects', 'like', '%' . $keyword . '%')
                          ->orWhere('topics', 'like', '%' . $keyword . '%');
                    } else {
                        $q->where('title', 'like', '%' . $keyword . '%')
                          ->orWhere('description', 'like', '%' . $keyword . '%');
                    }
                });
            }

            switch ($sort) {
                case 'latest':
                    $query->latest();
                    break;
                case 'most_viewed':
                    $query->orderBy('view_count', 'desc');
                    break;
                case 'most_downloaded':
                    $query->orderBy('download_count', 'desc');
                    break;
                case 'oldest_updated':
                default:
                    $query->orderBy('updated_at', 'asc');
                    $sort = 'oldest_updated'; // dam bao sort luon hop le
                    break;
            }
            
            $extraData['resources'] = $query->paginate(15)->withQueryString();
            $extraData['totalCount'] = $query->count();
            $extraData['currentSort'] = $sort;
            $extraData['currentField'] = $field;
            $extraData['keyword'] = $keyword;
            $extraData['currentFolderId'] = $folderId;
            $extraData['folders'] = \App\Models\DigitalFolder::where(function($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })->where('is_active', true)
              ->with(['children' => function($q) {
                  $q->where('is_active', true)->withCount('resources')->orderBy('sort_order');
              }])
              ->withCount('resources')
              ->orderBy('sort_order')
              ->get();
        }

        // Nạp dữ liệu OER nếu truy cập trang tài nguyên giáo dục mở
        if ($code === 'tai-nguyen-giao-duc-mo' || $siteNode->masterpage === 'oer') {
            $sort = request()->query('sort', 'latest');
            $subjectId = request()->query('subject_id');
            $keyword = request()->query('q');
            
            $query = \App\Models\OpenEducationalResource::where('is_active', true)->with('subject');
            
            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }
            
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('author', 'like', '%' . $keyword . '%')
                      ->orWhere('publisher', 'like', '%' . $keyword . '%');
                });
            }

            switch ($sort) {
                case 'oldest_updated':
                    $query->orderBy('updated_at', 'asc');
                    break;
                case 'most_viewed':
                    $query->orderBy('view_count', 'desc');
                    break;
                case 'most_downloaded':
                    $query->orderBy('download_count', 'desc');
                    break;
                case 'latest':
                default:
                    $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
                    break;
            }
            
            $extraData['oerResources'] = $query->paginate(15)->withQueryString();
            $extraData['totalOerCount'] = \App\Models\OpenEducationalResource::where('is_active', true)->count();
            $extraData['oerSubjects'] = \App\Models\OerSubject::where('is_active', true)->withCount('resources')->orderBy('sort_order')->get();
            $extraData['currentSort'] = $sort;
            $extraData['currentSubjectId'] = $subjectId;
            $extraData['keyword'] = $keyword;

            // Also set legacy variables for compatibility
            $extraData['resources'] = $extraData['oerResources'];
            $extraData['totalCount'] = $extraData['totalOerCount'];
        }

        // Nạp dữ liệu CSDL trực tuyến nếu truy cập trang cơ sở dữ liệu
        if ($code === 'co-so-du-lieu') {
            $extraData['onlineDatabases'] = \App\Models\OnlineDatabase::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        // 1. Kiểm tra template preview qua query param
        $previewTemplate = request()->query('preview_template');
        if ($previewTemplate && view()->exists("site.pages.{$previewTemplate}")) {
            return view("site.pages.{$previewTemplate}", array_merge([
                'node' => $siteNode,
                'siteNode' => $siteNode,
                'menuItems' => $menuItems,
                'footerItems' => $footerItems,
                'breadcrumb' => $breadcrumb
            ], $extraData));
        }

        // 2. Ưu tiên render theo masterpage nếu có
        if ($siteNode->masterpage && view()->exists("site.pages.{$siteNode->masterpage}")) {
            return view("site.pages.{$siteNode->masterpage}", array_merge([
                'node' => $siteNode,
                'siteNode' => $siteNode,
                'menuItems' => $menuItems,
                'footerItems' => $footerItems,
                'breadcrumb' => $breadcrumb
            ], $extraData));
        }

        // 3. Fallback theo node_code hoặc code của siteNode
        $targetView = $siteNode->node_code;
        if (view()->exists("site.pages.{$targetView}")) {
            return view("site.pages.{$targetView}", array_merge([
                'node' => $siteNode,
                'siteNode' => $siteNode,
                'menuItems' => $menuItems,
                'footerItems' => $footerItems,
                'breadcrumb' => $breadcrumb
            ], $extraData));
        }

        // 4. Mặc định render template chung
        return view('site.page', array_merge([
            'node' => $siteNode,
            'siteNode' => $siteNode,
            'menuItems' => $menuItems,
            'footerItems' => $footerItems,
            'breadcrumb' => $breadcrumb
        ], $extraData));
    }

    /**
     * Display sitemap
     */
    public function sitemap()
    {
        $tree = SiteNode::getTree();
        
        return view('site.sitemap', compact('tree'));
    }

    /**
     * Generate XML sitemap
     */
    public function xmlSitemap()
    {
        $nodes = SiteNode::active()->get();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($nodes as $node) {
            if ($node->canAccess() && ($node->hasContent() || $node->route_name || $node->url)) {
                $xml .= '<url>';
                $xml .= '<loc>' . url($node->getUrl()) . '</loc>';
                $xml .= '<lastmod>' . $node->updated_at->format('Y-m-d') . '</lastmod>';
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';
            }
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Display details of an online database without site node in DB.
     */
    public function onlineDatabaseDetail(Request $request, $id = null)
    {
        $CSDLId = $id ?: $request->query('CSDLId');
        $CSDLName = $request->query('CSDLName') ?: $request->query('name') ?: 'Cơ sở dữ liệu';

        // Search in OnlineDatabase model
        $database = null;
        $parent = null;
        $sidebarItems = collect();
        $sectionLabel = 'Tài nguyên';
        try {
            if ($CSDLId) {
                $database = \App\Models\OnlineDatabase::where('is_active', true)->find($CSDLId);
                if ($database) {
                    $CSDLName = $database->title;
                }
            }
            $parent = SiteNode::where('node_code', 'tai-nguyen')->first();
            if ($parent) {
                $sidebarItems = $parent->activeChildren()->orderBy('sort_order')->get();
                $sectionLabel = $parent->display_name;
            }
        } catch (\Exception $e) {
            // Fail silently and fallback
        }
        
        $node = new SiteNode();
        $node->id = 9999;
        $node->display_name = $CSDLName;
        $node->node_code = 'co-so-du-lieu-detail';
        $node->icon = 'fas fa-database';
        
        if ($parent) {
            $node->parent = $parent;
            $node->parent_id = $parent->id;
        }

        // Menu and footer items for layouts.site
        $menuItems = collect();
        $footerItems = collect();
        $breadcrumb = [];
        try {
            $menuItems = SiteNode::getMenuItems('menu');
            $footerItems = SiteNode::getMenuItems('footer');
            if ($parent) {
                $breadcrumb = $parent->getBreadcrumb();
            }
        } catch (\Exception $e) {
            // Fail silently
        }
        $breadcrumb[] = [
            'name' => $CSDLName,
            'url' => ''
        ];

        return view('site.pages.online-database-detail', compact(
            'CSDLId',
            'CSDLName',
            'database',
            'node',
            'menuItems',
            'footerItems',
            'breadcrumb',
            'parent',
            'sidebarItems',
            'sectionLabel'
        ));
    }

    /**
     * Serve image from PORTAL_NEWS_MEDIA dynamically.
     */
    public function viewImageMedia(Request $request)
    {
        $imageId = $request->query('imageId');
        $ext = $request->query('ext') ?: 'png';
        
        if (empty($imageId)) {
            abort(404);
        }

        // Ensure target directory exists
        $targetDir = storage_path('app/public/news-media');
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $filename = "media_{$imageId}.{$ext}";
        $localPath = $targetDir . '/' . $filename;
        $fileFound = false;

        // 1. Try local news_media table in MySQL first
        try {
            $localMedia = \Illuminate\Support\Facades\DB::table('news_media')->find($imageId);
            if ($localMedia && !empty($localMedia->file_path)) {
                $fullLocalPath = public_path($localMedia->file_path);
                if (file_exists($fullLocalPath)) {
                    $localPath = $fullLocalPath;
                    $ext = $localMedia->media_extension ?: pathinfo($localPath, PATHINFO_EXTENSION);
                    $fileFound = true;
                }
            }
        } catch (\Exception $e) {
            // Ignore and fallback
        }

        // 2. Try to find the file locally in news-media directory if already downloaded
        if (!$fileFound && !file_exists($localPath)) {
            $matchingFiles = glob($targetDir . "/media_{$imageId}.*");
            if (!empty($matchingFiles)) {
                $localPath = $matchingFiles[0];
                $filename = basename($localPath);
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $fileFound = true;
            }
        } else if (file_exists($localPath)) {
            $fileFound = true;
        }

        // 3. If not found locally, fetch it from SQL Server on demand (Self-healing proxy!)
        if (!$fileFound) {
            try {
                $oldConn = new \PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
                $oldConn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                $stmt = $oldConn->prepare("SELECT MEDIAEX, MEDIACONTENT FROM PORTAL_NEWS_MEDIA WHERE MEDIAID = :id");
                $stmt->execute([':id' => $imageId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($row) {
                    $dbExt = $row['MEDIAEX'] ?: $ext;
                    $imageBinary = $row['MEDIACONTENT'];
                    if (is_resource($imageBinary)) {
                        $imageBinary = stream_get_contents($imageBinary);
                    }

                    if (!empty($imageBinary)) {
                        $filename = "media_{$imageId}.{$dbExt}";
                        $localPath = $targetDir . '/' . $filename;
                        file_put_contents($localPath, $imageBinary);
                        $ext = $dbExt;
                        $fileFound = true;
                    }
                }
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        // 4. Serve the file if found
        if ($fileFound && file_exists($localPath)) {
            $mimeType = match (strtolower($ext)) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
                'svg' => 'image/svg+xml',
                default => 'image/png',
            };

            return response()->file($localPath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        abort(404);
    }
}

