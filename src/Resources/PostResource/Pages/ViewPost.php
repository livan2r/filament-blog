<?php

namespace Firefly\FilamentBlog\Resources\PostResource\Pages;

use Filament\Actions\Action;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Pages\ViewRecord\Concerns\Translatable;
use Firefly\FilamentBlog\Events\BlogPublished;
use Firefly\FilamentBlog\Models\Post;
use Firefly\FilamentBlog\Resources\PostResource;
use Illuminate\Contracts\Support\Htmlable;

class ViewPost extends ViewRecord
{
    Use Translatable;

    protected static string $resource = PostResource::class;

    public function getTitle(): string|Htmlable
    {
        $record = $this->getRecord();

        return $record->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->url(PostResource::getUrl())
                ->label(__('admin.return'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Actions\LocaleSwitcher::make(),
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil'),
            Action::make('sendNotification')
                ->label('Send Notification')
                ->requiresConfirmation()
                ->icon('heroicon-o-bell')->action(function (Post $record) {
                    event(new BlogPublished($record));
                })
                ->disabled(function (Post $record) {
                    return $record->isNotPublished();
                }),
            Action::make('preview')
                ->label('Preview')
                ->requiresConfirmation()
                ->icon('heroicon-o-eye')->url(function (Post $record) {
                    return route('filamentblog.post.show', $record->slug);
                }, true)
                ->disabled(function (Post $record) {
                    return $record->isNotPublished();
                }),
        ];
    }
}
