@extends('layouts.front-page')
@section('content')

  @while(have_posts()) @php(the_post())

  @include('front-page.home-intro')

  @include('front-page.home-events')

  @include('front-page.home-menu')


@endwhile
@endsection
