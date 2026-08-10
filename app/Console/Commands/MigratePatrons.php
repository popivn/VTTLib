<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PatronDetail;
use App\Models\PatronGroup;
use App\Models\LoanTransaction;
use App\Models\PatronTransaction;
use App\Models\PatronLockHistory;
use App\Models\PrintQueue;
use App\Models\BookItem;

class MigratePatrons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-patrons {--dry-run : Chạy thử nghiệm mô phỏng dữ liệu mẫu} {--limit=10 : Số lượng bản ghi mẫu khi chạy dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chuyển đổi dữ liệu độc giả và thông tin liên quan từ SQL Server sang MySQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if ($isDryRun) {
            $this->info("=== CHẾ ĐỘ CHẠY THỬ NGHIỆM (DRY-RUN) - GIỚI HẠN: {$limit} BẢN GHI ===");
        } else {
            $this->info("=== BẮT ĐẦU CHUYỂN ĐỔI DỮ LIỆU ĐỘC GIẢ CHÍNH THỨC ===");
        }

        try {
            // 1. Kết nối database cũ (SQL Server)
            $this->info('Đang kết nối tới database cũ (SQL Server)...');
            $sqlSrv = new \PDO("sqlsrv:Server=192.168.1.33;Database=TDHVTTOAN;TrustServerCertificate=true", "sa", "@123456");
            $sqlSrv->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->info('Kết nối SQL Server thành công!');

            // 2. Lấy danh sách nhóm độc giả
            $this->info('Đang đồng bộ nhóm độc giả...');
            $stmtTypes = $sqlSrv->query("SELECT * FROM PATRONTYPES");
            $oldTypes = $stmtTypes->fetchAll(\PDO::FETCH_ASSOC);
            $groupMap = [];

            foreach ($oldTypes as $type) {
                $code = trim($type['PATRONTYPE']);
                $name = trim($type['PATRONTYPENAME'] ?? $code);
                
                if ($isDryRun) {
                    $this->line(" - Giả lập nhóm độc giả: {$code} - {$name}");
                    $groupMap[$code] = 999; // ID giả lập
                } else {
                    $group = PatronGroup::firstOrCreate(
                        ['code' => $code],
                        [
                            'name' => $name,
                            'description' => trim($type['PATRONTYPENOTE'] ?? ''),
                            'is_active' => true,
                        ]
                    );
                    $groupMap[$code] = $group->id;
                }
            }
            $this->info('Đồng bộ nhóm độc giả hoàn tất!');

            // Chuẩn bị helper xử lý trùng/thiếu Email
            $usedEmails = [];
            if (!$isDryRun) {
                $emails = User::pluck('email')->toArray();
                foreach ($emails as $em) {
                    $usedEmails[strtolower($em)] = strtolower($em);
                }
            }

            $cleanEmail = function($email, $patronId) use (&$usedEmails) {
                $email = strtolower(trim($email));
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $email = strtolower($patronId) . '@vttu.edu.vn';
                }
                
                $original = $email;
                $counter = 1;
                while (isset($usedEmails[$email])) {
                    $parts = explode('@', $original);
                    $email = $parts[0] . '_' . $patronId . ($counter > 1 ? '_' . $counter : '') . '@' . ($parts[1] ?? 'vttu.edu.vn');
                    $counter++;
                }
                $usedEmails[$email] = $email;
                return $email;
            };

            // Helper chuyển đổi định dạng ngày
            $parseDate = function($str) {
                if (empty($str)) return null;
                $str = trim($str);
                $d = \DateTime::createFromFormat('d/m/Y', $str);
                if ($d) return $d->format('Y-m-d');
                $d = \DateTime::createFromFormat('Y-m-d', $str);
                if ($d) return $d->format('Y-m-d');
                $d = \DateTime::createFromFormat('Y-m-d H:i:s', $str);
                if ($d) return $d->format('Y-m-d');
                $d = \DateTime::createFromFormat('Y-m-d H:i:s.u', $str);
                if ($d) return $d->format('Y-m-d');
                $time = strtotime($str);
                if ($time) return date('Y-m-d', $time);
                return null;
            };

