@extends('layouts.site')

@section('title', 'Chương trình đào tạo VTTU - Thư viện VTTU')

@section('content')
@php
    $majors = \App\Models\CurriculumMajor::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get();

    // Prepare node if not set
    if (!isset($node) || !$node) {
        $node = \App\Models\SiteNode::where('node_code', 'chuong-trinh-dao-tao-vttu')->first() 
            ?? \App\Models\SiteNode::where('node_code', 'gioi-thieu')->first();
    }
@endphp

@include('site.partials.inner-page', [
    'node' => $node,
    'accent' => 'primary',
    'badgeText' => 'Chương trình đào tạo VTTU',
    'badgeIcon' => 'fas fa-graduation-cap',
    'sectionLabel' => 'Giới thiệu'
])
@endsection
