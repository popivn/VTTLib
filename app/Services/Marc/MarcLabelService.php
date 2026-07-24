<?php

namespace App\Services\Marc;

use App\Models\MarcTagDefinition;
use App\Models\MarcSubfieldDefinition;

class MarcLabelService
{
    /**
     * Memory cache for loaded tag definitions
     */
    protected static ?array $tagCache = null;

    /**
     * Memory cache for loaded subfield definitions
     */
    protected static ?array $subfieldCache = null;

    /**
     * Preload all tag and subfield definitions into memory to eliminate N+1 queries
     */
    public static function preload(): void
    {
        if (self::$tagCache === null) {
            self::$tagCache = MarcTagDefinition::pluck('label', 'tag')->toArray();
        }

        if (self::$subfieldCache === null) {
            self::$subfieldCache = [];
            $subfieldDefs = MarcSubfieldDefinition::with('tagDefinition')->get();
            foreach ($subfieldDefs as $def) {
                if ($def->tagDefinition && !empty($def->label)) {
                    $key = $def->tagDefinition->tag . '_' . $def->code;
                    self::$subfieldCache[$key] = $def->label;
                }
            }
        }
    }

    /**
     * Get label for a MARC tag (DB first with in-memory cache, fallback to config)
     */
    public function getTagLabel(string $tag): string
    {
        self::preload();

        if (isset(self::$tagCache[$tag]) && !empty(self::$tagCache[$tag])) {
            return self::$tagCache[$tag];
        }

        $configLabels = config('marc.tags', []);
        return $configLabels[$tag] ?? __('Trường MARC (:tag)', ['tag' => $tag]);
    }

    /**
     * Get label for a MARC subfield
     */
    public function getSubfieldLabel(string $tag, string $code): string
    {
        self::preload();

        $key = $tag . '_' . $code;
        if (isset(self::$subfieldCache[$key]) && !empty(self::$subfieldCache[$key])) {
            return self::$subfieldCache[$key];
        }

        return __('Trường con :code', ['code' => $code]);
    }

    /**
     * Reset memory cache (useful for testing or background queues)
     */
    public static function resetCache(): void
    {
        self::$tagCache = null;
        self::$subfieldCache = null;
    }
}
