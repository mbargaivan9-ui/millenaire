@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Rapport Financier</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.reports.dashboard') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Revenu Total</h5>
                    <h3 class="text-success">{{ number_format($totalRevenue, 0) }} €</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Transactions</h5>
                    <h3 class="text-info">{{ $transactionCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Moyenne par Transaction</h5>
                    <h3 class="text-warning">{{ number_format($averageAmount, 2) }} €</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Détail des Paiements</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Étudiant</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->completed_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $payment->student->user->name }}</td>
                            <td>{{ number_format($payment->amount, 2) }} €</td>
                            <td>{{ $payment->provider }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
