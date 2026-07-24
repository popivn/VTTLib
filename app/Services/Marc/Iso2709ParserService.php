<?php

namespace App\Services\Marc;

use Illuminate\Support\Facades\Log;

class Iso2709ParserService
{
    /**
     * Split raw MARC content into individual records
     * Handles both ISO 2709 binary format and Text-based MARC records
     */
    public function splitRecords(string $content): array
    {
        // Strip UTF-8 BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $records = [];

        // ISO 2709 records are terminated by 0x1D (Record Terminator)
        $rt = chr(0x1D);
        if (strpos($content, $rt) !== false) {
            $parts = explode($rt, $content);
            foreach ($parts as $part) {
                $part = trim($part);
                if (strlen($part) >= 24) {
                    $records[] = $part . $rt;
                }
            }
        }

        // If no ISO 2709 record terminator found, fallback to line-based text MARC splitting
        if (empty($records)) {
            $lines = preg_split('/\r?\n/', $content);
            $currentRecord = '';

            foreach ($lines as $line) {
                if (preg_match('/^\d{5}[a-z]/i', $line) && strlen($currentRecord) > 0) {
                    $records[] = $currentRecord;
                    $currentRecord = $line;
                } else {
                    $currentRecord .= ($currentRecord ? "\n" : '') . $line;
                }
            }

            if (strlen(trim($currentRecord)) >= 24) {
                $records[] = trim($currentRecord);
            }
        }

        return $records;
    }

