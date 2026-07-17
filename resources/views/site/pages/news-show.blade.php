@extends('layouts.site')

@section('title', $news->title . ' - VTTLib')

@section('content')
    @include('site.partials.inner-page', [
        'node'         => $node,
        'accent'       => 'red',
        'badgeText'    => 'Chi tiết tin tức',
        'badgeIcon'    => 'fas fa-newspaper',
        'sectionLabel' => 'Tin tức & Sự kiện',
    ])
@endsection
