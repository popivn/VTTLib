@extends('layouts.admin')

@section('content')
@php
$initialFields = [];
foreach($definitions as $tag) {
    $existingField = isset($record) ? $record->fields->where('tag', $tag->tag)->first() : null;
    $subfieldsData = [['id' => null, 'code' => '', 'value' => '']];
    $subfieldDefinitions = $tag->subfields
        ->map(fn($s) => ['code' => strtolower(ltrim(trim((string) $s->code), '$')), 'label' => $s->label])
        ->keyBy('code');

    if ($existingField && $existingField->subfields->count() > 0) {
        $subfieldsData = $existingField->subfields->map(fn($s) => [
            'id' => $s->id,
            'code' => strtolower(ltrim(trim((string) $s->code), '$')),
            'value' => $s->value
        ])->toArray();

        foreach ($existingField->subfields as $savedSubfield) {
            $savedCode = strtolower(ltrim(trim((string) $savedSubfield->code), '$'));
            if ($savedCode !== '' && !$subfieldDefinitions->has($savedCode)) {
                $subfieldDefinitions->put($savedCode, ['code' => $savedCode, 'label' => __('Saved')]);
            }
        }
    }

    $initialFields[$tag->tag] = [
        'tag' => $tag->tag,
        'label' => $tag->label,
        'ind1' => $existingField->indicator1 ?? ' ',
        'ind2' => $existingField->indicator2 ?? ' ',
        'subfields' => $subfieldsData,
        'subfieldDefinitions' => $subfieldDefinitions->values()
    ];
}

$initialItemsData = (isset($record) && $record->items->count() > 0)
    ? $record->items->map(fn($i) => [
        'id' => $i->id, 
        'branch_id' => $i->branch_id, 
        'storage_location_id' => $i->storage_location_id,
        'barcode' => $i->barcode, 
        'accession_number' => $i->accession_number, 
        'storage_type' => $i->storage_type,
        'status' => $i->status,
        'notes' => $i->notes
    ])->toArray()
    : [];
@endphp

