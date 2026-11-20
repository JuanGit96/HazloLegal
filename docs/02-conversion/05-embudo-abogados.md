# Embudo 4 · Captación y retención de abogados

La segunda línea de ingreso. Aquí el cliente es el abogado.

## El argumento

> Nosotros atraemos a quien tiene el problema. Tú atiendes el caso.

Cada mes llega tráfico buscando cómo resolver algo legal. La mayoría se lo
resuelve sola con un formato. Una parte necesita un abogado, y hoy no sabe a
quién llamar.

Lo que el abogado compra no es publicidad: es **estar donde ya está el que
tiene el problema**, en el momento en que lo tiene.

## Dónde empieza

| Entrada | Dónde |
|---|---|
| Banda en la home | «¿Eres abogado?», sobre fondo oscuro |
| `/abogados` | El directorio, que un abogado también visita |
| Búsqueda «conseguir clientes abogado Colombia» | `/abogados/registro` |

## La landing (`/abogados/registro`)

Cuatro promesas concretas, no adjetivos:

1. **Perfil público en el directorio** — con especialidad, ciudad y forma de
   atender. Indexable en Google.
2. **Apareces en los instructivos que circulan** — el PDF que el cliente
   imprime y lleva a la ventanilla lleva su nombre.
3. **Aviso cuando alguien pide acompañamiento** — en su área y su ciudad. El
   primero que acepta se queda el caso.
4. **Contactos que autorizaron ser contactados** — solo de quien marcó esa
   casilla. «Sin listas compradas y sin sorpresas con la SIC».

Los planes se pintan desde la base de datos, con su prueba gratis.

## El registro

Formulario con datos, áreas, ciudad y plan de interés. Pide declarar que la
tarjeta profesional es suya y está vigente, y aceptar términos y tratamiento de
datos.

Hoy el alta la confirma el equipo: no hay cobro automático. Es deliberado
mientras no haya pasarela, y la landing lo dice: «Te escribimos en menos de 24
horas hábiles. La prueba gratis empieza cuando tu perfil se publica».

## Qué recibe según el plan

| | Directorio $50.000 | Crecimiento $150.000 | Destacado $350.000 |
|---|---|---|---|
| Perfil público | ✓ | ✓ | ✓ |
| Avisos de casos | ✓ | ✓ | ✓ |
| Nombre en instructivos | — | ✓ | ✓ |
| Acceso a contactos | — | ✓ | ✓ |
| Destacado en el directorio | — | — | ✓ |
| Presencia en artículos | — | — | ✓ |

Cada interruptor cambia lo que el sistema hace. `includes_pdf_placement`
decide si `LawyerPlacementService` lo incluye en los instructivos;
`includes_lead_data` decide si el recurso de contactos existe para él.

## La retención: el portal

Un abogado renueva si ve que llegan clientes. Por eso el portal (`/portal`)
abre con **sus** números: visitas a su perfil, clics para contactarlo, casos
que tomó y el porcentaje de invitaciones que convirtió.

Si esas cifras no crecen, la suscripción no vale lo que cuesta, y es mejor que
lo vea él antes de descubrirlo cuando se va.

Detalle en [Portal del abogado](../04-panel/02-portal-del-abogado.md).

## Los puentes que lo alimentan

| Puente | Cómo funciona |
|---|---|
| Instructivo | `LawyerPlacementService` elige abogados con `includes_pdf_placement`, especialistas del área primero, rotando por `last_placement_at` |
| Artículos | Las guías de una especialidad ofrecen el directorio de esa área |
| Landing de área | `/tramites/{area}` muestra abogados de esa área |
| Casos complejos | Los avisos por correo |
| Contactos | Las personas que autorizaron ser contactadas |

## Dónde termina

| Resultado | Qué queda |
|---|---|
| Se suscribe | MRR, perfil público, entra al reparto de casos |
| Prueba y no renueva | Hay que mirar sus métricas: ¿no llegaron clientes, o no los atendió? |
| No se registra | Queda como lead con origen `lawyer_signup` |
