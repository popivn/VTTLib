@extends('layouts.site')

@section('title', (isset($category) ? $category->name : (isset($tag) ? 'Thẻ: #' . $tag->name : 'Tin tức')) . ' - VTTLib')

@section('content')
    @include('site.partials.inner-page', [
        'node'         => $node,
        'accent'       => 'red',
        'badgeText'    => isset($category) ? $category->name : (isset($tag) ? 'Thẻ: #' . $tag->name : 'Tin tức'),
        'badgeIcon'    => 'fas fa-newspaper',
        'sectionLabel' => 'Tin tức & Sự kiện',
    ])
@endsection
