# Portal del abogado

`/portal` · un panel de Filament **aparte** del de administración.

Es un panel separado y no una sección escondida a propósito: lo que separa a
los dos públicos no es un menú, es el límite de lo que cada consulta puede
devolver.

## Quién entra

Un usuario con `is_active = true` y una ficha de abogado enlazada
(`lawyers.user_id`). No necesita `is_staff`; de hecho, un usuario del equipo
**no** entra aquí.

`App\Domain\Lawyers\Services\CurrentLawyer` resuelve quién es el abogado que
entró. Todo el portal se acota con eso, para que ningún recurso tenga que
acordarse de filtrar.

## Resumen

Cuatro cifras, y son **suyas**, no del negocio:

- **Visitas a su perfil**, con aviso si el perfil no está publicado.
- **Clics para contactarlo** desde el directorio.
- **Casos que tomó**, con el porcentaje de invitaciones que convirtió. La
  carrera por un caso la gana quien responde primero, y eso se puede mejorar.
- **Contactos abiertos este mes**, con el recordatorio de que cada apertura
  queda registrada.

Si estas cifras no crecen, la suscripción no vale lo que cuesta. Es mejor que
lo vea él antes de descubrirlo cuando se va.

## Mis casos

Solo los que **ganó en la carrera**. El filtro está en
`MyCaseResource::getEloquentQuery()`, en el origen.

En la ficha, dos secciones: el caso (título, área, urgencia, presupuesto, lo
que contó el cliente, el plazo que mencionó) y **el contacto del cliente**, con
esta línea encima:

> Estos datos son tuyos porque aceptaste el caso primero. Ningún otro abogado
> de la red los ve.

Adivinar la URL del caso de otro devuelve 404: `resolveRecord()` vuelve a pasar
por la consulta acotada. Doble cierre, porque esto es lo que no puede fallar.

## Contactos

Solo aparece si su plan incluye `includes_lead_data`. Sin eso, ni el menú
existe y la ruta devuelve 403.

Tres filtros, los tres obligatorios:

1. Solo quien marcó **«quiero que un abogado me contacte»** y no ha revocado.
2. Solo de **sus áreas de práctica**. Entregar más de lo necesario no se
   justifica ante el titular ni ante la SIC.
3. Solo si el plan lo incluye.

**El listado no muestra correo ni teléfono.** Muestra el nombre parcial, el
área, la ciudad, un extracto de lo que la persona necesita, cuándo llegó y
cuántos abogados ya lo abrieron —porque un contacto muy visto ya recibió varias
llamadas.

Para ver los datos hay que pulsar **«Ver datos de contacto»**, que abre un
modal y **registra la apertura** en `lead_accesses` con fecha e IP. El modal lo
dice antes de abrirse:

> Esta persona autorizó que un abogado de la red la contacte. Quedará
> registrado que fuiste tú quien abrió sus datos.

Si mañana un titular ejerce su derecho a saber quién accedió a sus datos, hay
una respuesta.

## Mi perfil

El abogado edita lo suyo, pero **no todo lo suyo**:

| Puede editar | No puede |
|---|---|
| Especialidad, firma, ciudad, biografías | **Tarjeta profesional** |
| Foto, logo | Calificación interna |
| Áreas, especialidades, servicios, idiomas | Prioridad en el directorio |
| Años de ejercicio, atención virtual, primera llamada gratis | Si está publicado |
| Teléfono, WhatsApp, web, LinkedIn | Su plan |
| Título y descripción SEO de su perfil | |

La tarjeta profesional porque no se publica nunca; las otras porque son
criterios del negocio, no del abogado.

Si su perfil no está publicado, la página lo dice arriba y explica que se
publica cuando la suscripción lo incluye.

## Mi suscripción

Qué plan tiene, en qué estado, qué paga, cuándo renueva o cuándo termina la
prueba.

Debajo, **qué incluye y qué no**, en términos de lo que el sistema hace, con lo
no incluido tachado. Un abogado que no entiende qué paga no renueva.

Y si hay planes superiores, se muestran con su precio.

## Lo que falta

Está en el panel de administración, en «Próximos módulos → Portal del abogado
(fase 2)»: bandeja de casos invitados con aceptar y rechazar desde la
interfaz, subir el documento terminado, registro de horas, liquidación de
honorarios, pausar invitaciones durante vacaciones, marcar un contacto como
contactado o descartado, y pagar la suscripción desde el propio portal.
