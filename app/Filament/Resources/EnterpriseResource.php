<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnterpriseResource\Pages;
use App\Filament\Resources\EnterpriseResource\RelationManagers;
use App\Models\Category;
use App\Models\Enterprise;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnterpriseResource extends Resource
{
    protected static ?string $model = Enterprise::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        return (string) Enterprise::where('is_seen', '0')->count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Enterprise applies';
    }
    public static function form(Form $form): Form
    {
        return $form
        ->schema(components: [
            Select::make('status')
                ->label('Status')
                ->options([
                 'pending'  => 'Pending',
                 'active'   => 'Active',
                 'rejected' => 'Rejected',
                 'reviewed' => 'Reviewed'
                ])
                ->default('pending'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Enterprise::query()->latest('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->label('Company Name')->sortable()->searchable()
                ->color(fn ($record) => $record->is_seen ? 'black' : 'danger'),

                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Phone'),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('description')->label('Description')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
            ->filters([
                // Existing category filter
                Tables\Filters\Filter::make('category')
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value']) || $data['value'] === null) {
                            return $query;
                        }
                        return $query->when($data['value'], function (Builder $query, $value) {
                            return $query->where('category_id', $value);
                        });
                    })
                    ->form([
                        Forms\Components\Select::make('value')
                            ->label('Category')
                            ->placeholder('All Categories')
                            ->options(
                                Category::query()
                                    ->select('id', 'name')
                                    ->distinct()
                                    ->pluck('name', 'id')
                                    ->toArray()
                            ),
                    ]),

                // New status filter
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'rejected' => 'Rejected',
                        'reviewed' => 'Reviewed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
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
            'index' => Pages\ListEnterprises::route('/'),
            'edit' => Pages\EditEnterprise::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }


}
