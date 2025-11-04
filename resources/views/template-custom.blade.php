{{--
  Template Name: Custom Template
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @include('partials.page-header')

    @if (! empty($customTemplate['intro_title']) || ! empty($customTemplate['intro_text']))
      <section class="template-custom__intro">
        @if (! empty($customTemplate['intro_title']))
          <h2 class="template-custom__intro-title">{{ $customTemplate['intro_title'] }}</h2>
        @endif

        @if (! empty($customTemplate['intro_text']))
          <p class="template-custom__intro-text">{{ $customTemplate['intro_text'] }}</p>
        @endif
      </section>
    @endif

    @include('partials.content-page')
  @endwhile
@endsection
