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
use Filament\Tables\Actions\Action;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        return (string) Candidate::where('is_seen', '0')->count();
    }

    public static function getNavigationLabel(): string
    {
        return 'Candidates applies';
    }

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
                      'reviewed' => 'Reviewed'
                    ])
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Candidate::query()->latest('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->color(fn ($record) => $record->is_seen ? 'black' : 'danger'),
                Tables\Columns\TextColumn::make('status')->label('Status')
                ->color(fn ($record) => $record->is_seen ? 'black' : 'danger'),
                Tables\Columns\TextColumn::make('name')->label('Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('first_name')->label('First Name'),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Phone')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('cv_file')
                    ->label('CV')
                    ->url(fn($record) => asset('files/' . $record->cv_file))
                    ->openUrlInNewTab()
                    ->default('No CV Uploaded'),
                Tables\Columns\TextColumn::make('description')->label('Description')->limit(50),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'reviewed' => 'Reviewed'
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('markAsSeen')
                    ->action(fn (Candidate $record) => $record->update(['is_seen' => 1]))
                    ->color('success')
                    ->after(fn () => redirect(request()->header('Referer')))
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
