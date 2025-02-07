<?php

namespace App\Filament\Pages;

use App\Models\Chat;
use Filament\Pages\Page;

class Message extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.message';

    public static function getNavigationBadge(): ?string
    {
        return (string) Chat::where('is_seen', '0')->count();
    }
}
