@extends('layouts.site')

@section('title', $node->meta_title ?: $node->display_name . ' - Thư viện số')

@section('content')
    @include('site.partials.inner-page', [
        'node'         => $node,
        'accent'       => 'red',
        'badgeText'    => 'Khảo sát',
        'badgeIcon'    => 'fas fa-poll',
        'sectionLabel' => 'Khảo sát ý kiến bạn đọc',
    ])
@endsection
