<?php

namespace App\Services\Marc;

use App\Models\MarcFramework;
use App\Models\MarcTagDefinition;
use App\Models\MarcSubfieldDefinition;
use Illuminate\Support\Facades\DB;

class MarcFrameworkService
{
    protected MarcLabelService $labelService;

    public function __construct(MarcLabelService $labelService)
    {
        $this->labelService = $labelService;
    }

    /**
     * Auto-detect matching frameworks in database based on extracted tags
     */
    public function detectMatchingFrameworks(array $allTags): array
    {
        $extractedTagCodes = array_keys($allTags);
        $totalExtracted = count($extractedTagCodes);
        $matchingFrameworks = [];

        $allFrameworks = MarcFramework::where('is_active', true)->with('tags')->get();

        foreach ($allFrameworks as $fw) {
            $fwTags = $fw->tags->pluck('tag')->toArray();
            if (empty($fwTags)) {
                continue;
            }

            $matchedTags = array_intersect($extractedTagCodes, $fwTags);
            $matchRatio = $totalExtracted > 0 ? (count($matchedTags) / $totalExtracted) : 0;

            $matchingFrameworks[] = [
                'id' => $fw->id,
                'name' => $fw->name,
                'code' => $fw->code,
                'matched_tags' => count($matchedTags),
                'total_file_tags' => $totalExtracted,
                'total_fw_tags' => count($fwTags),
                'match_ratio' => round($matchRatio * 100),
                'is_compatible' => $matchRatio >= 0.5,
            ];
        }

        // Sort compatible first, then by match ratio descending
        usort($matchingFrameworks, function ($a, $b) {
            if ($a['is_compatible'] !== $b['is_compatible']) {
                return $b['is_compatible'] <=> $a['is_compatible'];
            }
            return $b['match_ratio'] <=> $a['match_ratio'];
        });

        return $matchingFrameworks;
    }

    /**
     * Ensure definitions exist for all parsed tags/subfields and link them to chosen framework
     */
    public function ensureDefinitionsForParsedRecords(MarcFramework $framework, array $parsedRecords): void
    {
        $existingFrameworkTagIds = $framework->tags()->pluck('marc_tag_definitions.id')->toArray();

        foreach ($parsedRecords as $record) {
            foreach ($record['fields'] as $tag => $fieldDataArr) {
                $tagDef = MarcTagDefinition::firstOrCreate(
                    ['tag' => $tag],
                    [
                        'label' => $this->labelService->getTagLabel($tag),
                        'repeatable' => true,
                        'mandatory' => false
                    ]
                );

                if (!in_array($tagDef->id, $existingFrameworkTagIds)) {
                    $framework->tags()->attach($tagDef->id);
                    $existingFrameworkTagIds[] = $tagDef->id;
                }

                foreach ($fieldDataArr as $fieldData) {
                    if (isset($fieldData['subfields'])) {
                        foreach ($fieldData['subfields'] as $sf) {
                            $code = $sf['code'];
                            if ($code === '_') continue;

                            MarcSubfieldDefinition::firstOrCreate(
                                [
                                    'marc_tag_definition_id' => $tagDef->id,
                                    'code' => $code
                                ],
                                [
                                    'label' => $this->labelService->getSubfieldLabel($tag, $code),
                                    'repeatable' => true,
                                    'mandatory' => false
                                ]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Create a new MarcFramework from extracted file tags
     */
    public function saveFrameworkFromExtractedData(string $name, string $code, ?string $description, array $allTags): MarcFramework
    {
        return DB::transaction(function () use ($name, $code, $description, $allTags) {
            $framework = MarcFramework::create([
                'name' => $name,
                'code' => strtoupper($code),
                'description' => $description ?? __('Tự động trích xuất từ file MARC'),
                'is_active' => true
            ]);

            foreach ($allTags as $tagData) {
                $tagDef = MarcTagDefinition::firstOrCreate(
                    ['tag' => $tagData['tag']],
                    [
                        'label' => $tagData['label'] ?? $this->labelService->getTagLabel($tagData['tag']),
                        'repeatable' => true,
                        'mandatory' => false
                    ]
                );

                $framework->tags()->attach($tagDef->id);

                if (!empty($tagData['subfields'])) {
                    foreach ($tagData['subfields'] as $code => $dummy) {
                        if ($code === '_') continue;

                        MarcSubfieldDefinition::firstOrCreate(
                            [
                                'marc_tag_definition_id' => $tagDef->id,
                                'code' => $code
                            ],
                            [
                                'label' => $this->labelService->getSubfieldLabel($tagData['tag'], $code),
                                'repeatable' => true,
                                'mandatory' => false
                            ]
                        );
                    }
                }
            }

            return $framework;
        });
    }
}
