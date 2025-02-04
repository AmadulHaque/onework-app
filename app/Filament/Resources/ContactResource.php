<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class ContactResource extends Resource
{

    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        return (string) Contact::where('is_seen', '0')->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Contact::query()->latest('created_at'))
            ->columns([
                BadgeColumn::make('is_seen')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->is_seen ? null : 'New')
                    ->colors([
                        'danger' => 'New', // Display red badge for "New" records
                    ]),
                Columns\TextColumn::make('name')->label('name'),
                Columns\TextColumn::make('email')->label('Email'),
                Columns\TextColumn::make('phone')->label('Phone'),
                Columns\TextColumn::make('message')->label('Message'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('markAsSeen')
                    ->label('Mark as Seen')
                    ->icon('heroicon-o-eye')
                    ->action(fn (Contact $record) => $record->update(['is_seen' => 1]))
                    ->color('success')
                    ->visible(fn (Contact $record) => !$record->is_seen) // Show action only if not seen
                    ->after(function () {
                        return redirect(request()->header('Referer')); // Refresh the page
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListContacts::route('/'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
