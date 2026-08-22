@extends('layouts.site')

@section('title', 'Sitemap - Thư viện số')

@section('content')
<div class="min-h-screen">
    <!-- Page Header -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">Sitemap</h1>
                <p class="text-xl">Sơ đồ trang web của thư viện số</p>
            </div>
        </div>
    </section>

    <!-- Sitemap Content -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                @if($tree && count($tree) > 0)
                    <div class="space-y-8">
                        @foreach($tree as $node)
                            <div class="border-l-4 border-blue-500 pl-6">
                                <div class="mb-4">
                                    <a href="{{ $node['url'] ?? '/' . $node['node_code'] }}" 
                                       class="text-xl font-semibold text-blue-600 hover:text-blue-800 transition">
                                        {{ $node['display_name'] }}
                                    </a>
                                    @if($node['description'])
                                        <p class="text-gray-600 mt-1">{{ $node['description'] }}</p>
                                    @endif
                                </div>
                                
                                @if(!empty($node['children']))
                                    <div class="ml-6 space-y-3">
                                        @foreach($node['children'] as $child)
                                            <div class="border-l-2 border-gray-300 pl-4">
                                                <a href="{{ $child['url'] ?? '/' . $child['node_code'] }}" 
                                                   class="text-lg font-medium text-gray-700 hover:text-blue-600 transition">
                                                    {{ $child['display_name'] }}
                                                </a>
                                                @if($child['description'])
                                                    <p class="text-gray-500 text-sm mt-1">{{ $child['description'] }}</p>
                                                @endif
                                                
                                                @if(!empty($child['children']))
                                                    <div class="ml-4 mt-2 space-y-2">
                                                        @foreach($child['children'] as $grandchild)
                                                            <div class="border-l border-gray-200 pl-3">
                                                                <a href="{{ $grandchild['url'] ?? '/' . $grandchild['node_code'] }}" 
                                                                   class="text-base text-gray-600 hover:text-blue-600 transition">
                                                                    {{ $grandchild['display_name'] }}
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-sitemap text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Chưa có trang nào</h3>
                        <p class="text-gray-500">Vui lòng thêm các trang vào hệ thống.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold">Liên kết nhanh</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <i class="fas fa-home text-blue-600 text-3xl mb-4"></i>
                    <h3 class="font-semibold mb-2">Trang chủ</h3>
                    <a href="/" class="text-blue-600 hover:text-blue-800">Về trang chủ →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <i class="fas fa-search text-green-600 text-3xl mb-4"></i>
                    <h3 class="font-semibold mb-2">Tra cứu</h3>
                    <a href="/opac" class="text-green-600 hover:text-green-800">Tra cứu OPAC →</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <i class="fas fa-phone text-purple-600 text-3xl mb-4"></i>
                    <h3 class="font-semibold mb-2">Liên hệ</h3>
                    <a href="/lien-he" class="text-purple-600 hover:text-purple-800">Liên hệ chúng tôi →</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
