@extends('layouts.app')
@section('page-title')
    {{ __('Write a Review') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Write a Review') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('How is it living here?') }}</h5>
                    <small class="text-muted">{{ __('Your review may be shown on our website after we read it. Please do not include private details.') }}</small>
                </div>
                <div class="card-body">
                    @if ($review)
                        <div class="alert alert-{{ $review->status === 'approved' ? 'success' : 'info' }} small">
                            {{ $review->status === 'approved' ? __('Your review is shown on the website. Saving changes sends it for approval again.') : __('Your review is waiting for approval. You can still change it.') }}
                        </div>
                    @endif
                    {!! Form::open(['route' => 'my-review.save', 'method' => 'POST']) !!}
                    <div class="form-group mb-3">
                        <label class="form-label d-block">{{ __('Rating') }}</label>
                        <div class="st-rating" role="radiogroup" aria-label="{{ __('Rating') }}">
                            @for ($i = 5; $i >= 1; $i--)
                                <input type="radio" name="rating" id="rate{{ $i }}" value="{{ $i }}" {{ (int) old('rating', $review->rating ?? 5) === $i ? 'checked' : '' }}>
                                <label for="rate{{ $i }}" title="{{ __(':count out of 5', ['count' => $i]) }}"><i class="fas fa-star"></i></label>
                            @endfor
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label" for="review">{{ __('Your review') }}</label>
                        <textarea class="form-control" id="review" name="review" rows="5" maxlength="1000" required>{{ old('review', $review->review ?? '') }}</textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label" for="name">{{ __('Name to show') }}</label>
                        <input type="text" class="form-control" id="name" name="name" maxlength="80" required value="{{ old('name', $review->name ?? (\Auth::user()->first_name ? \Auth::user()->first_name . ' ' . mb_substr((string) \Auth::user()->last_name, 0, 1) . '.' : '')) }}">
                        <small class="text-muted">{{ __('For example your first name and the first letter of your last name.') }}</small>
                    </div>
                    <button type="submit" class="btn btn-secondary">{{ __('Send review') }}</button>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-page')
    <style>
        .st-rating { display: inline-flex; flex-direction: row-reverse; gap: 4px; }
        .st-rating input { position: absolute; opacity: 0; width: 1px; height: 1px; }
        .st-rating label { font-size: 28px; color: #d1d5db; cursor: pointer; }
        .st-rating input:checked ~ label, .st-rating label:hover, .st-rating label:hover ~ label { color: #f59e0b; }
        .st-rating input:focus-visible + label { outline: 2px solid #f59e0b; border-radius: 4px; }
    </style>
@endpush
