# Qué cubren las pruebas

`ddev artisan test` — **80 pruebas, 326 aserciones.**

Las pruebas de este proyecto no están para subir una métrica de cobertura: cada
una cubre algo que, si se rompe, cuesta dinero o expone legalmente a la
empresa.

## `CheckoutFlowTest` — el embudo de compra (9)

| Prueba | Qué protege |
|---|---|
| Un trámite gratuito se entrega sin pasar por pago | Que un cobro de $0 no espante a quien vino por algo gratis. Comprueba además que el PDF se generó |
| Los adicionales se suman al precio base y quedan congelados | El cálculo (49.900 + 0 + 19.900 + 40.000 = 109.800) y que el precio se guarda en la línea del pedido |
| El acompañamiento marca el pedido como atendido por abogado | Que `OrderTier` se deduce y no se elige |
| El embudo de pago llega hasta la confirmación | El recorrido completo con sus correos |
| El pedido exige aceptar términos y tratamiento de datos | Ley 1581 de 2012 |
| **El consentimiento queda registrado tal como lo marcó el cliente** | Que autorizar el contacto de abogados **no** autoriza los correos |
| **La referencia sola no abre el pedido de otro** | `HL-2026-00042` es adivinable: 403 sin el token |
| Se puede sumar un adicional a un pedido ya entregado | La venta posterior, y que vuelve a cobro si cuesta |
| Un pedido ya pagado no se cobra dos veces | Idempotencia del pago |

## `ComplexCaseTest` — la carrera por un caso

| Prueba | Qué protege |
|---|---|
| El primer abogado que acepta se queda el caso | La regla del producto |
| **El segundo ve que ya fue tomado y no recibe datos** | Que los datos del cliente no se filtran a quien no ganó |
| Recargar el enlace de aceptación no rompe nada | Idempotencia |
| Un abogado puede rechazar con motivo | Incluido el motivo que llega después del rechazo |
| Un token inválido devuelve 404 | |
| El formulario exige autorización de datos | |

## `LawyerPortalTest` — aislamiento del portal (8)

| Prueba | Qué protege |
|---|---|
| Un abogado entra a su portal pero no al de administración | Que no vea pedidos, ingresos ni clientes ajenos |
| Un usuario del equipo no entra al portal | La separación en los dos sentidos |
| **Solo ve los casos que él mismo ganó** | Ni listándolos ni abriéndolos por URL |
| Sin la característica en el plan no hay contactos | Que la suscripción signifique algo |
| **Solo ve contactos que autorizaron y de sus áreas** | La frontera legal del negocio de datos |
| Ni el que autorizó enseña su correo en el listado | Hay que abrirlo, y queda registrado |
| Edita su perfil pero no su tarjeta profesional | |
| La suscripción dice qué incluye y qué no | |

## `PublicSeoTest` — SEO y honestidad (15)

| Prueba | Qué protege |
|---|---|
| La home trae los datos estructurados | `LegalService`, `WebSite`, `FAQPage`, `ItemList`, canónica, og |
| La ficha declara servicio, oferta y FAQ | Con `priceCurrency: COP` |
| Un artículo declara autor acreditado | La señal E-E-A-T de contenido YMYL |
| **La tarjeta profesional nunca se publica** | En cuatro páginas distintas |
| **El sitio no promete revisión de cada documento** | |
| **La home no nombra alternativas gratuitas** | |
| El sitemap lista catálogo y blog | |
| Robots bloquea el embudo y publica el sitemap | |
| **Ningún estático tapa las rutas de SEO** | Un `public/robots.txt` se comería la ruta |
| Las páginas del embudo no se indexan | |
| La home muestra la garantía y el WhatsApp | |
| La home ofrece lo gratis antes que lo pago | Si no, nadie entra al embudo |
| Cada adicional muestra su precio antes de pagar | Sin sorpresas en la factura |
| La home pide consentimiento de forma visible | |
| El consentimiento de abogados es casilla aparte | |

## `SignatureFlowTest` — la firma electrónica (7)

| Prueba | Qué protege |
|---|---|
| El enlace abre la pantalla del celular | Y que no tiene navegación: quien llega ahí ya decidió |
| **Firmar estampa el trazo y deja la evidencia** | Fecha, IP y huella SHA-256 del PDF |
| El documento firmado lleva el certificado | |
| No se puede firmar dos veces con el mismo enlace | |
| Hay que confirmar que es uno quien firma | |
| Un enlace vencido no firma | |
| Un token inventado no existe | |

## `WompiPaymentTest` — los cobros (5)

| Prueba | Qué protege |
|---|---|
| La pantalla sale hacia Wompi con firma de integridad | Que nadie edite el monto en el enlace |
| Un evento aprobado pone el pedido en marcha | |
| **Un evento sin firma válida no cambia nada** | Que no baste conocer la URL del webhook |
| Un evento repetido no entrega dos veces | Los webhooks se reintentan |
| Un pago rechazado deja el pedido sin pagar | |

## `ContactFormsTest` — contacto y datos (4)

| Prueba | Qué protege |
|---|---|
| El contacto deja registro y manda los dos correos | Al equipo y al remitente, con su radicado |
| **La eliminación exige identificar al titular** | Borrar sin verificar quién pide es el problema contrario |
| La solicitud queda con su plazo legal | 15 días hábiles |
| El contacto exige autorización de datos | |

## `AdminPanelTest` — que el panel cargue (21)

Todos los listados, el tablero, las fichas de edición, las once páginas de
hoja de ruta, la matriz de precios, los ajustes, y que el panel exija
autenticación.

Parece trivial y no lo es: en Filament 5 un argumento de closure mal nombrado
o una columna que ya no existe revientan **en tiempo de ejecución**, no al
compilar. Sin estas pruebas, un cambio de catálogo deja el panel caído y nadie
se entera hasta abrirlo.

## Cuándo añadir una prueba

La regla de `CLAUDE.md`: si tocas **el reparto de casos, los precios o el
SEO**, añade la prueba antes.

Y en general: si lo que vas a cambiar, al romperse, costaría dinero o
expondría datos de alguien, eso lleva prueba.
