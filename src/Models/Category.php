<?php

namespace Firefly\FilamentBlog\Models;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Firefly\FilamentBlog\Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory,
        HasTranslations;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public array $translatable = [
        'name',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, config('filamentblog.tables.prefix').'category_'.config('filamentblog.tables.prefix').'post');
    }

    public static function getForm()
    {
        return [
            TextInput::make('name')
                ->prefixIcon('heroicon-o-tag')
                ->prefixIconColor('secondary')
                ->label(__('filament-blog::admin.category.name.label'))
                ->helperText(__('filament-blog::admin.category.name.desc'))
                ->live(true)
                ->live(true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $operation, ?string $old, ?string $state) {

                    $set('slug', Str::slug($state));
                })
                ->unique(config('filamentblog.tables.prefix').'categories', 'name', null, 'id')
                ->required()
                ->maxLength(155),

            TextInput::make('slug')
                ->prefixIcon('heroicon-o-tag')
                ->prefixIconColor('secondary')
                ->label(__('filament-blog::admin.category.slug.label'))
                ->helperText(__('filament-blog::admin.category.slug.desc'))
                ->unique(config('filamentblog.tables.prefix').'categories', 'slug', null, 'id')
                ->readOnly()
                ->maxLength(255),
        ];
    }

    protected static function newFactory()
    {
        return new CategoryFactory();
    }

    public function getTable()
    {
        return config('filamentblog.tables.prefix') . 'categories';
    }
}
