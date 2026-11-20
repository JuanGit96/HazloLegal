# Arquitectura: visión general

## Stack

| Pieza | Versión | Por qué |
|---|---|---|
| PHP | 8.5 | Tipos en propiedades, enums, `readonly` |
| Laravel | 13.x | Estándar del ecosistema |
| MariaDB | 11.8 | Relacional; los pedidos y los casos tienen relaciones que importan |
| Filament | 5.10 | Panel completo sin escribirlo a mano. **Su API cambió fuerte respecto de v3 y v4** |
| dompdf | — | Los PDF del cliente (documento e instructivo) |
| ddev | — | Entorno local reproducible en Docker |

**No hay build step en el front.** El CSS se escribe a mano en
`public/css/site.css` con tokens en `:root`, y el JS en `public/js/site.js`.
El despliegue es copiar archivos. Esto es una decisión, no una carencia: un
sitio que se despliega sin `npm` no se rompe por una dependencia transitiva un
viernes.

Los estáticos propios se enlazan con `@asset('css/site.css')`, una directiva
Blade que les añade la fecha de modificación como parámetro (`?v=...`). Sin
eso, un cambio de CSS no le llega al visitante hasta que haga una recarga
forzada. Está en `app/Providers/AppServiceProvider.php`.

## La regla que ordena todo

> **Los controladores y Filament orquestan; los Services deciden.**

En la práctica:

- Un controlador recibe la petición, llama a **un** Service y devuelve la
  vista. Si un controlador tiene lógica de negocio, está en el sitio
  equivocado.
- Los Services no conocen `Request` ni `Response`. Reciben DTOs y devuelven
  modelos o DTOs.
- Los DTOs (`Data/`) son `final readonly` con un `fromArray()` estático.
- Nada de repositorios genéricos sobre Eloquent. Los scopes van en el modelo.

## Dominios

El código de negocio vive en `app/Domain/<Contexto>/`, cada uno con la misma
forma: `Models/ Services/ Data/ Enums/ Mail/`.

```
app/Domain/
  Catalog/        Trámites, adicionales, precios
  Orders/         Pedidos, adicionales contratados, pagos
  Documents/      Generación de PDF y revisión de redacción
  LegalCases/     Casos complejos y la carrera entre abogados
  Lawyers/        Abogados y directorio
  Subscriptions/  Planes y suscripciones de abogados
  Content/        Blog y categorías
  Tools/          Calculadoras
  Seo/            Metadatos, JSON-LD y sitemap
  Settings/       Ajustes editables desde el panel
  Shared/         Leads, referencias, WhatsApp, garantías
```

El detalle de cada uno está en [Dominios](02-dominios.md).

## Fuera de `Domain/`

```
app/Http/Controllers/Web/   Un controlador por sección pública
app/Http/Requests/          Validación de formularios
app/Filament/               Panel de administración (/admin)
app/Filament/Lawyer/        Portal del abogado (/portal)
app/Providers/Filament/     Los dos paneles
resources/views/            Blade: páginas, componentes, correos, PDF
public/css/site.css         Todo el CSS del sitio público
```

## Los dos paneles

Son paneles de Filament distintos, no secciones de uno solo:

| Panel | Ruta | Quién entra | Qué ve |
|---|---|---|---|
| Administración | `/admin` | `is_staff = true` | Todo el negocio |
| Portal del abogado | `/portal` | Tiene ficha de abogado | Solo lo suyo |

Lo que separa a los dos públicos no es un menú escondido: es el límite de lo
que cada consulta puede devolver. Ver
[Accesos y permisos](../04-panel/03-accesos-y-permisos.md).

## Rutas públicas

| Ruta | Nombre | Qué es |
|---|---|---|
| `/` | `home` | Home |
| `/documentos` | `documents.index` | Catálogo |
| `/documentos/{slug}` | `documents.show` | Ficha del trámite (landing SEO) |
| `/tramites/{area}` | `documents.area` | Landing por área de práctica |
| `/gratis` | `tools.index` | Hub de herramientas gratuitas |
| `/gratis/calculadoras/{slug}` | `tools.calculator` | Una calculadora |
| `/contratar/{slug}` | `checkout.configure` | Checkout con adicionales |
| `/pago/{referencia}` | `checkout.payment` | Pago |
| `/pedido/{referencia}` | `checkout.confirmation` | Pedido y descargas |
| `/pedido/{ref}/descargar/{kind}` | `checkout.download` | PDF del documento o del instructivo |
| `/caso-complejo` | `cases.create` | Formulario de caso complejo |
| `/casos/oferta/{token}/{decision}` | `cases.offer.respond` | Aceptar o rechazar desde el correo |
| `/abogados` | `lawyers.index` | Directorio |
| `/abogados/{slug}` | `lawyers.show` | Perfil de abogado |
| `/abogados/registro` | `lawyers.join` | Captación de abogados |
| `/blog`, `/blog/{slug}` | `blog.*` | Blog |
| `/garantia` | `pages.guarantee` | Certificado de Garantía |
| `/sitemap.xml`, `/robots.txt` | `sitemap`, `robots` | SEO técnico |

Las URL están en español y sin parámetros técnicos: son las que indexa Google y
las que el cliente lee antes de hacer clic.

## Decisiones que conviene conocer antes de tocar nada

- **`Model::shouldBeStrict()`** está activo fuera de producción: una relación
  cargada perezosamente o una asignación masiva accidental **revientan** en
  desarrollo y en los tests. Es a propósito: es mejor verlo en el test que en
  una consulta N+1 en producción.
- **Los montos son pesos enteros.** `90000` es $90.000. En Filament,
  `->money('COP')` sin divisor.
- **Las fechas** usan `translatedFormat()`, locale `es`, zona
  `America/Bogota`.
- **Las referencias visibles** (`HL-2026-00042`, `CC-…`, `GAR-…`) las genera
  `Shared\Services\ReferenceGenerator`.
- **La referencia no es una credencial.** Es correlativa y adivinable; los
  pedidos llevan además un `access_token`.
