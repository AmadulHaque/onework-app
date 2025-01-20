<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('status')
                    ->label('Status')
                    ->options([
                      'pending' => 'Pending',
                      'approved' => 'Approved',
                      'rejected' => 'Rejected',
                      'interview' => 'Interview'
                    ])
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Candidate::query()->latest('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('first_name')->label('First Name'),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Phone')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('description')->label('Description')->limit(50),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),

            ])
            ->filters([
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'interview' => 'Interview'
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
            'index' => Pages\ListCandidates::route('/'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }


    public static function canCreate(): bool
    {
        return false;
    }
}
