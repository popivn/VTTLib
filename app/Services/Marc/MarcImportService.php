<?php

namespace App\Services\Marc;

use App\Models\BibliographicRecord;
use App\Models\MarcFramework;
use App\Models\MarcTagDefinition;
use App\Models\MarcSubfieldDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarcImportService
{
    protected MarcFrameworkService $frameworkService;

    public function __construct(MarcFrameworkService $frameworkService)
    {
        $this->frameworkService = $frameworkService;
    }

    /**
     * Import or Update BibliographicRecords from parsed MARC data
     */
    public function importParsedRecords(int $frameworkId, string $actionType, array $parsedRecords): array
    {
        return DB::transaction(function () use ($frameworkId, $actionType, $parsedRecords) {
            $framework = MarcFramework::findOrFail($frameworkId);
            $results = [];

            // Ensure definitions exist for all parsed tags/subfields
            $this->frameworkService->ensureDefinitionsForParsedRecords($framework, $parsedRecords);

            foreach ($parsedRecords as $parsed) {
                try {
                    if ($actionType === 'update') {
                        $record = $this->findExistingRecordFromMarc($parsed);
                        if ($record) {
                            $record->update([
                                'leader' => $parsed['leader'],
                                'status' => 'pending'
                            ]);
                            $record->fields()->delete();
                        } else {
                            $record = $this->createNewBibliographicRecord($framework, $parsed);
                        }
                    } else {
                        $record = $this->createNewBibliographicRecord($framework, $parsed);
                    }

                    // Create MARC fields and subfields in database
                    $this->createMarcFieldsFromParsed($record, $parsed['fields']);

                    $results[] = [
                        'row_index' => $parsed['row_index'] ?? 0,
                        'success' => true,
                        'record_id' => $record->id,
                        'title' => $parsed['title'] ?? 'Untitled'
                    ];
                } catch (\Exception $e) {
                    Log::error('Import single MARC record error: ' . $e->getMessage());
                    $results[] = [
                        'row_index' => $parsed['row_index'] ?? 0,
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $results;
        });
    }

    /**
     * Create new BibliographicRecord entry
     */
    protected function createNewBibliographicRecord(MarcFramework $framework, array $parsed): BibliographicRecord
    {
        return BibliographicRecord::create([
            'framework' => $framework->code,
            'leader' => $parsed['leader'],
            'status' => 'pending',
            'record_type' => 'book',
            'subject_category' => 'General',
            'serial_frequency' => 'unknown',
            'date_type' => 'bc',
            'acquisition_method' => 'untraced',
            'document_format' => 'none',
            'cataloging_standard' => 'AACR2'
        ]);
    }

    /**
     * Create MARC fields and subfields for BibliographicRecord
     */
    protected function createMarcFieldsFromParsed(BibliographicRecord $record, array $parsedFields): void
    {
        foreach ($parsedFields as $tag => $fieldInstances) {
            $tagDef = MarcTagDefinition::where('tag', $tag)->first();
            if (!$tagDef) continue;

            foreach ($fieldInstances as $instanceIndex => $instance) {
                $fieldRecord = $record->fields()->create([
                    'marc_tag_definition_id' => $tagDef->id,
                    'tag' => $tag,
                    'ind1' => $instance['indicators'][0] ?? '#',
                    'ind2' => $instance['indicators'][1] ?? '#',
                    'sort_order' => $instanceIndex
                ]);

                if (isset($instance['subfields'])) {
                    foreach ($instance['subfields'] as $sfIndex => $sf) {
                        $code = $sf['code'];
                        $value = $sf['value'];

                        $subfieldDef = MarcSubfieldDefinition::where('marc_tag_definition_id', $tagDef->id)
                            ->where('code', $code)
                            ->first();

                        if (!$subfieldDef) {
                            $subfieldDef = MarcSubfieldDefinition::create([
                                'marc_tag_definition_id' => $tagDef->id,
                                'code' => $code,
                                'label' => 'Subfield ' . $code,
                                'repeatable' => true,
                                'mandatory' => false
                            ]);
                        }

                        $fieldRecord->subfields()->create([
                            'marc_subfield_definition_id' => $subfieldDef->id,
                            'code' => $code,
                            'value' => $value,
                            'sort_order' => $sfIndex
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Find existing record by ISBN or Title
     */
    protected function findExistingRecordFromMarc(array $parsed): ?BibliographicRecord
    {
        if (!empty($parsed['isbn'])) {
            $isbnClean = preg_replace('/[^0-9X]/i', '', $parsed['isbn']);
            $record = BibliographicRecord::whereHas('fields.subfields', function ($q) use ($isbnClean) {
                $q->where('code', 'a')->whereRaw("REPLACE(REPLACE(value, '-', ''), ' ', '') LIKE ?", ["%{$isbnClean}%"]);
            })->first();

            if ($record) return $record;
        }

        if (!empty($parsed['title'])) {
            $titleClean = trim($parsed['title']);
            return BibliographicRecord::whereHas('fields', function ($q) use ($titleClean) {
                $q->where('tag', '245')->whereHas('subfields', function ($sq) use ($titleClean) {
                    $sq->where('code', 'a')->where('value', 'LIKE', "%{$titleClean}%");
                });
            })->first();
        }

        return null;
    }
}
