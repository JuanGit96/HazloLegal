# Convenciones de código

Esto es el resumen; la fuente es `CLAUDE.md` en la raíz, que es lo que lee un
asistente antes de tocar el código.

## Generales

- Todo lo que ve el usuario, en **español de Colombia**. Los identificadores de
  código, en inglés.
- Los comentarios explican **por qué**, no qué. Si el código ya lo dice, sobra.
- Montos en pesos enteros: `90000` = $90.000, nunca centavos.
- Fechas con `translatedFormat()`. Locale `es`, zona `America/Bogota`.

## Dominios

- `app/Domain/<Contexto>/{Models,Services,Data,Enums,Mail}`.
- Los controladores llaman a **un** Service.
- Los Services no conocen `Request` ni `Response`.
- Los DTOs son `final readonly` con `fromArray()` estático.
- Los enums llevan `getLabel()`, y `getColor()` / `getIcon()` cuando Filament
  los pinta. Implementan los contratos de `Filament\Support\Contracts`.
- Nada de repositorios genéricos. Los scopes van en el modelo.

## Filament 5 — la API cambió respecto de v3 y v4

Lo que se usa aquí:

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
- `App\Models\User` implementa `FilamentUser`. Sin `canAccessPanel()`, en
  producción el panel devuelve 403 a todo el mundo.

## Sitio público

- CSS a mano en `public/css/site.css`, con tokens en `:root`. Sin Tailwind y
  sin build step.
- **No** usar `/assets/` en las rutas de los estáticos: algunos bloqueadores de
  contenido filtran esa ruta y dejan el sitio sin estilos. Van en `/css/`,
  `/js/`, `/img/`.
- Los estáticos propios se enlazan con `@asset(...)` para que lleven versión.
- Light-only a propósito (`color-scheme: light only`). La paleta es parte de la
  señal de confianza.
- Componentes Blade en `resources/views/components/`.
- Las FAQ usan `<details>` nativo: accesible, sin JS y legible para Google.
- Todo JS es progresivo. El sitio funciona sin él.
- **Si añades marcado con una clase nueva, escribe su regla en `site.css`.**
  Sin build step nadie te avisa: la página simplemente sale rota.

## Correos

- Mailables en `Domain/<Contexto>/Mail/`, vistas en `resources/views/emails/`.
- Layout de tablas con estilos en línea. Nada de flexbox, grid ni variables
  CSS: Outlook y Gmail los descartan.
- Los envíos los dispara el Service, nunca el controlador.

## PDF

- dompdf no entiende flexbox ni grid: lo que parezca columnas se hace con
  tablas.
- Tampoco hereda bien los estilos: las reglas se escriben explícitas.

## Tests

`ddev artisan test` antes de dar nada por terminado. Si tocas el reparto de
casos, los precios o el SEO, **añade el test antes**.
