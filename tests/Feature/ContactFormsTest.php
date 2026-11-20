<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Enums\ContactKind;
use App\Domain\Shared\Mail\ContactAcknowledgementMail;
use App\Domain\Shared\Mail\ContactReceivedMail;
use App\Domain\Shared\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_formulario_de_contacto_deja_registro_y_manda_los_dos_correos(): void
    {
        Mail::fake();

        $this->post(route('pages.contact.store'), [
            'name' => 'Camila Restrepo',
            'email' => 'camila@example.com',
            'phone' => '3001234567',
            'subject' => 'Duda sobre un contrato',
            'message' => 'Quiero saber si el contrato de arrendamiento sirve para local comercial.',
            'accepts_privacy' => '1',
        ])->assertSessionHas('contactStatus');

        $message = ContactMessage::query()->firstOrFail();

        $this->assertSame(ContactKind::Contact, $message->kind);
        $this->assertStringStartsWith('MSG-', $message->reference);

        // Uno para el equipo y otro para quien escribió, con su radicado.
        Mail::assertSent(ContactReceivedMail::class);
        Mail::assertSent(ContactAcknowledgementMail::class);
    }

    public function test_la_solicitud_de_eliminacion_exige_identificar_al_titular(): void
    {
        $this->post(route('pages.data-deletion.store'), [
            'name' => 'Alguien Sin Documento',
            'email' => 'alguien@example.com',
        ])->assertSessionHasErrors(['document_number', 'confirms_ownership']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_la_solicitud_de_eliminacion_queda_con_su_plazo_legal(): void
    {
        Mail::fake();

        $this->post(route('pages.data-deletion.store'), [
            'name' => 'Jorge Enrique Páez',
            'email' => 'jorge@example.com',
            'document_type' => 'CC',
            'document_number' => '1020304050',
            'confirms_ownership' => '1',
        ])->assertSessionHas('deletionStatus');

        $message = ContactMessage::query()->firstOrFail();

        $this->assertSame(ContactKind::DataDeletion, $message->kind);
        $this->assertStringStartsWith('DAT-', $message->reference);
        $this->assertTrue($message->confirms_ownership);

        // 15 días hábiles es el plazo de la Ley 1581 de 2012.
        $this->assertSame(15, $message->kind->responseDays());
        $this->assertFalse($message->isOverdue());
    }

    public function test_el_contacto_exige_autorizacion_de_datos(): void
    {
        $this->post(route('pages.contact.store'), [
            'name' => 'Sin Permiso',
            'email' => 'sin@example.com',
            'message' => 'Un mensaje suficientemente largo para pasar la validación.',
        ])->assertSessionHasErrors('accepts_privacy');
    }
}
