@extends('layouts.site')

@section('title', Str::limit($news->title, 45) . ' | Thư viện VTTU')

@section('meta-description')
    <meta name="description" content="{{ Str::limit(strip_tags($news->summary ?: $news->content), 150) }}">
@endsection

@section('content')
    @include('site.partials.inner-page', [
        'node'         => $node,
        'accent'       => 'red',
        'badgeText'    => 'Chi tiết tin tức',
        'badgeIcon'    => 'fas fa-newspaper',
        'sectionLabel' => 'Tin tức & Sự kiện',
    ])
@endsection