<div class="w-full space-y-4 animate-in fade-in duration-500 pb-20" x-data="catalogWizard()">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-lg font-bold text-foreground tracking-tight">
                {{ isset($record) ? __('Chỉnh sửa bản ghi MARC') : __('Form biên mục MARC21') }}
            </h1>
            <p class="text-xs text-muted-foreground mt-0.5">
                {{ isset($record) ? __('Cập nhật thông tin chi tiết cho bản ghi biên mục #:id', ['id' => $record->id]) : __('Nhập thông tin thư mục và ấn phẩm theo cấu trúc trường MARC21') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.marc.book') }}" class="btn-compact-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                <span>{{ __('Quay lại') }}</span>
            </a>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
        <div class="flex border-b border-border">
            <template x-for="(step, index) in steps" :key="index">
                <button type="button"
                    @click="goToStep(index)"
                    class="flex-1 py-2 px-3 text-xs font-semibold transition-all duration-200 focus:outline-none flex items-center justify-center gap-2 border-r last:border-r-0 border-border"
                    :class="currentStep === index ? 'bg-primary/10 text-primary border-b-2 border-b-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50'">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold"
                        :class="currentStep === index ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                        x-text="index + 1"></span>
                    <span x-text="step.title"></span>
                </button>
            </template>
        </div>
    </div>

    <form id="catalogForm" action="{{ isset($record) ? route('admin.marc.book.update', $record->id) : route('admin.marc.book.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if(isset($record))
            @method('PUT')
        @endif
        <input type="hidden" name="tab" :value="currentStep">

        <!-- Step 1: Leader/Metadata -->
        <div x-show="currentStep === 0" x-cloak class="space-y-4">
            <div class="bg-card text-foreground rounded-md border border-border shadow-sm p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-foreground">{{ __('Leader bản ghi') }}</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ __('Leader là 24 ký tự đầu tiên chứa các thông tin trạng thái, loại bản ghi, v.v.') }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-stretch">
                    <div class="lg:col-span-8 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2 space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">
                                    {{ __('Khung biên mục') }} <span class="text-destructive">*</span>
                                </label>
                                <select x-model="formData.framework" name="framework"
                                    onchange="const url = new URL(window.location); url.searchParams.set('framework_id', this.options[this.selectedIndex].getAttribute('data-id')); window.location.href = url.toString();"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    @foreach($frameworks as $fw)
                                    <option value="{{ $fw->code }}" data-id="{{ $fw->id }}" {{ $frameworkId == $fw->id ? 'selected' : '' }}>
                                        {{ $fw->name }} ({{ $fw->code }})
                                    </option>
                                    @endforeach
                                </select>
                                <p class="text-[9px] text-muted-foreground uppercase tracking-widest font-bold mt-0.5">
                                    {{ __('Trang sẽ tải lại khi thay đổi') }}
                                </p>
                            </div>

                            <div class="md:col-span-2 space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">
                                    {{ __('Kiểu tài liệu') }} <span class="text-destructive">*</span>
                                </label>
                                <select x-model="formData.document_type_id" name="document_type_id"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="">{{ __('Chọn kiểu tài liệu') }}</option>
                                    @foreach($documentTypes as $type)
                                    <option value="{{ $type->id }}" {{ (isset($record) && $record->document_type_id == $type->id) || (!isset($record) && $type->id == (old('document_type_id') ?? null)) ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Trạng thái') }}</label>
                                <select x-model="formData.status" name="status"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="pending">{{ __('Mới') }}</option>
                                    <option value="approved">{{ __('Đã duyệt') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center gap-2 p-3 bg-primary/5 rounded-sm border border-primary/10">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_featured" x-model="formData.is_featured" class="sr-only peer">
                                    <div class="w-9 h-5 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                                <div class="flex flex-col leading-none">
                                    <span class="text-xs font-bold text-foreground">{{ __('Sách nổi bật') }}</span>
                                    <span class="text-[9px] text-destructive font-medium italic mt-0.5">{{ __('Hiển thị ở khu vực nổi bật trên trang chủ') }}</span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Thể loại') }}</label>
                                <select x-model="formData.record_type" name="record_type"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="book">{{ __('Sách') }}</option>
                                    <option value="article">{{ __('Bài trích') }}</option>
                                    <option value="collection">{{ __('Bộ sưu tập') }}</option>
                                    <option value="file">{{ __('Tập tin máy tính') }}</option>
                                    <option value="address">{{ __('Địa chỉ') }}</option>
                                    <option value="map">{{ __('Bản đồ') }}</option>
                                    <option value="mixed">{{ __('Tài liệu hỗn hợp') }}</option>
                                    <option value="audio">{{ __('Âm thanh') }}</option>
                                    <option value="journal">{{ __('Ấn phẩm định kỳ') }}</option>
                                    <option value="digital">{{ __('Số hóa') }}</option>
                                    <option value="resource">{{ __('Tài liệu số') }}</option>
                                    <option value="video">{{ __('Tài liệu chiếu hình') }}</option>
                                    <option value="visual">{{ __('Thiết bị, vật thể') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Kiểu biểu ghi') }}</label>
                                <select x-model="formData.bibliographic_level" name="bibliographic_level"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    @foreach($bibliographicLevels as $level)
                                    <option value="{{ $level->code }}" {{ (isset($record) && $record->bibliographic_level == $level->code) || (!isset($record) && $level->code == 'a') ? 'selected' : '' }}>
                                        {{ app()->getLocale() == 'en' ? $level->name_en : $level->name_vi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Tần suất xuất bản tạp chí') }}</label>
                                <select x-model="formData.serial_frequency" name="serial_frequency"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="unknown">{{ __('Không xác định') }}</option>
                                    <option value="a">{{ __('Hàng năm') }}</option>
                                    <option value="b">{{ __('Hai tháng/kỳ') }}</option>
                                    <option value="c">{{ __('Hai kỳ/tuần') }}</option>
                                    <option value="d">{{ __('Nhật báo') }}</option>
                                    <option value="e">{{ __('Hai tuần/kỳ') }}</option>
                                    <option value="f">{{ __('Hai kỳ/năm') }}</option>
                                    <option value="g">{{ __('Hai năm/kỳ') }}</option>
                                    <option value="h">{{ __('Ba năm/kỳ') }}</option>
                                    <option value="i">{{ __('Ba kỳ/tuần') }}</option>
                                    <option value="j">{{ __('Ba kỳ/tháng') }}</option>
                                    <option value="m">{{ __('Báo tháng') }}</option>
                                    <option value="q">{{ __('Báo quý') }}</option>
                                    <option value="s">{{ __('Hai kỳ/tháng') }}</option>
                                    <option value="t">{{ __('Ba kỳ/năm') }}</option>
                                    <option value="u">{{ __('Không biết') }}</option>
                                    <option value="w">{{ __('Tuần báo') }}</option>
                                    <option value="z">{{ __('Khác') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Loại ngày xuất bản') }}</label>
                                <select x-model="formData.date_type" name="date_type"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="bc">{{ __('Có liên quan đến ngày trước CN') }}</option>
                                    <option value="c">{{ __('Ấn phẩm tiếp diễn đang xuất bản') }}</option>
                                    <option value="d">{{ __('Ấn phẩm tiếp diễn đã ngừng xuất bản') }}</option>
                                    <option value="e">{{ __('Ngày chi tiết') }}</option>
                                    <option value="i">{{ __('Ngày bao gồm của bộ sưu tập') }}</option>
                                    <option value="k">{{ __('Khoảng năm của phần lớn bộ sưu tập') }}</option>
                                    <option value="m">{{ __('Nhiều ngày') }}</option>
                                    <option value="n">{{ __('Ngày không xác định') }}</option>
                                    <option value="p">{{ __('Ngày phân phối và sản xuất khác nhau') }}</option>
                                    <option value="q">{{ __('Ngày nghi vấn') }}</option>
                                    <option value="r">{{ __('Ngày in lại và ngày gốc') }}</option>
                                    <option value="s">{{ __('Một ngày xác định hoặc có thể') }}</option>
                                    <option value="t">{{ __('Ngày xuất bản và ngày bản quyền') }}</option>
                                    <option value="u">{{ __('Trạng thái ấn phẩm tiếp diễn không xác định') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Hình thức nhận (Tạp chí)') }}</label>
                                <select x-model="formData.acquisition_method" name="acquisition_method"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="vol_date">{{ __('Tập và ngày tháng năm') }}</option>
                                    <option value="untraced">{{ __('Ấn phẩm không theo dõi') }}</option>
                                    <option value="date">{{ __('Ngày tháng năm') }}</option>
                                    <option value="month_year">{{ __('Tháng và năm') }}</option>
                                    <option value="season_year">{{ __('Mùa và năm') }}</option>
                                    <option value="year">{{ __('Năm') }}</option>
                                    <option value="vol">{{ __('Tập') }}</option>
                                    <option value="vol_month_year">{{ __('Tập, tháng và năm') }}</option>
                                    <option value="vol_year">{{ __('Tập và năm') }}</option>
                                    <option value="vol_season_year">{{ __('Tập, mùa và năm') }}</option>
                                    <option value="other">{{ __('Khác') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Định dạng tài liệu') }}</label>
                                <select x-model="formData.document_format" name="document_format"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="none">{{ __('Không có định dạng đặc biệt') }}</option>
                                    <option value="a">{{ __('Vi phim') }}</option>
                                    <option value="b">{{ __('Vi phiếu') }}</option>
                                    <option value="c">{{ __('Vi phiếu mờ') }}</option>
                                    <option value="f">{{ __('Chữ in lớn') }}</option>
                                    <option value="g">{{ __('Chữ nổi') }}</option>
                                    <option value="r">{{ __('Bản sao, bản in thông thường') }}</option>
                                    <option value="s">{{ __('Điện tử') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Chuẩn biên mục') }}</label>
                                <select x-model="formData.cataloging_standard" name="cataloging_standard"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="AACR2">AACR-2</option>
                                    <option value="ISBD">ISBD</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Chủ đề') }}</label>
                                <select x-model="formData.subject_category" name="subject_category"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    <option value="Article">{{ __('Bài trích chọn lọc') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col justify-start space-y-2">
                        <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider text-center">{{ __('Ảnh bìa') }}</label>
                        <div class="relative group cursor-pointer w-full max-w-[200px] mx-auto" @click="$refs.coverInput.click()">
                            <div class="relative w-full aspect-[3/4] rounded-md overflow-hidden border border-border shadow-sm bg-muted flex items-center justify-center">
                                <img :src="coverPreview" class="w-full h-full object-contain">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center">
                                    <span class="bg-card text-foreground px-3 py-1.5 rounded-sm text-[10px] font-bold shadow border border-border">
                                        {{ __('Thay đổi ảnh bìa') }}
                                    </span>
                                </div>
                                <button type="button" @click.stop="removeCover()" class="absolute top-2 right-2 bg-destructive text-destructive-foreground p-1 rounded-sm shadow opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            <input type="file" name="cover_image" id="cover_image_input" x-ref="coverInput" class="hidden" accept="image/*" @change="previewCover($event)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: MARC Fields -->
        <div x-show="currentStep === 1" x-cloak class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 bg-primary/5 rounded-sm border border-primary/10 gap-3">
                <div class="text-xs font-bold text-primary flex items-center gap-1.5">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>{{ __('Thêm trường tùy chỉnh cho Snapshot này') }}</span>
                </div>
                <div class="flex gap-2">
                    <select id="quick_add_tag" class="h-9 px-3 py-1.5 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none">
                        <option value="">-- {{ __('Chọn Tag để thêm') }} --</option>
                        @foreach(\App\Models\MarcTagDefinition::orderBy('tag')->get() as $t)
                            <option value="{{ $t->tag }}">{{ $t->tag }} - {{ $t->label }}</option>
                        @endforeach
                    </select>
                    <button type="button" @click="addTagToSnapshot()" class="btn-compact-primary h-9">
                        {{ __('Thêm Tag') }}
                    </button>
                </div>
            </div>

            @foreach($definitions as $tag)
            <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden"
                x-data="{ expanded: {{ $tag->tag == '245' ? 'true' : 'false' }} }">
                <div class="p-3 bg-muted/30 flex justify-between items-center cursor-pointer border-b border-border"
                    @click="expanded = !expanded">
                    <div class="flex items-center gap-3">
                        <span class="bg-primary/10 border border-primary/20 text-primary px-2 py-0.5 rounded-sm font-mono font-bold text-xs">{{ $tag->tag }}</span>
                        <h4 class="font-bold text-foreground uppercase text-[10px] tracking-wider">{{ $tag->label }}</h4>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center">
                            <span class="text-[8px] text-muted-foreground font-bold uppercase mb-0.5">Ind</span>
                            <div class="flex gap-1">
                                <input type="text" name="fields[{{ $tag->tag }}][ind1]" x-model="marcFields['{{ $tag->tag }}'].ind1" placeholder="#" maxlength="1"
                                    class="w-6 h-6 p-0 text-center border border-input rounded-sm text-xs font-mono uppercase bg-background text-foreground focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                                    @click.stop>
                                <input type="text" name="fields[{{ $tag->tag }}][ind2]" x-model="marcFields['{{ $tag->tag }}'].ind2" placeholder="#" maxlength="1"
                                    class="w-6 h-6 p-0 text-center border border-input rounded-sm text-xs font-mono uppercase bg-background text-foreground focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                                    @click.stop>
                            </div>
                        </div>

                        <button type="button" 
                            @click.stop="
                                Swal.fire({
                                    title: '{{ __('Xác nhận xóa Tag?') }}',
                                    text: '{{ __('Bạn có chắc chắn muốn xóa Tag này khỏi bản ghi? Hành động này không thể hoàn tác.') }}',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: 'hsl(var(--destructive))',
                                    cancelButtonColor: 'hsl(var(--muted))',
                                    confirmButtonText: '{{ __('Xóa ngay') }}',
                                    cancelButtonText: '{{ __('Hủy bỏ') }}',
                                    customClass: {
                                        popup: 'bg-card text-foreground border border-border rounded-md p-4',
                                        title: 'text-foreground font-bold text-sm',
                                        htmlContainer: 'text-muted-foreground text-xs mt-2',
                                        confirmButton: 'px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                                        cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                                    },
                                    buttonsStyling: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        delete marcFields['{{ $tag->tag }}']; 
                                        $el.closest('.bg-card').remove();
                                        isDirty = true;
                                    }
                                })
                            "
                            class="btn-icon-danger">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>

                        <button type="button" class="btn-icon-compact text-muted-foreground">
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''"></i>
                        </button>
                    </div>
                </div>

                <div class="p-3" x-show="expanded" x-cloak x-collapse>
                    @if(intval($tag->tag) < 10)
                    <div class="space-y-3">
                        <template x-for="(row, index) in marcFields['{{ $tag->tag }}'].subfields" :key="index">
                            <div class="flex flex-col md:flex-row gap-3 items-start bg-muted/20 p-3 rounded-sm border border-border">
                                <input type="hidden" :name="'fields[' + '{{ $tag->tag }}' + '][subfields][' + index + '][code]'" value="_">
                                <input type="hidden" :name="'fields[' + '{{ $tag->tag }}' + '][subfields][' + index + '][id]'" x-model="row.id">
                                <div class="w-full flex gap-3 items-center">
                                    <span class="shrink-0 px-2 py-1 bg-muted text-muted-foreground border border-border rounded-sm text-xs font-mono font-bold">{{ $tag->label }}</span>
                                    <input type="text"
                                        :name="'fields[' + '{{ $tag->tag }}' + '][subfields][' + index + '][value]'"
                                        x-model="row.value"
                                        placeholder="{{ __('Nhập giá trị') }}"
                                        class="flex-1 h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary font-mono transition-all">
                                </div>
                            </div>
                        </template>
                    </div>
                    @else
                    <div class="space-y-3">
                        <template x-for="(row, index) in marcFields['{{ $tag->tag }}'].subfields" :key="index">
                            <div class="flex flex-col md:flex-row gap-3 items-start bg-muted/20 p-3 rounded-sm border border-border group hover:border-primary/30 transition-colors">
                                <div class="w-full md:w-1/3">
                                    <div class="relative group/sub">
                                        <select :name="'fields[' + '{{ $tag->tag }}' + '][subfields][' + index + '][code]'"
                                            x-model="row.code"
                                            x-init="row.code = ((row.code ?? '').toString().trim().replace(/^\$/, ''))"
                                            x-effect="$el.value = ((row.code ?? '').toString().trim().replace(/^\$/, '').toLowerCase())"
                                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary font-mono transition-all appearance-none cursor-pointer">
                                            <option value="">{{ __('Chọn trường con') }}</option>
                                            <template x-for="def in marcFields['{{ $tag->tag }}'].subfieldDefinitions" :key="def.code">
                                                <option :value="def.code" :selected="def.code === ((row.code ?? '').toString().trim().replace(/^\$/, '').toLowerCase())" x-text="'$' + def.code + ' ' + def.label"></option>
                                            </template>
                                            <template x-if="row.code && !marcFields['{{ $tag->tag }}'].subfieldDefinitions.find(d => d.code === row.code)">
                                                <option :value="row.code" selected x-text="'$' + row.code + ' (Tùy chỉnh)'"></option>
                                            </template>
                                        </select>
                                        <button type="button" 
                                            @click="
                                                Swal.fire({
                                                    title: '<span class=\'text-sm font-bold uppercase tracking-wider\'>Chỉnh sửa Subfield</span>',
                                                    html: `
                                                        <div class='text-left space-y-3 pt-3'>
                                                            <div class='space-y-1'>
                                                                <label class='text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1'>Mã Subfield (1 ký tự)</label>
                                                                <div class='relative'>
                                                                    <span class='absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground/60 font-mono text-sm'>$</span>
                                                                    <input id='swal-input-code' 
                                                                        class='w-full h-9 pl-7 pr-3 py-1.5 bg-background border border-input rounded-sm text-sm font-mono focus:ring-1 focus:ring-primary focus:border-primary transition-all outline-none text-foreground' 
                                                                        maxlength='1' 
                                                                        value='${row.code}' 
                                                                        placeholder='a'>
                                                                </div>
                                                            </div>
                                                            <div class='space-y-1'>
                                                                <label class='text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1'>Tên hiển thị / Mô tả</label>
                                                                <input id='swal-input-label' 
                                                                    class='w-full h-9 px-3 py-1.5 bg-background border border-input rounded-sm text-sm focus:ring-1 focus:ring-primary focus:border-primary transition-all outline-none text-foreground' 
                                                                    value='${getSubfieldLabel('{{ $tag->tag }}', row.code)}' 
                                                                    placeholder='Nhập tên trường con...'>
                                                            </div>
                                                        </div>
                                                    `,
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Cập nhật',
                                                    cancelButtonText: 'Hủy',
                                                    confirmButtonColor: 'hsl(var(--primary))',
                                                    cancelButtonColor: 'hsl(var(--muted))',
                                                    customClass: {
                                                        popup: 'bg-card text-foreground border border-border rounded-md p-4 w-80',
                                                        title: 'text-foreground font-bold text-sm border-b border-border pb-2',
                                                        confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                                                        cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                                                    },
                                                    buttonsStyling: false,
                                                    focusConfirm: false,
                                                    preConfirm: () => {
                                                        const newCode = document.getElementById('swal-input-code').value.toLowerCase().replace(/^\$/, '').substring(0, 1);
                                                        const newLabel = document.getElementById('swal-input-label').value;
                                                        if (!newCode) {
                                                            Swal.showValidationMessage('Vui lòng nhập mã hiệu');
                                                            return false;
                                                        }
                                                        return { code: newCode, label: newLabel };
                                                    }
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        const { code, label } = result.value;
                                                        row.code = code;
                                                        const tagData = marcFields['{{ $tag->tag }}'];
                                                        const existingDef = tagData.subfieldDefinitions.find(d => d.code === code);
                                                        if (existingDef) {
                                                            existingDef.label = label;
                                                        } else {
                                                            tagData.subfieldDefinitions.push({ code: code, label: label });
                                                        }
                                                        isDirty = true;
                                                    }
                                                })
                                            "
                                            class="absolute right-7 top-1/2 -translate-y-1/2 text-[9px] bg-primary/10 text-primary px-1.5 py-0.5 rounded-sm font-bold opacity-0 group-hover/sub:opacity-100 transition-opacity border border-primary/20">
                                            Đổi mã
                                        </button>
                                    </div>
                                </div>

                                <div class="w-full md:w-2/3 flex gap-2">
                                    <input type="hidden" :name="'fields[' + '{{ $tag->tag }}' + '][subfields][' + index + '][id]'" x-model="row.id">
                                    <input type="text"
                                        :name="'fields[' + '{{ $tag->tag }}' + '][subfields][' + index + '][value]'"
                                        x-model="row.value"
                                        placeholder="{{ __('Nhập giá trị') }}"
                                        class="flex-1 h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">

                                    <button type="button" @click="
                                        Swal.fire({
                                            title: '{{ __('Xác nhận xóa?') }}',
                                            text: '{{ __('Xóa trường con này?') }}',
                                            icon: 'question',
                                            showCancelButton: true,
                                            confirmButtonColor: 'hsl(var(--destructive))',
                                            cancelButtonColor: 'hsl(var(--muted))',
                                            confirmButtonText: '{{ __('Xóa') }}',
                                            cancelButtonText: '{{ __('Hủy') }}',
                                            customClass: {
                                                popup: 'bg-card text-foreground border border-border rounded-md p-4',
                                                title: 'text-foreground font-bold text-sm',
                                                htmlContainer: 'text-muted-foreground text-xs mt-2',
                                                confirmButton: 'px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                                                cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                                            },
                                            buttonsStyling: false
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                marcFields['{{ $tag->tag }}'].subfields.splice(index, 1);
                                                isDirty = true;
                                            }
                                        })
                                    "
                                        class="btn-icon-danger opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="marcFields['{{ $tag->tag }}'].subfields.push({ id: null, code: '', value: '' })"
                        class="mt-3 btn-compact-secondary">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                        <span>{{ __('Thêm trường con') }}</span>
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Step 3: Distribution -->
        <div x-show="currentStep === 2" x-cloak class="space-y-4">
            @include('admin.marc_books.components.items_tab')
        </div>

        <!-- Step 4: Preview -->
        <div x-show="currentStep === 3" x-cloak class="space-y-4">
            @include('admin.marc_books.components.preview_tab')
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-start bg-card text-foreground p-3 rounded-md border border-border shadow-sm">
            <button type="button" @click="submitForm()" class="btn-compact-primary flex items-center gap-1.5 py-2.5 px-6">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>{{ isset($record) ? __('Cập nhật') : __('Lưu bản ghi mới') }}</span>
            </button>
        </div>
    </form>
</div>

<script>
    function catalogWizard() {
        return {
            currentStep: parseInt(new URLSearchParams(window.location.search).get('tab')) || 0,
            isDirty: false,
            steps: [
                { title: '{{ __("Thông tin Leader") }}' },
                { title: '{{ __("Biên mục") }}' },
                { title: '{{ __("Phân bổ") }}' },
                { title: '{{ __("Xem trước") }}' }
            ],
            formData: {
                framework: "{{ $currentFramework->code ?? ($record?->framework ?? 'AVMARC21') }}",
                status: "{{ $record?->status ?? 'pending' }}",
                is_featured: {{ $record && $record->is_featured ? 'true' : 'false' }},
                subject_category: "{{ $record?->subject_category ?? 'Article' }}",
                record_type: "{{ $record?->record_type ?? 'book' }}",
                bibliographic_level: "{{ $record?->bibliographic_level ?? 'a' }}",
                serial_frequency: "{{ $record?->serial_frequency ?? 'unknown' }}",
                date_type: "{{ $record?->date_type ?? 'bc' }}",
                acquisition_method: "{{ $record?->acquisition_method ?? 'untraced' }}",
                document_format: "{{ $record?->document_format ?? 'none' }}",
                cataloging_standard: "{{ $record?->cataloging_standard ?? 'AACR2' }}",
                document_type_id: {{ $record?->document_type_id ?? 'null' }}
            },
            marcFields: @json($initialFields),
            items: @json($initialItemsData),
            branches: @json($branches),
            editingIndex: null,
            batchQuantity: 1,
            newItem: {
                id: null,
                branch_id: '',
                storage_location_id: '',
                barcode: '',
                accession_number: '',
                storage_type: 'Book',
                quantity: 1,
                status: 'available',
                order_code: '',
                waits_for_print: false,
                notes: '',
                volume_issue: '',
                day: '',
                month_season: '',
                year: '',
                shelf: '',
                shelf_position: '',
                location: '',
                temporary_location: ''
            },
            get activeLocations() {
                if (!this.newItem.branch_id) return [];
                const branch = this.branches.find(b => b.id == this.newItem.branch_id);
                return branch ? branch.storage_locations : [];
            },
            getBranchName(id) {
                const branch = this.branches.find(b => b.id == id);
                return branch ? branch.name : '-';
            },
            getLocationName(branchId, locationId) {
                const branch = this.branches.find(b => b.id == branchId);
                if (!branch) return '-';
                const loc = branch.storage_locations.find(l => l.id == locationId);
                return loc ? loc.name : '-';
            },
            coverPreview: "{{ (isset($record) && $record->cover_image) ? asset('storage/' . $record->cover_image) : 'https://placehold.co/600x800/680102/white?text=VTTLib+Book' }}",
            
            init() {
                this.$watch('formData', () => this.isDirty = true);
                this.$watch('marcFields', () => this.isDirty = true);
                this.$watch('items', () => {
                    this.isDirty = true;
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                });
                this.$watch('currentStep', () => {
                    this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                });
                
                // Initial load of icons
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                
                // Khởi tạo Toast
                window.Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            },
            
            get leader() {
                let s = '00000';
                s += (this.formData.status === 'pending' ? 'n' : 'c');
                s += (this.formData.record_type === 'book' ? 'a' : 'm');
                s += 'm  a2200000 ';
                s += (this.formData.cataloging_standard === 'AACR2' ? 'i' : 'a');
                s += ' 4500';
                return s;
            },

            addTagToSnapshot() {
                const selectEl = document.getElementById('quick_add_tag');
                const selectedTag = selectEl ? selectEl.value : '';
                
                if (selectedTag) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('add_tag', selectedTag);
                    url.searchParams.set('tab', 1);
                    window.location.href = url.toString();
                } else {
                    Swal.fire({
                        title: '<span class="text-xs font-bold uppercase tracking-wider">{{ __("Thêm trường MARC mới") }}</span>',
                        input: 'text',
                        inputLabel: '{{ __("Nhập số hiệu Tag (ví dụ: 082, 852)") }}',
                        inputPlaceholder: 'Số hiệu tag...',
                        showCancelButton: true,
                        confirmButtonText: '{{ __("Thêm ngay") }}',
                        cancelButtonText: '{{ __("Hủy") }}',
                        confirmButtonColor: 'hsl(var(--primary))',
                        cancelButtonColor: 'hsl(var(--muted))',
                        customClass: {
                            popup: 'bg-card text-foreground border border-border rounded-md p-4 w-80',
                            title: 'text-foreground font-bold text-sm border-b border-border pb-2',
                            input: 'w-full !mx-0 h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground mt-2 focus:ring-1 focus:ring-primary focus:border-primary',
                            inputLabel: 'text-[10px] text-muted-foreground uppercase font-bold tracking-wider mb-1 mt-2',
                            confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                            cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                        },
                        buttonsStyling: false,
                        preConfirm: (value) => {
                            if (!value || isNaN(value)) {
                                Swal.showValidationMessage('Vui lòng nhập số hiệu tag hợp lệ');
                                return false;
                            }
                            return value;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const newTag = result.value;
                            const url = new URL(window.location.href);
                            url.searchParams.set('add_tag', newTag);
                            url.searchParams.set('tab', 1);
                            window.location.href = url.toString();
                            window.Toast.fire({ icon: 'success', title: 'Đã thêm trường MARC mới' });
                        }
                    });
                }
            },

            removeTag(tag) {
                Swal.fire({
                    title: '{{ __('Xác nhận xóa?') }}',
                    text: '{{ __('Trường MARC') }} ' + tag + ' {{ __('sẽ bị loại bỏ khỏi bản ghi này.') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'hsl(var(--destructive))',
                    cancelButtonColor: 'hsl(var(--muted))',
                    confirmButtonText: '{{ __('Đúng, xóa nó!') }}',
                    cancelButtonText: '{{ __('Hủy') }}',
                    customClass: {
                        popup: 'bg-card text-foreground border border-border rounded-md p-4',
                        title: 'text-foreground font-bold text-sm',
                        htmlContainer: 'text-muted-foreground text-xs mt-2',
                        confirmButton: 'px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                        cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        delete this.marcFields[tag];
                        this.isDirty = true;
                        window.Toast.fire({ icon: 'success', title: '{{ __('Đã xóa trường') }} ' + tag });
                    }
                });
            },

            addSubfield(tag) {
                this.marcFields[tag].subfields.push({ id: null, code: '', value: '' });
                this.isDirty = true;
                window.Toast.fire({ icon: 'success', title: '{{ __('Đã thêm trường con mới') }}' });
            },

            removeSubfield(tag, index) {
                this.marcFields[tag].subfields.splice(index, 1);
                this.isDirty = true;
                window.Toast.fire({ icon: 'success', title: '{{ __('Đã xóa trường con') }}' });
            },
            
            goToStep(index) {
                if (this.isDirty) {
                    Swal.fire({
                        title: '{{ __('Thay đổi chưa lưu!') }}',
                        text: '{{ __('Bạn có muốn tiếp tục mà không lưu không?') }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: 'hsl(var(--destructive))',
                        cancelButtonColor: 'hsl(var(--muted))',
                        confirmButtonText: '{{ __('Tiếp tục') }}',
                        cancelButtonText: '{{ __('Ở lại') }}',
                        customClass: {
                            popup: 'bg-card text-foreground border border-border rounded-md p-4',
                            title: 'text-foreground font-bold text-sm',
                            htmlContainer: 'text-muted-foreground text-xs mt-2',
                            confirmButton: 'px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                            cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.isDirty = false;
                            this.performGoToStep(index);
                        }
                    });
                } else {
                    this.performGoToStep(index);
                }
            },

            performGoToStep(index) {
                this.currentStep = index;
                const url = new URL(window.location);
                url.searchParams.set('tab', index);
                window.history.pushState({}, '', url);
            },

            previewCover(event) {
                const file = event.target.files[0];
                if (file) this.coverPreview = URL.createObjectURL(file);
            },

            removeCover() {
                this.coverPreview = 'https://placehold.co/600x800/680102/white?text=VTTLib+Book';
                document.getElementById('cover_image_input').value = '';
            },

            async addItem() {
                if (!this.newItem.storage_location_id) {
                    Swal.fire({
                        title: 'Cảnh báo',
                        text: 'Vui lòng chọn kho lưu trữ.',
                        icon: 'warning',
                        confirmButtonColor: 'hsl(var(--primary))',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'bg-card text-foreground border border-border rounded-md p-4',
                            title: 'text-foreground font-bold text-sm',
                            htmlContainer: 'text-muted-foreground text-xs mt-2',
                            confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (this.editingIndex !== null) {
                    this.items[this.editingIndex] = JSON.parse(JSON.stringify(this.newItem));
                    this.editingIndex = null;
                } else {
                    const quantity = parseInt(this.batchQuantity) || 1;
                    const baseBarcode = this.newItem.barcode || '';
                    const baseAcc = this.newItem.accession_number || '';

                    // Parse barcode: extract prefix and numeric suffix
                    let bcPrefix = '', bcNum = null, bcPad = 0;
                    if (baseBarcode) {
                        const m = baseBarcode.match(/^(.*?)(\d+)$/);
                        if (m) { bcPrefix = m[1]; bcNum = parseInt(m[2]); bcPad = m[2].length; }
                    }

                    // Parse accession_number: extract prefix and numeric suffix
                    let accPrefix = '', accNum = null, accPad = 0;
                    if (baseAcc) {
                        const m = baseAcc.match(/^(.*?)(\d+)$/);
                        if (m) { accPrefix = m[1]; accNum = parseInt(m[2]); accPad = m[2].length; }
                    }

                    // Generate all barcodes and accession numbers first
                    const pendingItems = [];
                    const pendingBarcodes = [];
                    const pendingAccs = [];

                    for (let i = 0; i < quantity; i++) {
                        const itemToAdd = JSON.parse(JSON.stringify(this.newItem));

                        if (baseBarcode && bcNum !== null) {
                            itemToAdd.barcode = bcPrefix + String(bcNum + i).padStart(bcPad, '0');
                        }
                        if (baseAcc && accNum !== null) {
                            itemToAdd.accession_number = accPrefix + String(accNum + i).padStart(accPad, '0');
                        }

                        pendingItems.push(itemToAdd);
                        if (itemToAdd.barcode) pendingBarcodes.push(itemToAdd.barcode);
                        if (itemToAdd.accession_number) pendingAccs.push(itemToAdd.accession_number);
                    }

                    // Check duplicates within the pending batch itself
                    const bcSet = new Set();
                    const accSet = new Set();
                    for (const bc of pendingBarcodes) {
                        if (bcSet.has(bc)) {
                            Swal.fire({
                                title: '{{ __("Lỗi") }}',
                                text: '{{ __("Mã vạch :bc bị trùng lặp trong danh sách.") }}'.replace(':bc', bc),
                                icon: 'error',
                                confirmButtonColor: 'hsl(var(--primary))',
                                confirmButtonText: 'OK',
                                customClass: { popup: 'bg-card text-foreground border border-border rounded-md p-4', title: 'text-foreground font-bold text-sm', htmlContainer: 'text-muted-foreground text-xs mt-2', confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider' },
                                buttonsStyling: false
                            });
                            return;
                        }
                        bcSet.add(bc);
                    }

                    // Check duplicates against existing items in queue (excluding items being edited)
                    for (const bc of pendingBarcodes) {
                        const dup = this.items.find((it, idx) => it.barcode === bc);
                        if (dup) {
                            Swal.fire({
                                title: '{{ __("Lỗi") }}',
                                text: '{{ __("Mã vạch :bc đã tồn tại trong danh sách.") }}'.replace(':bc', bc),
                                icon: 'error',
                                confirmButtonColor: 'hsl(var(--primary))',
                                confirmButtonText: 'OK',
                                customClass: { popup: 'bg-card text-foreground border border-border rounded-md p-4', title: 'text-foreground font-bold text-sm', htmlContainer: 'text-muted-foreground text-xs mt-2', confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider' },
                                buttonsStyling: false
                            });
                            return;
                        }
                    }

                    for (const acc of pendingAccs) {
                        const dup = this.items.find((it, idx) => it.accession_number === acc);
                        if (dup) {
                            Swal.fire({
                                title: '{{ __("Lỗi") }}',
                                text: '{{ __("Số đăng ký cá biệt :acc đã tồn tại trong danh sách.") }}'.replace(':acc', acc),
                                icon: 'error',
                                confirmButtonColor: 'hsl(var(--primary))',
                                confirmButtonText: 'OK',
                                customClass: { popup: 'bg-card text-foreground border border-border rounded-md p-4', title: 'text-foreground font-bold text-sm', htmlContainer: 'text-muted-foreground text-xs mt-2', confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider' },
                                buttonsStyling: false
                            });
                            return;
                        }
                    }

                    // Check duplicates against database via AJAX
                    if (pendingBarcodes.length > 0) {
                        Swal.fire({
                            title: '{{ __("Đang kiểm tra...") }}',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading(),
                            customClass: { popup: 'bg-card text-foreground border border-border rounded-md p-4' }
                        });

                        try {
                            const checkPromises = [];
                            for (const bc of pendingBarcodes) {
                                const url = '{{ route("admin.marc.book.distribution.check") }}?barcode=' + encodeURIComponent(bc) + '&type=barcode';
                                checkPromises.push(fetch(url).then(r => r.json()).then(data => ({ bc, exists: data.exists })));
                            }
                            for (const acc of pendingAccs) {
                                const url = '{{ route("admin.marc.book.distribution.check") }}?barcode=' + encodeURIComponent(acc) + '&type=accession_number';
                                checkPromises.push(fetch(url).then(r => r.json()).then(data => ({ acc, exists: data.exists })));
                            }

                            const results = await Promise.all(checkPromises);
                            Swal.close();

                            for (const r of results) {
                                if (r.bc && r.exists) {
                                    Swal.fire({
                                        title: '{{ __("Lỗi") }}',
                                        text: '{{ __("Mã vạch :bc đã tồn tại trong hệ thống.") }}'.replace(':bc', r.bc),
                                        icon: 'error',
                                        confirmButtonColor: 'hsl(var(--primary))',
                                        confirmButtonText: 'OK',
                                        customClass: { popup: 'bg-card text-foreground border border-border rounded-md p-4', title: 'text-foreground font-bold text-sm', htmlContainer: 'text-muted-foreground text-xs mt-2', confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider' },
                                        buttonsStyling: false
                                    });
                                    return;
                                }
                                if (r.acc && r.exists) {
                                    Swal.fire({
                                        title: '{{ __("Lỗi") }}',
                                        text: '{{ __("Số đăng ký cá biệt :acc đã tồn tại trong hệ thống.") }}'.replace(':acc', r.acc),
                                        icon: 'error',
                                        confirmButtonColor: 'hsl(var(--primary))',
                                        confirmButtonText: 'OK',
                                        customClass: { popup: 'bg-card text-foreground border border-border rounded-md p-4', title: 'text-foreground font-bold text-sm', htmlContainer: 'text-muted-foreground text-xs mt-2', confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider' },
                                        buttonsStyling: false
                                    });
                                    return;
                                }
                            }
                        } catch (e) {
                            Swal.close();
                            console.error('Check duplicate error:', e);
                        }
                    }

                    // All checks passed, add items
                    for (const item of pendingItems) {
                        this.items.push(item);
                    }
                }
                this.resetNewItem();
                this.batchQuantity = 1;
            },

            editItem(index) {
                this.editingIndex = index;
                this.newItem = JSON.parse(JSON.stringify(this.items[index]));
            },

            removeItem(index) {
                Swal.fire({
                    title: 'Xác nhận xóa?',
                    text: 'Xóa bản sách này khỏi hàng đợi?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'hsl(var(--destructive))',
                    cancelButtonColor: 'hsl(var(--muted))',
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy',
                    customClass: {
                        popup: 'bg-card text-foreground border border-border rounded-md p-4',
                        title: 'text-foreground font-bold text-sm',
                        htmlContainer: 'text-muted-foreground text-xs mt-2',
                        confirmButton: 'px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 rounded-sm text-xs font-bold uppercase tracking-wider mx-1',
                        cancelButton: 'px-4 py-2 bg-muted text-foreground hover:bg-muted/80 rounded-sm text-xs font-bold uppercase tracking-wider border border-border mx-1'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.items.splice(index, 1);
                        this.isDirty = true;
                    }
                });
            },

            resetNewItem() {
                this.newItem = {
                    id: null, branch_id: '', storage_location_id: '', barcode: '',
                    accession_number: '', storage_type: 'Book', quantity: 1, status: 'available',
                    order_code: '', waits_for_print: false, notes: '', volume_issue: '',
                    day: '', month_season: '', year: '', shelf: '', shelf_position: '',
                    location: '', temporary_location: ''
                };
                this.editingIndex = null;
            },

            getSubfieldLabel(tag, code) {
                if (!this.marcFields[tag]) return '';
                const normalizedCode = (code ?? '').toString().trim().replace(/^\$/, '').toLowerCase();
                const def = this.marcFields[tag].subfieldDefinitions.find(d => d.code === normalizedCode);
                return def ? def.label : '';
            },

            getActiveFields() {
                return Object.values(this.marcFields).filter(f => 
                    f.subfields.some(s => (s.code && s.code.trim() !== '') || (s.value && s.value.trim() !== ''))
                );
            },

            submitForm() {
                const form = document.getElementById('catalogForm');
                const formData = new FormData(form);

                Swal.fire({
                    title: 'Đang lưu...',
                    text: 'Vui lòng chờ trong giây lát',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'bg-card text-foreground border border-border rounded-md p-4',
                        title: 'text-foreground font-bold text-sm',
                        htmlContainer: 'text-muted-foreground text-xs mt-2'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(async response => {
                    const isJson = response.headers.get('content-type')?.includes('application/json');
                    const data = isJson ? await response.json() : null;

                    if (!response.ok) {
                        throw new Error(data?.message || `Lỗi hệ thống (${response.status})`);
                    }
                    return data;
                })
                .then(data => {
                    if (data && data.success) {
                        this.isDirty = false;
                        Swal.fire({
                            title: 'Thành công!',
                            text: data.message || 'Bản ghi đã được lưu.',
                            icon: 'success',
                            confirmButtonColor: 'hsl(var(--primary))',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'bg-card text-foreground border border-border rounded-md p-4',
                                title: 'text-foreground font-bold text-sm',
                                htmlContainer: 'text-muted-foreground text-xs mt-2',
                                confirmButton: 'px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 rounded-sm text-xs font-bold uppercase tracking-wider'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            window.location.href = data.redirect || '{{ route('admin.marc.book') }}';
                        });
                    } else {
                        throw new Error(data?.message || 'Có lỗi xảy ra khi lưu bản ghi.');
                    }
                })
                .catch(error => {
                    console.error('Save error:', error);
                    Swal.fire({
                        title: 'Lỗi hệ thống!',
                        text: error.message || 'Không thể kết nối tới máy chủ.',
                        icon: 'error',
                        confirmButtonColor: 'hsl(var(--destructive))',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'bg-card text-foreground border border-border rounded-md p-4',
                            title: 'text-foreground font-bold text-sm',
                            htmlContainer: 'text-muted-foreground text-xs mt-2',
                            confirmButton: 'px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 rounded-sm text-xs font-bold uppercase tracking-wider'
                        },
                        buttonsStyling: false
                    });
                });
            },
            
            debugSubfieldBindings() {}
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
