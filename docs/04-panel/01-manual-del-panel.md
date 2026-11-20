# Manual del panel de administración

`/admin` · Filament 5 · entran los usuarios con `is_staff = true`.

El menú está agrupado por cómo se trabaja, no por cómo están las tablas.

---

## Tablero

Dos bloques de métricas y tres gráficas.

**Resumen diario** — lo que se mira cada mañana:
- Pedidos por atender, con cuántos están vencidos y una mini-gráfica de 7 días.
- Ingresos del mes, comparados con el mes anterior.
- Casos sin abogado asignado.
- Garantías abiertas.

**El embudo del modelo** — si el negocio funciona:
- Documentos gratis del mes y **qué porcentaje acabó pagando**.
- Ingresos por adicionales: mide si el checkout persuade o solo cobra.
- MRR de suscripciones de abogados y cuántos están en prueba.
- Contactos compartibles (solo los que lo autorizaron).

Gráficas: tendencia de pedidos, trámites más pedidos, tiempos de respuesta de
los abogados.

---

## Operación diaria

### Pedidos
Listado con referencia, cliente, trámite, nivel, cuántos adicionales, estado,
valor y **el semáforo del plazo** (rojo si venció, ámbar si quedan menos de 4
horas).

Filtros: estado, nivel, trámite, solo vencidos, sin abogado asignado,
**autorizó que un abogado lo contacte**, y **pagado sin documento generado**
—que es el que hay que revisar si algo falló.

Acciones por fila:
- **En redacción** / **Entregado** — mueven el estado y paran el reloj.
- **Regenerar PDF** — rehace documento e instructivo con los datos actuales y
  los entrega. Sirve para dos cosas: corregir un dato del cliente (el PDF
  viejo deja de servir) y **entregar antes de tiempo** un pedido que está en
  la cola.
- **Ver documento** — abre el PDF generado.
- **Abrir** — la ficha completa.

En la ficha: estado y plazos, archivos generados, abogado patrocinado en el
instructivo, el desglose de precios, **los adicionales contratados con su
precio congelado** (solo lectura: es el histórico de lo que se cobró), los
datos del cliente, su caso, notas internas y el origen UTM de la venta.

Las dos casillas de consentimiento se ven pero **no se pueden cambiar desde
aquí**: las marcó el cliente.

### Casos complejos
Los casos y su reparto. En la ficha, la pestaña «Invitaciones enviadas»
muestra la carrera tal como ocurrió: a quién se invitó, quién aceptó, quién
rechazó y con qué motivo, y cuánto tardó cada uno.

### Garantías
Las reclamaciones del Certificado de Garantía, con su motivo y su estado.

### Mensajes y solicitudes
Lo que la gente escribe desde `/contacto` y las solicitudes de eliminación de
datos de `/eliminar-mis-datos`.

Cada fila lleva su radicado (`MSG-…` o `DAT-…`), quién escribió y **la fecha
límite para responder**, que se pinta en rojo al vencerse. En una solicitud de
datos ese plazo es legal —15 días hábiles, Ley 1581 de 2012—, no una promesa
comercial.

Acciones: responder (abre el correo), marcar resuelta (deja constancia de
quién la atendió y cuándo) y abrir para dejar notas internas. En una
eliminación conviene anotar ahí qué se borró y qué se conservó por obligación
contable o tributaria.

El badge del menú cuenta las sin resolver, y se pone rojo si alguna venció.

---

## Catálogo y precios

### Trámites
Listado con precio base, cuántos adicionales tiene, pedidos, visitas, si es
gratis, si admite firma, si está publicado, y dos indicadores de salud:
**SEO** (ámbar si le falta contenido largo o título) y **Plantilla** (ámbar si
no tiene cuerpo propio y usa el respaldo genérico).

La ficha tiene seis pestañas:

| Pestaña | Qué se configura |
|---|---|
| Básico | Nombre, slug, área, fase, promesa, resumen, fundamento legal |
| **Precio y niveles** | Precio base, tachado, descuento, horas de entrega, si admite autoservicio, si aparece entre los gratuitos, gancho |
| **Plantilla del documento** | Encabezado, cuerpo con marcadores, campos extra del formulario, pasos de radicación, ante quién se radica, plazo legal |
| Qué recibe el cliente | Qué recibe, qué necesitamos, casos de uso, **qué NO incluye** |
| Contenido SEO | El contenido largo de la landing |
| Preguntas frecuentes | Las FAQ, que se publican con marcado `FAQPage` |
| Metadatos | Título SEO, meta descripción, palabra clave |

