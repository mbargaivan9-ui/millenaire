@extends('layouts.app')

@section('title','Sécurité')

@section('content')
  <script>window.location.href = "{{ route('profile.security') }}";</script>
  <p style="text-align:center;padding:40px;color:var(--text-muted)">Redirection...</p>
@endsection
