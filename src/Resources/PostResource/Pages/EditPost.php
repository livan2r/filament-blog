<?php

namespace Firefly\FilamentBlog\Resources\PostResource\Pages;

use Filament\Actions;
use App\Filament\Resources\BaseClasses\EditRecord;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;
use Filament\Support\Enums\MaxWidth;
use Firefly\FilamentBlog\Enums\PostStatus;
use Firefly\FilamentBlog\Resources\PostResource;

class EditPost extends EditRecord
{
    Use Translatable;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->url(PostResource::getUrl())
                ->label(__('admin.return'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Actions\LocaleSwitcher::make(),
            Actions\ViewAction::make()
                ->icon('heroicon-o-eye')
                ->slideOver()
                ->modalIcon('heroicon-o-eye')
                ->modalIconColor('primary')
                ->modalWidth(MaxWidth::ThreeExtraLarge),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function beforeSave()
    {
        if ($this->data['status'] === PostStatus::PUBLISHED->value) {
            $this->record->published_at = $this->record->published_at ?? date('Y-m-d H:i:s');
        }
    }
}
