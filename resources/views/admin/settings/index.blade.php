@extends('layouts.admin')

@section('content')
    <div class="w-full space-y-4 animate-in fade-in duration-500 pb-8">
        <!-- Header Section -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-sm bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                <i data-lucide="settings" class="w-5 h-5"></i>
            </div>
            <div class="flex-1">
                <h1 class="text-lg font-bold text-foreground tracking-tight">{{ __('System Settings') }}</h1>
                <p class="text-xs text-muted-foreground">{{ __('Configure library information and system rules.') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn-compact-secondary">
                    <i data-lucide="settings-2" class="w-4 h-4 mr-1"></i>
                    <span>{{ __('Advanced Settings') }}</span>
                </a>
            </div>
        </div>

        <div x-data="{ 
            activeTab: new URLSearchParams(window.location.search).get('tab') || 'general',
            updateTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.pushState({}, '', url);
            }
        }" class="space-y-4">
            <!-- TABS NAVIGATION -->
            <div class="flex flex-wrap gap-1 p-1 bg-muted border border-border rounded-md w-fit">
                <button @click="updateTab('general')" 
                        :class="activeTab === 'general' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50'"
                        class="px-4 py-1.5 text-xs font-semibold rounded-sm transition-all duration-200">
                    {{ __('General Settings') }}
                </button>
                <button @click="updateTab('policies')" 
                        :class="activeTab === 'policies' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50'"
                        class="px-4 py-1.5 text-xs font-semibold rounded-sm transition-all duration-200">
                    {{ __('Library Policies') }}
                </button>
                <button @click="updateTab('infrastructure')" 
                        :class="activeTab === 'infrastructure' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50'"
                        class="px-4 py-1.5 text-xs font-semibold rounded-sm transition-all duration-200">
                    {{ __('Infrastructure') }}
                </button>
                <button @click="updateTab('suppliers')" 
                        :class="activeTab === 'suppliers' ? 'bg-background text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-muted/50'"
                        class="px-4 py-1.5 text-xs font-semibold rounded-sm transition-all duration-200">
                    {{ __('Suppliers') }}
                </button>
            </div>

            <!-- GENERAL TAB CONTENT -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                <!-- LIBRARY INFORMATION SECTION -->
                <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                    <div class="p-3 border-b border-border flex items-center gap-2 bg-muted/30">
                        <i data-lucide="building-2" class="w-4 h-4 text-primary"></i>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('Library Information') }}</h2>
                    </div>

                    <div class="p-3">
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Library Name (VI)') }}</label>
                                    <input type="text" name="library_name_vi" value="{{ \App\Models\SystemSetting::get('library_name_vi') }}"
                                        class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Library Name (EN)') }}</label>
                                    <input type="text" name="library_name_en" value="{{ \App\Models\SystemSetting::get('library_name_en') }}"
                                        class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Official Address') }}</label>
                                <input type="text" name="address" value="{{ \App\Models\SystemSetting::get('address') }}"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Phone') }}</label>
                                    <input type="text" name="phone" value="{{ \App\Models\SystemSetting::get('phone') }}"
                                        class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Email') }}</label>
                                    <input type="email" name="email" value="{{ \App\Models\SystemSetting::get('email') }}"
                                        class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Website') }}</label>
                                <input type="url" name="website" value="{{ \App\Models\SystemSetting::get('website') }}"
                                    class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Footer Short Description') }}</label>
                                <textarea name="footer_description" rows="2"
                                    class="w-full px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                                    placeholder="{{ __('Enter short description for footer...') }}">{{ \App\Models\SystemSetting::get('footer_description') }}</textarea>
                            </div>

                            <!-- SOCIAL LINKS -->
                            <div class="pt-2 border-t border-border space-y-3">
                                <h3 class="text-[11px] font-bold uppercase tracking-wider text-primary">{{ __('Social Media Links') }}</h3>
                                <div class="space-y-2">
                                    <div class="space-y-1">
                                        <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider">Facebook URL</label>
                                        <input type="url" name="facebook_url" value="{{ \App\Models\SystemSetting::get('facebook_url') }}"
                                            placeholder="https://facebook.com/..."
                                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] text-muted-foreground uppercase tracking-wider">YouTube URL</label>
                                        <input type="url" name="youtube_url" value="{{ \App\Models\SystemSetting::get('youtube_url') }}"
                                            placeholder="https://youtube.com/..."
                                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-[10px] text-muted-foreground uppercase tracking-wider">Zalo / Other Social URL</label>
                                        <input type="url" name="zalo_url" value="{{ \App\Models\SystemSetting::get('zalo_url') }}"
                                            placeholder="https://zalo.me/..."
                                            class="w-full h-9 px-3 py-1.5 text-sm border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="btn-compact-primary w-full h-9 justify-center">
                                    {{ __('Update Information') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- BARCODE CONFIGURATION SECTION -->
                <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                    <div class="p-3 border-b border-border flex items-center justify-between bg-muted/30">
                        <div class="flex items-center gap-2">
                            <i data-lucide="barcode" class="w-4 h-4 text-primary"></i>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('Barcode Rules') }}</h2>
                        </div>
                        <button onclick="openModal('createBarcodeModal')" class="btn-compact-secondary text-xs uppercase">
                            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
                            {{ __('New Rule') }}
                        </button>
                    </div>

                    <div class="overflow-x-auto min-h-[150px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2 px-3">{{ __('Rule Name') }}</th>
                                    <th class="py-2 px-3 w-28">{{ __('Pattern') }}</th>
                                    <th class="py-2 px-3 w-20">{{ __('Current') }}</th>
                                    <th class="py-2 px-3 w-20">{{ __('Status') }}</th>
                                    <th class="py-2 px-3 w-24 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($barcodeConfigs as $config)
                                    <tr class="table-row-hover group">
                                        <td class="py-2 px-3 text-xs font-bold text-foreground">{{ $config->name }}</td>
                                        <td class="py-2 px-3 font-mono text-[11px]">
                                            <span class="text-primary font-bold">{{ $config->prefix }}</span>
                                            <span class="text-muted-foreground/50">{{ str_repeat('0', $config->length) }}</span>
                                        </td>
                                        <td class="py-2 px-3 font-mono text-xs text-muted-foreground">{{ $config->current_number }}</td>
                                        <td class="py-2 px-3">
                                            @if($config->is_active)
                                                <span class="px-1.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] uppercase font-bold rounded-sm border border-emerald-500/20">{{ __('ACTIVE') }}</span>
                                            @else
                                                <span class="px-1.5 py-0.5 bg-muted text-muted-foreground text-[9px] uppercase font-bold rounded-sm border border-border">{{ __('STDBY') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-right">
                                            <div class="flex justify-end items-center gap-1">
                                                <button onclick="openEditBarcodeModal({{ $config }})" class="btn-icon-compact text-primary" title="{{ __('Edit') }}">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form action="{{ route('admin.settings.barcode.destroy', $config->id) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-icon-danger" title="{{ __('Del') }}" onclick="return confirm('{{ __('Terminate this rule?') }}')">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($barcodeConfigs->isEmpty())
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-muted-foreground italic text-xs">{{ __('No rules defined yet.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="m-3 p-2 bg-amber-500/5 rounded border border-amber-500/10 flex items-start gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <p class="text-[10px] text-amber-600 dark:text-amber-400 leading-relaxed font-medium">
                            {{ __('Only one rule per target type (Book Item / Patron) can be active at once. Activating a new rule will suspend the previous one.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- LIBRARY POLICIES TAB -->
            <div x-show="activeTab === 'policies'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                    <div x-data="{ 
                        subTab: new URLSearchParams(window.location.search).get('sub') || 'general',
                        updateSubTab(sub) {
                            this.subTab = sub;
                            const url = new URL(window.location);
                            url.searchParams.set('sub', sub);
                            window.history.pushState({}, '', url);
                        }
                    }">
                        <!-- Sub-tabs Navigation -->
                        <div class="px-3 py-2 border-b border-border bg-muted/20 flex flex-wrap items-center gap-1">
                            <button @click="updateSubTab('general')" :class="subTab === 'general' ? 'text-primary border-b-2 border-primary font-bold' : 'text-muted-foreground border-b-2 border-transparent hover:text-foreground'" class="px-5 py-1.5 text-xs font-semibold uppercase tracking-wider transition-all">{{ __('General Policy') }}</button>
                            <div class="h-3.5 w-px bg-border"></div>
                            <button @click="updateSubTab('holidays')" :class="subTab === 'holidays' ? 'text-primary border-b-2 border-primary font-bold' : 'text-muted-foreground border-b-2 border-transparent hover:text-foreground'" class="px-5 py-1.5 text-xs font-semibold uppercase tracking-wider transition-all">{{ __('Holidays') }}</button>
                            <div class="h-3.5 w-px bg-border"></div>
                            <button @click="updateSubTab('services')" :class="subTab === 'services' ? 'text-primary border-b-2 border-primary font-bold' : 'text-muted-foreground border-b-2 border-transparent hover:text-foreground'" class="px-5 py-1.5 text-xs font-semibold uppercase tracking-wider transition-all">{{ __('Services') }}</button>
                            <div class="h-3.5 w-px bg-border"></div>
                            <button @click="updateSubTab('books')" :class="subTab === 'books' ? 'text-primary border-b-2 border-primary font-bold' : 'text-muted-foreground border-b-2 border-transparent hover:text-foreground'" class="px-5 py-1.5 text-xs font-semibold uppercase tracking-wider transition-all">{{ __('Books') }}</button>
                        </div>

                        <div class="p-3">
                            <!-- Policy: General Tab -->
                            <div x-show="subTab === 'general'">
                                <form action="{{ route('admin.settings.policy.update') }}" method="POST">
                                    @csrf
                                    <div class="space-y-3 max-w-xl">
                                        <!-- Thứ 2 - Thứ 7 -->
                                        <div class="bg-muted/10 rounded border border-border p-3">
                                            <h4 class="text-xs font-bold uppercase tracking-wider mb-2 text-primary">{{ __('Thứ 2 - Thứ 7') }}</h4>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="space-y-1">
                                                    <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Opening Time') }}</label>
                                                    <input type="time" name="opening_time_weekday" value="{{ \App\Models\SystemSetting::get('opening_time_weekday', '07:30') }}" 
                                                        class="w-full h-8 bg-background border border-border rounded px-2.5 text-xs text-foreground focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Closing Time') }}</label>
                                                    <input type="time" name="closing_time_weekday" value="{{ \App\Models\SystemSetting::get('closing_time_weekday', '20:00') }}" 
                                                        class="w-full h-8 bg-background border border-border rounded px-2.5 text-xs text-foreground focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Chủ nhật & Ngày lễ -->
                                        <div class="bg-muted/10 rounded border border-border p-3">
                                            <h4 class="text-xs font-bold uppercase tracking-wider mb-2 text-primary">{{ __('Chủ nhật & Ngày lễ') }}</h4>
                                            <div class="space-y-1">
                                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Mô tả thời gian') }}</label>
                                                <input type="text" name="sunday_holiday_hours" value="{{ \App\Models\SystemSetting::get('sunday_holiday_hours', 'Nghỉ') }}" 
                                                    placeholder="VD: Nghỉ, 08:00 - 12:00, Liên hệ trước..."
                                                    class="w-full h-8 bg-background border border-border rounded px-2.5 text-xs text-foreground focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                            </div>
                                        </div>

                                        <!-- Ghi chú -->
                                        <div class="bg-muted/10 rounded border border-border p-3">
                                            <h4 class="text-xs font-bold uppercase tracking-wider mb-2 text-primary">{{ __('Ghi chú') }}</h4>
                                            <div class="space-y-1">
                                                <label class="block text-[10px] text-muted-foreground uppercase font-bold tracking-wider">{{ __('Ghi chú hiển thị trên trang thời gian phục vụ') }}</label>
                                                <input type="text" name="service_hours_note" value="{{ \App\Models\SystemSetting::get('service_hours_note', 'Lịch phục vụ có thể thay đổi tùy theo kế hoạch đào tạo của Nhà trường.') }}" 
                                                    placeholder="VD: Lịch phục vụ có thể thay đổi..."
                                                    class="w-full h-8 bg-background border border-border rounded px-2.5 text-xs text-foreground focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-border flex justify-end">
                                        <button type="submit" class="btn-compact-primary">
                                            <i data-lucide="save" class="w-4 h-4 mr-1"></i>
                                            {{ __('Save Policy') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Policy: Books Tab -->
                            <div x-show="subTab === 'books'">
                                <form action="{{ route('admin.settings.policy.update_digital') }}" method="POST">
                                    @csrf
                                    <div class="space-y-3">
                                        <!-- Digital Download Policy Section -->
                                        <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                                            <div class="p-3 border-b border-border flex items-center gap-3 bg-muted/30">
                                                <div class="p-1.5 bg-primary/10 rounded text-primary">
                                                    <i data-lucide="download-cloud" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-xs font-bold uppercase tracking-wider">{{ __('Digital Download Permission') }}</h3>
                                                    <p class="text-[10px] text-muted-foreground font-medium">{{ __('Configure patron groups allowed to download PDF files.') }}</p>
                                                </div>
                                            </div>

                                            <div class="p-3">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                                    @php
                                                        $allowedGroups = json_decode(\App\Models\SystemSetting::get('digital_download_allowed_groups', '[]'), true) ?: [];
                                                        $viewLimits = json_decode(\App\Models\SystemSetting::get('digital_view_page_limits', '[]'), true) ?: [];
                                                    @endphp
                                                    @foreach($patronGroups as $group)
                                                    <div class="flex flex-col p-3 bg-muted/20 rounded border border-border hover:bg-muted/40 hover:border-primary/30 transition-all group">
                                                        <div class="relative flex items-start mb-3">
                                                            <div class="flex items-center h-5">
                                                                <input type="checkbox" name="digital_download_allowed_groups[]" value="{{ $group->id }}" 
                                                                       id="group-{{ $group->id }}"
                                                                       {{ in_array($group->id, $allowedGroups) ? 'checked' : '' }}
                                                                       class="w-3.5 h-3.5 text-primary border-border rounded focus:ring-primary/20 bg-background transition-all cursor-pointer">
                                                            </div>
                                                            <label for="group-{{ $group->id }}" class="ml-2.5 cursor-pointer">
                                                                <span class="block text-xs font-bold text-foreground">{{ $group->name }}</span>
                                                                <span class="block text-[9px] text-muted-foreground font-mono uppercase tracking-tighter">{{ $group->code }}</span>
                                                            </label>
                                                            @if(in_array($group->id, $allowedGroups))
                                                            <div class="absolute top-0 right-0">
                                                                <span class="flex h-1.5 w-1.5">
                                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                                                </span>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        
                                                        <div class="mt-auto pt-2 border-t border-border/50 space-y-1">
                                                            <label class="text-[9px] text-muted-foreground uppercase font-black tracking-widest">{{ __('Preview Page Limit') }}</label>
                                                            <div class="relative">
                                                                <input type="number" name="digital_view_page_limits[{{ $group->id }}]" 
                                                                       value="{{ $viewLimits[$group->id] ?? 0 }}"
                                                                       min="0"
                                                                       class="w-full h-8 bg-background border border-border rounded py-1 px-2.5 text-xs font-bold text-primary focus:ring-1 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                                                                <div class="absolute inset-y-0 right-2.5 flex items-center pointer-events-none text-muted-foreground/30">
                                                                    <i data-lucide="file-text" class="w-3 h-3"></i>
                                                                </div>
                                                            </div>
                                                            <p class="text-[8px] text-muted-foreground italic">{{ __('0 = Unlimited') }}</p>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>

                                                @if($patronGroups->isEmpty())
                                                <div class="text-center py-6 text-muted-foreground italic text-xs border border-dashed border-border rounded">
                                                    {{ __('No patron groups defined yet.') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Guest Policy Section -->
                                        <div class="bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden">
                                            <div class="p-3 border-b border-border flex items-center gap-3 bg-muted/30">
                                                <div class="p-1.5 bg-amber-500/10 rounded text-amber-500">
                                                    <i data-lucide="user-minus" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-xs font-bold uppercase tracking-wider">{{ __('Guest Configuration') }}</h3>
                                                    <p class="text-[10px] text-muted-foreground font-medium">{{ __('Permissions for unassigned users.') }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div class="flex items-center gap-3 p-3 bg-muted/10 rounded border border-border border-dashed">
                                                    <div class="p-2 bg-muted rounded-full">
                                                        <i data-lucide="lock" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                                    </div>
                                                    <div>
                                                        <span class="block text-xs font-bold">{{ __('Download Document') }}</span>
                                                        <span class="block text-[9px] text-muted-foreground uppercase tracking-widest">{{ __('Locked by Default') }}</span>
                                                    </div>
                                                </div>
                                                <div class="p-3 bg-muted/10 rounded border border-border border-dashed space-y-1.5">
                                                    <label class="text-[9px] text-muted-foreground uppercase font-black tracking-widest">{{ __('Preview Page Limit') }}</label>
                                                    <div class="relative">
                                                        <input type="number" name="digital_view_page_limits[guest]" 
                                                               value="{{ $viewLimits['guest'] ?? 10 }}"
                                                               min="0"
                                                               class="w-full h-8 bg-background border border-border rounded py-1 px-2.5 text-xs font-bold text-primary focus:ring-1 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                                                        <div class="absolute inset-y-0 right-2.5 flex items-center pointer-events-none text-muted-foreground/30">
                                                            <i data-lucide="file-text" class="w-3 h-3"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Note Info -->
                                        <div class="p-3 bg-accent/10 rounded-md border border-border flex items-start gap-3">
                                            <i data-lucide="info" class="w-4 h-4 text-primary shrink-0 mt-0.5"></i>
                                            <p class="text-[10px] text-muted-foreground leading-relaxed font-medium">
                                                {{ __('Note: Only selected groups will see the "Download" button. Other groups (including Guests) are restricted to online viewing with the configured preview page limit.') }}
                                            </p>
                                        </div>

                                        <div class="flex items-center justify-end pt-2">
                                            <button type="submit" class="btn-compact-primary">
                                                <i data-lucide="save" class="w-4 h-4 mr-1"></i>
                                                {{ __('Save Book Policy') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INFRASTRUCTURE TAB CONTENT -->
            <div x-show="activeTab === 'infrastructure'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                <!-- BRANCH MANAGEMENT SECTION -->
                <div class="bg-card text-foreground rounded-md border border-border overflow-hidden transition-colors shadow-sm">
                    <div class="p-3 border-b border-border flex items-center justify-between bg-muted/30 transition-colors">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-primary"></i>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('Branch Management') }}</h2>
                        </div>
                        <button onclick="openModal('createBranchModal')" class="btn-compact-secondary text-xs uppercase">
                            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
                            {{ __('New Branch') }}
                        </button>
                    </div>

                    <div class="overflow-x-auto min-h-[150px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2 px-3">{{ __('Name') }}</th>
                                    <th class="py-2 px-3 w-32">{{ __('Code') }}</th>
                                    <th class="py-2 px-3 w-20 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($branches as $branch)
                                    <tr class="table-row-hover group">
                                        <td class="py-2 px-3 font-bold text-xs">{{ $branch->name }}</td>
                                        <td class="py-2 px-3 font-mono text-[11px] text-primary font-bold uppercase tracking-wider">{{ $branch->code }}</td>
                                        <td class="py-2 px-3 text-right">
                                            <div class="flex justify-end items-center gap-1">
                                                <button onclick="openEditBranchModal({{ $branch }})" class="btn-icon-compact text-primary" title="{{ __('Edit') }}">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form action="{{ route('admin.settings.branches.destroy', $branch->id) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-icon-danger" title="{{ __('Del') }}" onclick="return confirm('{{ __('Delete this branch?') }}')">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- STORAGE LOCATION MANAGEMENT SECTION -->
                <div class="bg-card text-foreground rounded-md border border-border overflow-hidden transition-colors shadow-sm">
                    <div class="p-3 border-b border-border flex items-center justify-between bg-muted/30 transition-colors">
                        <div class="flex items-center gap-2">
                            <i data-lucide="database" class="w-4 h-4 text-primary"></i>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('Storage Locations') }}</h2>
                        </div>
                        @if($branches->isNotEmpty())
                            <button onclick="openModal('createLocationModal')" class="btn-compact-secondary text-xs uppercase">
                                <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
                                {{ __('New Location') }}
                            </button>
                        @endif
                    </div>

                    <div class="overflow-x-auto max-h-[300px] min-h-[150px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider sticky top-0 bg-card z-10">
                                <tr>
                                    <th class="py-2 px-3">{{ __('Name') }}</th>
                                    <th class="py-2 px-3 w-32">{{ __('Branch') }}</th>
                                    <th class="py-2 px-3 w-20 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($branches as $branch)
                                    @foreach($branch->storageLocations as $location)
                                        <tr class="table-row-hover group">
                                            <td class="py-2 px-3">
                                                <div class="font-bold text-xs">{{ $location->name }}</div>
                                                <div class="text-[10px] font-mono text-muted-foreground uppercase mt-0.5">{{ $location->code }}</div>
                                            </td>
                                            <td class="py-2 px-3">
                                                <span class="px-1.5 py-0.5 bg-primary/10 text-primary text-[10px] font-bold rounded-sm border border-primary/20">{{ $branch->name }}</span>
                                            </td>
                                            <td class="py-2 px-3 text-right">
                                                <div class="flex justify-end items-center gap-1">
                                                    <button onclick="openEditLocationModal({{ $location }})" class="btn-icon-compact text-primary" title="{{ __('Edit') }}">
                                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                    <form action="{{ route('admin.settings.locations.destroy', $location->id) }}" method="POST" class="inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn-icon-danger" title="{{ __('Del') }}" onclick="return confirm('{{ __('Delete this location?') }}')">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SUPPLIERS TAB CONTENT -->
            <div x-show="activeTab === 'suppliers'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-card text-foreground rounded-md border border-border overflow-hidden transition-colors shadow-sm">
                    <div class="p-3 border-b border-border flex items-center justify-between bg-muted/30 transition-colors">
                        <div class="flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4 text-primary"></i>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('Supplier Management') }}</h2>
                        </div>
                        <button onclick="openModal('createSupplierModal')" class="btn-compact-secondary text-xs uppercase">
                            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i>
                            {{ __('New Supplier') }}
                        </button>
                    </div>

                    <div class="overflow-x-auto min-h-[150px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-muted/50 border-b border-border text-muted-foreground uppercase font-bold text-[10px] tracking-wider">
                                <tr>
                                    <th class="py-2 px-3">{{ __('Supplier') }}</th>
                                    <th class="py-2 px-3 w-40">{{ __('Contact') }}</th>
                                    <th class="py-2 px-3 w-48">{{ __('Email/Phone') }}</th>
                                    <th class="py-2 px-3 w-24">{{ __('Status') }}</th>
                                    <th class="py-2 px-3 w-28 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($suppliers as $supplier)
                                    <tr class="table-row-hover group">
                                        <td class="py-2 px-3">
                                            <div class="font-bold text-xs">{{ $supplier->name }}</div>
                                            <div class="text-[10px] font-mono text-muted-foreground uppercase mt-0.5">{{ $supplier->code }}</div>
                                        </td>
                                        <td class="py-2 px-3 text-xs">{{ $supplier->contact_name ?? '-' }}</td>
                                        <td class="py-2 px-3 text-xs">
                                            <div class="font-semibold">{{ $supplier->email ?? '-' }}</div>
                                            <div class="text-muted-foreground mt-0.5">{{ $supplier->phone ?? '-' }}</div>
                                        </td>
                                        <td class="py-2 px-3">
                                            @if($supplier->is_active)
                                                <span class="px-1.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] uppercase font-bold rounded-sm border border-emerald-500/20">{{ __('Active') }}</span>
                                            @else
                                                <span class="px-1.5 py-0.5 bg-muted text-muted-foreground text-[9px] uppercase font-bold rounded-sm border border-border">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-right">
                                            <div class="flex justify-end items-center gap-1">
                                                <button onclick="openEditSupplierModal({{ $supplier }})" class="btn-icon-compact text-primary" title="{{ __('Edit') }}">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form action="{{ route('admin.settings.suppliers.destroy', $supplier->id) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-icon-danger" title="{{ __('Del') }}" onclick="return confirm('{{ __('Delete this supplier?') }}')">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-muted-foreground italic text-xs">{{ __('No suppliers defined yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    @include('admin.settings.modals')

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        function openEditBarcodeModal(config) {
            document.getElementById('editBarcodeForm').action = `{{ route('admin.settings.barcode.update', ['config' => ':id']) }}`.replace(':id', config.id);
            document.getElementById('edit_name').value = config.name;
            document.getElementById('edit_prefix').value = config.prefix || '';
            document.getElementById('edit_length').value = config.length;
            document.getElementById('edit_is_active').checked = config.is_active;
            openModal('editBarcodeModal');
        }
        function openEditSupplierModal(supplier) {
            document.getElementById('editSupplierForm').action = `{{ route('admin.settings.suppliers.update', ['supplier' => ':id']) }}`.replace(':id', supplier.id);
            document.getElementById('edit_sup_name').value = supplier.name;
            document.getElementById('edit_sup_code').value = supplier.code;
            document.getElementById('edit_sup_contact').value = supplier.contact_name || '';
            document.getElementById('edit_sup_phone').value = supplier.phone || '';
            document.getElementById('edit_sup_email').value = supplier.email || '';
            document.getElementById('edit_sup_is_active').checked = supplier.is_active;
            openModal('editSupplierModal');
        }
        function openEditBranchModal(branch) {
            document.getElementById('editBranchForm').action = `{{ route('admin.settings.branches.update', ['branch' => ':id']) }}`.replace(':id', branch.id);
            document.getElementById('edit_branch_name').value = branch.name;
            document.getElementById('edit_branch_code').value = branch.code;
            document.getElementById('edit_branch_phone').value = branch.phone || '';
            document.getElementById('edit_branch_address').value = branch.address || '';
            document.getElementById('edit_branch_is_active').checked = branch.is_active;
            openModal('editBranchModal');
        }
        function openEditLocationModal(location) {
            document.getElementById('editLocationForm').action = `{{ route('admin.settings.locations.update', ['location' => ':id']) }}`.replace(':id', location.id);
            document.getElementById('edit_location_name').value = location.name;
            document.getElementById('edit_location_code').value = location.code;
            document.getElementById('edit_location_description').value = location.description || '';
            document.getElementById('edit_location_is_active').checked = location.is_active;
            openModal('editLocationModal');
        }
    </script>
@endsection
