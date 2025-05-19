@extends('layouts.admin')

@section('title', 'Modifier mon profil')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-edit me-1"></i> Modifier mon profil
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-4 text-center">
                            <div class="mb-4">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                                         class="img-thumbnail rounded-circle"
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         alt="Avatar actuel">
                                @else
                                    <div class="avatar mx-auto" style="width: 150px; height: 150px; font-size: 60px;">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="avatar" class="form-label">Photo de profil</label>
                                <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar">
                                <div class="form-text">Formats: JPG, JPEG, PNG, GIF. Max: 2Mo</div>
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if(Auth::user()->avatar)
                            <div class="mb-3">
                                <a href="{{ route('profile.avatar.delete') }}" class="btn btn-outline-danger btn-sm"
                                   onclick="event.preventDefault(); document.getElementById('delete-avatar-form').submit();">
                                    <i class="fas fa-trash me-1"></i> Supprimer la photo
                                </a>
                                <form id="delete-avatar-form" action="{{ route('profile.avatar.delete') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                       value="{{ old('name', Auth::user()->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                       value="{{ old('email', Auth::user()->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rôle</label>
                                <input type="text" class="form-control"
                                       value="{{ Auth::user()->role === 'super_admin' ? 'Super Administrateur' : 'Administrateur' }}"
                                       disabled>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prévisualisation de l'image
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.querySelector('.avatar img');
                    if (!preview) {
                        // Si pas d'image, on utilise le div avatar
                        const avatarDiv = document.querySelector('.avatar');
                        avatarDiv.innerHTML = '';
                        preview = document.createElement('img');
                        avatarDiv.appendChild(preview);
                    }
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endsection
@endsection
