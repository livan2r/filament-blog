<?php

namespace Firefly\FilamentBlog\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\Website;
use Awcodes\Curator\Models\Media;
use Filament\Models\Contracts\HasAvatar;
use Firefly\FilamentBlog\Database\Factories\UserFactory;
use Firefly\FilamentBlog\Traits\HasBlog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class User extends Authenticatable implements HasAvatar
{
    use HasBlog, HasFactory, Notifiable, HasTranslations;

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

    public array $translatable = [
        'position',
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
            return Website::make()->getAvatar($this->email, 40);
        }

        $media = Media::find($this->avatar)?->url ?? null;
        return $media ?? Storage::disk('media')->url($this->avatar);
    }
}
