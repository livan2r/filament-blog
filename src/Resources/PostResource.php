<?php

namespace Firefly\FilamentBlog\Resources;

use App\Filament\Resources\Helper;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\Models\Media;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Firefly\FilamentBlog\Enums\PostStatus;
use Firefly\FilamentBlog\Models\Category;
use Firefly\FilamentBlog\Models\Post;
use Firefly\FilamentBlog\Models\Tag;
use Firefly\FilamentBlog\Resources\PostResource\Pages\EditPost;
use Firefly\FilamentBlog\Resources\PostResource\Pages\ManaePostSeoDetail;
use Firefly\FilamentBlog\Resources\PostResource\Pages\ManagePostComments;
use Firefly\FilamentBlog\Resources\PostResource\Pages\ViewPost;
use Firefly\FilamentBlog\Resources\PostResource\Widgets\BlogPostPublishedChart;
use Firefly\FilamentBlog\Tables\Columns\UserPhotoName;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    use Translatable;

    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-minus';

    protected static ?string $navigationGroup = 'Blog';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?int $navigationSort = 3;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationBadge(): ?string
    {
        return strval(Post::where('status', 'published')->count());
    }

    public static function form(Form $form): Form
    {
        return Helper::twoColumnsForm($form,
            firstColumn: [
                \Filament\Forms\Components\Section::make(__('filament-blog::admin.post.details'))
                    ->icon('heroicon-o-folder')
                    ->iconColor('primary')
                    ->compact()
                    ->schema([
                        Select::make('category_id')
                            ->label(__('filament-blog::admin.post.category.label'))
                            ->helperText(__('filament-blog::admin.post.category.desc'))
                            ->multiple()
                            ->preload()
                            ->createOptionForm(Category::getForm())
                            ->searchable()
                            ->relationship('categories', 'name'),

                        ToggleButtons::make('status')
                            ->label(__('filament-blog::admin.post.status.label'))
                            ->helperText(__('filament-blog::admin.post.status.desc'))
                            ->live()
                            ->inline()
                            ->inlineLabel(false)
                            ->options(PostStatus::class)
                            ->required(),

                        TextInput::make('title')
                            ->prefixIcon('heroicon-o-tag')
                            ->prefixIconColor('secondary')
                            ->label(__('filament-blog::admin.post.title.label'))
                            ->helperText(__('filament-blog::admin.post.title.desc'))
                            ->live(true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $set('slug', Str::slug($state));
                                $set('photo_alt_text', $state);
                            })
                            ->required()
                            ->unique(config('filamentblog.tables.prefix').'posts', 'title', null, 'id')
                            ->maxLength(255),

                        Hidden::make('photo_alt_text'),

                        TextInput::make('slug')
                            ->prefixIcon('heroicon-o-tag')
                            ->prefixIconColor('secondary')
                            ->label(__('filament-blog::admin.post.slug.label'))
                            ->helperText(__('filament-blog::admin.post.slug.desc'))
                            ->maxLength(255),

                        TextInput::make('sub_title')
                            ->prefixIcon('heroicon-o-tag')
                            ->prefixIconColor('secondary')
                            ->label(__('filament-blog::admin.post.sub_title.label'))
                            ->helperText(__('filament-blog::admin.post.sub_title.desc'))
                            ->maxLength(255)
                            ->required(),

                        CuratorPicker::make('cover_photo_path')
                            ->label(__('filament-blog::admin.post.cover_photo_path.label'))
                            ->color('primary')
                            ->outlined(false)
                            ->constrained()
                            ->required()
                            ->helperText(__('filament-blog::admin.post.cover_photo_path.desc')),

                        Textarea::make('excerpt')
                            ->label(__('filament-blog::admin.post.excerpt.label'))
                            ->helperText(__('filament-blog::admin.post.excerpt.desc'))
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->inlineLabel(false),

                \Filament\Forms\Components\Section::make(__('filament-blog::admin.post.body.label'))
                    ->icon('heroicon-o-document-text')
                    ->iconColor('primary')
                    ->compact()
                    ->schema([
                        MarkdownEditor::make('body')
                            ->hiddenLabel()
                            ->minHeight('30rem')
                            ->maxHeight('40rem')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->inlineLabel(false),
            ],
            secondColumn: [
                \Filament\Forms\Components\Section::make(__('filament-blog::admin.post.adjustments'))
                    ->icon('heroicon-o-cog')
                    ->iconColor('primary')
                    ->compact()
                    ->schema([
                        Select::make('tag_id')
                            ->label(__('filament-blog::admin.post.tag.label'))
                            ->helperText(__('filament-blog::admin.post.tag.desc'))
                            ->multiple()
                            ->preload()
                            ->createOptionForm(Tag::getForm())
                            ->searchable()
                            ->relationship('tags', 'name')
                            ->columnSpanFull(),

                        DateTimePicker::make('scheduled_for')
                            ->label(__('filament-blog::admin.post.scheduled_for.label'))
                            ->helperText(__('filament-blog::admin.post.scheduled_for.desc'))
                            ->visible(function ($get) {
                                return $get('status') === PostStatus::SCHEDULED->value;
                            })
                            ->required(function ($get) {
                                return $get('status') === PostStatus::SCHEDULED->value;
                            })
                            ->minDate(now()->addMinutes(5))
                            ->native(false),

                        Select::make(config('filamentblog.user.foreign_key'))
                            ->label(__('filament-blog::admin.post.author.label'))
                            ->helperText(__('filament-blog::admin.post.author.desc'))
                            ->prefixIcon('heroicon-o-cursor-arrow-rays')
                            ->prefixIconColor('secondary')
                            ->relationship('user', config('filamentblog.user.columns.name'))
                            ->nullable(false)
                            ->default(auth()->id()),
                    ])->inlineLabel(false),
            ],
            helperText: __('filament-blog::admin.post.helper_text')
        );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.common.id.label'))
                    ->sortable(),
                CuratorColumn::make('cover_photo_path')
                    ->label(__('filament-blog::admin.post.cover_photo_path.label'))
                    ->disk('media')
                    ->width(80),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('filament-blog::admin.post.title.label'))
                    ->description(function (Post $record) {
                        return Str::limit($record->sub_title, 100);
                    })
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categories.name')
                    ->label(__('filament-blog::admin.post.category.label'))
                    ->badge()
                    ->color('primary')
                    ->inline()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament-blog::admin.post.status.label'))
                    ->badge()
                    ->color(function ($state) {
                        return $state->getColor();
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('filament-blog::admin.post.published_at.label'))
                    ->badge()
                    ->dateTime()
                    ->formatStateUsing(function ($state) {
                        return $state->format('Y-m-d H:i');
                    })
                    ->sortable(),
                UserPhotoName::make('user')
                    ->label(__('filament-blog::admin.post.author.label'))
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->label(__('filament-blog::admin.post.author.label'))
                    ->relationship('user', config('filamentblog.user.columns.name'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('filament-blog::admin.post.category.label'))
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->size(ActionSize::Medium)
                    ->tooltip(__('filament-actions::view.single.label'))
                    ->slideOver()
                    ->modalIcon('heroicon-o-eye')
                    ->modalIconColor('primary')
                    ->modalWidth(MaxWidth::ThreeExtraLarge),
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->size(ActionSize::Medium)
                    ->tooltip(__('filament-actions::edit.single.label')),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->size(ActionSize::Medium)
                    ->tooltip(__('filament-actions::delete.single.label')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            ImageEntry::make('cover_photo_url')
                ->hiddenLabel(),
            TextEntry::make('status')
                ->badge()->color(function ($state) {
                    return $state->getColor();
                }),
            TextEntry::make('published_at')->visible(function (Post $record) {
                return $record->status === PostStatus::PUBLISHED;
            }),
            TextEntry::make('scheduled_for')->visible(function (Post $record) {
                return $record->status === PostStatus::SCHEDULED;
            }),
            TextEntry::make('excerpt')
                ->hiddenLabel()
                ->columnSpanFull(),
            TextEntry::make('body')
                ->hiddenLabel()
                ->markdown()
                ->columnSpanFull()
        ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            //ViewPost::class,
            ManaePostSeoDetail::class,
            ManagePostComments::class,
            EditPost::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [
            //            \Firefly\FilamentBlog\Resources\PostResource\RelationManagers\SeoDetailRelationManager::class,
            //            \Firefly\FilamentBlog\Resources\PostResource\RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            BlogPostPublishedChart::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Firefly\FilamentBlog\Resources\PostResource\Pages\ListPosts::route('/'),
            'create' => \Firefly\FilamentBlog\Resources\PostResource\Pages\CreatePost::route('/create'),
            'edit' => \Firefly\FilamentBlog\Resources\PostResource\Pages\EditPost::route('/{record}/edit'),
            //'view' => \Firefly\FilamentBlog\Resources\PostResource\Pages\ViewPost::route('/{record}'),
            'comments' => \Firefly\FilamentBlog\Resources\PostResource\Pages\ManagePostComments::route('/{record}/comments'),
            'seoDetail' => \Firefly\FilamentBlog\Resources\PostResource\Pages\ManaePostSeoDetail::route('/{record}/seo-details'),
        ];
    }
}
