@extends('layouts.admin')

@section('content')
<div class="space-y-4 pb-10">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-card p-3 rounded-md border border-border sticky top-0 z-20 backdrop-blur-sm shadow-sm">
        <div>
            <h1 class="text-lg font-bold text-foreground">{{ __('Create_News') }}</h1>
            <p class="text-[10px] text-muted-foreground uppercase tracking-widest mt-0.5">{{ __('Create_News_Instruction') }}</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" onclick="autoGenerateNews()" 
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded bg-muted text-muted-foreground hover:bg-muted/80 border border-border active:scale-95 transition-all text-xs font-semibold cursor-pointer">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                {{ __('Auto_Generate') }}
            </button>
            <a href="{{ route('admin.news.index') }}" 
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded bg-muted text-muted-foreground hover:bg-muted/80 border border-border active:scale-95 transition-all text-xs font-semibold cursor-pointer">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                {{ __('Back') }}
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')
        
        <div class="space-y-4">
            <!-- Main Content (Full Width) -->
            <div class="space-y-4">
                <!-- Basic Information -->
                <div class="bg-card rounded-md border border-border p-4 shadow-sm">
                    <h3 class="text-xs font-bold mb-3 text-primary uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        {{ __('Basic_Information') }}
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Title') }} *</label>
                                <input type="text" name="title" required
                                       class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary" 
                                       placeholder="{{ __('Enter article title') }}"
                                       value="{{ old('title') }}">
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Slug (URL)</label>
                                <input type="text" name="slug" 
                                       class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary" 
                                       placeholder="{{ __('Auto-generated from title') }}"
                                       value="{{ old('slug') }}">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Summary') }}</label>
                            <textarea name="summary" rows="2"
                                       class="flex min-h-[60px] w-full rounded-sm border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary"
                                       placeholder="{{ __('Article summary') }}">{{ old('summary') }}</textarea>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Content') }} *</label>
                            <div class="border border-input rounded-sm overflow-hidden focus-within:ring-1 focus-within:ring-primary">
                                <textarea name="content" id="content" rows="18"
                                          class="w-full bg-background text-sm focus:outline-none">{{ old('content') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Grid Below Content -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Publication Settings -->
                <div class="bg-card rounded-md border border-border p-3 shadow-sm">
                    <h3 class="text-xs font-bold mb-3 text-primary uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        {{ __('Publication') }}
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Status') }} *</label>
                            <select name="status" class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-primary">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending Review') }}</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Publish Date') }}</label>
                            <input type="datetime-local" name="published_at" 
                                   class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-primary"
                                   value="{{ old('published_at') ?? now()->format('Y-m-d\TH:i') }}">
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Expiry Date') }}</label>
                            <input type="datetime-local" name="expired_at" 
                                   class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-primary"
                                   value="{{ old('expired_at') }}">
                        </div>
                        
                        <div class="space-y-1.5 pt-1 border-t border-border mt-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="is_featured" id="is_featured" 
                                       class="rounded-sm border-input bg-background text-primary focus:ring-primary w-3.5 h-3.5" {{ old('is_featured') ? 'checked' : '' }}>
                                <span class="text-[11px] font-medium text-foreground group-hover:text-primary transition-colors">{{ __('Featured News') }}</span>
                            </label>
                            
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="allow_comments" id="allow_comments" 
                                       class="rounded-sm border-input bg-background text-primary focus:ring-primary w-3.5 h-3.5" {{ old('allow_comments') ? 'checked' : '' }}>
                                <span class="text-[11px] font-medium text-foreground group-hover:text-primary transition-colors">{{ __('Allow Comments') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Categorization -->
                <div class="bg-card rounded-md border border-border p-3 shadow-sm">
                    <h3 class="text-xs font-bold mb-3 text-primary uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                        {{ __('Categorization') }}
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Category') }}</label>
                            <select name="category_id" class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-primary">
                                <option value="">-- {{ __('Select Category') }} --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">Tags</label>
                            <input type="text" name="tags" 
                                   class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-primary" 
                                   placeholder="tag1, tag2, tag3"
                                   value="{{ old('tags') ? implode(', ', old('tags')) : '' }}">
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Language') }}</label>
                            <select name="language" class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-primary">
                                <option value="vi" {{ old('language') == 'vi' ? 'selected' : '' }}>{{ __('Vietnamese') }}</option>
                                <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>{{ __('English') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div class="bg-card rounded-md border border-border p-3 shadow-sm">
                    <h3 class="text-xs font-bold mb-3 text-primary uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="image" class="w-4 h-4"></i>
                        {{ __('Media') }}
                    </h3>
                    <div class="space-y-3" x-data="{ uploadType: 'url' }">
                        <div class="flex p-1 bg-muted rounded border border-border">
                            <button type="button" @click="uploadType = 'url'" 
                                     :class="uploadType === 'url' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                     class="flex-1 py-1 text-[9px] font-bold uppercase tracking-widest rounded transition-all">
                                URL
                            </button>
                            <button type="button" @click="uploadType = 'upload'" 
                                     :class="uploadType === 'upload' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                     class="flex-1 py-1 text-[9px] font-bold uppercase tracking-widest rounded transition-all">
                                {{ __('Upload') }}
                            </button>
                        </div>

                        <div x-show="uploadType === 'url'">
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Featured Image (URL)') }}</label>
                            <input type="text" name="featured_image" 
                                   class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-primary" 
                                   placeholder="{{ __('Enter image URL') }}"
                                   value="{{ old('featured_image') }}"
                                   oninput="handleUrlPreview(this.value)">
                        </div>
                        
                        <div x-show="uploadType === 'upload'" x-cloak>
                            <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Upload Featured Image') }}</label>
                            <input type="hidden" name="image_removed" id="image_removed" value="0">
                            <div class="relative group">
                                <input type="file" name="featured_image_file" accept="image/*"
                                       class="hidden" id="featured_image_file"
                                       onchange="previewImage(this)">
                                <label for="featured_image_file" 
                                       class="flex flex-col items-center justify-center w-full h-20 border border-dashed border-input rounded-sm bg-background/50 cursor-pointer hover:bg-muted transition-all">
                                    <i data-lucide="upload-cloud" class="w-5 h-5 text-muted-foreground mb-1"></i>
                                    <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">{{ __('Click or drag and drop image') }}</p>
                                </label>
                            </div>
                        </div>
                        
                        <div id="image-preview-container" class="{{ old('featured_image') ? '' : 'hidden' }} border border-border rounded p-1.5 bg-muted/50">
                            <div class="flex justify-between items-center mb-1.5">
                                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">{{ __('Preview') }}:</p>
                                <button type="button" onclick="removeImage()" 
                                        class="text-[9px] font-bold text-destructive hover:underline flex items-center">
                                    <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i> {{ __('Delete') }}
                                </button>
                            </div>
                            <div class="relative rounded overflow-hidden aspect-video bg-muted border border-border">
                                <img id="image-preview" src="{{ old('featured_image') }}" 
                                     class="w-full h-full object-cover">
                                <div id="image-error-msg" class="hidden absolute inset-0 bg-destructive/10 backdrop-blur-[2px] flex items-center justify-center text-destructive text-[10px] font-bold p-2 text-center">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Settings (Full Width) -->
            <div class="bg-card rounded-md border border-border p-3 shadow-sm">
                <h3 class="text-xs font-bold mb-3 text-primary uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    SEO
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Meta Title') }}</label>
                        <input type="text" name="meta_title" 
                               class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary" 
                               placeholder="{{ __('SEO Title') }}"
                               value="{{ old('meta_title') }}">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Meta Keywords') }}</label>
                        <input type="text" name="meta_keywords" 
                               class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary" 
                               placeholder="{{ __('keyword 1, keyword 2, keyword 3') }}"
                               value="{{ old('meta_keywords') }}">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Meta Description') }}</label>
                        <textarea name="meta_description" rows="1"
                                   class="flex h-9 w-full rounded-sm border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary"
                                   placeholder="{{ __('SEO Description') }}">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-2 mt-4 pt-3 border-t border-border">
            <button type="submit" name="save_draft" value="1" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-1.5 rounded bg-muted text-muted-foreground hover:bg-muted/80 border border-border active:scale-95 transition-all text-xs font-semibold cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                {{ __('Save Draft') }}
            </button>
            <button type="submit" 
                    class="inline-flex items-center justify-center gap-2 px-5 py-1.5 rounded bg-primary text-primary-foreground hover:bg-primary/90 active:scale-95 transition-all text-xs font-semibold cursor-pointer shadow-sm">
                <i data-lucide="send" class="w-4 h-4"></i>
                {{ __('Publish Now') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/{{ env('TinyEMC') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
        branding: false,
        promotion: false,
        language: 'vi',
        skin: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oxide-dark' : 'oxide'),
        content_css: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default'),
        setup: function (editor) {
            editor.on('change keyup', function () {
                editor.save();
            });
        }
    });

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('image-preview-container');
    const errorMsg = document.getElementById('image-error-msg');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
            errorMsg.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function handleUrlPreview(url) {
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('image-preview-container');
    const errorMsg = document.getElementById('image-error-msg');
    const defaultImage = "https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=1000&q=80";

    if (url.trim() === '') {
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');
    preview.src = url;
    errorMsg.classList.add('hidden');

    preview.onerror = function() {
        preview.src = defaultImage;
        errorMsg.classList.remove('hidden');
        errorMsg.innerText = "⚠️ Lỗi URL ảnh";
    };
}

function removeImage() {
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('image-preview-container');
    const urlInput = document.querySelector('input[name="featured_image"]');
    const fileInput = document.getElementById('featured_image_file');
    const removedInput = document.getElementById('image_removed');
    
    if (urlInput) urlInput.value = '';
    if (fileInput) fileInput.value = '';
    if (removedInput) removedInput.value = '1';
    
    container.classList.add('hidden');
    preview.src = '';
}

document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput = document.querySelector('input[name="slug"]');
    
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.value) {
                const slug = this.value.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    }
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

function autoGenerateNews() {
    fetch('{{ route("admin.news.auto-generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('input[name="title"]').value = data.news.title;
            document.querySelector('input[name="slug"]').value = data.news.slug;
            document.querySelector('textarea[name="summary"]').value = data.news.summary;
            
            if (tinymce.get('content')) {
                tinymce.get('content').setContent(data.news.content);
            } else {
                document.querySelector('textarea[name="content"]').value = data.news.content;
            }
            
            document.querySelector('select[name="status"]').value = data.news.status;
            document.querySelector('input[name="featured_image"]').value = data.news.featured_image;
            if (data.news.category_id) {
                document.querySelector('select[name="category_id"]').value = data.news.category_id;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire('Thành công', 'Đã tạo nội dung mẫu!', 'success');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush
@endsection
