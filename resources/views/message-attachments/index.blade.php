@extends('layouts.app')
@section('title', 'Pièces Jointes')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h4 mb-4">Pièces Jointes des Messages</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>Fichier</th><th>Type</th><th>Taille</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($attachments ?? [] as $attachment)
                        <tr>
                            <td><i class="bi bi-paperclip me-1"></i>{{ $attachment->file_name }}</td>
                            <td>{{ $attachment->file_type ?? '—' }}</td>
                            <td>{{ $attachment->file_size ?? '—' }}</td>
                            <td>{{ Str::limit($attachment->message->content ?? '—', 40) }}</td>
                            <td>{{ $attachment->created_at->format('d/m/Y') }}</td>
                            <td><a href="{{ asset($attachment->file_path) }}" download class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune pièce jointe.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
