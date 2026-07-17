<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display news listing page.
     */
    public function index(Request $request)
    {
        $query = News::where('status', 'published')
            ->with(['category', 'author', 'tags'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('published_at', 'desc');

        // Filters
        if ($request->filled('category')) {
            $category = NewsCategory::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        if ($request->filled('tag')) {
            $tag = NewsTag::where('slug', $request->tag)->first();
            if ($tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('news_tags.id', $tag->id);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $news = $query->paginate(12);
        
        // Get featured news
        $featuredNews = News::where('status', 'published')
            ->orderBy('sort_order', 'asc')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        // Get categories
        $categories = NewsCategory::active()
            ->withCount('publishedNews')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Get popular tags
        $popularTags = NewsTag::getPopularTags(15);

        $menuItems = \App\Models\SiteNode::getMenuItems('menu');
        $footerItems = \App\Models\SiteNode::getMenuItems('footer');
        $node = \App\Models\SiteNode::where('node_code', 'tin-tuc')->first() ?? new \App\Models\SiteNode(['node_code' => 'tin-tuc', 'display_name' => 'Tin tức', 'icon' => 'fas fa-newspaper']);

        return view('site.pages.news-index', compact('news', 'featuredNews', 'categories', 'popularTags', 'menuItems', 'footerItems', 'node'));
    }

    /**
     * Display single news article.
     */
    public function show($slug)
    {
        $news = News::with(['category', 'author', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment view count
        $news->incrementView();

        // Get related news
        $relatedNews = $news->getRelatedNews(5);

        // Get previous and next news
        $previousNews = News::published()
            ->where('published_at', '<', $news->published_at)
            ->orderBy('published_at', 'desc')
            ->first();

        $nextNews = News::published()
            ->where('published_at', '>', $news->published_at)
            ->orderBy('published_at', 'asc')
            ->first();

        $menuItems = \App\Models\SiteNode::getMenuItems('menu');
        $footerItems = \App\Models\SiteNode::getMenuItems('footer');
        $node = \App\Models\SiteNode::where('node_code', 'tin-tuc')->first() ?? new \App\Models\SiteNode(['node_code' => 'tin-tuc', 'display_name' => 'Tin tức', 'icon' => 'fas fa-newspaper']);

        return view('site.pages.news-show', compact(
            'news', 
            'relatedNews', 
            'previousNews', 
            'nextNews',
            'menuItems',
            'footerItems',
            'node'
        ));
    }

    /**
     * Display news by category.
     */
    public function category($slug)
    {
        $category = NewsCategory::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $news = News::where('status', 'published')
            ->where('category_id', $category->id)
            ->with(['author', 'tags'])
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // Get breadcrumb
        $breadcrumb = $category->getBreadcrumb();

        $menuItems = \App\Models\SiteNode::getMenuItems('menu');
        $footerItems = \App\Models\SiteNode::getMenuItems('footer');
        $node = \App\Models\SiteNode::where('node_code', 'tin-tuc')->first() ?? new \App\Models\SiteNode(['node_code' => 'tin-tuc', 'display_name' => 'Tin tức', 'icon' => 'fas fa-newspaper']);
        $categories = NewsCategory::active()->withCount('publishedNews')->orderBy('sort_order')->orderBy('name')->get();
        $popularTags = NewsTag::getPopularTags(15);

        return view('site.pages.news-index', [
            'news' => $news,
            'category' => $category,
            'breadcrumb' => $breadcrumb,
            'menuItems' => $menuItems,
            'footerItems' => $footerItems,
            'node' => $node,
            'categories' => $categories,
            'popularTags' => $popularTags
        ]);
    }

    /**
     * Display news by tag.
     */
    public function tag($slug)
    {
        $tag = NewsTag::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $news = News::where('status', 'published')
            ->whereHas('tags', function ($q) use ($tag) {
                $q->where('news_tags.id', $tag->id);
            })
            ->with(['category', 'author', 'tags'])
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $menuItems = \App\Models\SiteNode::getMenuItems('menu');
        $footerItems = \App\Models\SiteNode::getMenuItems('footer');
        $node = \App\Models\SiteNode::where('node_code', 'tin-tuc')->first() ?? new \App\Models\SiteNode(['node_code' => 'tin-tuc', 'display_name' => 'Tin tức', 'icon' => 'fas fa-newspaper']);
        $categories = NewsCategory::active()->withCount('publishedNews')->orderBy('sort_order')->orderBy('name')->get();
        $popularTags = NewsTag::getPopularTags(15);

        return view('site.pages.news-index', [
            'news' => $news,
            'tag' => $tag,
            'menuItems' => $menuItems,
            'footerItems' => $footerItems,
            'node' => $node,
            'categories' => $categories,
            'popularTags' => $popularTags
        ]);
    }

    /**
     * Display featured news.
     */
    public function featured()
    {
        $news = News::featured()
            ->published()
            ->with(['category', 'author', 'tags'])
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        $menuItems = \App\Models\SiteNode::getMenuItems('menu');
        $footerItems = \App\Models\SiteNode::getMenuItems('footer');
        $node = \App\Models\SiteNode::where('node_code', 'tin-tuc')->first() ?? new \App\Models\SiteNode(['node_code' => 'tin-tuc', 'display_name' => 'Tin tức', 'icon' => 'fas fa-newspaper']);
        $categories = NewsCategory::active()->withCount('publishedNews')->orderBy('sort_order')->orderBy('name')->get();
        $popularTags = NewsTag::getPopularTags(15);

        return view('site.pages.news-index', [
            'news' => $news,
            'isFeatured' => true,
            'menuItems' => $menuItems,
            'footerItems' => $footerItems,
            'node' => $node,
            'categories' => $categories,
            'popularTags' => $popularTags
        ]);
    }

    /**
     * Search news.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return redirect()->route('news.index');
        }

        $news = News::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->with(['category', 'author', 'tags'])
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        // Get search suggestions
        $suggestions = News::published()
            ->where('title', 'like', "%{$query}%")
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->pluck('title');

        $menuItems = \App\Models\SiteNode::getMenuItems('menu');
        $footerItems = \App\Models\SiteNode::getMenuItems('footer');
        $node = \App\Models\SiteNode::where('node_code', 'tin-tuc')->first() ?? new \App\Models\SiteNode(['node_code' => 'tin-tuc', 'display_name' => 'Tin tức', 'icon' => 'fas fa-newspaper']);
        $categories = NewsCategory::active()->withCount('publishedNews')->orderBy('sort_order')->orderBy('name')->get();
        $popularTags = NewsTag::getPopularTags(15);

        return view('site.pages.news-index', [
            'news' => $news,
            'searchQuery' => $query,
            'suggestions' => $suggestions,
            'menuItems' => $menuItems,
            'footerItems' => $footerItems,
            'node' => $node,
            'categories' => $categories,
            'popularTags' => $popularTags
        ]);
    }

    /**
     * RSS feed.
     */
    public function rss()
    {
        $news = News::published()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        return response()
            ->view('news.rss', compact('news'))
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }

    /**
     * Sitemap for news.
     */
    public function sitemap()
    {
        $categories = NewsCategory::active()->get();
        $tags = NewsTag::active()->get();
        $news = News::published()
            ->orderBy('published_at', 'desc')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('news.sitemap', compact('categories', 'tags', 'news'))
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    /**
     * API endpoint for news (JSON).
     */
    public function api(Request $request)
    {
        $query = News::published()
            ->with(['category:id,name,slug', 'author:id,name', 'tags:id,name,slug']);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('news_tags.id', $request->tag_id);
            });
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        if ($request->filled('limit')) {
            $query->limit($request->limit);
        }

        $news = $query->orderBy('published_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $news,
            'total' => $news->count()
        ]);
    }

    /**
     * Like/unlike news (AJAX).
     */
    public function like(News $news)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để thích bài viết!'
            ], 401);
        }

        // Simple like system - in real app, you'd have a separate likes table
        $news->incrementLike();

        return response()->json([
            'success' => true,
            'message' => 'Đã thích bài viết!',
            'like_count' => $news->like_count
        ]);
    }
}
