<?php

namespace Firefly\FilamentBlog\Resources\PostResource\Pages;

use App\Services\Website;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Firefly\FilamentBlog\Resources\PostResource;
use Firefly\FilamentBlog\Resources\PostResource\Widgets\BlogPostPublishedChart;

class ListPosts extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Action::make(__('admin.cache.flush'))
                ->icon('heroicon-o-arrow-path')
                ->button()
                ->requiresConfirmation()
                ->modalHeading(__('admin.cache.flush'))
                ->modalDescription(__('admin.cache.msg', ['model' => __('admin.website')]))
                ->color('warning')
                ->action(fn() => Website::make()->clearCache()),
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BlogPostPublishedChart::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(function ($query) {
                    $query->published();
                })->icon('heroicon-o-check-badge'),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(function ($query) {
                    $query->pending();
                })
                ->icon('heroicon-o-clock'),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(function ($query) {
                    $query->scheduled();
                })
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
