<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

/** Construye enlaces wa.me con mensaje prellenado y trazabilidad. */
final class WhatsAppLinkBuilder
{
    public function link(?string $message = null, array $context = []): string
    {
        $number = preg_replace('/\D+/', '', (string) config('hazlolegal.contact.whatsapp'));
        $text = $message ?? 'Hola, quiero resolver un trámite legal.';

        if ($context !== []) {
            $text .= ' ('.collect($context)->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ').')';
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode($text);
    }

    public function forDocument(string $documentName): string
    {
        return $this->link("Hola, necesito una {$documentName}. ¿Me ayudan?");
    }

    public function forOrder(string $reference): string
    {
        return $this->link("Hola, tengo una duda sobre mi pedido {$reference}.");
    }

    public function forCase(string $reference): string
    {
        return $this->link("Hola, consulto por mi caso {$reference}.");
    }

    public function display(): string
    {
        return (string) config('hazlolegal.contact.whatsapp_display');
    }
}
