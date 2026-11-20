# Estrategia SEO

El objetivo es aparecer primero cuando alguien en Colombia busca cómo resolver
un trámite legal. No «abogados en Bogotá»: eso lo pelean cien bufetes con
presupuesto. **«Derecho de petición formato»**, que es lo que de verdad se
busca y lo que nadie responde bien.

## Por qué esto es contenido YMYL

Google clasifica el contenido legal como **YMYL** (*Your Money or Your Life*):
páginas que pueden afectar la salud, la seguridad financiera o los derechos de
alguien. Las evalúa con más dureza y premia la señal de **E-E-A-T**
(experiencia, pericia, autoridad, confianza).

Consecuencias prácticas, y todas están implementadas:

- **Los artículos necesitan autor acreditado.** `posts.author_lawyer_id` no es
  decorativo: es la señal de posicionamiento más importante en este nicho. Hay
  un test que falla si un artículo sale sin autor en el JSON-LD.
- **Las afirmaciones legales citan la norma.** Cada trámite tiene
  `legal_basis`.
- **La página «Nosotros» tiene nombres y trayectorias**, no una foto de stock.
- **Las promesas son verificables.** El Certificado de Garantía dice qué cubre
  y qué no. Una promesa que no se puede cumplir acaba en reseñas negativas, y
  las reseñas son señal.

Lo que **no** se hace, y conviene entender por qué: no se publican números de
tarjeta profesional. Sería una señal de autoridad, sí, pero comprometería al
abogado nombrado con una revisión individual que no hace. La autoridad se
construye con el nombre, la especialidad y la biografía.

## Los tres tipos de página que posicionan

### 1. Ficha de trámite (`/documentos/{slug}`)

El activo principal. Cada una ataca una búsqueda transaccional concreta
(«eliminar reporte datacrédito», «formato promesa compraventa vehículo») y
contiene:

- `body`: contenido largo (mínimo recomendado 600 palabras).
- Qué recibes / Qué necesitamos / **Qué no incluye**.
- Casos de uso: capturan búsquedas de cola larga en lenguaje natural.
- FAQ con marcado `FAQPage`.
- Precio visible, que alimenta el `Offer` del JSON-LD.

### 2. Landing por área (`/tramites/{area}`)

Cola media: «trámites de derecho laboral», «documentos de familia». Agrupa los
trámites del área y ofrece el directorio de abogados de esa área. Es el puente
natural entre el que busca información y el que necesita a alguien.

### 3. Blog (`/blog/{slug}`)

Cola larga informacional: «cómo hacer una tutela por salud», «cuánto tarda
datacrédito en borrar un reporte». Aquí entra quien todavía no sabe que existe
una solución pagada.

Cada artículo:
- Lo firma o revisa un abogado identificado (`author_lawyer_id`).
- Enlaza al trámite que resuelve lo que explica.
- Ofrece el directorio de su especialidad.

**Este es el único sitio donde se puede hablar de plantillas gratuitas o de
IA.** En el embudo no, porque le planta la idea a quien no la traía; aquí sí,
porque quien llega ya la está buscando.

## Las herramientas gratuitas como estrategia SEO

`/gratis` y las calculadoras no son solo captación: son páginas que posicionan
por sí mismas y atraen enlaces.

«Calculadora de liquidación laboral» tiene volumen de búsqueda alto, intención
clarísima y competencia mediocre. Quien la usa tiene un problema laboral ahora
mismo y un número en la cabeza: es el mejor lead del embudo y, además, la
página gana autoridad que se reparte al resto del sitio.

Por eso los parámetros del año (salario mínimo, auxilio de transporte) se
editan desde el panel: **una calculadora con el salario mínimo del año pasado
da resultados falsos**, y eso además de ser un problema de producto es un
problema de posicionamiento.

## Arquitectura de enlaces internos

```
Home ──► /gratis ──► ficha del trámite ──► checkout
  │         │
  │         └──► calculadora ──► trámite relacionado
  │
  ├──► /documentos ──► /tramites/{area} ──► ficha
  │                         │
  │                         └──► /abogados?area=...
  │
  └──► /blog ──► artículo ──► ficha del trámite
                    │
                    └──► directorio de la especialidad
```

Cada página pasa autoridad a la siguiente y todas acaban en el checkout o en el
directorio.

## Lo que se bloquea

`robots.txt` bloquea `/contratar`, `/pago`, `/pedido` y `/admin`. Las páginas
del embudo llevan además `noindex, nofollow`: no aportan nada a la búsqueda y
diluirían el presupuesto de rastreo.

## Cómo se mide

Pendiente de conectar: Search Console y analítica. En el panel ya se registran
`views_count` por trámite y `profile_views` / `contact_clicks` por abogado, que
son la parte interna de la medición.