    /**
     * Parse a raw MARC record into structured arrays (Leader, Directory, Fields, Subfields)
     * Fully ISO 2709 compliant without hardcoded book strings.
     */
    public function parseRecord(string $raw): ?array
    {
        try {
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', trim($raw));
            if (strlen($raw) < 24) {
                return null;
            }

            $leader = substr($raw, 0, 24);
            $baseAddress = (int)substr($leader, 12, 5);

            if ($baseAddress <= 24) {
                $baseAddress = 24;
            }

            $ft = chr(0x1E); // Field terminator
            $rt = chr(0x1D); // Record terminator

            // Locate directory string (up to field terminator 0x1E or base address offset)
            $dirEndPos = strpos($raw, $ft, 24);
            if ($dirEndPos !== false && ($dirEndPos <= $baseAddress)) {
                $directoryStr = substr($raw, 24, $dirEndPos - 24);
                $dataSection = substr($raw, $dirEndPos + 1);
            } else {
                $directoryStr = substr($raw, 24, max(0, $baseAddress - 24));
                $dataSection = substr($raw, $baseAddress);
            }

            $dataSection = rtrim($dataSection, $rt);

            $fields = [];
            $title = '';
            $author = '';
            $isbn = '';
            $publisher = '';
            $year = '';
            $subjects = [];

            // Parse all 12-byte directory entries (Tag 3, Length 4, Start 5)
            for ($i = 0; $i + 11 < strlen($directoryStr); $i += 12) {
                $tag = substr($directoryStr, $i, 3);
                $length = (int)substr($directoryStr, $i + 3, 4);
                $start = (int)substr($directoryStr, $i + 7, 5);

                $fieldData = '';
                if ($start < strlen($dataSection)) {
                    $fieldData = substr($dataSection, $start, $length);
                    $fieldData = rtrim($fieldData, $ft);
                }

                $parsedField = $this->parseFieldData($tag, $fieldData);
                $indicators = $parsedField['indicators'];
                $subfields = $parsedField['subfields'];

                if (!empty($subfields) || !empty($fieldData)) {
                    $fields[$tag][] = [
                        'indicators' => $indicators,
                        'subfields' => $subfields,
                        'raw' => $fieldData
                    ];

                    // Extract common bibliographic fields
                    $this->extractCommonData($tag, $subfields, $title, $author, $isbn, $publisher, $year, $subjects);
                }
            }

            ksort($fields);

            if (empty($fields)) {
                return null;
            }

            return [
                'leader' => $leader,
                'title' => rtrim($title, ' /:.') ?: __('Bản ghi MARC không tiêu đề'),
                'author' => rtrim($author, ',. '),
                'isbn' => preg_replace('/[^0-9X-]/i', '', $isbn),
                'publisher' => rtrim($publisher, ',. '),
                'year' => preg_replace('/[^0-9]/', '', $year),
                'subjects' => array_values(array_unique($subjects)),
                'fields' => $fields,
                'raw_base64' => base64_encode($raw)
            ];
        } catch (\Exception $e) {
            Log::error('Iso2709ParserService parse error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse field indicators and subfields for a specific MARC tag
     */
    protected function parseFieldData(string $tag, string $fieldData): array
    {
        $indicators = ['#', '#'];
        $subfields = [];

        // Control fields (00X) have no indicators and no subfield codes
        if (intval($tag) < 10) {
            $subfields[] = ['code' => '_', 'value' => trim($fieldData)];
            return ['indicators' => $indicators, 'subfields' => $subfields];
        }

        $fieldStr = trim($fieldData);
        $sf = chr(0x1F); // Subfield delimiter

        // Standard subfield delimiter ($ or 0x1F)
        if (strpos($fieldStr, $sf) !== false || strpos($fieldStr, '$') !== false) {
            if (strlen($fieldStr) >= 2) {
                $indicators[0] = $fieldStr[0] === ' ' ? '#' : $fieldStr[0];
                $indicators[1] = $fieldStr[1] === ' ' ? '#' : $fieldStr[1];
                $subfieldStr = substr($fieldStr, 2);
            } else {
                $subfieldStr = $fieldStr;
            }

            $delim = strpos($subfieldStr, $sf) !== false ? $sf : '$';
            $parts = explode($delim, $subfieldStr);

            foreach ($parts as $part) {
                $part = trim($part);
                if (strlen($part) >= 1) {
                    $code = $part[0];
                    $value = trim(substr($part, 1));
                    if ($value !== '') {
                        $subfields[] = ['code' => $code, 'value' => $value];
                    }
                }
            }

            return ['indicators' => $indicators, 'subfields' => $subfields];
        }

        // Text MARC format (e.g., "10aTitle", "##aPlacebPublishercYear")
        if (strlen($fieldStr) >= 2 && (ctype_digit($fieldStr[0]) || $fieldStr[0] === '#' || $fieldStr[0] === ' ')) {
            if (!ctype_alpha($fieldStr[0]) || $fieldStr[0] === '#') {
                $indicators[0] = $fieldStr[0] === ' ' ? '#' : $fieldStr[0];
                $indicators[1] = $fieldStr[1] === ' ' ? '#' : $fieldStr[1];
                $fieldStr = substr($fieldStr, 2);
            }
        }

        $fieldStr = ltrim($fieldStr, '#$');

        // Regex match code letter + value string
        if (preg_match_all('/([a-z0-9])([^a-z0-9#$\x1F].*?)(?=(?:[a-z0-9][^a-z0-9#$\x1F]|$))/ui', $fieldStr, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $code = $m[1];
                $val = trim($m[2]);
                if ($val !== '') {
                    $subfields[] = ['code' => $code, 'value' => $val];
                }
            }
        }

        if (empty($subfields)) {
            $parts = preg_split('/[#$\x1F]+/', $fieldStr);
            foreach ($parts as $part) {
                $part = trim($part);
                if (strlen($part) >= 1) {
                    $code = ctype_alnum($part[0]) ? $part[0] : 'a';
                    $val = trim(ctype_alnum($part[0]) ? substr($part, 1) : $part);
                    if ($val !== '') {
                        $subfields[] = ['code' => $code, 'value' => $val];
                    }
                }
            }
        }

        if (empty($subfields) && !empty($fieldStr)) {
            $subfields[] = ['code' => 'a', 'value' => $fieldStr];
        }

        return ['indicators' => $indicators, 'subfields' => $subfields];
    }

    /**
     * Dynamically extract common bibliographic fields (Title, Author, ISBN, Publisher, Year, Subjects)
     */
    protected function extractCommonData(string $tag, array $subfields, string &$title, string &$author, string &$isbn, string &$publisher, string &$year, array &$subjects): void
    {
        foreach ($subfields as $sf) {
            $code = $sf['code'];
            $val = $sf['value'];

            // Tag 245 - Title Statement
            if ($tag === '245') {
                if ($code === 'a' && empty($title)) {
                    $title = $val;
                } elseif ($code === 'b' && !empty($title)) {
                    $title .= ' : ' . $val;
                }
            }

            // Tag 100 / 700 - Author
            if (in_array($tag, ['100', '110', '700', '710']) && $code === 'a' && empty($author)) {
                $author = $val;
            }

            // Tag 020 - ISBN
            if ($tag === '020' && $code === 'a' && empty($isbn)) {
                $isbn = $val;
            }

            // Tag 260 / 264 - Publisher & Year
            if (in_array($tag, ['260', '264'])) {
                if ($code === 'b' && empty($publisher)) {
                    $publisher = $val;
                }
                if ($code === 'c' && empty($year)) {
                    $year = $val;
                }
            }

            // Tag 650 / 651 - Subjects
            if (in_array($tag, ['650', '651']) && $code === 'a') {
                $subjects[] = $val;
            }
        }
    }
}
