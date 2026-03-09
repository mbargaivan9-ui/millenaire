@extends('layouts.app')

@section('title','Mon Profil')

@section('content')
  {{-- Redirect to the full profile page --}}
  <script>window.location.href = "{{ route('profile.show') }}";</script>
  <p style="text-align:center;padding:40px;color:var(--text-muted)">Redirection vers le profil...</p>
@endsection
