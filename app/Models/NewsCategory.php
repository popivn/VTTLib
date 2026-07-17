<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class NewsCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'language',
        'customer_id',
        'meta_title',
        'meta_description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Relationships
     */
    public function parent()
    {
        return $this->belongsTo(NewsCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(NewsCategory::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function activeChildren()
    {
        return $this->children()->where('is_active', true);
    }

    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }

    public function publishedNews()
    {
        return $this->news()->published();
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByLanguage(Builder $query, $language = 'vi')
    {
        return $query->where('language', $language);
    }

    /**
     * Accessors & Mutators
     */
    public function getUrlAttribute()
    {
        return '/tin-tuc/chuyen-muc/' . $this->slug;
    }

    public function getNewsCountAttribute()
    {
        return $this->publishedNews()->count();
    }

    /**
     * Methods
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function getDepth()
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    public function getBreadcrumb()
    {
        $breadcrumb = [];
        $current = $this;
        
        while ($current) {
            array_unshift($breadcrumb, [
                'name' => $current->name,
                'url' => $current->url
            ]);
            $current = $current->parent;
        }
        
        return $breadcrumb;
    }

    public function getLatestNews($limit = 10)
    {
        return $this->publishedNews()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Static methods
     */
    public static function getTree($language = 'vi')
    {
        return self::with(['activeChildren' => function ($query) {
                $query->withCount('publishedNews');
            }])
            ->active()
            ->root()
            ->byLanguage($language)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public static function getActiveCategories($language = 'vi')
    {
        return self::active()
            ->byLanguage($language)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
