# Convenciones de HazloLegal

Lee esto antes de tocar el código. El `README.md` explica qué es el proyecto;
esto explica cómo se escribe.

## Arquitectura

Basada en servicios, con dominios acotados en `app/Domain/<Contexto>/`.
Contextos: `Catalog`, `Orders`, `LegalCases`, `Lawyers`, `Content`, `Seo`,
`Shared`. Cada uno con `Models/ Services/ Data/ Enums/ Mail/`.

**La regla:** los controladores y Filament orquestan; los Services deciden.

- Un controlador recibe la petición, llama a **un** Service y devuelve la vista.
  Si un controlador tiene lógica de negocio, está en el sitio equivocado.
- Los Services no conocen `Request` ni `Response`. Reciben DTOs y devuelven
  modelos o DTOs.
- Los DTOs (`Data/`) son `final readonly` con un `fromArray()` estático.
- Los enums llevan `getLabel()`, y `getColor()` / `getIcon()` cuando Filament
  los va a pintar. Implementan los contratos de `Filament\Support\Contracts`.
- Nada de repositorios genéricos sobre Eloquent. Los scopes van en el modelo.

## Base de datos

- Migraciones con clase anónima.
- Modelos con `protected $guarded = ['id']` y `protected function casts()`.
- Toda columna con semántica de negocio se castea a su enum.
- Las referencias visibles al cliente (`HL-2026-00042`, `CC-…`, `GAR-…`) las
  genera `Shared\Services\ReferenceGenerator`.

## Filament 5

La API cambió respecto de v3 y v4. Lo que se usa aquí:

- Recursos en `app/Filament/Resources/<Plural>/` con `Schemas/XForm.php` y
  `Tables/XsTable.php` separados.
- `Filament\Schemas\Schema` para formularios; `->components([...])`.
- Layout desde `Filament\Schemas\Components\` (`Section`, `Grid`, `Tabs`).
- Campos desde `Filament\Forms\Components\`.
- **Todas** las acciones desde `Filament\Actions\`, no desde `Tables\Actions`.
- En tablas: `->recordActions([...])` y `->toolbarActions([...])`.
- Las pestañas de un listado usan `Filament\Schemas\Components\Tabs\Tab`,
  **no** `Resources\Pages\ListRecords\Tab` (no existe).
- Iconos: el enum `Filament\Support\Icons\Heroicon`.
- Grupos del menú: las constantes de `App\Filament\Support\NavGroup`.
- En los closures, los parámetros se resuelven **por nombre** (`$state`,
  `$record`, `$get`, `$set`) o por tipo si es un Model. `fn (int $s) => …`
  revienta en tiempo de ejecución, no al compilar.
- `App\Models\User` implementa `FilamentUser`. Sin eso, en producción el panel
  devuelve 403 a todo el mundo.

## Sitio público

- CSS escrito a mano en `public/css/site.css`, con tokens en `:root`. Sin Tailwind
  y sin build step: se despliega copiando archivos.
- **No** usar `/assets/` en las rutas de los estáticos: algunos bloqueadores de
  contenido filtran esa ruta y dejan el sitio sin estilos.
- Light-only a propósito (`color-scheme: light only`). La paleta es parte de la
  señal de confianza.
- Componentes Blade en `resources/views/components/`.
- Las FAQ usan `<details>` nativo: accesible, sin JS y legible para Google.
- Todo JS es progresivo. El sitio funciona sin él.

## SEO

Cada página pública pasa una `SeoMeta` a la vista, construida por `SeoManager`.
Nunca escribir `<title>` ni JSON-LD a mano en una plantilla.

- Los JSON-LD los arma `SchemaGenerator`.
- Los artículos necesitan `author_lawyer_id`: el contenido legal es YMYL y la
  autoría acreditada es la señal de posicionamiento más importante.
- Las páginas del embudo van con `noindex`.
- Si se añade un tipo de página pública, añadirlo también a `SitemapBuilder`.

## Correos

- Mailables en `Domain/<Contexto>/Mail/`, vistas en `resources/views/emails/`.
- El layout es de tablas con estilos en línea. No usar flexbox, grid ni
  variables CSS: Outlook y Gmail los descartan.
- Componentes de correo en `resources/views/components/email/`.
- Los envíos los dispara el Service correspondiente, nunca el controlador.

## Idioma y formato

- Todo lo que ve el usuario, en español de Colombia. Los identificadores de
  código, en inglés.
- Los comentarios explican **por qué**, no qué. Si el código ya lo dice, sobra.
- Montos en pesos enteros (90000 = $90.000), nunca en centavos. En Filament,
  `->money('COP')` sin divisor.
- Fechas con `translatedFormat()`. El locale es `es` y la zona `America/Bogota`.

## Tests

`ddev artisan test` antes de dar nada por terminado. Hay cobertura de:

- El embudo de compra completo.
- La carrera por un caso complejo, incluido el segundo abogado que llega tarde.
- Que los datos del cliente **no** se filtren a quien no ganó el caso.
- Los datos estructurados de cada tipo de página.
- Que todos los listados y fichas del panel cargan.

Si tocas el reparto de casos, los precios o el SEO, añade el test antes.

## Honestidad del producto

Esto no es un detalle de estilo, es parte del producto y de la exposición legal
de la empresa:

- **Nunca prometer que un abogado revisa cada documento.** En los trámites
  automatizados no ocurre. Lo que se afirma es que el *formato* lo escribieron
  abogados y que se mantiene actualizado.
- **Nunca publicar números de tarjeta profesional.** El campo existe para
  control interno; sacarlo al sitio comprometería al abogado nombrado con una
  revisión individual que no hace.
- Nunca prometer un resultado judicial. Se garantiza la calidad del documento,
  no la decisión de un juez.
- Decir qué **no** incluye cada trámite antes de cobrar.
- No inventar testimonios, cifras ni abogados. Es publicidad engañosa y la SIC
  la sanciona.
- **No nombrar alternativas gratuitas dentro del embudo.** Mencionar la IA o
  las plantillas gratis en la home le planta la idea a quien no la traía. Ese
  contenido va en el blog, donde llega quien ya la está buscando.
- Los datos de demostración van marcados como tales en el seeder.

`PublicSeoTest` tiene tests que fallan si alguna de estas reglas se rompe.
