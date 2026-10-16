@extends('layouts.app')
@section('page-title')
    {{ __('Tenant Reviews') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('front-home.index', ['tab' => 'visitors']) }}">{{ __('Website') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Reviews') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Tenant Reviews') }}</h5>
                            <small class="text-muted">{{ __('Reviews your tenants wrote from their account. Approved reviews appear in the Testimonials section of your website.') }}</small>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('website-reviews.index') }}" class="btn {{ $status === '' ? 'btn-secondary' : 'btn-light-secondary' }}">{{ __('All') }}</a>
                                @foreach (\App\Models\WebsiteReview::statuses() as $key => $label)
                                    <a href="{{ route('website-reviews.index', ['status' => $key]) }}" class="btn {{ $status === $key ? 'btn-secondary' : 'btn-light-secondary' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($reviews as $review)
                        <div class="border rounded p-3 mb-3 {{ $review->status === 'pending' ? 'border-warning' : '' }}">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <h6 class="mb-1">{{ $review->name }} <small class="text-muted fw-normal">· {{ dateFormat($review->created_at) }}</small></h6>
                                    <div class="text-warning mb-2" aria-label="{{ __(':count out of 5', ['count' => $review->rating]) }}">
                                        @for ($i = 1; $i <= 5; $i++)<i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>@endfor
                                    </div>
                                </div>
                                <div>
                                    @php $colors = ['pending' => 'warning', 'approved' => 'success', 'hidden' => 'secondary']; @endphp
                                    <span class="badge bg-light-{{ $colors[$review->status] ?? 'secondary' }}">{{ \App\Models\WebsiteReview::statuses()[$review->status] ?? $review->status }}</span>
                                </div>
                            </div>
                            <p class="mb-3" style="white-space: pre-line">{{ $review->review }}</p>
                            @if (Gate::check('edit front home page'))
                                <div class="d-flex flex-wrap gap-2">
                                    @if ($review->status !== 'approved')
                                        {!! Form::open(['route' => ['website-reviews.status', $review->id], 'method' => 'POST']) !!}
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-sm btn-success"><i class="ti ti-check"></i> {{ __('Show on website') }}</button>
                                        {!! Form::close() !!}
                                    @endif
                                    @if ($review->status !== 'hidden')
                                        {!! Form::open(['route' => ['website-reviews.status', $review->id], 'method' => 'POST']) !!}
                                        <input type="hidden" name="status" value="hidden">
                                        <button class="btn btn-sm btn-light-secondary"><i class="ti ti-eye-off"></i> {{ __('Hide') }}</button>
                                        {!! Form::close() !!}
                                    @endif
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['website-reviews.destroy', $review->id]]) !!}
                                    <a class="btn btn-sm btn-light-danger confirm_dialog" href="#"><i class="ti ti-trash"></i> {{ __('Delete') }}</a>
                                    {!! Form::close() !!}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="ti ti-star f-36 d-block mb-2"></i>
                            {{ __('No reviews yet. Tenants can write one from the "Write a Review" page in their account.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
