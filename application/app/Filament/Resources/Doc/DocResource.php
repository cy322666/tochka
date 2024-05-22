<?php

namespace App\Filament\Resources\Doc;

use App\Filament\Resources\Doc\DocResource\Pages;
use App\Models\Docs\Doc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocResource extends Resource
{
    protected static ?string $model = \App\Models\Docs\Doc::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название'),
                Tables\Columns\TextColumn::make('created_at_doc')
                    ->label('Создан')
                    ->dateTime(),
//                Tables\Columns\TextColumn::make('href')
//                    ->url(fn(Doc $doc) => $doc->href)
//                    ->label('Прямая ссылка'),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at_doc')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Созданы от'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Созданы до'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at_doc', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at_doc', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('')
                    ->icon('heroicon-o-arrow-down')
                    ->url(fn (Doc $record) => $record->href)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\Action::make('download_mass')
                        ->label('Скачать выбранные')
                        ->accessSelectedRecords()
                        ->action(function (Doc $record, Collection $selectedRecords) {
                            $selectedRecords->each(
                                fn (Doc $selectedRecord) => $selectedRecord->href
                            );
                        }),
                ]),
            ])
            ->paginated([30, 50, 100, 'all']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocs::route('/'),
//            'create' => Pages\CreateDoc::route('/create'),
//            'edit' => Pages\EditDoc::route('/{record}/edit'),
        ];
    }
}