            // Mapping giới tính
            $genderMap = [
                'M' => 'male',
                'F' => 'female',
                'NAM' => 'male',
                'NU' => 'female',
                'NỮ' => 'female',
            ];

            // 3. Chuyển đổi dữ liệu độc giả chính thức (PATRONS)
            $this->info('Đang đọc danh sách độc giả từ SQL Server...');
            $queryPatrons = "SELECT * FROM PATRONS";
            if ($isDryRun) {
                $queryPatrons = "SELECT TOP {$limit} * FROM PATRONS";
            }
            $stmtPatrons = $sqlSrv->query($queryPatrons);
            $oldPatrons = $stmtPatrons->fetchAll(\PDO::FETCH_ASSOC);
            $totalPatrons = count($oldPatrons);
            $this->info("Tìm thấy {$totalPatrons} độc giả để chuyển đổi.");

            $patronIdMap = []; // old PATRONID -> new patron_detail_id
            $barcodesMap = []; // barcode -> book_item_id

            if (!$isDryRun) {
                $barcodesMap = BookItem::pluck('id', 'barcode')->toArray();
            }

            $defaultPassword = Hash::make('123456'); // fallback, sẽ bị override bởi username

            $bar = $this->output->createProgressBar($totalPatrons);
            $bar->start();

