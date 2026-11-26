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

El resumen está construido sobre una idea incómoda: **la mayoría de los meses
un abogado no gana un caso aquí**. Si el panel solo contara casos ganados,
parecería que no pasa nada y la suscripción no se renovaría. Pasa bastante, y
hay que enseñarlo.

El saludo ya dice lo que hay que hacer hoy: si tiene casos abiertos, los
nombra; si no, nombra cuánta gente abrió su ficha este mes.

### Casos esperando su postulación

Lo primero de la página, y sin carga diferida: es lo único sobre lo que puede
actuar. Lista las ofertas pendientes de casos **todavía sin asignar y sin
vencer**, con cuántos abogados se han postulado ya —saber que hay tres cambia
la decisión— y un botón para postularse.

El cliente no aparece. Nombre, correo y teléfono solo existen para quien sea
elegido.

### Sus últimos 30 días

Cuatro cifras comparadas con los 30 anteriores, con su minigráfica. Se muestra
el periodo y no el acumulado de siempre: un total que solo sube parece bueno
aunque lleve meses plano, y la pregunta al renovar es si esto *sigue*
funcionando.

Sale de `daily_metrics` (ver [crecimiento orgánico](../07-crecimiento/01-crecimiento-organico.md#qué-se-mide-y-dónde)).

### Tu visibilidad, día a día

Tres líneas sobre el mismo embudo: aparecer en un listado → que abran la ficha
→ que pulsen para escribir. Juntas dicen **dónde** se pierde la gente: muchas
impresiones y pocas aperturas es un problema de titular; aperturas sin clics
es un problema de ficha. Eso es accionable; un número suelto no.

### Lo que te ha dado tu plan

El acumulado desde que se suscribió, métrica por métrica. Y cuando una está en
cero, **qué hacer para moverla** («añade tu WhatsApp», «amplía tus áreas»). Un
cero sin explicación solo frustra.

Son hechos contados, nunca un «valor equivalente en publicidad»: una cifra
inventada de ese tipo es justo lo que la SIC sanciona.

## Mis casos

Solo los que el cliente **eligió**. El filtro está en
`MyCaseResource::getEloquentQuery()`, en el origen.

En la ficha, dos secciones: el caso (título, área, urgencia, presupuesto, lo
que contó el cliente, el plazo que mencionó) y **el contacto del cliente**, con
esta línea encima:

> Estos datos son tuyos porque el cliente te eligió. Ningún otro abogado de
> la red los ve.

Adivinar la URL del caso de otro devuelve 404: `resolveRecord()` vuelve a pasar
por la consulta acotada. Doble cierre, porque esto es lo que no puede fallar.

## Acompañamientos

Los pedidos en los que el cliente **pagó** para que un abogado redacte el
documento y se le asignaron a él. Es trabajo pagado, no una oportunidad: antes
no estaba en ninguna parte y el abogado se enteraba por correo, que es la
forma más fácil de que algo se pierda.

Muestra la referencia, el trámite, el cliente, el estado y el plazo
comprometido —en rojo si ya venció, porque la garantía la cubre la empresa— y
un botón para escribirle por WhatsApp.

La consulta se acota por `assigned_lawyer_id` en
`MyAccompanimentResource::getEloquentQuery()`.

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

Y si hay planes superiores, se muestran con **el precio que la pasarela le va
a cobrar**, no con el de lista: si la pantalla dice una cifra y dos clics
después aparece otra, lo que se pierde es la confianza, aunque cambie a favor
del abogado. Cuando hay promoción se enseñan las dos cifras y la fecha en que
empieza a regir la segunda.

El botón de renovar solo sale cuando de verdad hace falta su mano: sin tarjeta
comprobada, o con un cobro que falló. Con tarjeta al día el cobro sale solo, y
un botón de «renovar» ahí haría pensar que hay algo pendiente que no existe.

Al final, en pequeño y sin botón, el enlace a la salida.

## Antes de irte

La pantalla de cancelación (`/portal/cancelar-suscripcion`) no está en el menú
a propósito, y es **el único sitio del portal donde se ofrece un plan más
barato**. Un botón de «bajarme de plan» en la pantalla de la cuenta le planta
la idea a quien no la traía; aquí ya decidió irse, así que la opción barata
recupera plata en vez de regalarla.

Lo que ve, en este orden:

1. **Sus cifras de los últimos 30 días.** No argumentos: sus números. Si son
   malos se va con razón y es mejor saberlo; si son buenos, puede que nadie se
   lo hubiera dicho.
2. **Los planes más baratos**, con lo que deja de tener en cada uno. Dejar que
   alguien se baje sin saber qué pierde es ganarse la queja del mes siguiente.
3. **Lo que se queda aunque se vaya**: sus guías siguen publicadas y con su
   firma. Lo que desaparece es la ficha del directorio y el enlace a ella
   desde esas guías.
4. **Cancelar de todas formas**, con el motivo. Saber por qué se va es lo
   único que una baja deja de útil.

Nada de lo que se decide ahí ocurre ese día. Se guarda (`scheduled_plan_id` o
`ends_at`) y lo ejecuta el comando diario el día de la renovación: a quien se
va no se le cobra, y a quien se bajó se le cobra el plan nuevo. Hasta entonces
conserva todo, y puede deshacerlo desde la misma pantalla.

El cambio se confirma por correo el mismo día que se pide, con la fecha en que
empieza a regir. Si la primera noticia del cambio es el cargo, lo siguiente es
una llamada al banco.

## Lo que falta

Está en el panel de administración, en «Próximos módulos → Portal del abogado
(fase 2)»: bandeja de casos invitados con aceptar y rechazar desde la
interfaz, subir el documento terminado, registro de horas, liquidación de
honorarios, pausar invitaciones durante vacaciones, marcar un contacto como
contactado o descartado, y pagar la suscripción desde el propio portal.
