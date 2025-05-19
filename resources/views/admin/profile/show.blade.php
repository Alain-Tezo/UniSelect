@extends('layouts.admin')

@section('title', 'Mon Profil')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-user me-1"></i> Mon Profil
                </div>
                <div>
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="mb-4">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                     class="img-thumbnail rounded-circle"
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     alt="Avatar de {{ Auth::user()->name }}">
                            @else
                                <div class="avatar mx-auto" style="width: 150px; height: 150px; font-size: 60px;">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-{{ Auth::user()->role === 'super_admin' ? 'danger' : 'primary' }} fs-6">
                                {{ Auth::user()->role === 'super_admin' ? 'Super Admin' : 'Administrateur' }}
                            </span>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('profile.edit.password') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-key me-1"></i> Changer de mot de passe
                            </a>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">Nom</th>
                                    <td>{{ Auth::user()->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ Auth::user()->email }}</td>
                                </tr>
                                <tr>
                                    <th>Rôle</th>
                                    <td>{{ Auth::user()->role === 'super_admin' ? 'Super Administrateur' : 'Administrateur' }}</td>
                                </tr>
                                <tr>
                                    <th>Membre depuis</th>
                                    <td>{{ Auth::user()->created_at->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière mise à jour</th>
                                    <td>{{ Auth::user()->updated_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
