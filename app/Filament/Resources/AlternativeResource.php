<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlternativeResource\Pages;
use App\Filament\Resources\AlternativeResource\RelationManagers\ResourcesRelationManager;
use App\Models\Alternative;
use Faker\Provider\ar_EG\Text;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AlternativeResource extends Resource
{
    protected static ?string $model = Alternative::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(), 
            TextInput::make('url')->required(), 
            Textarea::make('description')->columnSpanFull(), 
            Textarea::make('notes')->columnSpanFull(),
            Fieldset::make()
                ->relationship('logo')
                ->schema([
                    Hidden::make('type')->default('logo'),
                    FileUpload::make('path')->label('Logo')->image(),
                ])->columnSpan(1)->columns(1),
            Select::make('tags')->relationship('tags', 'name')
                ->multiple()->searchable()->preload()->native(false)
                ->createOptionForm([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                        ->required(),
                        TextInput::make('slug')
                        ->required(),
                        ])
                    ]),
            DateTimePicker::make('approved_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo.path')->circular(), 
                TextColumn::make('name')->searchable(), 
                IconColumn::make('approved_at')->label('Approved')
                    ->boolean(fn (Alternative $record): bool => $record->approved_at !== null),
                TextColumn::make('tags.name')->badge()->searchable(),
                TextColumn::make('url')
                    ->url(fn(Alternative $record) => $record->url)
                    ->color('info')
                    ->openUrlInNewTab()->searchable()->limit(50),
                TextColumn::make('resources.url')
                    ->label('Resources')
                    ->formatStateUsing(function ($record) {
                        return $record->resources->map(function ($resource) {
                            return "<a href='{$resource->url}' target='_blank'>{$resource->url}</a>";
                        })->implode('<br>');
                    })
                    ->html()
                    ->disabledClick()
                    ->color('info'),
                TextColumn::make('description')->limit(50), 
                TextColumn::make('notes')->limit(50),
                TextColumn::make('created_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
                ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
                ResourcesRelationManager::class,
            ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlternatives::route('/'),
            'create' => Pages\CreateAlternative::route('/create'),
            'edit' => Pages\EditAlternative::route('/{record}/edit'),
        ];
    }
}
