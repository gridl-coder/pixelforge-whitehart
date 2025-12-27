@extends('layouts.front-page')
@section('content')

  @while(have_posts())

    @php(the_post())

    @include('front-page.home-intro')

    @include('front-page.home-events')

    @include('front-page.home-menu')

    @include('front-page.home-booking')

    @include('front-page.home-contact')

  @endwhile
@endsection
