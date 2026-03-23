@extends('layouts.app')

@section('title', 'Test Step 4')

@push('styles')
<style>
body { color: blue; }
</style>
@endpush

@section('content')
<div class="container">
    <h1>Test Page</h1>
    <p>This is a test page to verify Blade stack functionality</p>
</div>
@endsection

@push('scripts')
<script>
console.log('Test script loaded');
</script>
@endpush
