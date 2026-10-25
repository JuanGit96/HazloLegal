<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Content\Models\FaqEntry;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            // ----------------------------------------- Home
            [
                'group' => 'home',
                'question' => '¿Qué estoy pagando exactamente?',
                'answer' => "Estás pagando el trabajo jurídico que ya está hecho antes de que llegues: el formato de cada trámite lo escribieron abogados colombianos, con las normas que aplican a ese caso concreto, y lo actualizamos cuando la ley o la jurisprudencia cambian.\n\nA eso se suma el flujo que evita que te lo rechacen &mdash;validación de tus datos y los de la contraparte antes de armar el documento&mdash; y el [Certificado de Garantía](/garantia): si sale con un error nuestro, lo corregimos o te devolvemos el dinero.\n\nY si tu caso es simple y quieres intentarlo por tu cuenta, nuestras [guías gratuitas](/blog) explican cada trámite paso a paso. Preferimos que lo resuelvas aunque no nos pagues.",
                'sort_order' => 1,
            ],
            [
                'group' => 'home',
                'question' => '¿Quién hace mi documento?',
                'answer' => "Depende del trámite, y lo decimos en la ficha de cada uno antes de que pagues.\n\n**Trámites automatizados.** Son los de estructura estable, como un acuerdo de confidencialidad o un derecho de petición de Datacrédito. El documento se arma sobre un formato que redactó un abogado de nuestro equipo y que mantenemos actualizado. **No se revisa caso por caso**, y por eso cuesta lo que cuesta.\n\n**Trámites de redacción a la medida.** Son los que dependen de tus hechos concretos: una tutela, una liquidación laboral, unos estatutos. Aquí sí hay un abogado leyendo tu caso y escribiendo para ti.\n\n**Casos complejos.** Un abogado de la red toma el caso y te representa directamente.",
                'sort_order' => 2,
            ],
            [
                'group' => 'home',
                'question' => '¿Cuánto tardan realmente?',
                'answer' => 'El plan estándar entrega en **24 horas hábiles** y el Express en **menos de 4 horas**. El reloj arranca cuando confirmamos el pago y recibimos la información completa de tu caso, no antes. Nuestro promedio real de entrega es de 19 horas. Si nos pasamos del plazo por una razón nuestra, el trámite queda sin costo.',
                'sort_order' => 3,
            ],
            [
                'group' => 'home',
                'question' => '¿Qué pasa si el documento tiene un error?',
                'answer' => 'Lo corregimos en máximo 24 horas, sin costo y sin discutir, dentro de los 30 días siguientes a la entrega. Y si una entidad lo rechaza por un defecto de forma que cometimos nosotros, te devolvemos el 100 % de lo que pagaste. Está por escrito en el [Certificado de Garantía](/garantia).',
                'sort_order' => 4,
            ],
            [
                'group' => 'home',
                'question' => '¿Necesito ir a una oficina o firmar ante notario?',
                'answer' => 'No. Todo el proceso es remoto. Los documentos privados &mdash;contratos, NDA, promesas de compraventa&mdash; no requieren notaría para ser válidos, y los puedes firmar electrónicamente desde el celular con validez plena según la Ley 527 de 1999. Solo algunos trámites específicos exigen escritura pública, y cuando sea el caso te lo advertimos antes de que pagues.',
                'sort_order' => 5,
            ],
            [
                'group' => 'home',
                'question' => '¿Cómo pago y es seguro?',
                'answer' => 'Puedes pagar con Nequi, Daviplata, PSE, transferencia bancaria o tarjeta. El pago se hace dentro del sitio, sobre conexión cifrada, y recibes la confirmación con la referencia de tu pedido por correo. No pedimos datos de tarjeta por WhatsApp ni por teléfono: si alguien lo hace en nuestro nombre, no somos nosotros.',
                'sort_order' => 6,
            ],
            [
                'group' => 'home',
                'question' => '¿Qué pasa con mis datos personales?',
                'answer' => 'Los tratamos conforme a la Ley 1581 de 2012 y solo para prestarte el servicio. No los vendemos ni los compartimos con terceros con fines comerciales. Puedes pedir su eliminación cuando quieras escribiendo a nuestro correo de contacto. El detalle completo está en la [política de tratamiento de datos](/tratamiento-de-datos-personales).',
                'sort_order' => 7,
            ],
            [
                'group' => 'home',
                'question' => '¿Atienden en toda Colombia?',
                'answer' => 'Sí. Trabajamos con normativa nacional y atendemos desde cualquier ciudad del país. Para trámites que dependen de una entidad territorial &mdash;como las fotomultas, que las impone cada secretaría de tránsito&mdash; adaptamos el documento a la entidad que corresponde a tu caso.',
                'sort_order' => 8,
            ],

            // ----------------------------------------- Garantía
            [
                'group' => 'guarantee',
                'question' => '¿Qué cubre exactamente el Certificado de Garantía?',
                'answer' => "Cubre lo que depende de nosotros:\n\n- Errores de ortografía, gramática o redacción.\n- Datos tuyos o de la contraparte mal transcritos.\n- Normas mal citadas o derogadas.\n- Documentos que una entidad rechace por un defecto de forma atribuible a nuestra redacción.\n- Incumplimiento del plazo de entrega que prometimos.",
                'sort_order' => 1,
            ],
            [
                'group' => 'guarantee',
                'question' => '¿Qué no cubre?',
                'answer' => "Con total transparencia, no cubre:\n\n- **El resultado de la decisión** de un juez, una EPS o una entidad. Ningún abogado del mundo puede garantizar un fallo, y quien lo prometa está mintiendo.\n- **Información que no nos diste** o que nos diste incorrecta.\n- **Cambios de opinión** sobre lo que querías pedir después de entregado el documento.\n- Hechos ocurridos después de la entrega.\n- Trámites o pagos ante terceros (Cámara de Comercio, notarías, juzgados).",
                'sort_order' => 2,
            ],
            [
                'group' => 'guarantee',
                'question' => '¿Cómo activo la garantía?',
                'answer' => 'Llenas el formulario de esta página con la referencia de tu pedido y la descripción de lo que pasó. Un abogado la revisa y te responde dentro de las 24 horas hábiles siguientes. Si procede una corrección, la hacemos de inmediato; si procede la devolución, la tramitamos al mismo medio de pago.',
                'sort_order' => 3,
            ],
            [
                'group' => 'guarantee',
                'question' => '¿Cuánto tiempo tengo para reclamar?',
                'answer' => '30 días calendario desde la entrega del documento. Es un plazo amplio a propósito: queremos que tengas tiempo de radicarlo y ver la respuesta de la entidad antes de que la garantía expire.',
                'sort_order' => 4,
            ],
            [
                'group' => 'guarantee',
                'question' => '¿Hay límite de correcciones?',
                'answer' => 'No, mientras sean errores atribuibles a nosotros. Si lo que pides es un cambio de alcance &mdash;otra pretensión, otra contraparte, otros hechos&mdash; eso es un documento nuevo y te lo diremos con claridad en vez de cobrártelo sin avisar.',
                'sort_order' => 5,
            ],

            // ----------------------------------------- Firma electrónica
            [
                'group' => 'signature',
                'question' => '¿La firma electrónica con OTP es válida en Colombia?',
                'answer' => 'Sí. La Ley 527 de 1999 y el Decreto 2364 de 2012 reconocen plena validez y fuerza probatoria a la firma electrónica, siempre que sea confiable y apropiada para el propósito del mensaje. Un código OTP enviado al celular, junto con la verificación del correo y el registro de la IP y la fecha, cumple los requisitos de identificación y no repudio que exige la norma.',
                'sort_order' => 1,
            ],
            [
                'group' => 'signature',
                'question' => '¿Qué diferencia hay entre firma electrónica y firma digital?',
                'answer' => 'La **firma electrónica** es cualquier mecanismo que identifique al firmante y vincule su voluntad: OTP, trazo biométrico, clave. La **firma digital** es una especie más robusta que usa certificados emitidos por una entidad de certificación acreditada por la ONAC. Ambas son válidas; la digital tiene presunción legal de autenticidad y es obligatoria en ciertos trámites. Para contratos privados entre particulares, la firma electrónica con OTP es suficiente.',
                'sort_order' => 2,
            ],
            [
                'group' => 'signature',
                'question' => '¿Qué documentos NO se pueden firmar electrónicamente?',
                'answer' => 'Los que la ley exige que consten en escritura pública: compraventa de inmuebles, hipotecas, constitución de usufructo, capitulaciones matrimoniales, y los testamentos. También algunos trámites que requieren presencia física ante autoridad. Si tu caso es uno de estos, te lo advertimos antes de cobrarte.',
                'sort_order' => 3,
            ],
            [
                'group' => 'signature',
                'question' => '¿Qué pasa si la otra parte niega haber firmado?',
                'answer' => 'Por eso guardamos el rastro de auditoría: hora exacta, dirección IP, correo al que se envió el enlace, número al que llegó el OTP y el momento en que se validó. Ese conjunto de evidencia es lo que permite sostener la autenticidad en un proceso. Sin ese rastro, una firma electrónica es mucho más fácil de atacar.',
                'sort_order' => 4,
            ],

            // ----------------------------------------- Casos complejos
            [
                'group' => 'cases',
                'question' => '¿Cuánto cuesta que un abogado tome mi caso complejo?',
                'answer' => 'Depende por completo del alcance, y por eso no publicamos un precio: sería mentirte. Lo que sí es gratis y sin compromiso es la cotización. Cuentas tu caso, un abogado especializado lo evalúa y te propone un esquema de honorarios claro antes de que decidas nada.',
                'sort_order' => 1,
            ],
            [
                'group' => 'cases',
                'question' => '¿Cuánto tardan en responderme?',
                'answer' => 'Tu caso se envía simultáneamente a los abogados de nuestra red especializados en esa área. El primero que lo acepta se queda con él, y en ese momento recibes un correo con su nombre, su especialidad y su experiencia. La mayoría de los casos se asignan en pocas horas; el plazo máximo es de 48 horas.',
                'sort_order' => 2,
            ],
            [
                'group' => 'cases',
                'question' => '¿Qué tipo de casos manejan?',
                'answer' => 'Demandas y litigios, sucesiones, embargos y procesos ejecutivos, conflictos laborales avanzados, restitución de inmuebles, incidentes de desacato, procesos de familia y responsabilidad civil. Si tu caso no encaja en ninguno, cuéntanoslo igual: te diremos con franqueza si podemos ayudarte o si necesitas otro tipo de especialista.',
                'sort_order' => 3,
            ],
            [
                'group' => 'cases',
                'question' => '¿Qué pasa si ningún abogado acepta mi caso?',
                'answer' => 'Te escribimos para explicarte por qué. Puede ser que esté fuera de nuestras áreas, que el término ya haya vencido o que no sea viable. En cualquier caso te lo diremos de frente y, cuando podamos, te orientaremos sobre a dónde acudir. No cobramos nada por la cotización.',
                'sort_order' => 4,
            ],
        ];

        foreach ($faqs as $faq) {
            FaqEntry::query()->updateOrCreate(
                ['question' => $faq['question']],
                [...$faq, 'is_published' => true],
            );
        }
    }
}
