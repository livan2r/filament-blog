<?php

namespace Firefly\FilamentBlog\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Awcodes\Curator\Models\Media;
use Filament\Models\Contracts\HasAvatar;
use Firefly\FilamentBlog\Database\Factories\UserFactory;
use Firefly\FilamentBlog\Traits\HasBlog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasAvatar
{
    use HasBlog, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'password' => 'hashed',
    ];

    public function canComment()
    {
        return true;
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected static function newFactory()
    {
        return new UserFactory();
    }

    /**
     * Get the user's avatar URL.
     *
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        return Media::find($this->avatar)?->url ?? null;
    }
}
