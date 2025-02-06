<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use App\Models\Candidate;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Actions\Action;

class EditCandidate extends EditRecord
{
    protected static string $resource = CandidateResource::class;


    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Mark the record as seen
        if (!$this->record->is_seen) {
            $this->record->update(['is_seen' => true]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
