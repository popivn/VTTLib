@extends('layouts.site')

@section('title', $node->meta_title ?: $node->display_name . ' - Thư viện')

@section('meta-description', $node->meta_description)
@section('meta-keywords', $node->meta_keywords)

@section('content')
    @if(str_starts_with($node->node_code, 'guide-') || ($node->parent && $node->parent->node_code === 'co-so-du-lieu'))
        @include('site.partials.inner-page', [
            'node' => $node,
            'accent' => 'primary',
            'sectionLabel' => 'Hướng dẫn CSDL'
        ])
    @else
        <div class="min-h-screen">
            <!-- Breadcrumb -->
            @if($breadcrumb && count($breadcrumb) > 1)
            <nav class="bg-gray-100 py-3">
                <div class="container mx-auto px-4">
                    <ol class="flex items-center space-x-2 text-sm">
                        @foreach($breadcrumb as $index => $item)
                            @if($index > 0)
                                <li class="text-gray-400">/</li>
                            @endif
                            @if($index === count($breadcrumb) - 1)
                                <li class="text-gray-600 font-medium">{{ $item['name'] }}</li>
                            @else
                                <li>
                                    <a href="{{ $item['url'] }}" class="text-vttu-red hover:text-vttu-dark">
                                        {{ $item['name'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </div>
            </nav>
            @endif

            <!-- Page Header -->
            @if(!$node->activeItems()->count())
            <section class="bg-white border-b border-slate-100 py-12">
                <div class="container mx-auto px-4">
                    <div class="text-center">
                        <h1 class="text-4xl font-black mb-4 text-vttu-dark">{{ $node->display_name }}</h1>
                        @if($node->description)
                            <p class="text-xl text-vttu-red/80">{{ $node->description }}</p>
                        @endif
                    </div>
                </div>
            </section>
            @endif

            <!-- Page Content from Items -->
            @if($node->activeItems()->count() > 0)
                @foreach($node->activeItems()->ordered()->get() as $item)
                    @include('site.items.' . $item->item_type, ['item' => $item])
                @endforeach
            @elseif($node->content)
                <!-- Fallback to legacy content -->
                <section class="py-16">
                    <div class="container mx-auto px-4">
                        <div class="prose max-w-none">
                            {!! $node->content !!}
                        </div>
                    </div>
                </section>
            @else
                <!-- Empty state -->
                <section class="py-16">
                    <div class="container mx-auto px-4">
                        <div class="text-center py-12">
                            <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-file-alt text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">Trang đang được cập nhật</h3>
                            <p class="text-gray-500">Nội dung của trang này sẽ sớm được bổ sung.</p>
                        </div>
                    </div>
                </section>
            @endif

            <!-- Related Pages -->
            @if($node->getSiblings()->count() > 0)
            <section class="py-16 bg-white border-t border-slate-100">
                <div class="container mx-auto px-4">
                    <h2 class="text-2xl font-black text-center mb-8 text-vttu-dark">Trang liên quan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($node->getSiblings()->take(6) as $sibling)
                        <div class="bg-white p-6 rounded-3xl shadow-xl shadow-black/5 border border-slate-100 hover:border-vttu-red/20 transition group">
                            <div class="flex items-center mb-3">
                                @if($sibling->icon)
                                    <i class="{{ $sibling->icon }} text-vttu-red mr-3"></i>
                                @endif
                                <h3 class="font-black text-vttu-dark group-hover:text-vttu-red transition-colors">{{ $sibling->display_name }}</h3>
                            </div>
                            @if($sibling->description)
                                <p class="text-vttu-dark/70 text-sm mb-4 line-clamp-2">{{ $sibling->description }}</p>
                            @endif
                            <a href="{{ $sibling->getUrl() }}" class="text-vttu-red hover:text-vttu-dark font-black text-sm uppercase tracking-wider">
                                Xem thêm →
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
        </div>
    @endif
@endsection
