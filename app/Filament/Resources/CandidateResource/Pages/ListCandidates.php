<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Candidate;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;


    protected function getTableRecordClasses($record): ?string
    {
        return $record->is_seen ? 'bg-white' : 'bg-red-100'; // Highlight unseen candidates in red
    }
    protected function getTableActions(): array
    {
        return [
            Action::make('markAsSeen')
                ->action(fn (Candidate $record) => $record->update(['is_seen' => 1]))
                ->color('success')
                ->after(fn () => redirect(request()->header('Referer'))) // Refresh the page
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
