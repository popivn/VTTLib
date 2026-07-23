@extends('layouts.admin')

@section('content')
<div class="w-full space-y-4 animate-in fade-in duration-500 pb-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-lg font-bold text-foreground tracking-tight">{{ __('Cataloged Records') }}</h1>
            <p class="text-xs text-muted-foreground mt-0.5">{{ __('View and manage cataloged books and documents in MARC21 standard') }}</p>
        </div>
        <a href="{{ route('admin.marc.book.form') }}" class="btn-compact-primary">
            <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
            <span>{{ __('New Catalog Record') }}</span>
        </a>
    </div>

    <!-- Advanced Search Section -->
    <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
        <div class="p-3 border-b border-border bg-muted/30">
            <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('Advanced Search') }}</h2>
        </div>
        <div class="p-3">
            <form method="GET" class="space-y-3">
                <!-- Basic Search -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2 space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Search by Title, Author, ISBN, Publisher, Subject, Notes') }}</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Enter search terms...') }}"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Sort By') }}</label>
                        <div class="flex gap-2">
                            <select name="sort_by"
                                class="flex-[2] h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ __('Created Date') }}</option>
                                <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>{{ __('Title') }}</option>
                                <option value="author" {{ request('sort_by') == 'author' ? 'selected' : '' }}>{{ __('Author') }}</option>
                                <option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>{{ __('Updated Date') }}</option>
                            </select>
                            <select name="sort_order"
                                class="flex-1 h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>{{ __('Descending') }}</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>{{ __('Ascending') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Filters Row 1 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Cataloging Framework') }}</label>
                        <select name="framework"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">{{ __('All Frameworks') }}</option>
                            @foreach($frameworks as $code => $name)
                            <option value="{{ $code }}" {{ request('framework') == $code ? 'selected' : '' }}>{{ $code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Document Type') }}</label>
                        <select name="record_type"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach($recordTypes as $type)
                            <option value="{{ $type }}" {{ request('record_type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Status') }}</label>
                        <select name="status"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending Approval') }}</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Subject Category') }}</label>
                        <select name="subject_category"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($subjectCategories as $category)
                            <option value="{{ $category }}" {{ request('subject_category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filters Row 2 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('From Date') }}</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('To Date') }}</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('MARC Tag') }}</label>
                        <select name="marc_tag"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <option value="">{{ __('Select Tag') }}</option>
                            @foreach($commonMarcTags as $tag)
                            <option value="{{ $tag }}" {{ request('marc_tag') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('MARC Value') }}</label>
                        <input type="text" name="marc_value" value="{{ request('marc_value') }}"
                            placeholder="{{ __('Tag value...') }}"
                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 pt-1.5">
                    <button type="submit" class="btn-compact-primary">
                        <i data-lucide="search" class="w-4 h-4 mr-1"></i>
                        <span>{{ __('Search') }}</span>
                    </button>
                    <a href="{{ route('admin.marc.book') }}" class="btn-compact-secondary">
                        <i data-lucide="filter-x" class="w-4 h-4 mr-1"></i>
                        <span>{{ __('Clear Filters') }}</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    @if(request()->anyFilled(['search', 'framework', 'record_type', 'status', 'subject_category', 'date_from', 'date_to', 'marc_tag', 'marc_value']))
    <div class="p-3 bg-primary/10 border border-primary/20 text-primary rounded-sm text-xs flex justify-between items-center">
        <div>
            <p class="font-semibold">
                {{ __('Found :count records matching your search', ['count' => $records->total()]) }}
            </p>
            @if(request()->filled('search'))
            <p class="text-[10px] opacity-80 mt-0.5">
                {{ __('Search term: ":term"', ['term' => request('search')]) }}
            </p>
            @endif
        </div>
        <a href="{{ route('admin.marc.book') }}" class="font-bold hover:underline">
            {{ __('Clear Filters') }}
        </a>
    </div>
    @endif

    <!-- Results Table -->
    <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3.5 w-32">{{ __('ID / KÝ HIỆU') }}</th>
                        <th class="py-2.5 px-3.5">{{ __('NỘI DUNG CHÍNH') }}</th>
                        <th class="py-2.5 px-3.5 w-48">{{ __('THỂ LOẠI / SỐ LƯỢNG') }}</th>
                        <th class="py-2.5 px-3.5 w-28">{{ __('TRẠNG THÁI') }}</th>
                        <th class="py-2.5 px-3.5 w-44 text-right">{{ __('THAO TÁC') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($records as $record)
                    @php
                    $marcData = [];
                    foreach ($record->fields as $field) {
                        $subfields = [];
                        foreach ($field->subfields as $sub) {
                            $subfields[$sub->code] = $sub->value;
                        }
                        if (!isset($marcData[$field->tag])) {
                            $marcData[$field->tag] = $subfields;
                        } else {
                            $marcData[$field->tag] = array_merge($marcData[$field->tag], $subfields);
                        }
                    }

                    // Classification & Cutter / Call Number
                    $ddcVal = $marcData['082']['a'] ?? ($marcData['090']['a'] ?? '');
                    $cutterVal = $marcData['082']['b'] ?? ($marcData['090']['b'] ?? ($marcData['090']['c'] ?? ''));

                    // Author
                    $author = $marcData['100']['a'] ?? ($marcData['700']['a'] ?? '');

                    // Title components
                    $titleA = !empty($marcData['245']['a']) ? rtrim(trim($marcData['245']['a']), ' /:-') : '';
                    $titleB = !empty($marcData['245']['b']) ? rtrim(trim($marcData['245']['b']), ' /:-') : '';
                    $titleC = !empty($marcData['245']['c']) ? rtrim(trim($marcData['245']['c']), ' /:-') : '';

                    // Publication components
                    $pubPlace = rtrim(trim($marcData['260']['a'] ?? ($marcData['264']['a'] ?? '')), ' :-/;');
                    $publisher = rtrim(trim($marcData['260']['b'] ?? ($marcData['264']['b'] ?? '')), ' :-/;');
                    $pubYear = rtrim(trim($marcData['260']['c'] ?? ($marcData['264']['c'] ?? '')), ' :-/;.[]');

                    $pubParts = [];
                    if ($pubPlace) $pubParts[] = $pubPlace;
                    if ($publisher) $pubParts[] = $publisher;
                    $pubLocation = implode(' : ', $pubParts);
                    if ($pubYear) {
                        $pubLocation = ($pubLocation ? $pubLocation . ' , ' : '') . $pubYear;
                    }

                    $mainTitle = $titleA ?: __('Untitled');
                    $fullTitleDisplay = $mainTitle
                        . ($titleB ? ' : ' . $titleB : '')
                        . ($titleC ? ' / ' . $titleC : '')
                        . ($pubLocation ? ' . - ' . $pubLocation : '');

                    // Physical description (300)
                    $physPages = trim($marcData['300']['a'] ?? '');
                    $physDetails = trim($marcData['300']['b'] ?? '');
                    $physSize = trim($marcData['300']['c'] ?? '');
                    $physAcc = trim($marcData['300']['e'] ?? '');
                    $physParts = array_filter([$physPages, $physDetails, $physSize, $physAcc]);
                    $physDesc = implode('; ', $physParts);

                    // Note & Summary
                    $note = $marcData['500']['a'] ?? ($marcData['504']['a'] ?? ($marcData['505']['a'] ?? ''));
                    $summary = $marcData['520']['a'] ?? '';

                    // Category Name
                    $categoryName = $record->documentType ? $record->documentType->name : ($record->record_type === 'book' ? 'Books - Sách' : ucfirst($record->record_type));
                    $itemCount = $record->items ? $record->items->count() : 0;
                    @endphp
                    <tr class="record-row table-row-hover group cursor-pointer hover:bg-muted/40 transition-colors"
                        data-id="{{ $record->id }}"
                        data-edit-url="{{ route('admin.marc.book.form', $record->id) }}"
                        title="{{ __('Click to expand details, double-click to edit') }}">
                        
                        <!-- ID & Call Numbers -->
                        <td class="py-3 px-3.5 align-top">
                            <div class="font-bold text-foreground text-sm leading-tight">{{ $record->id }}</div>
                            @if($ddcVal)
                            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 leading-none">{{ $ddcVal }}</div>
                            @endif
                            @if($cutterVal)
                            <div class="text-xs font-semibold text-sky-600 dark:text-sky-400 mt-0.5 leading-none">{{ $cutterVal }}</div>
                            @endif
                        </td>

                        <!-- Main Content (Author & Title & Publication) -->
                        <td class="py-3 px-3.5 align-top border-l border-border/60">
                            @if($author)
                            <div class="font-bold text-xs text-red-900 dark:text-red-400 mb-0.5 leading-tight hover:underline">
                                {{ $author }}
                            </div>
                            @endif
                            <div class="text-xs text-foreground/90 leading-normal">
                                {{ $fullTitleDisplay }}
                            </div>
                        </td>

                        <!-- Category & Total Items -->
                        <td class="py-3 px-3.5 align-top text-xs text-muted-foreground leading-relaxed">
                            <div><span class="font-medium">{{ __('Thể loại') }}:</span> <span class="text-sky-600 dark:text-sky-400 font-semibold">{{ $categoryName }}</span></div>
                            <div><span class="font-medium">{{ __('Tổng số') }}:</span> <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $itemCount }}</span></div>
                        </td>

                        <!-- Status -->
                        <td class="py-3 px-3.5 align-top">
                            @if($record->isApproved())
                            <span class="inline-flex px-2 py-0.5 rounded-sm text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                {{ __('Đã duyệt') }}
                            </span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-sm text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                {{ __('Chờ duyệt') }}
                            </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-3.5 align-top text-right">
                            <div class="flex justify-end items-center gap-1">
                                <a href="{{ route('admin.marc.book.form', $record->id) }}?tab=0"
                                   class="btn-icon-compact text-blue-500"
                                   title="{{ __('Leader Information') }}">
                                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('admin.marc.book.form', $record->id) }}?tab=1"
                                   class="btn-icon-compact text-primary"
                                   title="{{ __('Cataloging') }}">
                                    <i data-lucide="book" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('admin.marc.book.form', $record->id) }}?tab=2"
                                   class="btn-icon-compact text-green-500"
                                   title="{{ __('Distribution') }}">
                                    <i data-lucide="git-branch" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('admin.marc.book.form', $record->id) }}?tab=3"
                                   class="btn-icon-compact text-purple-500"
                                   title="{{ __('Preview') }}">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ route('admin.marc.export.download') }}?format=marc&record_id={{ $record->id }}"
                                   class="btn-icon-compact text-emerald-500"
                                   title="{{ __('Export MARC') }}">
                                    <i data-lucide="file-output" class="w-3.5 h-3.5"></i>
                                </a>
                                <button type="button"
                                        class="delete-record btn-icon-danger"
                                        data-id="{{ $record->id }}"
                                        data-title="{{ $mainTitle }}"
                                        title="{{ __('Delete') }}">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Expanded Detail Row (Single-click) -->
                    <tr id="detail-{{ $record->id }}" class="hidden bg-muted/20 dark:bg-slate-900/40 border-b border-border/80 transition-all duration-200">
                        <td colspan="5" class="py-2.5 px-4">
                            <div class="ml-2 pl-4 border-l-2 border-primary/40 space-y-1.5 text-xs text-foreground/90 font-sans">
                                @if($author)
                                <div class="flex gap-2">
                                    <span class="font-semibold text-muted-foreground w-28 shrink-0">{{ __('Tác giả') }}:</span>
                                    <span class="font-bold text-red-900 dark:text-red-400">{{ $author }}</span>
                                </div>
                                @endif
                                
                                <div class="flex gap-2">
                                    <span class="font-semibold text-muted-foreground w-28 shrink-0">{{ __('Nhan đề') }}:</span>
                                    <span class="text-foreground font-normal">{{ $fullTitleDisplay }}</span>
                                </div>

                                @if($physDesc)
                                <div class="flex gap-2">
                                    <span class="font-semibold text-muted-foreground w-28 shrink-0">{{ __('Mô tả vật lý') }}:</span>
                                    <span class="text-foreground">{{ $physDesc }}</span>
                                </div>
                                @endif

                                @if($note)
                                <div class="flex gap-2">
                                    <span class="font-semibold text-muted-foreground w-28 shrink-0">{{ __('Ghi chú') }}:</span>
                                    <span class="text-foreground">{{ $note }}</span>
                                </div>
                                @endif

                                @if($summary)
                                <div class="flex gap-2">
                                    <span class="font-semibold text-muted-foreground w-28 shrink-0">{{ __('Tóm tắt') }}:</span>
                                    <span class="text-muted-foreground leading-relaxed">{{ $summary }}</span>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-muted-foreground italic text-xs">
                            <i data-lucide="database-backup" class="w-8 h-8 text-muted-foreground/35 mx-auto mb-2"></i>
                            <p>{{ __('No records found') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
        <div class="p-3 border-t border-border bg-muted/10">
            {{ $records->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // MARC tag and value validation
        const marcTag = document.querySelector('select[name="marc_tag"]');
        const marcValue = document.querySelector('input[name="marc_value"]');

        function validateMarcFields() {
            if (marcTag && marcValue) {
                if (marcTag.value && !marcValue.value) {
                    marcValue.setCustomValidity("{{ __('Please enter a MARC value since a tag is selected') }}");
                } else if (!marcTag.value && marcValue.value) {
                    marcTag.setCustomValidity("{{ __('Please select a MARC tag since a value is entered') }}");
                } else {
                    marcValue.setCustomValidity('');
                    marcTag.setCustomValidity('');
                }
            }
        }

        if (marcTag && marcValue) {
            marcTag.addEventListener('change', validateMarcFields);
            marcValue.addEventListener('input', validateMarcFields);
        }

        // Delete functionality
        document.querySelectorAll('.delete-record').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.dataset.id;
                const title = this.dataset.title;

                Swal.fire({
                    title: '{{ __("Are you sure you want to delete this?") }}',
                    text: `{{ __("You are about to delete record") }}: ${title}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'hsl(var(--destructive))',
                    cancelButtonColor: 'hsl(var(--muted))',
                    confirmButtonText: '{{ __("Yes, delete it!") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                    customClass: {
                        popup: 'bg-card text-foreground border border-border rounded-md p-4',
                        title: 'text-foreground font-bold text-sm',
                        htmlContainer: 'text-muted-foreground text-xs mt-2',
                        confirmButton: 'px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                        cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                    },
                    buttonsStyling: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`{{ route('admin.marc.book.destroy', ['record' => ':id']) }}`.replace(':id', id), {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: '{{ __("Deleted!") }}',
                                        text: data.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'bg-card text-foreground border border-border rounded-md p-4',
                                            title: 'text-foreground font-bold text-sm',
                                            htmlContainer: 'text-muted-foreground text-xs mt-2',
                                            confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider'
                                        },
                                        buttonsStyling: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: '{{ __("Error!") }}',
                                        text: data.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'bg-card text-foreground border border-border rounded-md p-4',
                                            title: 'text-foreground font-bold text-sm',
                                            htmlContainer: 'text-muted-foreground text-xs mt-2',
                                            confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider'
                                        },
                                        buttonsStyling: false
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    title: '{{ __("Error!") }}',
                                    text: '{{ __("An error occurred during deletion") }}',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'bg-card text-foreground border border-border rounded-md p-4',
                                        title: 'text-foreground font-bold text-sm',
                                        htmlContainer: 'text-muted-foreground text-xs mt-2',
                                        confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider'
                                    },
                                    buttonsStyling: false
                                });
                            });
                    }
                });
            });
        });
    });
    
    // Single-click to expand detail row, Double-click to edit record form
    let clickTimer = null;
    let preventClick = false;

    document.querySelectorAll('tbody tr.record-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a, button, input, select, label')) {
                return;
            }
            const recordId = this.dataset.id;
            clickTimer = setTimeout(() => {
                if (!preventClick) {
                    const detailRow = document.getElementById('detail-' + recordId);
                    if (detailRow) {
                        detailRow.classList.toggle('hidden');
                    }
                }
                preventClick = false;
            }, 220);
        });

        row.addEventListener('dblclick', function(e) {
            if (e.target.closest('a, button, input, select, label')) {
                return;
            }
            clearTimeout(clickTimer);
            preventClick = true;
            const editUrl = this.getAttribute('data-edit-url');
            if (editUrl) {
                window.location.href = editUrl + '?tab=0';
            }
        });
    });
</script>
@endpush