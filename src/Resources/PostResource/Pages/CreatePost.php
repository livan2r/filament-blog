<?php

namespace Firefly\FilamentBlog\Resources\PostResource\Pages;

use Carbon\Carbon;
use App\Filament\Resources\BaseClasses\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\Translatable;
use Firefly\FilamentBlog\Events\BlogPublished;
use Firefly\FilamentBlog\Jobs\PostScheduleJob;
use Firefly\FilamentBlog\Resources\PostResource;
use Firefly\FilamentBlog\Resources\SeoDetailResource;
use Filament\Actions\Action;
use Filament\Actions;

class CreatePost extends CreateRecord
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
        ];
    }

    //    protected function mutateFormDataBeforeCreate(array $data): array
    //    {
    //        dd($data);
    //    }

    protected function afterCreate()
    {
        if ($this->record->isScheduled()) {

            $now = Carbon::now();
            $scheduledFor = Carbon::parse($this->record->scheduled_for);
            PostScheduleJob::dispatch($this->record)
                ->delay($now->diffInSeconds($scheduledFor));
        }
        if ($this->record->isStatusPublished()) {
            $this->record->published_at = date('Y-m-d H:i:s');
            $this->record->save();
            event(new BlogPublished($this->record));
        }
    }

    /*protected function getRedirectUrl(): string
    {
        return SeoDetailResource::getUrl('create', ['post_id' => $this->record->id]);
    }*/
}
