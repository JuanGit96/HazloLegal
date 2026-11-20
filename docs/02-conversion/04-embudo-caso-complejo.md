# Embudo 3 · El caso complejo

Para lo que no cabe en un formato: una sucesión con herederos en disputa, una
demanda laboral, una tutela cuyos hechos hay que narrar. Aquí no se vende un
documento: se reparte un caso entre abogados reales.

## Dónde empieza

| Entrada | Dónde |
|---|---|
| «Tengo un caso complejo» | Home y CTA de varias páginas |
| «¿Tu caso es distinto o más complicado?» | Ficha del trámite |
| Catálogo sin resultados | «Puede ser que lo manejemos como caso particular» |
| Tercera tarjeta de precios | «Que lo haga un abogado» |

## Paso a paso

### 1. El formulario (`/caso-complejo`)
Área de práctica, título, descripción, urgencia, presupuesto orientativo, si
tiene documentos, y los datos de contacto. Más la autorización de tratamiento
de datos, obligatoria.

### 2. La recepción
`CaseIntakeService` crea el caso con referencia `CC-2026-00001` y estado
`new`. El cliente va a `/caso-complejo/recibido/{referencia}`.

### 3. El reparto
`CaseDispatchService` busca abogados que:

- estén activos y acepten casos,
- ejerzan esa área de práctica,
- no hayan llegado a su cupo de casos simultáneos.

A cada uno le manda un correo con el caso **sin los datos de contacto del
cliente** y dos enlaces: aceptar y rechazar. Cada enlace lleva un token propio,
que es la credencial: no hay login.

### 4. Las postulaciones
Aceptar **no asigna el caso**: postula al abogado.

Cuantos más se postulen, mejor elige el cliente, así que no hay carrera: todos
los invitados pueden postularse mientras el caso siga abierto.

- **El que se postula** ve una pantalla que dice «te postulaste». **No recibe
  los datos del cliente**: eso pasa solo si lo eligen.
- **El que rechaza** puede dejar un motivo. Si marcó el rechazo desde el enlace
  y después escribe el motivo, también se guarda.

Al cliente se le avisa **una sola vez**, con la primera postulación. Un correo
por cada abogado sería ruido, y la página siempre muestra la lista al día.

### 5. El cliente elige
`/caso-complejo/{referencia}/elegir?t={llave}` muestra a los postulados con su
experiencia, su ciudad, si atiende virtual y si da primera llamada gratis.

La llave del enlace es la credencial: `CC-2026-00001` es correlativa, así que
sin ella cualquiera podría leer el caso de otro cambiando un número.

Al elegir, `CaseAssignmentService::chooseLawyer()` asigna dentro de una
transacción con `lockForUpdate()` —un doble clic no puede dejar el caso con dos
abogados— y entonces sí:

- **El elegido** recibe los datos de contacto del cliente.
- **Los demás** quedan descartados y reciben un aviso. **Nunca ven los datos.**

### 6. El cliente
Recibe el nombre del abogado que eligió y cómo contactarlo.

## Dónde termina

| Resultado | Estado |
|---|---|
| Un abogado acepta | `assigned`, con `assigned_lawyer_id` y `assigned_at` |
| Nadie acepta antes de `expires_at` | Sigue abierto; el panel lo muestra en «Casos sin abogado» |
| Todos rechazan | Igual, con los motivos registrados |

En el panel, el tablero tiene una tarjeta «Casos sin abogado» precisamente para
que esto no se quede quieto.

## Por qué cambió

Antes ganaba el primero que aceptaba. Premiaba la velocidad de respuesta del
abogado, que no es lo que al cliente le conviene: quien tiene un problema de
familia prefiere escoger entre tres perfiles a que le toque el que estaba
mirando el correo.

Con el cliente eligiendo:

- **El cliente decide**, que es lo que esperaría de cualquier servicio que
  paga.
- **El abogado sigue teniendo prisa**, porque postularse tarde es competir con
  perfiles que el cliente ya leyó.
- **Los datos personales salen una sola vez y hacia una sola persona**, que es
  la regla que no se negocia.

| Estado | Qué significa |
|---|---|
| `dispatched` | Enviado a los abogados, nadie se ha postulado |
| `shortlisted` | Hay postulados, el cliente tiene que elegir |
| `assigned` | El cliente eligió |
