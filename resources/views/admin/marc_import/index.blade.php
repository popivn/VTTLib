@extends('layouts.admin')

@section('content')
<div class="w-full space-y-4 animate-in fade-in duration-500 pb-20">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h2 class="text-lg font-bold text-foreground tracking-tight">{{ __('MARC Records Import') }}</h2>
            <p class="text-xs text-muted-foreground mt-0.5">{{ __('Import biên mục từ file chuẩn MARC21 (.mrc, .txt)') }}</p>
        </div>
        <a href="{{ route('admin.marc.book') }}" class="btn-compact-secondary">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
            <span>{{ __('Back to Cataloging') }}</span>
        </a>
    </div>

    <!-- MARC FILE IMPORT (.mrc / .txt) -->
    <div id="panelMarc" class="space-y-4">
        <!-- MARC Upload Form -->
        <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
            <div class="p-3">
                <form id="marcImportForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="action_type" value="create">

                    <!-- Options -->
                    <div class="grid grid-cols-1 gap-3 mb-3">
                        <div class="space-y-1">
                            <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">
                                {{ __('Khung biên mục (tuỳ chọn)') }}
                            </label>
                            <select name="framework_id" id="marc_framework_id"
                                class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="">{{ __('-- Tự động trích xuất từ file --') }}</option>
                                @foreach($frameworks as $framework)
                                <option value="{{ $framework->id }}">{{ $framework->name }} ({{ $framework->code }})</option>
                                @endforeach
                            </select>
                            <p class="text-[9px] text-muted-foreground mt-0.5">{{ __('Để trống nếu muốn tạo khung mới từ file MARC') }}</p>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider mb-1">
                            {{ __('File MARC') }} <span class="text-destructive">*</span>
                        </label>
                        <div id="marcDropZone" class="border-2 border-dashed border-border rounded-md p-6 text-center hover:border-primary transition-all duration-200 bg-muted/20">
                            <input type="file" name="marc_file" id="marc_file" accept=".mrc,.txt" required class="hidden">
                            <label for="marc_file" class="cursor-pointer block">
                                <div id="marcUploadPlaceholder" class="flex flex-col items-center">
                                    <div class="w-12 h-12 bg-primary/10 text-primary border border-primary/20 rounded-full flex items-center justify-center mb-2">
                                        <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-foreground">{{ __('Click để chọn file hoặc kéo thả') }}</span>
                                    <span class="text-[10px] text-muted-foreground mt-0.5">{{ __('Hỗ trợ: .mrc (ISO 2709), .txt (MARC text) - Tối đa 10MB') }}</span>
                                </div>
                                <div id="marcFileSelectedState" class="hidden flex flex-col items-center">
                                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-full flex items-center justify-center mb-2 animate-bounce">
                                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                                    </div>
                                    <span id="marcSelectedFileName" class="text-xs font-bold text-emerald-600 dark:text-emerald-400"></span>
                                    <span id="marcSelectedFileSize" class="text-[10px] text-muted-foreground mt-0.5"></span>
                                    <button type="button" onclick="resetMarcFile()" class="mt-2 text-[10px] text-destructive hover:underline">{{ __('Xoá file') }}</button>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-primary/5 border border-primary/15 rounded-sm p-3 mb-3 flex items-start gap-3">
                        <i data-lucide="info" class="w-4 h-4 text-primary shrink-0 mt-0.5"></i>
                        <div>
                            <h4 class="text-xs font-bold text-primary">{{ __('Hướng dẫn') }}</h4>
                            <ul class="text-[10px] text-muted-foreground mt-1 space-y-1 list-disc list-inside">
                                <li>{{ __('Hệ thống sẽ tự động phân tích cấu trúc MARC từ file') }}</li>
                                <li>{{ __('Sau khi upload, bạn có thể xem trước dữ liệu và khung biên mục được trích xuất') }}</li>
                                <li>{{ __('Bạn có thể lưu khung biên mục mới hoặc chọn khung đã có để import') }}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-2">
                        <button type="submit" id="marcUploadBtn" disabled class="flex-grow btn-compact-primary py-2.5 h-10 flex items-center justify-center gap-1.5">
                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                            <span class="uppercase font-bold tracking-wider text-xs">{{ __('Upload & Phân tích') }}</span>
                        </button>
                        <button type="button" id="marcResetBtn" class="btn-compact-secondary py-2.5 h-10 px-6 flex items-center justify-center">
                            {{ __('Reset') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MARC Validation Results -->
        <div id="marcValidationResults" class="hidden space-y-4">
            <!-- Summary -->
            <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                <div class="p-3">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wider mb-3">{{ __('Kết quả phân tích') }}</h3>
                    <div class="grid grid-cols-3 gap-3 mb-3">
                        <div class="bg-muted/50 rounded-sm border border-border p-3 text-center">
                            <div class="text-lg font-bold text-foreground" id="marcTotalRecords">0</div>
                            <div class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider mt-0.5">{{ __('Tổng bản ghi') }}</div>
                        </div>
                        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-sm p-3 text-center">
                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400" id="marcValidRecords">0</div>
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-bold tracking-wider mt-0.5">{{ __('Hợp lệ') }}</div>
                        </div>
                        <div class="bg-destructive/10 border border-destructive/20 rounded-sm p-3 text-center">
                            <div class="text-lg font-bold text-destructive" id="marcInvalidRecords">0</div>
                            <div class="text-[10px] text-destructive uppercase font-bold tracking-wider mt-0.5">{{ __('Lỗi') }}</div>
                        </div>
                    </div>

                    <!-- Preview Records -->
                    <h4 class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider mb-2">{{ __('Xem trước bản ghi (tối đa 5)') }}</h4>
                    <div id="marcPreviewContainer" class="space-y-3 mb-3"></div>

                    <!-- Errors -->
                    <div id="marcErrorsSection" class="hidden mb-3">
                        <h4 class="text-[10px] text-destructive uppercase font-bold tracking-wider mb-1.5">{{ __('Bản ghi lỗi') }}</h4>
                        <div class="bg-destructive/5 border border-destructive/15 rounded-sm p-3 max-h-40 overflow-y-auto">
                            <div id="marcErrorsList" class="space-y-1.5 text-xs"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extracted Framework & Tag Selection -->
            <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                <div class="p-3">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <h3 class="text-xs font-bold text-foreground uppercase tracking-wider">{{ __('Khung biên mục trích xuất & Lọc Tag Import') }}</h3>
                            <p class="text-[10px] text-muted-foreground mt-0.5">{{ __('Tick chọn các Tag MARC bạn muốn import vào hệ thống (Bỏ chọn nếu muốn loại bỏ Tag nội bộ như 930, 941...)') }}</p>
                        </div>
                        <button type="button" id="saveFrameworkBtn" class="btn-compact-primary py-2 px-3 text-xs flex items-center gap-1">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            <span>{{ __('Lưu khung biên mục này') }}</span>
                        </button>
                    </div>

                    <div id="marcFrameworkTable" class="overflow-x-auto rounded-sm border border-border">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2 px-3 w-10 text-center">
                                        <input type="checkbox" id="selectAllTagsCheckbox" checked class="w-3.5 h-3.5 rounded border-border text-primary focus:ring-primary/20 cursor-pointer">
                                    </th>
                                    <th class="py-2 px-3 w-20">{{ __('Tag') }}</th>
                                    <th class="py-2 px-3">{{ __('Tên trường') }}</th>
                                    <th class="py-2 px-3 w-40">{{ __('Trường con') }}</th>
                                </tr>
                            </thead>
                            <tbody id="marcFrameworkBody" class="divide-y divide-border text-xs">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Process Import -->
            <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                <div class="p-3">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wider mb-3">{{ __('Xác nhận Import') }}</h3>
                    <input type="hidden" id="marcProcessFramework" value="__create_new__">

                    <div class="flex space-x-3">
                        <button type="button" id="marcProcessBtn" disabled class="flex-grow btn-compact-primary py-2.5 h-10 flex items-center justify-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span class="uppercase font-bold tracking-wider text-xs">{{ __('Tiến hành Import') }}</span>
                        </button>
                        <button type="button" id="marcCancelBtn" class="btn-compact-secondary py-2.5 h-10 px-6 flex items-center justify-center">
                            {{ __('Huỷ') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- MARC Processing Results -->
            <div id="marcProcessingResults" class="hidden bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                <div class="p-3">
                    <h3 class="text-xs font-bold text-foreground uppercase tracking-wider mb-3">{{ __('Kết quả Import') }}</h3>
                    <div id="marcProcessingResultsContent"></div>
                </div>
            </div>
        </div>
    </div><!-- /panelMarc -->
</div>
@endsection

@push('scripts')
<script>
    // Translations object - extracted from Blade to avoid IDE parser issues with curly braces inside template literals
    const __ = {
        row: "{{ __('Row') }}",
        title: "{{ __('Title') }}",
        status: "{{ __('Status') }}",
        details: "{{ __('Details') }}",
        totalProcessed: "{{ __('Total Processed') }}",
        successful: "{{ __('Successful') }}",
        success: "{{ __('Success') }}",
        failed: "{{ __('Failed') }}",
        done: "{{ __('Done') }}",
        processImport: "{{ __('Process Import') }}",
        uploadValidate: "{{ __('Upload & Validate') }}",
        validating: "{{ __('Validating...') }}",
        analyzing: "{{ __('Đang phân tích...') }}",
        uploadAnalyze: "{{ __('Upload & Phân tích') }}",
        year: "{{ __('Năm') }}",
        record: "{{ __('Bản ghi') }}",
        frameworkIncludes: "{{ __('Khung sẽ bao gồm') }}",
        marcFields: "{{ __('trường MARC') }}",
        creatingFramework: "{{ __('Đang tạo khung...') }}",
        proceedImport: "{{ __('Tiến hành Import') }}",
        importing: "{{ __('Đang import...') }}",
        totalProcess: "{{ __('Tổng xử lý') }}",
        successLabel: "{{ __('Thành công') }}",
        failLabel: "{{ __('Thất bại') }}",
        titleField: "{{ __('Nhan đề') }}",
        statusField: "{{ __('Trạng thái') }}",
        detailField: "{{ __('Chi tiết') }}",
        ok: "{{ __('OK') }}",
        errorLabel: "{{ __('Lỗi') }}",
        complete: "{{ __('Hoàn tất') }}",
    };

    document.addEventListener('DOMContentLoaded', function() {
        let validImportData = [];
        const form = document.getElementById('importForm');
        const fileInput = document.getElementById('excel_file');
        const frameworkSelect = document.getElementById('framework_id');
        const actionTypeSelect = document.getElementById('action_type');
        const uploadBtn = document.getElementById('uploadBtn');
        const downloadTemplateBtn = document.getElementById('downloadTemplate');
        const resetBtn = document.getElementById('resetBtn');

        const dropZone = document.getElementById('dropZone');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const fileSelectedState = document.getElementById('fileSelectedState');
        const selectedFileName = document.getElementById('selectedFileName');
        const selectedFileSize = document.getElementById('selectedFileSize');

        const validationResults = document.getElementById('validationResults');
        const processingResults = document.getElementById('processingResults');

        // SweetAlert standard configurations
        const swalConfig = {
            customClass: {
                popup: 'bg-card text-foreground border border-border rounded-md p-4 w-80',
                title: 'text-foreground font-bold text-sm border-b border-border pb-2',
                htmlContainer: 'text-muted-foreground text-xs mt-2',
                confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
            },
            buttonsStyling: false
        };

        // Enable/disable buttons based on form state
        function updateButtonStates() {
            const hasFile = fileInput.files.length > 0;
            const hasFramework = frameworkSelect.value !== '';
            uploadBtn.disabled = !(hasFile && hasFramework);
            downloadTemplateBtn.disabled = !hasFramework;

            if (hasFile) {
                const file = fileInput.files[0];
                selectedFileName.textContent = file.name;
                selectedFileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';

                uploadPlaceholder.classList.add('hidden');
                fileSelectedState.classList.remove('hidden');
                dropZone.classList.add('border-emerald-500/30', 'bg-emerald-500/5');
                dropZone.classList.remove('border-border', 'bg-muted/20');
            } else {
                uploadPlaceholder.classList.remove('hidden');
                fileSelectedState.classList.add('hidden');
                dropZone.classList.remove('border-emerald-500/30', 'bg-emerald-500/5');
                dropZone.classList.add('border-border', 'bg-muted/20');
            }
        }

        fileInput.addEventListener('change', updateButtonStates);
        frameworkSelect.addEventListener('change', updateButtonStates);

        downloadTemplateBtn.addEventListener('click', function() {
            const frameworkId = frameworkSelect.value;
            if (frameworkId) {
                window.location.href = `{{ route('admin.marc.import.template') }}?framework_id=${frameworkId}`;
            }
        });

        // Drag & drop Excel
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-primary', 'bg-primary/5'); });
        dropZone.addEventListener('dragleave', e => { e.preventDefault(); dropZone.classList.remove('border-primary', 'bg-primary/5'); });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-primary/5');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                updateButtonStates();
            }
        });

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${__.validating}
            `;

            try {
                const response = await fetch('{{ route('admin.marc.import.upload') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    const result = await response.json();
                    if (result.success) {
                        showValidationResults(result.data);
                        Swal.fire({
                            ...swalConfig,
                            icon: 'success',
                            title: "{{ __('File Uploaded') }}",
                            text: "{{ __('File has been uploaded and validated successfully.') }}",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            ...swalConfig,
                            icon: 'error',
                            title: "{{ __('Error') }}",
                            text: result.message
                        });
                    }
                } else {
                    const html = await response.text();
                    const errorWindow = window.open('', '_blank');
                    errorWindow.document.write(html);
                    errorWindow.document.close();
                    Swal.fire({
                        ...swalConfig,
                        icon: 'warning',
                        title: "{{ __('Debug Output') }}",
                        text: "{{ __('The server returned an HTML response. It has been opened in a new tab for debugging.') }}"
                    });
                }
            } catch (error) {
                Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: "{{ __('Error') }}",
                    text: error.message
                });
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = `
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span class="uppercase font-bold tracking-wider text-xs">${__.uploadValidate}</span>
                `;
                lucide.createIcons();
            }
        });

        function showValidationResults(data) {
            document.getElementById('totalRows').textContent = data.total_rows;
            document.getElementById('validRows').textContent = data.valid_rows;
            document.getElementById('invalidRows').textContent = data.invalid_rows;

            const previewContainer = document.getElementById('previewContainer');
            previewContainer.innerHTML = '';

            data.preview.forEach(record => {
                const card = document.createElement('div');
                card.className = "bg-muted/30 border border-border rounded-sm p-3 font-mono text-xs overflow-x-auto";
                card.innerHTML = `
                    <div class="flex justify-between items-center mb-1.5 pb-1.5 border-b border-border">
                        <span class="text-xs font-bold text-primary">#{{ __('Row') }} ${record.row_index}</span>
                        <span class="text-xs text-muted-foreground">${record.title}</span>
                    </div>
                    <pre class="text-foreground leading-relaxed whitespace-pre-wrap font-mono text-xs">${record.raw_marc}</pre>
                `;
                previewContainer.appendChild(card);
            });
            const errorsSection = document.getElementById('errorsSection');
            const errorsList = document.getElementById('errorsList');
            if (data.errors && data.errors.length > 0) {
                errorsSection.classList.remove('hidden');
                document.getElementById('createFrameworkSection').classList.remove('hidden');
                errorsList.innerHTML = '';
                data.errors.forEach(error => {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'flex items-start gap-1.5';
                    errorDiv.innerHTML = `<span class="text-destructive font-medium">${__.row} ${error.row_index}:</span> <span class="text-muted-foreground">${error.errors.join(', ')}</span>`;
                    errorsList.appendChild(errorDiv);
                });
            } else {
                errorsSection.classList.add('hidden');
                document.getElementById('createFrameworkSection').classList.add('hidden');
            }
            validImportData = data.valid_data || [];
            document.getElementById('processBtn').disabled = data.valid_rows === 0;
            validationResults.classList.remove('hidden');
            validationResults.scrollIntoView({
                behavior: 'smooth'
            });
        }

        function showProcessingResults(data) {
            const container = document.getElementById('processingResultsContent');
            const total = data.length;
            const successCount = data.filter(r => r.success).length;
            const failCount = total - successCount;

            container.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    <div class="p-3 bg-muted/50 rounded-sm border border-border text-center">
                        <div class="text-lg font-bold text-foreground">${total}</div>
                        <div class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider mt-0.5">${__.totalProcessed}</div>
                    </div>
                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-sm text-center">
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">${successCount}</div>
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-bold tracking-wider mt-0.5">${__.successful}</div>
                    </div>
                    <div class="p-3 bg-destructive/10 border border-destructive/20 rounded-sm text-center">
                        <div class="text-lg font-bold text-destructive">${failCount}</div>
                        <div class="text-[10px] text-destructive uppercase font-bold tracking-wider mt-0.5">${__.failed}</div>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-sm border border-border mb-3">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider">
                            <tr>
                                <th class="py-2 px-3 w-16">${__.row}</th>
                                <th class="py-2 px-3">${__.title}</th>
                                <th class="py-2 px-3 w-28">${__.status}</th>
                                <th class="py-2 px-3">${__.details}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border text-xs">
                            ${data.map(result => `
                                <tr class="table-row-hover">
                                    <td class="py-2 px-3">${result.row_index}</td>
                                    <td class="py-2 px-3 font-semibold">${result.title || '-'}</td>
                                    <td class="py-2 px-3">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[9px] font-bold uppercase tracking-wider border ${result.success ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'}">
                                            ${result.success ? __.success : __.failed}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-xs ${result.success ? 'text-muted-foreground' : 'text-destructive'}">
                                        ${result.success ? 'ID: ' + result.record_id : result.error}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="location.reload()" class="btn-compact-primary py-2 px-6">
                        ${__.done}
                    </button>
                </div>
            `;

            validationResults.classList.add('hidden');
            processingResults.classList.remove('hidden');
            processingResults.scrollIntoView({
                behavior: 'smooth'
            });
        }

        document.getElementById('createFrameworkBtn').addEventListener('click', async function() {
            const ts = Date.now().toString().slice(-6);
            const {
                value: formValues
            } = await Swal.fire({
                ...swalConfig,
                title: '{{ __("Tạo Khung biên mục mới") }}',
                html: '<div class="text-left space-y-3 pt-3">' +
                    '<div class="space-y-1">' +
                    '<label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1">Tên khung mới</label>' +
                    '<input id="swal-input1" class="w-full h-9 px-3 py-1.5 bg-background border border-input rounded-sm text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none text-foreground" value="{{ __("Khung sách giáo trình") }} ' + ts + '" placeholder="{{ __("Tên khung (VD: Tài liệu số)") }}">' +
                    '</div>' +
                    '<div class="space-y-1">' +
                    '<label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1">Mã viết tắt</label>' +
                    '<input id="swal-input2" class="w-full h-9 px-3 py-1.5 bg-background border border-input rounded-sm text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none text-foreground font-mono" value="GIAOTRINH_' + ts + '" placeholder="{{ __("Mã khung (VD: DIGI)") }}">' +
                    '</div>' +
                    '</div>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '{{ __("Tạo ngay") }}',
                cancelButtonText: '{{ __("Huỷ") }}',
                preConfirm: () => {
                    const name = document.getElementById('swal-input1').value.trim();
                    const code = document.getElementById('swal-input2').value.trim();
                    if (!name || !code) {
                        Swal.showValidationMessage('Vui lòng nhập đầy đủ tên và mã khung');
                        return false;
                    }
                    return [name, code];
                }
            });

            if (formValues && formValues[0] && formValues[1]) {
                const formData = new FormData(form);
                formData.append('framework_name', formValues[0]);
                formData.append('framework_code', formValues[1]);

                Swal.fire({
                    ...swalConfig,
                    title: '{{ __("Đang khởi tạo...") }}',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch('{{ route('admin.marc.import.create-framework') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const result = await response.json();
                    if (result.success) {
                        Swal.fire({
                            ...swalConfig,
                            icon: 'success',
                            title: '{{ __("Thành công") }}',
                            text: result.message
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            ...swalConfig,
                            icon: 'error',
                            title: '{{ __("Lỗi") }}',
                            text: result.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: '{{ __("Lỗi kết nối") }}',
                        text: error.message
                    });
                }
            }
        });

        document.getElementById('processBtn').addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = `...`;
            try {
                const response = await fetch('{{ route('admin.marc.import.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        framework_id: frameworkSelect.value,
                        action_type: actionTypeSelect.value,
                        validated_data: validImportData
                    })
                });
                const result = await response.json();
                if (result.success) {
                    showProcessingResults(result.data);
                } else {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: "{{ __('Error') }}",
                        text: result.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: "{{ __('Error') }}",
                    text: error.message
                });
            } finally {
                this.disabled = false;
                this.innerHTML = `${__.processImport}`;
            }
        });

        resetBtn.addEventListener('click', function() {
            form.reset();
            updateButtonStates();
            validationResults.classList.add('hidden');
            processingResults.classList.add('hidden');
        });

        document.getElementById('cancelBtn').addEventListener('click', function() {
            validationResults.classList.add('hidden');
        });
    });

    // ========================================================================
    // TAB SWITCHING
    // ========================================================================
    function switchTab(tab) {
        const tabExcel = document.getElementById('tabExcel');
        const tabMarc = document.getElementById('tabMarc');
        const panelExcel = document.getElementById('panelExcel');
        const panelMarc = document.getElementById('panelMarc');

        const activeClass = 'border-primary text-primary bg-primary/5';
        const inactiveClass = 'border-transparent text-muted-foreground hover:text-foreground hover:bg-muted/50';

        // Update URL param
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);

        if (tab === 'excel') {
            panelExcel.classList.remove('hidden');
            panelMarc.classList.add('hidden');
            tabExcel.className = `flex-1 py-2 px-3 text-xs font-semibold border-b-2 ${activeClass} transition duration-200 flex items-center justify-center gap-1.5`;
            tabMarc.className = `flex-1 py-2 px-3 text-xs font-semibold border-b-2 ${inactiveClass} transition duration-200 flex items-center justify-center gap-1.5`;
        } else {
            panelExcel.classList.add('hidden');
            panelMarc.classList.remove('hidden');
            tabMarc.className = `flex-1 py-2 px-3 text-xs font-semibold border-b-2 ${activeClass} transition duration-200 flex items-center justify-center gap-1.5`;
            tabExcel.className = `flex-1 py-2 px-3 text-xs font-semibold border-b-2 ${inactiveClass} transition duration-200 flex items-center justify-center gap-1.5`;
        }
    }

    // Auto-open tab from URL param on page load
    (function() {
        const urlTab = new URLSearchParams(window.location.search).get('tab');
        if (urlTab === 'marc' || urlTab === 'excel') {
            switchTab(urlTab);
        }
    })();

    // ========================================================================
    // MARC FILE IMPORT TAB LOGIC
    // ========================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const marcForm = document.getElementById('marcImportForm');
        const marcFileInput = document.getElementById('marc_file');
        const marcUploadBtn = document.getElementById('marcUploadBtn');
        const marcDropZone = document.getElementById('marcDropZone');
        const marcUploadPlaceholder = document.getElementById('marcUploadPlaceholder');
        const marcFileSelectedState = document.getElementById('marcFileSelectedState');
        const marcSelectedFileName = document.getElementById('marcSelectedFileName');
        const marcSelectedFileSize = document.getElementById('marcSelectedFileSize');

        let extractedFrameworkData = [];

        const swalConfig = {
            customClass: {
                popup: 'bg-card text-foreground border border-border rounded-md p-4 w-80',
                title: 'text-foreground font-bold text-sm border-b border-border pb-2',
                htmlContainer: 'text-muted-foreground text-xs mt-2',
                confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
            },
            buttonsStyling: false
        };

        // File selection UI
        function updateMarcFileState() {
            const hasFile = marcFileInput.files.length > 0;
            marcUploadBtn.disabled = !hasFile;

            if (hasFile) {
                const file = marcFileInput.files[0];
                marcSelectedFileName.textContent = file.name;
                marcSelectedFileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                marcUploadPlaceholder.classList.add('hidden');
                marcFileSelectedState.classList.remove('hidden');
                marcDropZone.classList.add('border-emerald-500/30', 'bg-emerald-500/5');
                marcDropZone.classList.remove('border-border', 'bg-muted/20');
            } else {
                marcUploadPlaceholder.classList.remove('hidden');
                marcFileSelectedState.classList.add('hidden');
                marcDropZone.classList.remove('border-emerald-500/30', 'bg-emerald-500/5');
                marcDropZone.classList.add('border-border', 'bg-muted/20');
            }
        }

        marcFileInput.addEventListener('change', updateMarcFileState);

        // Drag & drop MARC
        marcDropZone.addEventListener('dragover', e => { e.preventDefault(); marcDropZone.classList.add('border-primary', 'bg-primary/5'); });
        marcDropZone.addEventListener('dragleave', e => { e.preventDefault(); marcDropZone.classList.remove('border-primary', 'bg-primary/5'); });
        marcDropZone.addEventListener('drop', e => {
            e.preventDefault();
            marcDropZone.classList.remove('border-primary', 'bg-primary/5');
            if (e.dataTransfer.files.length > 0) {
                marcFileInput.files = e.dataTransfer.files;
                updateMarcFileState();
            }
        });

        // Upload & parse
        marcForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            marcUploadBtn.disabled = true;
            marcUploadBtn.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${__.analyzing}
            `;

            try {
                const formData = new FormData(marcForm);
                const response = await fetch('{{ route("admin.marc.import.upload-marc") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();
                if (result.success) {
                    showMarcResults(result.data);
                    Swal.fire({
                        ...swalConfig,
                        icon: 'success',
                        title: '{{ __("Phân tích thành công") }}',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: '{{ __("Lỗi") }}',
                        text: result.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: '{{ __("Lỗi kết nối") }}',
                    text: error.message
                });
            } finally {
                marcUploadBtn.disabled = false;
                marcUploadBtn.innerHTML = `
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span>${__.uploadAnalyze}</span>
                `;
                lucide.createIcons();
            }
        });

        let currentParsedRecords = [];

        function showMarcResults(data) {
            document.getElementById('marcTotalRecords').textContent = data.total_records;
            document.getElementById('marcValidRecords').textContent = data.valid_records;
            document.getElementById('marcInvalidRecords').textContent = data.invalid_records;
            currentParsedRecords = data.parsed_records || [];

            // Preview & Interactive Editor Records
            renderInteractiveRecordsEditor(currentParsedRecords);

            // Errors
            const errorsSection = document.getElementById('marcErrorsSection');
            const errorsList = document.getElementById('marcErrorsList');
            if (data.errors && data.errors.length > 0) {
                errorsSection.classList.remove('hidden');
                errorsList.innerHTML = '';
                data.errors.forEach(err => {
                    const div = document.createElement('div');
                    div.className = 'text-destructive';
                    div.textContent = `${__.record} #${err.row_index}: ${err.errors.join(', ')}`;
                    errorsList.appendChild(div);
                });
            } else {
                errorsSection.classList.add('hidden');
            }

            // Extracted framework table
            extractedFrameworkData = data.extracted_framework || [];
            const fwBody = document.getElementById('marcFrameworkBody');
            fwBody.innerHTML = '';
            extractedFrameworkData.forEach(tag => {
                const sfCodes = Object.values(tag.subfields).join(', ');
                const tr = document.createElement('tr');
                tr.className = 'table-row-hover';
                tr.innerHTML = `
                    <td class="py-2 px-3 text-center">
                        <input type="checkbox" class="tag-select-checkbox w-3.5 h-3.5 rounded border-border text-primary focus:ring-primary/20 cursor-pointer" value="${tag.tag}" checked>
                    </td>
                    <td class="py-2 px-3 font-mono font-bold text-primary">${tag.tag}</td>
                    <td class="py-2 px-3 text-foreground">${tag.label}</td>
                    <td class="py-2 px-3 font-mono text-muted-foreground">${sfCodes || '-'}</td>
                `;
                fwBody.appendChild(tr);
            });

            // Select all checkbox handler
            document.getElementById('selectAllTagsCheckbox').checked = true;
            document.getElementById('selectAllTagsCheckbox').onchange = function() {
                const isChecked = this.checked;
                document.querySelectorAll('.tag-select-checkbox').forEach(cb => cb.checked = isChecked);
            };

            // Default: always create new framework, auto-enable process button
            const marcProcessFramework = document.getElementById('marcProcessFramework');
            const marcProcessBtn = document.getElementById('marcProcessBtn');
            marcProcessFramework.value = '__create_new__';
            marcProcessBtn.disabled = false;

            document.getElementById('marcValidationResults').classList.remove('hidden');
            document.getElementById('marcValidationResults').scrollIntoView({ behavior: 'smooth' });
        }

        function renderInteractiveRecordsEditor(records) {
            const previewContainer = document.getElementById('marcPreviewContainer');
            previewContainer.innerHTML = '';

            records.forEach((rec, recIdx) => {
                const card = document.createElement('div');
                card.className = 'bg-card border border-border rounded-md shadow-sm p-4 space-y-3';
                
                let fieldsRowsHtml = '';
                const lockedTags = ['001', '005', '008'];

                if (rec.fields) {
                    Object.keys(rec.fields).forEach(tag => {
                        const instances = rec.fields[tag];
                        const isLocked = lockedTags.includes(tag);

                        instances.forEach((inst, instIdx) => {
                            const ind1 = inst.indicators ? inst.indicators[0] : '#';
                            const ind2 = inst.indicators ? inst.indicators[1] : '#';

                            let subfieldsHtml = '';
                            if (inst.subfields) {
                                inst.subfields.forEach((sf, sfIdx) => {
                                    if (isLocked) {
                                        subfieldsHtml += `
                                            <div class="flex items-center gap-1.5 bg-muted/30 px-2 py-1 rounded border border-border">
                                                <span class="font-mono text-[11px] font-bold text-amber-600 dark:text-amber-400">$${sf.code}</span>
                                                <span class="text-xs font-mono text-muted-foreground truncate select-all">${sf.value}</span>
                                            </div>
                                        `;
                                    } else {
                                        subfieldsHtml += `
                                            <div class="flex items-center gap-1 bg-muted/20 p-1.5 rounded border border-border group/sf">
                                                <span class="font-mono text-[11px] font-bold text-primary px-1">$${sf.code}</span>
                                                <input type="text" 
                                                       value="${sf.value.replace(/"/g, '&quot;')}" 
                                                       data-rec="${recIdx}" data-tag="${tag}" data-inst="${instIdx}" data-sf="${sfIdx}"
                                                       onchange="updateSubfieldValue(${recIdx}, '${tag}', ${instIdx}, ${sfIdx}, this.value)"
                                                       class="w-full bg-background text-xs font-mono px-2 py-1 border border-input rounded text-foreground focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                                                <button type="button" 
                                                        onclick="removeSubfield(${recIdx}, '${tag}', ${instIdx}, ${sfIdx})"
                                                        class="p-1 text-muted-foreground hover:text-destructive transition-colors opacity-70 group-hover/sf:opacity-100" 
                                                        title="Xoá subfield này">
                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </div>
                                        `;
                                    }
                                });
                            }

                            fieldsRowsHtml += `
                                <div class="p-2 rounded border border-border/80 ${isLocked ? 'bg-muted/40' : 'bg-muted/10'} space-y-2">
                                    <div class="flex items-center justify-between gap-2 border-b border-border/50 pb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-xs font-black ${isLocked ? 'text-amber-600 dark:text-amber-400' : 'text-primary'} bg-primary/10 px-2 py-0.5 rounded border border-primary/20">${tag}</span>
                                            ${isLocked ? '<span class="text-[9px] font-bold uppercase tracking-wider text-amber-600 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">Read-Only</span>' : ''}
                                            ${!isLocked ? `
                                                <div class="flex items-center gap-1 text-[10px] font-mono text-muted-foreground">
                                                    <span>Ind1:</span>
                                                    <input type="text" maxlength="1" value="${ind1}" 
                                                           onchange="updateIndicator(${recIdx}, '${tag}', ${instIdx}, 0, this.value)"
                                                           class="w-6 h-5 text-center bg-background border border-input rounded text-xs focus:ring-1 focus:ring-primary outline-none">
                                                    <span>Ind2:</span>
                                                    <input type="text" maxlength="1" value="${ind2}" 
                                                           onchange="updateIndicator(${recIdx}, '${tag}', ${instIdx}, 1, this.value)"
                                                           class="w-6 h-5 text-center bg-background border border-input rounded text-xs focus:ring-1 focus:ring-primary outline-none">
                                                </div>
                                            ` : ''}
                                        </div>

                                        ${!isLocked ? `
                                            <div class="flex items-center gap-1">
                                                <button type="button" onclick="promptAddSubfield(${recIdx}, '${tag}', ${instIdx})" 
                                                        class="btn-compact-secondary text-[10px] py-0.5 px-2 flex items-center gap-1">
                                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                                    <span>+ Subfield</span>
                                                </button>
                                                <button type="button" onclick="removeTag(${recIdx}, '${tag}', ${instIdx})" 
                                                        class="p-1 text-muted-foreground hover:text-destructive transition-colors" title="Xoá Tag này">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </div>
                                        ` : ''}
                                    </div>

                                    <div class="space-y-1.5">
                                        ${subfieldsHtml}
                                    </div>
                                </div>
                            `;
                        });
                    });
                }

                card.innerHTML = `
                    <div class="flex items-center justify-between pb-2 border-b border-border cursor-pointer select-none" 
                         onclick="toggleRecordCollapse(${recIdx})">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-primary/10 text-primary font-mono font-bold text-xs rounded border border-primary/20">Bản ghi #${rec.row_index}</span>
                            <h4 class="text-xs font-bold text-foreground line-clamp-1">${rec.title || 'Untitled'}</h4>
                        </div>
                        <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                            <button type="button" onclick="promptAddTag(${recIdx})" class="btn-compact-primary text-[10px] py-1 px-2.5 flex items-center gap-1">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Thêm Tag mới</span>
                            </button>
                            <button type="button" onclick="toggleRecordCollapse(${recIdx})" class="p-1 rounded text-muted-foreground hover:bg-muted transition-all">
                                <i id="recordChevron_${recIdx}" data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"></i>
                            </button>
                        </div>
                    </div>

                    <div id="recordBody_${recIdx}" class="space-y-2 pt-2 transition-all">
                        ${fieldsRowsHtml}
                    </div>
                `;

                previewContainer.appendChild(card);
            });

            lucide.createIcons();
        }

        window.toggleRecordCollapse = function(recIdx) {
            const body = document.getElementById(`recordBody_${recIdx}`);
            const chevron = document.getElementById(`recordChevron_${recIdx}`);
            if (!body) return;

            if (body.classList.contains('hidden')) {
                body.classList.remove('hidden');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            } else {
                body.classList.add('hidden');
                if (chevron) chevron.style.transform = 'rotate(-90deg)';
            }
        };

        // Editor helper functions
        window.updateSubfieldValue = function(recIdx, tag, instIdx, sfIdx, val) {
            if (currentParsedRecords[recIdx]?.fields?.[tag]?.[instIdx]?.subfields?.[sfIdx]) {
                currentParsedRecords[recIdx].fields[tag][instIdx].subfields[sfIdx].value = val;
            }
        };

        window.updateIndicator = function(recIdx, tag, instIdx, indPos, val) {
            if (currentParsedRecords[recIdx]?.fields?.[tag]?.[instIdx]) {
                if (!currentParsedRecords[recIdx].fields[tag][instIdx].indicators) {
                    currentParsedRecords[recIdx].fields[tag][instIdx].indicators = ['#', '#'];
                }
                currentParsedRecords[recIdx].fields[tag][instIdx].indicators[indPos] = val || '#';
            }
        };

        window.removeSubfield = function(recIdx, tag, instIdx, sfIdx) {
            if (currentParsedRecords[recIdx]?.fields?.[tag]?.[instIdx]?.subfields) {
                currentParsedRecords[recIdx].fields[tag][instIdx].subfields.splice(sfIdx, 1);
                renderInteractiveRecordsEditor(currentParsedRecords);
            }
        };

        window.removeTag = function(recIdx, tag, instIdx) {
            if (currentParsedRecords[recIdx]?.fields?.[tag]) {
                currentParsedRecords[recIdx].fields[tag].splice(instIdx, 1);
                if (currentParsedRecords[recIdx].fields[tag].length === 0) {
                    delete currentParsedRecords[recIdx].fields[tag];
                }
                renderInteractiveRecordsEditor(currentParsedRecords);
            }
        };

        window.promptAddSubfield = async function(recIdx, tag, instIdx) {
            const { value: formValues } = await Swal.fire({
                ...swalConfig,
                title: `Thêm Subfield cho Tag ${tag}`,
                html: `
                    <div class="text-left space-y-3 pt-2">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Mã Subfield (VD: a, b, c, d...)</label>
                            <input id="sfCode" class="w-full h-8 px-2 py-1 border border-input rounded text-xs font-mono uppercase bg-background text-foreground" placeholder="a">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Giá trị</label>
                            <input id="sfVal" class="w-full h-8 px-2 py-1 border border-input rounded text-xs bg-background text-foreground" placeholder="Nhập giá trị...">
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Thêm',
                cancelButtonText: 'Huỷ',
                preConfirm: () => {
                    const code = document.getElementById('sfCode').value.trim().toLowerCase();
                    const value = document.getElementById('sfVal').value.trim();
                    if (!code || !value) {
                        Swal.showValidationMessage('Vui lòng nhập cả mã subfield và giá trị');
                        return false;
                    }
                    return { code, value };
                }
            });

            if (formValues) {
                if (!currentParsedRecords[recIdx].fields[tag][instIdx].subfields) {
                    currentParsedRecords[recIdx].fields[tag][instIdx].subfields = [];
                }
                currentParsedRecords[recIdx].fields[tag][instIdx].subfields.push(formValues);
                renderInteractiveRecordsEditor(currentParsedRecords);
            }
        };

        const availableTagDefs = @json($tagDefinitions ?? []);

        window.promptAddTag = async function(recIdx) {
            let optionsHtml = availableTagDefs.map(t => `<option value="${t.tag}">${t.tag} - ${t.label}</option>`).join('');
            if (!optionsHtml) {
                optionsHtml = '<option value="245">245 - Nhan đề chính</option><option value="100">100 - Tác giả chính</option><option value="260">260 - Xuất bản</option><option value="300">300 - Mô tả vật lý</option><option value="500">500 - Ghi chú chung</option>';
            }

            const { value: formValues } = await Swal.fire({
                ...swalConfig,
                title: 'Thêm Tag MARC mới',
                html: `
                    <div class="text-left space-y-3 pt-2">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Chọn Tag MARC có sẵn</label>
                            <select id="selectTagCode" onchange="document.getElementById('newTagCode').value = this.value" class="w-full h-9 px-2 py-1 border border-input rounded text-xs bg-background text-foreground focus:ring-1 focus:ring-primary outline-none">
                                <option value="">-- Chọn trường Tag MARC --</option>
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Hoặc nhập mã Tag thủ công (3 chữ số)</label>
                            <input id="newTagCode" class="w-full h-8 px-2 py-1 border border-input rounded text-xs font-mono bg-background text-foreground" placeholder="245">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Mã Subfield đầu tiên (VD: a)</label>
                            <input id="newSfCode" class="w-full h-8 px-2 py-1 border border-input rounded text-xs font-mono uppercase bg-background text-foreground" value="a">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-muted-foreground uppercase">Giá trị Subfield</label>
                            <input id="newSfVal" class="w-full h-8 px-2 py-1 border border-input rounded text-xs bg-background text-foreground" placeholder="Nhập giá trị...">
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Thêm Tag',
                cancelButtonText: 'Huỷ',
                preConfirm: () => {
                    const tag = document.getElementById('newTagCode').value.trim();
                    const code = document.getElementById('newSfCode').value.trim().toLowerCase();
                    const value = document.getElementById('newSfVal').value.trim();

                    if (!/^\d{3}$/.test(tag)) {
                        Swal.showValidationMessage('Mã Tag phải gồm đúng 3 chữ số (VD: 245, 100)');
                        return false;
                    }
                    if (['001', '005', '008'].includes(tag)) {
                        Swal.showValidationMessage('Không thể thêm thủ công các trường Control System (001, 005, 008)');
                        return false;
                    }
                    if (!code || !value) {
                        Swal.showValidationMessage('Vui lòng nhập đầy đủ mã subfield và giá trị');
                        return false;
                    }
                    return { tag, code, value };
                }
            });

            if (formValues) {
                if (!currentParsedRecords[recIdx].fields[formValues.tag]) {
                    currentParsedRecords[recIdx].fields[formValues.tag] = [];
                }
                currentParsedRecords[recIdx].fields[formValues.tag].push({
                    indicators: ['#', '#'],
                    subfields: [{ code: formValues.code, value: formValues.value }]
                });
                renderInteractiveRecordsEditor(currentParsedRecords);
            }
        };

        // Save framework button
        document.getElementById('saveFrameworkBtn').addEventListener('click', async function() {
            if (extractedFrameworkData.length === 0) {
                Swal.fire({
                    ...swalConfig,
                    icon: 'warning',
                    title: '{{ __("Không có dữ liệu") }}',
                    text: '{{ __("Chưa có khung biên mục để lưu. Hãy upload file trước.") }}'
                });
                return;
            }

            const ts = Date.now().toString().slice(-6);
            const { value: formValues } = await Swal.fire({
                ...swalConfig,
                title: '{{ __("Lưu Khung biên mục") }}',
                html: '<div class="text-left space-y-3 pt-3">' +
                    '<div class="space-y-1">' +
                    '<label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1">{{ __("Tên khung biên mục") }}</label>' +
                    '<input id="swal-fw-name" class="w-full h-9 px-3 py-1.5 bg-background border border-input rounded-sm text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none text-foreground" value="{{ __("Khung sách giáo trình") }} ' + ts + '" placeholder="{{ __("VD: Sách giáo trình y khoa") }}">' +
                    '</div>' +
                    '<div class="space-y-1">' +
                    '<label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1">{{ __("Mã khung (viết tắt, không dấu)") }}</label>' +
                    '<input id="swal-fw-code" class="w-full h-9 px-3 py-1.5 bg-background border border-input rounded-sm text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none text-foreground font-mono" value="GIAOTRINH_' + ts + '" placeholder="{{ __("VD: SGTYKHOA") }}" style="text-transform:uppercase">' +
                    '</div>' +
                    `<div class="text-[10px] text-muted-foreground font-bold uppercase tracking-wider mt-1">${__.frameworkIncludes} <span class="text-primary">${extractedFrameworkData.length}</span> ${__.marcFields}</div>` +
                    '</div>',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '{{ __("Lưu khung") }}',
                cancelButtonText: '{{ __("Huỷ") }}',
                preConfirm: () => {
                    const name = document.getElementById('swal-fw-name').value.trim();
                    const code = document.getElementById('swal-fw-code').value.trim();
                    if (!name || !code) {
                        Swal.showValidationMessage('{{ __("Vui lòng nhập đầy đủ tên và mã khung") }}');
                        return false;
                    }
                    return { name, code };
                }
            });

            if (formValues) {
                Swal.fire({
                    ...swalConfig,
                    title: '{{ __("Đang lưu...") }}',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const tagsPayload = extractedFrameworkData.map(t => ({
                        tag: t.tag,
                        label: t.label,
                        subfields: Object.values(t.subfields)
                    }));

                    const response = await fetch('{{ route("admin.marc.import.save-framework-marc") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            framework_name: formValues.name,
                            framework_code: formValues.code,
                            tags: tagsPayload
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        const select = document.getElementById('marcProcessFramework');
                        const opt = document.createElement('option');
                        opt.value = result.data.framework_id;
                        opt.textContent = `${result.data.framework_name} (${result.data.framework_code})`;
                        opt.selected = true;
                        select.appendChild(opt);
                        document.getElementById('marcProcessBtn').disabled = false;

                        Swal.fire({
                            ...swalConfig,
                            icon: 'success',
                            title: '{{ __("Khung biên mục đã lưu") }}',
                            text: `Khung ${result.data.framework_name} đã được tạo thành công và đã được chọn để import.`
                        });
                    } else {
                        Swal.fire({
                            ...swalConfig,
                            icon: 'error',
                            title: '{{ __("Lỗi") }}',
                            text: result.message
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: '{{ __("Lỗi kết nối") }}',
                        text: error.message
                    });
                }
            }
        });

        // Process MARC import
        document.getElementById('marcProcessBtn').addEventListener('click', async function() {
            let frameworkId = document.getElementById('marcProcessFramework').value;
            const actionType = document.getElementById('marc_action_type').value;

            if (!frameworkId) {
                Swal.fire({
                    ...swalConfig,
                    icon: 'warning',
                    title: '{{ __("Chưa chọn khung") }}',
                    text: '{{ __("Vui lòng chọn khung biên mục trước khi import.") }}'
                });
                return;
            }

            // If "create new from file" selected, prompt for name/code first
            if (frameworkId === '__create_new__') {
                if (extractedFrameworkData.length === 0) {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'warning',
                        title: '{{ __("Không có dữ liệu") }}',
                        text: '{{ __("Chưa có khung biên mục để tạo. Hãy upload file trước.") }}'
                    });
                    return;
                }

                const ts = Date.now().toString().slice(-6);
                const { value: formValues } = await Swal.fire({
                    ...swalConfig,
                    title: '{{ __("Tạo khung biên mục từ file") }}',
                    html: '<div class="text-left space-y-3 pt-3">' +
                        '<div class="space-y-1">' +
                        '<label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1">{{ __("Tên khung biên mục") }}</label>' +
                        '<input id="swalFwName" class="w-full h-9 px-3 py-1.5 bg-background border border-input rounded-sm text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none text-foreground" value="{{ __("Khung sách giáo trình") }} ' + ts + '" placeholder="{{ __("Ví dụ: Khung sách giáo trình") }}">' +
                        '</div>' +
                        '<div class="space-y-1">' +
                        '<label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1">{{ __("Mã khung (viết hoa)") }}</label>' +
                        '<input id="swalFwCode" class="w-full h-9 px-3 py-1.5 bg-background border border-input rounded-sm text-sm focus:ring-1 focus:ring-primary focus:border-primary outline-none text-foreground font-mono" value="GIAOTRINH_' + ts + '" placeholder="{{ __("Ví dụ: GIAOTRINH") }}">' +
                        '</div>' +
                        '</div>',
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: '{{ __("Tạo & Import") }}',
                    cancelButtonText: '{{ __("Huỷ") }}',
                    preConfirm: () => {
                        const name = document.getElementById('swalFwName').value.trim();
                        const code = document.getElementById('swalFwCode').value.trim().toUpperCase();
                        if (!name || !code) {
                            Swal.showValidationMessage('{{ __("Vui lòng nhập đầy đủ tên và mã khung") }}');
                            return false;
                        }
                        if (code.length > 20) {
                            Swal.showValidationMessage('{{ __("Mã khung tối đa 20 ký tự") }}');
                            return false;
                        }
                        return { name, code };
                    }
                });

                if (!formValues) return;

                // Create framework first
                this.disabled = true;
                this.innerHTML = `
                    <svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    ${__.creatingFramework}
                `;

                try {
                    const tagsPayload = extractedFrameworkData.map(t => ({
                        tag: t.tag,
                        label: t.label,
                        subfields: Object.values(t.subfields)
                    }));

                    const fwResponse = await fetch('{{ route("admin.marc.import.save-framework-marc") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            framework_name: formValues.name,
                            framework_code: formValues.code,
                            tags: tagsPayload
                        })
                    });

                    const fwResult = await fwResponse.json();
                    if (!fwResult.success) {
                        Swal.fire({
                            ...swalConfig,
                            icon: 'error',
                            title: '{{ __("Lỗi tạo khung") }}',
                            text: fwResult.message
                        });
                        return;
                    }

                    frameworkId = fwResult.data.framework_id;
                    Swal.fire({
                        ...swalConfig,
                        icon: 'success',
                        title: '{{ __("Đã tạo khung") }}',
                        text: `${fwResult.data.framework_name} (${fwResult.data.framework_code})`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    await new Promise(r => setTimeout(r, 1600));
                } catch (error) {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: '{{ __("Lỗi kết nối") }}',
                        text: error.message
                    });
                    return;
                } finally {
                    this.disabled = false;
                    this.innerHTML = `
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>${__.proceedImport}</span>
                    `;
                    lucide.createIcons();
                }
            }

            // Confirm import
            const confirmResult = await Swal.fire({
                ...swalConfig,
                title: '{{ __("Xác nhận Import") }}',
                text: '{{ __("Bạn có chắc chắn muốn tiến hành import các bản ghi MARC đã phân tích?") }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '{{ __("Tiến hành") }}',
                cancelButtonText: '{{ __("Huỷ") }}'
            });

            if (!confirmResult.isConfirmed) return;

            this.disabled = true;
            this.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${__.importing}
            `;

            try {
                // Collect selected tags
                const selectedTags = Array.from(document.querySelectorAll('.tag-select-checkbox:checked')).map(cb => cb.value);

                const response = await fetch('{{ route("admin.marc.import.process-marc") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        framework_id: frameworkId,
                        action_type: actionType,
                        records: currentParsedRecords,
                        selected_tags: selectedTags
                    })
                });

                const result = await response.json();
                if (result.success) {
                    showMarcProcessingResults(result.data);
                    Swal.fire({
                        ...swalConfig,
                        icon: 'success',
                        title: '{{ __("Import thành công") }}',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: '{{ __("Lỗi") }}',
                        text: result.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    ...swalConfig,
                    icon: 'error',
                    title: '{{ __("Lỗi kết nối") }}',
                    text: error.message
                });
            } finally {
                this.disabled = false;
                this.innerHTML = `
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>${__.proceedImport}</span>
                `;
                lucide.createIcons();
            }
        });

        function showMarcProcessingResults(data) {
            const container = document.getElementById('marcProcessingResultsContent');
            const total = data.length;
            const successCount = data.filter(r => r.success).length;
            const failCount = total - successCount;

            container.innerHTML = `
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <div class="p-3 bg-muted/50 rounded-sm border border-border text-center">
                        <div class="text-lg font-bold text-foreground">${total}</div>
                        <div class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider mt-0.5">${__.totalProcess}</div>
                    </div>
                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-sm text-center">
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">${successCount}</div>
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 uppercase font-bold tracking-wider mt-0.5">${__.successLabel}</div>
                    </div>
                    <div class="p-3 bg-destructive/10 border border-destructive/20 rounded-sm text-center">
                        <div class="text-lg font-bold text-destructive">${failCount}</div>
                        <div class="text-[10px] text-destructive uppercase font-bold tracking-wider mt-0.5">${__.failLabel}</div>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-sm border border-border mb-3">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider">
                            <tr>
                                <th class="py-2 px-3 w-16">#</th>
                                <th class="py-2 px-3">${__.titleField}</th>
                                <th class="py-2 px-3 w-28">${__.statusField}</th>
                                <th class="py-2 px-3">${__.detailField}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border text-xs">
                            ${data.map(r => `
                                <tr class="table-row-hover">
                                    <td class="py-2 px-3">${r.row_index}</td>
                                    <td class="py-2 px-3 font-semibold">${r.title || '-'}</td>
                                    <td class="py-2 px-3">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[9px] font-bold uppercase tracking-wider border ${r.success ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-destructive/10 text-destructive border-destructive/20'}">
                                            ${r.success ? __.ok : __.errorLabel}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-xs ${r.success ? 'text-muted-foreground' : 'text-destructive'}">
                                        ${r.success ? 'ID: ' + r.record_id : r.error}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="location.reload()" class="btn-compact-primary py-2 px-6">
                        ${__.complete}
                    </button>
                </div>
            `;

            document.getElementById('marcProcessingResults').classList.remove('hidden');
            document.getElementById('marcProcessingResults').scrollIntoView({ behavior: 'smooth' });
        }

        // Cancel
        document.getElementById('marcCancelBtn').addEventListener('click', function() {
            document.getElementById('marcValidationResults').classList.add('hidden');
        });

        // Reset
        document.getElementById('marcResetBtn').addEventListener('click', function() {
            marcForm.reset();
            updateMarcFileState();
            document.getElementById('marcValidationResults').classList.add('hidden');
            document.getElementById('marcProcessingResults').classList.add('hidden');
        });
    });

    // Global helper
    function resetMarcFile() {
        document.getElementById('marc_file').value = '';
        document.getElementById('marcUploadPlaceholder').classList.remove('hidden');
        document.getElementById('marcFileSelectedState').classList.add('hidden');
        document.getElementById('marcDropZone').classList.remove('border-emerald-500/30', 'bg-emerald-500/5');
        document.getElementById('marcDropZone').classList.add('border-border', 'bg-muted/20');
        document.getElementById('marcUploadBtn').disabled = true;
    }
</script>
@endpush