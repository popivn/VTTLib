<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class News extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'featured_image',
        'category_id',
        'author_id',
        'status',
        'published_at',
        'expired_at',
        'is_featured',
        'sort_order',
        'allow_comments',
        'view_count',
        'like_count',
        'comment_count',
        'language',
        'customer_id',
        'article_type_id',
        'news_author_id',
        'old_item_id',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'comment_count' => 'integer'
    ];

    protected $dates = [
        'published_at',
        'expired_at',
        'deleted_at'
    ];

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function articleType()
    {
        return $this->belongsTo(NewsArticleType::class, 'article_type_id');
    }

    public function newsAuthor()
    {
        return $this->belongsTo(NewsAuthor::class, 'news_author_id');
    }

    public function media()
    {
        return $this->hasMany(NewsMedia::class, 'news_id');
    }

    public function tags()
    {
        return $this->belongsToMany(NewsTag::class, 'news_tag', 'news_id', 'tag_id');
    }

    /**
     * Scopes
     */
    public function scopePublished(Builder $query)
    {
        return $query->where('status', 'published')
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expired_at')
                          ->orWhere('expired_at', '>', now());
                    });
    }

    public function scopeDraft(Builder $query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeArchived(Builder $query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeFeatured(Builder $query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByLanguage(Builder $query, $language = 'vi')
    {
        return $query->where('language', $language);
    }

    public function scopeByCategory(Builder $query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor(Builder $query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeByTag(Builder $query, $tagSlug)
    {
        return $query->whereHas('tags', function ($q) use ($tagSlug) {
            $q->where('slug', $tagSlug);
        });
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('summary', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Accessors & Mutators
     */
    public function getUrlAttribute()
    {
        return '/tin-tuc-chi-tiet/' . $this->slug;
    }

    public function getFormattedPublishedAtAttribute()
    {
        return $this->published_at ? $this->published_at->format('d/m/Y H:i') : '';
    }

    public function getVideoUrlAttribute()
    {
        if (!$this->content) return null;

        // Extract YouTube URL (first match only)
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $this->content, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Extract Vimeo URL (first match only)
        if (preg_match('/vimeo\.com\/(\d+)/i', $this->content, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        // Extract Google Drive URL (first match only)
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/i', $this->content, $matches)) {
            return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
        }

        // Extract video tag src (first match only)
        if (preg_match('/<video[^>]+src=["\']([^"\']+)["\']/i', $this->content, $matches)) {
            return $matches[1];
        }

        // Extract iframe src (first match only)
        if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $this->content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'draft' => 'Soạn thảo',
            'pending' => 'Chờ duyệt',
            'published' => 'Đã đăng',
            'archived' => 'Lưu trữ',
            default => 'Không xác định'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'draft' => 'gray',
            'pending' => 'yellow',
            'published' => 'green',
            'archived' => 'red',
            default => 'gray'
        };
    }

    /**
     * Methods
     */
    public function isPublished()
    {
        return $this->status === 'published' && 
               (!$this->published_at || $this->published_at->isPast()) &&
               (!$this->expired_at || $this->expired_at->isFuture());
    }

    public function isExpired()
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function canBePublished()
    {
        return in_array($this->status, ['draft', 'pending']) && 
               !empty($this->title) && 
               !empty($this->content);
    }

    public function publish()
    {
        $this->status = 'published';
        $this->published_at = now();
        $this->save();
    }

    public function archive()
    {
        $this->status = 'archived';
        $this->save();
    }

    public function incrementView()
    {
        $this->increment('view_count');
    }

    public function incrementLike()
    {
        $this->increment('like_count');
    }

    public function decrementLike()
    {
        $this->decrement('like_count');
    }

    public function getExcerpt($length = 150)
    {
        if ($this->summary) {
            return Str::limit(strip_tags($this->summary), $length);
        }
        return Str::limit(strip_tags($this->content), $length);
    }

    public function getReadingTime()
    {
        $words = str_word_count(strip_tags($this->content));
        $minutes = ceil($words / 200); // Assume 200 words per minute
        return $minutes . ' phút đọc';
    }

    public function getRelatedNews($limit = 5)
    {
        return self::published()
            ->byLanguage($this->language)
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                $query->where('category_id', $this->category_id)
                      ->orWhereHas('tags', function ($q) {
                          $tagIds = $this->tags->pluck('id');
                          $q->whereIn('tag_id', $tagIds);
                      });
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function syncTags(array $tagNames)
    {
        $tagIds = [];
        
        foreach ($tagNames as $tagName) {
            $tag = NewsTag::firstOrCreate([
                'slug' => Str::slug($tagName),
                'language' => $this->language
            ], [
                'name' => $tagName,
                'is_active' => true
            ]);
            
            $tagIds[] = $tag->id;
        }
        
        $this->tags()->sync($tagIds);
    }

    /**
     * Static methods
     */
    public static function getFeaturedNews($limit = 5, $language = 'vi')
    {
        return self::published()
            ->featured()
            ->byLanguage($language)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getLatestNews($limit = 10, $language = 'vi')
    {
        return self::published()
            ->byLanguage($language)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getPopularNews($limit = 5, $language = 'vi')
    {
        return self::published()
            ->byLanguage($language)
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title);
            }
            
            if (empty($news->author_id) && auth()->check()) {
                $news->author_id = auth()->id();
            }
        });

        static::updating(function ($news) {
            if ($news->isDirty('title')) {
                $news->slug = Str::slug($news->title);
            }
        });
    }
}
