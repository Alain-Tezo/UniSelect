<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Vérifie si l'utilisateur est un super administrateur
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Vérifie si l'utilisateur est un administrateur (simple ou super)
     *
     * @return bool
     */
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }
    
    /**
     * Récupère les filières que l'administrateur peut gérer
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'admin_filiere', 'user_id', 'filiere_id')
                    ->withTimestamps();
    }
    
    /**
     * Vérifie si l'administrateur a accès à la filière spécifiée
     * 
     * @param int $filiereId
     * @return bool
     */
    public function canManageFiliere($filiereId)
    {
        // Les super admins peuvent toujours gérer toutes les filières
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Pour les admins simples, vérifier si la filière est dans leur liste
        return $this->filieres()->where('filieres.id', $filiereId)->exists();
    }
}
