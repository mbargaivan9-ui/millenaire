@extends('layouts.app')

@section('title','Mon Profil')

@section('content')
    <h1 class="page-title">Mon Profil</h1>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="#">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input class="form-control" value="{{ $user->name }}" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" value="{{ $user->email }}" disabled>
                </div>
                <a href="#" class="btn btn-primary">Modifier mon profil</a>
            </form>
        </div>
    </div>
@endsection
