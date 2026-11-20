# Qué es HazloLegal

Una empresa colombiana que convierte trámites legales repetitivos en
documentos que cualquiera puede generar, entender y radicar por su cuenta —y
que conecta con un abogado a quien de verdad lo necesita.

## El problema que resuelve

En Colombia, un ciudadano con un problema legal corriente (la EPS le niega un
procedimiento, Datacrédito no le borra un reporte ya pagado, una entidad no le
responde) tiene dos caminos, y los dos son malos:

- **Ir a un abogado.** Entre $300.000 y $800.000, una cita presencial, y el
  precio no se sabe hasta después de la primera consulta.
- **Buscarlo en internet.** Plantillas de 2014 que citan normas derogadas, sin
  saber si sirven ni dónde se radican.

Lo caro del trámite nunca fue el papel. Era el tiempo, el desplazamiento y no
saber cuánto se iba a terminar pagando.

## Qué vende

Tres cosas, y conviene no confundirlas:

| Qué | Para quién | Cómo se cobra |
|---|---|---|
| **Documentos legales** | Quien tiene un trámite concreto | Gratis o precio fijo publicado, más adicionales |
| **Acompañamiento de un abogado** | Quien tiene un caso que no cabe en un formato | Adicional sobre el documento, o cotización |
| **Visibilidad para abogados** | Abogados que quieren clientes | Suscripción mensual o anual |

## Los tres niveles de servicio

1. **Hazlo tú** — El documento sale completo, gratis en varios trámites. Se
   revisa automáticamente la redacción. El cliente lo descarga y lo radica.
2. **Hazlo con guía** — El documento más el instructivo de radicación: dónde
   entregarlo, qué anexar, qué plazo tiene la entidad y qué hacer si no
   responde. Desde $19.900.
3. **Que lo haga un abogado** — Para lo que exige narrar hechos propios, hacer
   cálculos o escribir cláusulas condicionales. Un abogado redacta sobre el
   caso, no sobre un formato.

No son paquetes cerrados. El cliente **arma** su trámite sumando adicionales
sobre el documento; el «nivel» es una consecuencia de lo que sumó, no una
elección previa. Esto está explicado en
[el motor de precios](../01-arquitectura/03-precios-y-adicionales.md).

## El catálogo hoy

Diez trámites sembrados. Tres son gratis, dos no se pueden pedir sin abogado:

| Trámite | Precio base | Gratis | Autoservicio |
|---|---|---|---|
| Derecho de petición | $0 | sí | sí |
| Derecho de petición por fotomultas | $0 | sí | sí |
| Eliminar reporte en Datacrédito | $0 | sí | sí |
| Acuerdo de confidencialidad (NDA) | $49.900 | no | sí |
| Promesa de compraventa de vehículo | $59.900 | no | sí |
| Contrato de arrendamiento de vivienda | $89.900 | no | sí |
| Contrato de prestación de servicios | $99.900 | no | sí |
| Acción de tutela en salud | — | no | **no** |
| Revisión de liquidación laboral | — | no | **no** |
| Constitución de S.A.S. | — | no | **no** |

Los tres últimos no tienen autoservicio a propósito. Una tutela se gana o se
pierde en la narración de los hechos; un formato automático produciría
documentos que el juez niega, y eso costaría la reputación entera. Se venden
solo con acompañamiento.

Además hay cuatro calculadoras laborales gratuitas (liquidación, indemnización
por despido, horas extras e intereses de mora) y un directorio público de
abogados.

## Qué NO es

- **No es un bufete.** Los formatos los escriben abogados y se actualizan
  cuando cambia la norma, pero en los trámites automatizados **ningún abogado
  revisa cada documento antes de enviarlo**. Decir lo contrario expondría a la
  empresa y al abogado nombrado. Ver [Reglas de honestidad](04-reglas-de-honestidad.md).
- **No es un marketplace de abogados.** El directorio existe, pero el producto
  principal es el documento. El abogado es el siguiente paso de quien lo
  necesita, no la primera pantalla.
- **No promete resultados judiciales.** Se garantiza la calidad del documento,
  no la decisión de un juez.

## Dónde está cada cosa

| Qué | Dónde |
|---|---|
| Sitio público | `/` · [rutas](../01-arquitectura/01-vision-general.md#rutas-públicas) |
| Herramientas gratis | `/gratis` |
| Catálogo | `/documentos` |
| Checkout | `/contratar/{slug}` |
| Directorio de abogados | `/abogados` |
| Captación de abogados | `/abogados/registro` |
| Panel de administración | `/admin` |
| Portal del abogado | `/portal` |
