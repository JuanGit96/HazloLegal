# Embudo 1 · El documento gratuito

El imán del negocio. Quien llega aquí no quiere pagar, y está bien: lo que se
busca es que se lleve algo que sirva y deje un contacto con su permiso.

## Dónde empieza

| Entrada | Ruta |
|---|---|
| Búsqueda «derecho de petición gratis» | `/gratis` o la ficha del trámite |
| Home | La franja «Lo que puedes resolver hoy y gratis» |
| Catálogo | La franja verde de trámites gratuitos antes de los chips de área |
| Una calculadora | Tras el resultado, si hay `related_document_id` |

## Paso a paso

### 1. La tarjeta
`tool-card` con el gancho del trámite (`legal_documents.hook`) y la etiqueta
«Gratis». El CTA dice **«Generarlo ahora»**, no «Ver más»: nombrar la acción
convierte más que nombrar el destino.

### 2. El checkout (`/contratar/{slug}`)
La misma pantalla que la de pago, porque el modelo es el mismo. Arriba, la
píldora «Este trámite es gratis». Debajo, los adicionales con la línea:

> El documento es gratis. Esto es lo que puedes sumarle si lo necesitas.

El total en vivo dice «Gratis» y el botón dice **«Generar mi documento
gratis»**. Si marca un adicional, el botón cambia solo a «Continuar al pago».

### 3. El caso y los datos
Un `textarea` con un ejemplo concreto en el `placeholder` —fechas, entidad,
número de radicado—, porque un campo vacío con «describe tu caso» produce tres
líneas inservibles.

Encima, la franja del gancho: «Corregimos ortografía y redacción · Sin vacíos
legales · Tono formal ante la entidad».

Luego nombre, correo, celular, tipo y número de documento, ciudad.

### 4. Los consentimientos
Cuatro casillas, **dos obligatorias y dos opcionales**:

| Casilla | Obligatoria | Qué autoriza |
|---|---|---|
| Términos y condiciones | sí | — |
| Tratamiento de datos (Ley 1581) | sí | Procesar sus datos para entregar el documento |
| «Quiero que un abogado me contacte» | no | Compartir el caso con abogados de la red |
| «Quiero recibir guías legales» | no | Correos |

Las dos últimas son **independientes**. Autorizar una no autoriza la otra, y
hay un test que lo comprueba.

### 5. Lo que pasa al enviar

`OrderService::place()`:

1. Cotiza con `PricingService`.
2. Pasa el texto por `DocumentReviewer`. **Lo que se guarda es el texto ya
   corregido**, porque es el que termina en el documento.
3. Crea el pedido con `access_token` y, como el total es 0, ya en estado
   `Paid`.
4. Genera el PDF del documento (y el instructivo si lo contrató).
5. Captura el lead con lo que el titular autorizó.
6. Manda el correo con los PDF adjuntos.

**No hay pantalla de pago.** Meter un cobro de cero pesos es la forma más
rápida de perder a quien venía por algo gratis.

### 6. La confirmación (`/pedido/{ref}?t=...`)

- Los archivos para descargar, con su explicación.
- **Qué se revisó de su redacción**: lo que se corrigió y lo que debería
  revisar él mismo, con la línea «Corregimos ortografía, tono y puntuación. No
  cambiamos los hechos que nos contaste».
- El resumen del pedido.
- **Un solo adicional ofrecido**, después de entregar. A estas alturas ya vio
  que el documento sirve, y ese es el momento en que la oferta es honesta y
  además funciona. Se suma al mismo pedido: «No vuelves a escribir nada».

### 7. El correo

Cambia de tono según haya algo que cobrar. En el gratuito:

- Asunto: «Tu {trámite} está listo».
- Los PDF adjuntos.
- Un aviso en amarillo: **revisa tus datos antes de firmar**.
- Y, explícitamente: «Este trámite es automatizado: no pasa por la revisión
  individual de un abogado».

## Dónde termina

| Resultado | Qué queda |
|---|---|
| Se lleva el documento | Un lead y, si autorizó, un contacto que un abogado verá |
| Suma un adicional | Un pedido de pago |
| Vuelve meses después | Confianza ya construida |

## Qué puede fallar

- **El texto del cliente es demasiado corto.** `DocumentReviewer` lo avisa en
  `warnings`, que se pintan en la confirmación.
- **El trámite no tiene plantilla propia.** Se usa el respaldo genérico de
  `LegalDocumentPdfBuilder`. En el panel, la columna «Plantilla» lo marca en
  ámbar.
- **Falla la generación del PDF.** `prepareDeliverables()` lo registra y sigue:
  el cliente ya hizo su parte y el documento se puede regenerar desde el panel.