            foreach ($oldPatrons as $patron) {
                $patronId = trim($patron['PATRONID']);
                $displayName = trim($patron['DISPLAYNAME'] ?? $patron['PATRONNAME'] ?? $patronId);
                $fullName = trim($patron['PATRONNAME'] ?? $displayName);

                // Lấy nhóm
                $groupId = $groupMap[trim($patron['PATRONTYPE'])] ?? null;

                // Xử lý giới tính
                $gender = 'other';
                if (!empty($patron['SEX'])) {
                    $gender = $genderMap[strtoupper(trim($patron['SEX']))] ?? 'other';
                }

                // Xử lý ảnh đại diện
                $profileImagePath = null;
                if (!empty($patron['PATRONPICTURE'])) {
                    $filename = $patronId . '.jpg';
                    $filepath = storage_path('app/public/patrons/' . $filename);
                    if (file_exists($filepath)) {
                        $profileImagePath = 'patrons/' . $filename;
                    } else {
                        $imgData = $patron['PATRONPICTURE'];
                        if (is_resource($imgData)) {
                            $imgData = stream_get_contents($imgData);
                        }
                        if (!empty($imgData)) {
                            if (!$isDryRun) {
                                $dir = storage_path('app/public/patrons');
                                if (!file_exists($dir)) {
                                    mkdir($dir, 0755, true);
                                }
                                file_put_contents($filepath, $imgData);
                            }
                            $profileImagePath = 'patrons/' . $filename;
                        }
                    }
                }

                $cardStatus = 'normal';
                if ($patron['STATUS'] != 0) {
                    $cardStatus = 'locked';
                }

                if ($isDryRun) {
                    $patronIdMap[$patronId] = rand(100, 999);
                } else {
                    DB::transaction(function () use ($patronId, $fullName, $displayName, $defaultPassword, $groupId, $patron, $parseDate, $gender, $profileImagePath, $cardStatus, &$patronIdMap, $cleanEmail) {
                        // 1. Tạo hoặc cập nhật User
                        $user = User::where('username', $patronId)->first();
                        if (!$user) {
                            $email = $cleanEmail($patron['EMAIL'] ?? '', $patronId);
                            $user = User::create([
                                'name' => $displayName,
                                'username' => $patronId,
                                'full_name' => $fullName,
                                'email' => $email,
                                'password' => Hash::make($patronId),
                                'status' => 'active',
                                'is_first_login' => true,
                            ]);
                        } else {
                            $user->update([
                                'name' => $displayName,
                                'full_name' => $fullName,
                            ]);
                        }

                        // 2. Tạo hoặc cập nhật PatronDetail
                        $patronDetail = PatronDetail::where('patron_code', $patronId)->first();
                        
                        $detailData = [
                            'user_id' => $user->id,
                            'patron_group_id' => $groupId,
                            'patron_code' => $patronId,
                            'id_card' => trim($patron['UID'] ?? ''),
                            'mssv' => trim($patron['OTHERID'] ?? '') ?: null,
                            'phone_contact' => trim($patron['PHONE'] ?? '') ?: null,
                            'display_name' => $displayName,
                            'card_status' => $cardStatus,
                            'is_read_only' => false,
                            'is_waiting_for_print' => false,
                            'dob' => $parseDate($patron['BIRDTH']),
                            'gender' => $gender,
                            'profile_image' => $profileImagePath ?: ($patronDetail->profile_image ?? null),
                            'school_name' => trim($patron['SCHOOLNAME'] ?? '') ?: null,
                            'batch' => trim($patron['COURSE'] ?? '') ?: null,
                            'department' => trim($patron['DEPARTMENT'] ?? '') ?: null,
                            'phone' => trim($patron['PHONE'] ?? '') ?: null,
                            'fax' => trim($patron['FAX'] ?? '') ?: null,
                            'branch' => 'all',
                            'classification' => 'individual',
                            'card_fee' => (float)($patron['FEES'] ?? 0),
                            'deposit' => (float)($patron['ACCOUNTBALANCE'] ?? 0),
                            'balance' => (float)($patron['ACCOUNTBALANCE'] ?? 0),
                            'registration_date' => $parseDate($patron['CREATIONDATE']),
                            'expiry_date' => $parseDate($patron['EXPIRATIONDATE']),
                            'notes' => trim($patron['NOTES'] ?? '') ?: null,
                        ];

                        if (!$patronDetail) {
                            $patronDetail = PatronDetail::create($detailData);
                        } else {
                            $patronDetail->update($detailData);
                        }

                        // 3. Địa chỉ (Chỉ tạo nếu chưa tồn tại)
                        if (!empty($patron['ADDRESS1'])) {
                            $patronDetail->addresses()->firstOrCreate(
                                ['address_line' => trim($patron['ADDRESS1'])],
                                ['type' => 'home', 'is_primary' => true]
                            );
                        }
                        if (!empty($patron['ADDRESS2'])) {
                            $patronDetail->addresses()->firstOrCreate(
                                ['address_line' => trim($patron['ADDRESS2'])],
                                ['type' => 'work', 'is_primary' => false]
                            );
                        }

                        $patronIdMap[$patronId] = $patronDetail->id;
                    });
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
            $this->info("Đã đồng bộ xong danh sách độc giả chính thức!");

            // 4. Lịch sử gia hạn thẻ (PATRONRENEWCARDLOG -> patron_card_renewals)
            $this->info('Đang đồng bộ nhật ký gia hạn thẻ...');
            $queryRenew = "SELECT * FROM PATRONRENEWCARDLOG";
            if ($isDryRun) {
                $queryRenew = "SELECT TOP {$limit} * FROM PATRONRENEWCARDLOG";
            }
            $stmtRenew = $sqlSrv->query($queryRenew);
            $renewLogs = $stmtRenew->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($renewLogs as $log) {
                $oldPatronId = trim($log['PATRONID']);
                $patronDetailId = $patronIdMap[$oldPatronId] ?? null;
                if (!$patronDetailId) continue;

                $createdAt = $log['CREATEDATE'] ? date('Y-m-d H:i:s', strtotime($log['CREATEDATE'])) : null;

                if ($isDryRun) {
                    $this->line(" - Giả lập gia hạn cho độc giả {$oldPatronId} phí cước: " . ($log['RENEW_FEES'] ?? 0));
                } else {
                    // Check if already exists to avoid duplicate
                    $exists = DB::table('patron_card_renewals')
                        ->where('patron_detail_id', $patronDetailId)
                        ->where('created_at', $createdAt)
                        ->exists();

                    if (!$exists) {
                        DB::table('patron_card_renewals')->insert([
                            'patron_detail_id' => $patronDetailId,
                            'old_expiry_date' => $log['OLDDUEDATE'] ? date('Y-m-d H:i:s', strtotime($log['OLDDUEDATE'])) : null,
                            'new_expiry_date' => $log['NEWDUEDATE'] ? date('Y-m-d H:i:s', strtotime($log['NEWDUEDATE'])) : null,
                            'renew_fee' => (float)($log['RENEW_FEES'] ?? 0),
                            'created_at' => $createdAt ?: now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            $this->info('Đồng bộ nhật ký gia hạn hoàn tất!');

            // 5. Độc giả đăng ký trực tuyến (PATRONSREGISTER -> users & patron_details)
            $this->info('Đang đồng bộ danh sách đăng ký thẻ trực tuyến...');
            $queryRegister = "SELECT * FROM PATRONSREGISTER";
            if ($isDryRun) {
                $queryRegister = "SELECT TOP {$limit} * FROM PATRONSREGISTER";
            }
            $stmtRegister = $sqlSrv->query($queryRegister);
            $registeredPatrons = $stmtRegister->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($registeredPatrons as $patron) {
                $patronId = trim($patron['PATRONID']);
                $displayName = trim($patron['DISPLAYNAME'] ?? $patron['FIRSTNAME'] . ' ' . $patron['LASTNAME']);
                $fullName = trim($patron['FIRSTNAME'] . ' ' . $patron['LASTNAME']);
                
                $groupId = $groupMap[trim($patron['PATRONTYPE'])] ?? null;
                $gender = 'other';
                if (!empty($patron['SEX'])) {
                    $gender = $genderMap[strtoupper(trim($patron['SEX']))] ?? 'other';
                }

                if ($isDryRun) {
                    $this->line(" - Giả lập đăng ký trực tuyến độc giả: {$patronId} - {$displayName}");
                } else {
                    DB::transaction(function () use ($patronId, $fullName, $displayName, $groupId, $patron, $parseDate, $gender, $cleanEmail) {
                        $mssv = trim($patron['OTHERID'] ?? '') ?: null;
                        if ($mssv) {
                            $existingMssv = PatronDetail::where('mssv', $mssv)
                                ->where('patron_code', '!=', $patronId)
                                ->exists();
                            if ($existingMssv) {
                                // Skip duplicate online registration requests
                                return;
                            }
                        }

                        $user = User::where('username', $patronId)->first();
                        if (!$user) {
                            $email = $cleanEmail($patron['EMAIL'] ?? '', $patronId);
                            $user = User::create([
                                'name' => $displayName,
                                'username' => $patronId,
                                'full_name' => $fullName,
                                'email' => $email,
                                'password' => Hash::make($patronId),
                                'status' => 'inactive',
                                'is_first_login' => true,
                            ]);
                        } else {
                            $user->update([
                                'name' => $displayName,
                                'full_name' => $fullName,
                            ]);
                        }

                        $patronDetail = PatronDetail::where('patron_code', $patronId)->first();
                        $detailData = [
                            'user_id' => $user->id,
                            'patron_group_id' => $groupId,
                            'patron_code' => $patronId,
                            'id_card' => trim($patron['UID'] ?? ''),
                            'mssv' => trim($patron['OTHERID'] ?? '') ?: null,
                            'phone_contact' => trim($patron['PHONE'] ?? '') ?: null,
                            'display_name' => $displayName,
                            'card_status' => 'pending', // Chờ duyệt
                            'is_read_only' => false,
                            'is_waiting_for_print' => false,
                            'dob' => $parseDate($patron['BIRDTH']),
                            'gender' => $gender,
                            'school_name' => trim($patron['SCHOOLNAME'] ?? '') ?: null,
                            'batch' => trim($patron['COURSE'] ?? '') ?: null,
                            'department' => trim($patron['DEPARTMENT'] ?? '') ?: null,
                            'phone' => trim($patron['PHONE'] ?? '') ?: null,
                            'branch' => 'all',
                            'classification' => 'individual',
                            'registration_date' => $parseDate($patron['CREATIONDATE']),
                            'notes' => trim($patron['NOTES'] ?? '') ?: null,
                        ];

                        if (!$patronDetail) {
                            PatronDetail::create($detailData);
                        } else {
                            $patronDetail->update($detailData);
                        }
                    });
                }
            }
            $this->info('Đồng bộ danh sách đăng ký thẻ trực tuyến hoàn tất!');

            // 6. Giao dịch mượn trả hiện hành (CHECKOUTS -> loan_transactions)
            $this->info('Đang đồng bộ giao dịch mượn trả hiện hành...');
            $queryCheckouts = "SELECT * FROM CHECKOUTS";
            if ($isDryRun) {
                $queryCheckouts = "SELECT TOP {$limit} * FROM CHECKOUTS";
            }
            $stmtCheckouts = $sqlSrv->query($queryCheckouts);
            $checkouts = $stmtCheckouts->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($checkouts as $co) {
                $oldPatronId = trim($co['PATRONID']);
                $barcode = trim($co['BARCODE']);
                $patronDetailId = $patronIdMap[$oldPatronId] ?? null;

                if (!$patronDetailId) {
                    $this->warn("Không tìm thấy độc giả {$oldPatronId} cho giao dịch mượn sách {$barcode}. Bỏ qua.");
                    continue;
                }

                $bookItemId = $barcodesMap[$barcode] ?? null;
                if (!$bookItemId && !$isDryRun) {
                    $this->warn("Không tìm thấy cuốn sách có barcode {$barcode} trong MySQL. Bỏ qua.");
                    continue;
                }

                $loanDate = date('Y-m-d H:i:s', strtotime($co['CHECKOUTDATE']));

                if ($isDryRun) {
                    $this->line(" - Giả lập mượn sách: Độc giả {$oldPatronId} mượn cuốn sách {$barcode}");
                } else {
                    $exists = LoanTransaction::where('patron_detail_id', $patronDetailId)
                        ->where('book_item_id', $bookItemId)
                        ->where('loan_date', $loanDate)
                        ->exists();

                    if (!$exists) {
                        LoanTransaction::create([
                            'patron_detail_id' => $patronDetailId,
                            'book_item_id' => $bookItemId,
                            'loan_date' => $loanDate,
                            'due_date' => date('Y-m-d H:i:s', strtotime($co['DUEDATE'])),
                            'renewal_count' => (int)($co['RENEWCOUNT'] ?? 0),
                            'status' => 'borrowed',
                            'deposit' => (float)($co['DEPOSIT'] ?? 0),
                            'notes' => trim($co['CHECKOUTNOTE'] ?? '') ?: null,
                        ]);
                    }
                }
            }
            $this->info('Đồng bộ giao dịch mượn trả hiện hành hoàn tất!');

            // 7. Giao dịch tài chính (PATRONFISCALTRANS -> patron_transactions)
            $this->info('Đang đồng bộ lịch sử giao dịch tài chính...');
            $queryFiscal = "SELECT * FROM PATRONFISCALTRANS";
            if ($isDryRun) {
                $queryFiscal = "SELECT TOP {$limit} * FROM PATRONFISCALTRANS";
            }
            $stmtFiscal = $sqlSrv->query($queryFiscal);
            $fiscals = $stmtFiscal->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($fiscals as $fs) {
                $oldPatronId = trim($fs['PATRONID']);
                $patronDetailId = $patronIdMap[$oldPatronId] ?? null;
                if (!$patronDetailId) continue;

                $type = PatronTransaction::TYPE_FEE;
                if ($fs['FISCALTYPE'] == 1) { // Ví dụ cọc tiền
                    $type = PatronTransaction::TYPE_DEPOSIT;
                }

                $fiscalDate = date('Y-m-d H:i:s', strtotime($fs['FISCALDATE']));

                if ($isDryRun) {
                    $this->line(" - Giả lập giao dịch tài chính: Độc giả {$oldPatronId} số tiền " . ($fs['FISCALAMOUNT'] ?? 0));
                } else {
                    $exists = PatronTransaction::where('patron_detail_id', $patronDetailId)
                        ->where('type', $type)
                        ->where('amount', (float)($fs['FISCALAMOUNT'] ?? 0))
                        ->where('created_at', $fiscalDate)
                        ->exists();

                    if (!$exists) {
                        PatronTransaction::create([
                            'patron_detail_id' => $patronDetailId,
                            'type' => $type,
                            'amount' => (float)($fs['FISCALAMOUNT'] ?? 0),
                            'balance_before' => 0, 
                            'balance_after' => 0,
                            'description' => trim($fs['FISCALNOTE'] ?? 'Giao dịch tài chính chuyển đổi'),
                            'payment_method' => PatronTransaction::PAYMENT_CASH,
                            'created_at' => $fiscalDate,
                        ]);
                    }
                }
            }
            $this->info('Đồng bộ lịch sử giao dịch tài chính hoàn tất!');

            // 8. Nhật ký lịch sử khóa thẻ (PATRONBLOCKEDTRACKING -> patron_lock_history)
            $this->info('Đang đồng bộ nhật ký khóa thẻ...');
            $queryBlocked = "SELECT * FROM PATRONBLOCKEDTRACKING";
            if ($isDryRun) {
                $queryBlocked = "SELECT TOP {$limit} * FROM PATRONBLOCKEDTRACKING";
            }
            $stmtBlocked = $sqlSrv->query($queryBlocked);
            $blockLogs = $stmtBlocked->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($blockLogs as $log) {
                $oldPatronId = trim($log['PATRONID']);
                $patronDetailId = $patronIdMap[$oldPatronId] ?? null;
                if (!$patronDetailId) continue;

                $action = ($log['ACTIONTYPE'] == 1) ? PatronLockHistory::ACTION_LOCK : PatronLockHistory::ACTION_UNLOCK;
                $actionDate = date('Y-m-d H:i:s', strtotime($log['ACTIONDATE']));

                if ($isDryRun) {
                    $this->line(" - Giả lập sự kiện khóa thẻ: Độc giả {$oldPatronId} hành động {$action}");
                } else {
                    $exists = PatronLockHistory::where('patron_detail_id', $patronDetailId)
                        ->where('action', $action)
                        ->where('created_at', $actionDate)
                        ->exists();

                    if (!$exists) {
                        PatronLockHistory::create([
                            'patron_detail_id' => $patronDetailId,
                            'action' => $action,
                            'reason' => trim($log['NOTE'] ?? ''),
                            'locked_at' => ($action === PatronLockHistory::ACTION_LOCK) ? $actionDate : null,
                            'unlocked_at' => ($action === PatronLockHistory::ACTION_UNLOCK) ? $actionDate : null,
                            'created_at' => $actionDate,
                        ]);
                    }
                }
            }
            $this->info('Đồng bộ nhật ký khóa thẻ hoàn tất!');

            $this->info('=========================================');
            $this->info('QUÁ TRÌNH CHUYỂN ĐỔI DỮ LIỆU ĐÃ HOÀN TẤT THÀNH CÔNG!');
            $this->info('=========================================');

        } catch (\Exception $e) {
            $this->error('Đã xảy ra lỗi trong quá trình chuyển đổi dữ liệu:');
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
