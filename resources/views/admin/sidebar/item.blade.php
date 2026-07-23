<div class="sortable-item level-{{ $level }}" data-id="{{ $item->id }}">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="drag-handle me-3">
                <i class="fas fa-grip-vertical"></i>
            </div>
            
            <div class="me-3">
                <span class="badge bg-secondary order-display">{{ $item->order }}</span>
            </div>
            

            
            <div class="flex-grow-1">
                <strong>{{ $item->name }}</strong>
                @if($item->route_name)
                    <br><small class="text-muted">{{ $item->route_name }}</small>
                @endif
            </div>
            
            @if($level < 2)
                <div class="me-3">
                    <select class="form-select form-select-sm" onchange="updateParent({{ $item->id }}, this.value)">
                        <option value="">{{ __('Root Level') }}</option>
                        @foreach($rootItems as $potentialParent)
                            @if($potentialParent->id != $item->id)
                                        <option value="{{ $potentialParent->id }}" 
                                                {{ $item->parent_id == $potentialParent->id ? 'selected' : '' }}>
                                            {{ str_repeat('—', $level + 1) }} {{ $potentialParent->name }}
                                        </option>
                                    @endif
                                @endforeach
                    </select>
                </div>
            @endif
        </div>
        
        <div class="d-flex align-items-center">
            <div class="form-check form-switch me-3">
                <input class="form-check-input" type="checkbox" 
                       id="active-{{ $item->id }}"
                       {{ $item->is_active ? 'checked' : '' }}
                       onchange="toggleActive({{ $item->id }})">
                <label class="form-check-label" for="active-{{ $item->id }}">
                    {{ $item->is_active ? __('Active') : __('Inactive') }}
                </label>
            </div>
            
            <div class="text-muted small">
                ID: {{ $item->id }}
            </div>
        </div>
    </div>
    
    @if($item->children && $item->children->count() > 0)
        <div class="sortable-children mt-2">
            @foreach($item->children->sortBy('order') as $child)
                @include('admin.sidebar.item', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
