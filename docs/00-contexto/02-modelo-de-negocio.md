# Modelo de negocio: de dónde sale el dinero

Hay tres líneas de ingreso. La primera paga las cuentas hoy, la segunda es la
que escala, y la tercera es el margen.

## 1. Adicionales sobre el documento

El precio no sale de un plan cerrado. Sale de un **precio base** —que puede ser
cero— más los adicionales que el cliente suma. Cada adicional tiene un precio
propio que además puede variar por trámite, porque acompañar una tutela no
cuesta lo mismo que acompañar un contrato de arriendo.

| Adicional | Qué entrega | Precio por defecto |
|---|---|---|
| **Instructivo** | PDF con dónde radicar, qué anexar, qué plazo tiene la entidad | $19.900 |
| **Firma digital** | Firma electrónica desde el celular (Ley 527 de 1999) | $19.900 |
| **Express** | Entrega inmediata en lugar de 24 horas | $60.000 |
| **Acompañamiento** | Un abogado lee el caso y redacta sobre él | $129.900 |
| **Sesión** | Media hora con un abogado antes de radicar | $79.900 |

En los trámites de pago el instructivo va **incluido a precio cero**: ya está
dentro del precio, y sirve para que el PDF con nuestra marca circule igual.

Un derecho de petición empieza en $0 y puede llegar a $309.600 si el cliente lo
carga entero. Esa horquilla es el producto: nadie paga $309.600 por un derecho
de petición, pero mucha gente paga $19.900 por saber dónde radicarlo.

**Dónde vive:** `app/Domain/Catalog/Services/PricingService.php` y
`AddonOfferService.php`. Ver [el motor de precios](../01-arquitectura/03-precios-y-adicionales.md).

## 2. Suscripción de abogados

La línea que escala sin costo marginal. Cada mes llega tráfico de alta
intención buscando cómo resolver algo legal. La mayoría se lo resuelve sola con
un formato. Una parte necesita un abogado, y hoy no sabe a quién llamar.

| Plan | Mensual | Anual | Qué añade |
|---|---|---|---|
| **Directorio** | $50.000 | $500.000 | Perfil público, avisos de casos |
| **Crecimiento** | $150.000 | $1.500.000 | + nombre en los instructivos, acceso a contactos |
| **Destacado** | $350.000 | $3.500.000 | + destacado en el directorio, presencia en artículos |

Lo que el abogado compra no es publicidad: es **estar donde ya está el que
tiene el problema**, en el momento en que lo tiene. El instructivo que el
cliente imprime y lleva a la ventanilla lleva su nombre. El artículo que
explica cómo hacer una tutela termina ofreciendo su perfil.

Los precios y lo que incluye cada plan se administran desde el panel sin tocar
código, y cada interruptor cambia lo que el sistema hace de verdad, no solo lo
que la landing promete.

**Dónde vive:** `app/Domain/Subscriptions/` y el panel en
`app/Filament/Resources/LawyerPlans/`.

## 3. Los datos, dentro de la ley

Un documento gratuito que no deja nada es solo un costo. Lo que deja es un
contacto: nombre, correo, celular, ciudad, área y lo que la persona contó.

Ese contacto **solo se comparte con abogados si el titular marcó la casilla que
lo autoriza**, que es distinta y opcional frente a la de recibir correos. Las
dos finalidades se piden por separado porque son distintas, y autorizar una no
autoriza la otra.

Además:

- Un abogado solo ve contactos **de sus áreas de práctica**. Entregar más de lo
  necesario no se justifica ante el titular ni ante la SIC.
- El listado no muestra correo ni teléfono: hay que abrir el contacto, y esa
  apertura queda registrada en `lead_accesses` con fecha e IP. Si un titular
  pregunta quién accedió a sus datos, hay una respuesta.
- Revocar es un clic desde el panel, y entonces el contacto deja de aparecer
  para todos.

Esto no es cautela excesiva: es la Ley 1581 de 2012, y es también lo que hace
vendible la suscripción. Un abogado paga más por contactos que esperan su
llamada que por una lista comprada.

**Dónde vive:** `app/Domain/Shared/Services/LeadCaptureService.php`,
`app/Domain/Shared/Models/Lead.php` (scope `shareable`) y
`app/Filament/Lawyer/Resources/MyLeads/`.

## Por qué se regala el producto principal

La competencia (AbogadoYa y similares) regala derechos de petición y
calculadoras para alimentar un directorio de abogados de pago. Funciona por
tres razones:

1. **«Derecho de petición gratis» es la búsqueda, no «abogado».** Quien busca
   abogado ya decidió gastar; quien busca el formato todavía no, y es mucha más
   gente.
2. **El que descarga gratis vuelve.** No por el mismo trámite, por el
   siguiente. Y la segunda vez ya confía.
3. **El que no vuelve deja un contacto** que un abogado valora, con su permiso.

El riesgo del modelo es obvio: si lo gratuito no convierte, es puro costo. Por
eso el tablero del panel tiene una tarjeta específica con **qué porcentaje de
los pedidos del mes acabó pagando**. Si esa cifra no sube, el modelo no está
funcionando y hay que verlo antes de que se note en la caja.

## Los números que miden el modelo

En `/admin`, widget «El embudo del modelo»
(`app/Filament/Widgets/FunnelOverviewWidget.php`):

- Documentos gratis del mes y % que acabó pagando.
- Ingresos por adicionales: lo que se sumó sobre el precio base. Mide si el
  checkout persuade o solo cobra.
- MRR de suscripciones de abogados y cuántos están en prueba.
- Contactos compartibles: solo los que lo autorizaron.
