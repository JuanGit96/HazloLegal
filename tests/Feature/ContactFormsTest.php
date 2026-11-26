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

    public function test_lo_que_publicamos_es_el_correo_de_contacto_y_no_el_remitente(): void
    {
        // Son dos direcciones con oficios distintos: a contacto@ escribe la
        // gente, y hola@ es con el que salen los nuestros. Publicar el
        // remitente llenaría esa bandeja de respuestas a correos
        // automáticos y de rebotes.
        $this->assertSame('contacto@hazlolegal.co', config('hazlolegal.contact.email'));
        $this->assertSame('hola@hazlolegal.co', config('mail.from.address'));

        foreach (['pages.cookies', 'pages.privacy', 'pages.terms', 'pages.contact', 'pages.about'] as $ruta) {
            $this->get(route($ruta))
                ->assertOk()
                ->assertSeeText('contacto@hazlolegal.co')
                ->assertDontSeeText('hola@hazlolegal.co');
        }
    }

    public function test_el_correo_que_recibe_el_cliente_sale_del_remitente_y_responde_al_de_contacto(): void
    {
        Mail::fake();

        $this->post(route('pages.contact.store'), [
            'name' => 'Andrés Gómez',
            'email' => 'andres@example.com',
            'phone' => '3009876543',
            'subject' => 'Pregunta',
            'message' => 'Quisiera saber si ustedes hacen contratos de obra labor.',
            'accepts_privacy' => '1',
        ]);

        Mail::assertSent(ContactAcknowledgementMail::class, function (ContactAcknowledgementMail $mail) {
            $envelope = $mail->envelope();

            // Quien responda a un correo nuestro tiene que llegar a la
            // bandeja que alguien lee, no al buzón de envío.
            return collect($envelope->replyTo)->contains(
                fn ($address) => $address->address === 'contacto@hazlolegal.co'
            );
        });
    }

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
