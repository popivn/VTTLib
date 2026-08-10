<?php

namespace App\Imports;

use App\Models\PatronDetail;
use App\Models\User;
use App\Models\Role;
use App\Models\PatronAddress;
use App\Services\BarcodeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;

class PatronsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    use Importable;
    
    protected $barcodeService;
    protected $columnMapping = [];
    protected $importedCount = 0;
    protected $data = [];
    protected $failures = [];
    protected $expiryDate;
    protected $validateRows = false;

    public function __construct()
    {
        $this->barcodeService = app(BarcodeService::class);
    }

    public function setColumnMapping(array $mapping)
    {
        $this->columnMapping = $mapping;
        $this->validateRows = true; // Enable validation when mapping is set
        return $this;
    }

    public function setExpiryDate($date)
    {
        $this->expiryDate = $date;
        return $this;
    }

    public function model(array $row)
    {
        // Log the raw row data for debugging
        \Log::info('Processing row:', $row);
        
        // Store data for preview
        $this->data[] = $row;

        // In preview mode, just collect data and return null
        if (empty($this->columnMapping)) {
            return null;
        }

        // In import mode, validate and prepare data
        try {
            // Map columns
            $mappedData = $this->mapColumns($row);
            
            \Log::info('Mapped data:', $mappedData);
            
            // Validate required fields
            $requiredFields = ['patron_code', 'name', 'email'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (empty($mappedData[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                \Log::warning('Missing required fields: ' . implode(', ', $missingFields));
                return null;
            }

            // Check if patron already exists
            if (PatronDetail::where('patron_code', $mappedData['patron_code'])->exists()) {
                \Log::info('Patron already exists: ' . $mappedData['patron_code']);
                return null;
            }

            if (User::where('email', $mappedData['email'])->exists()) {
                \Log::info('Email already exists: ' . $mappedData['email']);
                return null;
            }

            $this->importedCount++;
            \Log::info('Imported count: ' . $this->importedCount);

            // Return null for now - actual creation handled in processImport
            return null;

        } catch (\Exception $e) {
            \Log::error('Error processing row: ' . $e->getMessage());
            $this->failures[] = new Failure(
                $this->importedCount,
                'general',
                [__('Lỗi xử lý dòng :row: :error', ['row' => $this->importedCount, 'error' => $e->getMessage()])]
            );
            return null;
        }
    }

    private function mapColumns(array $row)
    {
        $mapped = [];
        
        foreach ($this->columnMapping as $excelColumn => $dbField) {
            $mapped[$dbField] = $row[$excelColumn] ?? null;
        }

        // Convert Vietnamese gender to enum values
        if (isset($mapped['gender'])) {
            $gender = strtolower($mapped['gender']);
            if ($gender === 'nam' || $gender === 'male') {
                $mapped['gender'] = 'male';
            } elseif ($gender === 'nữ' || $gender === 'nu' || $gender === 'female') {
                $mapped['gender'] = 'female';
            } else {
                $mapped['gender'] = 'other';
            }
        }

        // Set defaults
        $mapped['card_status'] = 'normal';
        $mapped['registration_date'] = now()->format('Y-m-d');
        $mapped['expiry_date'] = $this->expiryDate ?? now()->addYear()->format('Y-m-d');
        $mapped['patron_group_id'] = 3; // Default to "Học Sinh" group (ID 3)
        $mapped['creator_id'] = auth()->id();

        return $mapped;
    }

    public function rules(): array
    {
        if (!$this->validateRows) {
            return []; // No validation in preview mode
        }
        
        return [
            'patron_code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mssv' => 'nullable|string|max:50',
            'id_card' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'batch' => 'nullable|string|max:100',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation to debug what's failing
     */
    public function withValidation($validator)
    {
        if (!$this->validateRows) {
            return; // Skip validation in preview mode
        }
        
        $validator->after(function ($validator) {
            if ($validator->fails()) {
                \Log::error('Validation failed:', [
                    'errors' => $validator->errors()->all(),
                    'data' => $validator->getData()
                ]);
            }
        });
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function failures(): array
    {
        return $this->failures;
    }

    /**
     * Process actual import after preview
     */
    public function processImport()
    {
        $importedCount = 0;
        $totalRows = count($this->data);
        
        \Log::info('processImport started with ' . $totalRows . ' rows');
        
        foreach ($this->data as $index => $row) {
            try {
                \Log::info('Processing row ' . $index . ':', $row);
                
                $mappedData = $this->mapColumns($row);
                
                \Log::info('Mapped data for row ' . $index . ':', $mappedData);
                
                // Skip if already exists
                if (PatronDetail::where('patron_code', $mappedData['patron_code'])->exists()) {
                    \Log::info('Skipping row ' . $index . ' - patron already exists: ' . $mappedData['patron_code']);
                    continue;
                }

                if (User::where('email', $mappedData['email'])->exists()) {
                    \Log::info('Skipping row ' . $index . ' - email already exists: ' . $mappedData['email']);
                    continue;
                }

                \Log::info('Creating patron for row ' . $index);
                
                DB::transaction(function () use ($mappedData, &$importedCount) {
                    // Create user
                    $user = User::create([
                        'name' => $mappedData['name'],
                        'username' => $mappedData['patron_code'],
                        'email' => $mappedData['email'],
                        'password' => Hash::make($mappedData['patron_code']),
                        'is_first_login' => true,
                    ]);

                    \Log::info('Created user with ID: ' . $user->id);

                    // Assign visitor role
                    $patronRole = Role::firstOrCreate(['name' => 'visitor']);
                    $user->roles()->attach($patronRole->id);

                    // Create patron detail
                    $patron = PatronDetail::create([
                        'user_id' => $user->id,
                        'patron_code' => $mappedData['patron_code'],
                        'display_name' => $mappedData['name'],
                        'card_status' => $mappedData['card_status'],
                        'phone' => $mappedData['phone'] ?? null,
                        'mssv' => $mappedData['mssv'] ?? null,
                        'id_card' => $mappedData['id_card'] ?? null,
                        'school_name' => $mappedData['school_name'] ?? null,
                        'department' => $mappedData['department'] ?? null,
                        'batch' => $mappedData['batch'] ?? null,
                        'dob' => $mappedData['dob'] ?? null,
                        'gender' => $mappedData['gender'] ?? null,
                        'patron_group_id' => $mappedData['patron_group_id'],
                        'registration_date' => $mappedData['registration_date'],
                        'expiry_date' => $mappedData['expiry_date'],
                        'creator_id' => $mappedData['creator_id'],
                        'notes' => $mappedData['notes'] ?? null,
                    ]);

                    \Log::info('Created patron detail with ID: ' . $patron->id);

                    // Create address if provided
                    if (!empty($mappedData['address'])) {
                        PatronAddress::create([
                            'patron_detail_id' => $patron->id,
                            'address_line' => $mappedData['address'],
                            'type' => 'home',
                            'is_primary' => true,
                        ]);
                    }

                    // Generate barcode
                    $this->barcodeService->incrementCounter('patron', $mappedData['patron_code']);
                    $this->barcodeService->saveAsFile(
                        $mappedData['patron_code'], 
                        'patrons/barcodes/' . $mappedData['patron_code'] . '.svg'
                    );

                    $importedCount++;
                    \Log::info('Successfully imported row, count now: ' . $importedCount);
                });

            } catch (\Exception $e) {
                \Log::error('Error importing row ' . $index . ': ' . $e->getMessage());
                $this->failures[] = new Failure(
                    $index,
                    'import',
                    [__('Lỗi import dòng :row: :error', ['row' => $index, 'error' => $e->getMessage()])]
                );
            }
        }

        $this->importedCount = $importedCount;
        \Log::info('processImport completed. Total imported: ' . $importedCount);
        
        return $importedCount;
    }
}
