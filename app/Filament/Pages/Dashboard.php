<?php

namespace App\Filament\Pages;

use App\Models\Candidate;
use App\Models\Enterprise;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    public Candidate $candidate;
    public Enterprise $enterprise;

    public function mount()
    {
        $this->candidate = new Candidate;
        $this->enterprise = new Enterprise;
    }
}
