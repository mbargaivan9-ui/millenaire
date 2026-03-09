@extends('layouts.app')

@section('title', 'Saisie des notes - ' . $template->name)

@section('content')
<livewire:grade-entry :template="$template" />
@endsection
