@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="container" style="padding: 30px 0;">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="padding: 20px; font-size: 18px; font-weight: 600;">{{ __('Dashboard') }}</div>

                <div class="card-body" style="padding: 30px;">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>{{ __('Welcome') }}, <strong>{{ auth()->user()->name }}</strong>!</p>
                    <p>{{ __('Dashboard Subtitle') }}</p>
                    
                    <div style="margin-top: 30px; padding: 20px; background: var(--surface-2); border-radius: 8px;">
                        <h5>{{ __('user role') }}: <span style="color: var(--primary); font-weight: 600;">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
