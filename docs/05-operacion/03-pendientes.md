# Pendientes

Estado a 19 de noviembre de 2026. Lo que está hecho está en el resto de la
documentación; esto es lo que falta, sin adornos.

## Pagos

**Wompi está integrado en sandbox**, tanto para las compras de clientes como
para las suscripciones de abogados. Lo que falta:

- **Pasar a producción**: cambiar `WOMPI_MODE` y las llaves, y registrar la
  URL del webhook (`/wompi/eventos`) en el panel de comercios de Wompi.
- **Cobro recurrente automático** de las suscripciones. Hoy cada periodo se
  cobra con un enlace y el abogado decide cada vez. El automático exige
  guardar una fuente de pago tokenizada, que es otro flujo con su propio
  consentimiento explícito.
- **Conciliación**: no hay pantalla que compare lo cobrado en Wompi con lo
  registrado aquí.

## Producto

### Firma digital por QR
**Implementada.** El cliente recibe un QR, lo escanea, firma con el dedo en el
celular y el trazo queda estampado en el PDF con un certificado de auditoría
(fecha, hora, IP, dispositivo y huella SHA-256 del archivo).

Lo que falta para apretarla más:

- **OTP por SMS** antes de firmar. Las columnas están (`otp_code`,
  `otp_sent_at`) pero no se usan: hoy la credencial es el enlace. Añadirlo
  sube el valor probatorio.
- **Firma de varias partes** en un mismo documento (un contrato tiene dos).
- **Sello de tiempo de una entidad certificadora**, que es lo que convierte
  una firma electrónica en firma digital en sentido estricto.

### Consulta de antecedentes
La competencia la ofrece. Su versión solo consulta automáticamente las medidas
correctivas de la Policía y enlaza al resto porque tienen captcha.

Raspar sitios del Estado es frágil y legalmente discutible. La decisión
pendiente es si se hace una herramienta guiada con enlaces oficiales y un CTA
a abogado, o nada. **No se ha decidido.**

### Importador de PDF
La mitad está: `legal_documents.template_fields` permite definir campos extra
desde el panel, pero **el checkout todavía no los pinta** y no hay importador
que lea un PDF existente y proponga sus campos.

### Logo
Pendiente a propósito, se dejó para el final. `brand_logo`, `brand_logo_dark` y
`brand_favicon` ya son administrables desde `Ajustes del sitio`; falta el
diseño.

## SEO

- **`HowTo` sin usar.** `SchemaGenerator::howTo()` existe pero ninguna vista lo
  llama. Las guías del blog que son pasos deberían emitirlo.
- **`llms.txt`** en la raíz, describiendo el sitio para los rastreadores de IA.
- **Medición.** No hay Search Console ni analítica conectadas. Hoy solo se
  registran `views_count` por trámite y `profile_views` / `contact_clicks` por
  abogado.
- **Imágenes OG por trámite.** El campo existe (`og_image_path`); falta
  generarlas.

## Portal del abogado (fase 2)

Documentado dentro del propio panel. Falta:

- Bandeja de casos invitados con aceptar y rechazar desde la interfaz.
- Subir el documento terminado.
- Registro de horas y actuaciones.
- Liquidación de honorarios.
- Pausar invitaciones durante vacaciones.
- Marcar un contacto como contactado o descartado, para que el siguiente
  abogado no repita la llamada.
- Pagar la suscripción desde el portal.

## Panel

- **Roles dentro del equipo.** Hoy quien tiene `is_staff` entra a todo. La
  separación está documentada en «Próximos módulos → Equipo y permisos».
- **Editar el instructivo desde el panel.** La plantilla del documento sí se
  edita; el diseño del instructivo está en el Blade.
- **Portal de cliente.** Hoy el cliente accede por el enlace con token. Un
  área donde ver su histórico está en el roadmap.

## Lo que entró el 19 de noviembre

- Entrega diferida: sin entrega inmediata, el documento se encola para el
  plazo configurado del trámite en lugar de salir al instante.
- Firma electrónica por QR, con certificado de auditoría en el PDF.
- Wompi en sandbox para compras y suscripciones.
- Formularios de contacto y de eliminación de datos, con registro en el panel
  y plazo legal visible.
- Los trámites sin autoservicio van directo a cotización.
- Instructivo sin páginas en blanco (3 en vez de 5).

## Deuda técnica conocida

- **`DeliverySpeed` sigue existiendo** y `orders.speed` se sigue escribiendo,
  aunque la velocidad ahora sale del adicional `express`. Funciona, pero son
  dos formas de decir lo mismo y habría que elegir una.
- **Los PDF se guardan en el disco `local`.** Para producción con más de un
  servidor hay que mover a S3 o equivalente.
- **Los correos se envían de forma síncrona.** Deberían ir a una cola, igual
  que ya lo hace la generación de documentos.
- **La cola necesita un worker corriendo.** En local lo levanta ddev
  (`web_extra_daemons`); en producción hace falta supervisor o equivalente, o
  los documentos no se entregan nunca.
- **No hay reintentos** si falla la generación de un PDF: se registra el error
  y el pedido queda sin archivo, a la espera de que alguien lo regenere desde
  el panel. El filtro «Pagado y sin documento generado» existe justo para eso.

## Antes de producción

Ver el checklist completo en
[Accesos y permisos](../04-panel/03-accesos-y-permisos.md#checklist-antes-de-producción).
