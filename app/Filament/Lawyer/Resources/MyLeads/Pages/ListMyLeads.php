<?php

declare(strict_types=1);

namespace App\Filament\Lawyer\Resources\MyLeads\Pages;

use App\Filament\Lawyer\Resources\MyLeads\MyLeadResource;
use Filament\Resources\Pages\ListRecords;

class ListMyLeads extends ListRecords
{
    protected static string $resource = MyLeadResource::class;

    public function getTitle(): string
    {
        return 'Contactos';
    }

    public function getSubheading(): ?string
    {
        return 'Personas de tus áreas que autorizaron que un abogado las contacte. '
            .'Cada vez que abres un contacto queda registrado quién lo hizo.';
    }
}