Y una pestaña de relación: **Adicionales de este trámite**, donde se añade cada
adicional con su precio para este trámite y las marcas de disponible,
recomendado y premarcado.

Los marcadores de la plantilla son: `{{ cliente.nombre }}`,
`{{ cliente.documento }}`, `{{ cliente.correo }}`, `{{ cliente.telefono }}`,
`{{ cliente.ciudad }}`, `{{ caso.resumen }}`, `{{ fecha.larga }}`,
`{{ tramite.nombre }}`, `{{ pedido.referencia }}`.

### Adicionales
El catálogo global de «toppings»: nombre, clave interna, emoji, familia,
argumento, descripción, viñetas, precio por defecto, descuento, etiqueta,
prueba social y si está activo.

La **familia** no es cosmética: define el orden en que se le ofrece al cliente
(primero lo barato y concreto, al final el abogado).

Pestaña de relación: **Trámites donde se ofrece**.

Apagar un adicional lo saca del checkout, pero **los pedidos viejos conservan
lo que pagaron**.

### Matriz de precios
El cruce completo: trámites en filas, adicionales en columnas.

En cada celda: una casilla para ofrecerlo, el precio (vacío = por defecto, 0 =
incluido), el precio efectivo calculado, y dos marcas — **R** (recomendado) y
**P** (premarcado).

Existe porque editar adicional por adicional son veinte pantallas y así nadie
revisa si los precios guardan relación entre sí.

---

## Red de abogados

### Abogados
Ficha completa: identidad, credenciales internas (**la tarjeta profesional
nunca se publica**), áreas y reparto de casos, **perfil público del
directorio**, notificaciones.

El perfil público incluye firma, logo, biografía larga, servicios, idiomas, si
atiende virtual, si da primera llamada gratis, web, LinkedIn, SEO del perfil y
el usuario del panel asociado.

El listado muestra su plan, si está en el directorio, visitas al perfil y clics
de contacto.

### Planes para abogados
Precios mensual y anual, meses de prueba, y los interruptores que **cambian lo
que el sistema hace**: perfil público, aparecer en los instructivos, avisos de
casos, acceso a contactos, destacado, presencia en artículos, prioridad.

### Suscripciones
Quién tiene qué plan, en qué estado, qué paga realmente (puede diferir del
precio de lista: aquí se registran los acuerdos puntuales), y las fechas de
prueba, renovación y destacado. El badge del menú avisa de los pagos
pendientes.

---

## Contenido y SEO

Artículos (con su autor abogado obligatorio), categorías, testimonios y
preguntas frecuentes por grupo.

---

## Marketing y leads

### Leads y consentimientos
Cada contacto con su origen, área, ciudad y **las dos casillas de
consentimiento por separado**: correos y contacto de abogados.

También: cuántos abogados lo han abierto, cuándo autorizó y bajo qué versión de
la política, y si revocó.

Acción **Revocar consentimiento**: lo saca de los envíos y de lo que ven los
abogados. Es un derecho del titular bajo la Ley 1581 de 2012 y tiene que poder
ejercerse en un clic cuando lo pide.

### Calculadoras
Nombre, slug, **clave de la fórmula** (una clave sin fórmula no calcula nada),
el trámite que se ofrece tras el resultado, si pide el correo, y los
**parámetros del año** — que hay que actualizar cada enero, porque una
calculadora con el salario mínimo del año pasado da resultados falsos.

---

## Próximos módulos

Once páginas que explican qué falta por construir, por qué, con qué prioridad y
qué hay ya en pie. No son maquetas: son documentación viva del roadmap dentro
del propio panel.

---

## Configuración

### Ajustes del sitio
Cuatro grupos:

| Grupo | Qué contiene |
|---|---|
| **Marca y logo** | Nombre, lema, logo, logo oscuro, favicon, colores |
| **Ganchos y textos** | Titular y subtítulo de la home, el gancho de revisión, prueba social |
| **Promociones** | Precios tachados, franja de promoción, ofrecer adicionales |
| **Operación** | Horas de entrega, documentos gratis, calculadoras, directorio |

El formulario se construye **desde la tabla**: añadir un ajuste nuevo es
insertar una fila con su grupo y su tipo, sin tocar código.

### Usuarios
Quién entra al panel.
