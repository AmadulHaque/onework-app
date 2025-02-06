<?php

namespace App\Filament\Resources\EnterpriseResource\Pages;

use App\Filament\Resources\EnterpriseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnterprise extends EditRecord
{
    protected static string $resource = EnterpriseResource::class;

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
