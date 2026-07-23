@foreach ($nodes as $node)
<div class="tree-node relative" data-node-id="{{ $node['id'] }}">
    <div class="tree-node-content group/item flex items-center gap-2 px-2.5 py-2 hover:bg-muted transition-colors rounded cursor-default">
        <!-- Toggle Button -->
        <div class="w-5 flex-shrink-0">
            @if(!empty($node['children']))
            <button onclick="toggleNode({{ $node['id'] }})"
                class="w-5 h-5 flex items-center justify-center rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-all" id="toggle-{{ $node['id'] }}">
                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"></i>
            </button>
            @endif
        </div>

        <!-- Node Icon -->
        <div class="w-6 h-6 flex-shrink-0 bg-muted/50 rounded-sm flex items-center justify-center text-muted-foreground border border-border overflow-hidden">
            @php
            $iconValue = $node['icon'] ?? 'fas fa-file';
            $isHtml = str_contains($iconValue, '<') && str_contains($iconValue, '>' );
                @endphp

                @if($isHtml)
                <div class="w-4 h-4 flex items-center justify-center [&>svg]:w-4 [&>svg]:h-4 [&>i]:text-[14px]">
                {!! $iconValue !!}
        </div>
        @else
        <i class="{{ $iconValue }} text-[14px]"></i>
        @endif
    </div>

    <!-- Node Info -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center flex-wrap gap-1.5">
            <span class="text-xs font-bold text-foreground truncate">{{ $node['display_name'] }}</span>

            <div class="flex items-center gap-1 shrink-0">
                <!-- Status Badge -->
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[8px] font-bold uppercase tracking-wider {{ $node['is_active'] ? 'bg-emerald-500/10 text-emerald-600' : 'bg-destructive/10 text-destructive' }} border border-border"
                    id="status-{{ $node['id'] }}">
                    {{ $node['is_active'] ? 'Hoạt động' : 'Ẩn' }}
                </span>

                <!-- Type Badge -->
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[8px] font-bold uppercase tracking-wider bg-muted text-muted-foreground border border-border">
                    {{ $node['display_type'] }}
                </span>

                <!-- Access Type -->
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[8px] font-bold uppercase tracking-wider bg-muted/50 text-muted-foreground border border-border">
                    @switch($node['access_type'])
                    @case('public') Công khai @break
                    @case('auth') ĐN @break
                    @case('roles') Vai trò @break
                    @endswitch
                </span>
            </div>
        </div>

        <!-- Node Details -->
        <div class="flex items-center gap-2 mt-0.5">
            <span class="text-[9px] text-muted-foreground font-mono bg-muted/30 px-1 rounded-sm">#{{ $node['node_code'] }}</span>
            @if($node['route_name'])
            <div class="flex items-center gap-1 text-[8px] text-muted-foreground">
                <i data-lucide="link" class="w-3 h-3"></i>
                <span>{{ $node['route_name'] }}</span>
            </div>
            @endif
            @if($node['url'])
            <div class="flex items-center gap-1 text-[8px] text-muted-foreground">
                <i data-lucide="external-link" class="w-3 h-3"></i>
                <span>{{ Str::limit($node['url'], 25) }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-1 opacity-0 group-hover/item:opacity-100 transition-opacity flex-shrink-0">
        @if(!empty($node['has_content']))
        @php
            $previewUrl = '#';
            if (!empty($node['redirect_to'])) {
                $previewUrl = $node['redirect_to'];
            } elseif (!empty($node['route_name'])) {
                $previewUrl = Route::has($node['route_name']) ? route($node['route_name']) : '#';
            } elseif (!empty($node['url'])) {
                $previewUrl = $node['url'];
            } else {
                $previewUrl = '/' . $node['node_code'];
            }
        @endphp
        <a href="{{ $previewUrl }}"
            target="_blank"
            class="w-6 h-6 flex items-center justify-center rounded-sm bg-muted hover:bg-primary text-muted-foreground hover:text-primary-foreground border border-border transition-all active:scale-90"
            title="Xem trang">
            <i data-lucide="eye" class="w-3 h-3"></i>
        </a>
        @endif

        <button onclick="toggleStatus({{ $node['id'] }})"
            class="w-6 h-6 flex items-center justify-center rounded-sm bg-muted hover:bg-muted text-muted-foreground hover:text-foreground border border-border transition-all active:scale-90"
            title="Đổi trạng thái">
            <i data-lucide="power" class="w-3 h-3"></i>
        </button>

        <a href="{{ route('admin.site-nodes.edit', $node['id']) }}"
            class="w-6 h-6 flex items-center justify-center rounded-sm bg-muted hover:bg-muted text-muted-foreground hover:text-foreground border border-border transition-all active:scale-90"
            title="Chỉnh sửa">
            <i data-lucide="edit-2" class="w-3 h-3"></i>
        </a>

        @if(empty($node['children']))
        <form id="delete-form-{{ $node['id'] }}"
            action="{{ route('admin.site-nodes.destroy', $node['id']) }}"
            method="POST"
            class="inline">
            @csrf
            @method('DELETE')
            <button type="button"
                onclick="confirmDelete({{ $node['id'] }})"
                class="w-6 h-6 flex items-center justify-center rounded-sm bg-muted hover:bg-muted text-muted-foreground hover:text-foreground border border-border transition-all active:scale-90"
                title="Xóa">
                <i data-lucide="trash-2" class="w-3 h-3"></i>
            </button>
        </form>
        @endif
    </div>
</div>

<!-- Children -->
@if(!empty($node['children']))
<div class="tree-children border-l border-border border-dashed ml-4 pl-3 mt-0.5 space-y-0.5" id="children-{{ $node['id'] }}">
    @include('admin.site-nodes.tree', ['nodes' => $node['children'], 'level' => $level + 1])
</div>
@endif
</div>
@endforeach

@if($level === 0)
<script>
    function confirmDelete(nodeId) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            text: "Bạn có chắc chắn muốn xóa node này? Hành động này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Vâng, xóa nó!',
            cancelButtonText: 'Hủy',
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#f1f5f9' : '#1e293b',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${nodeId}`).submit();
            }
        });
    }
</script>
@endif