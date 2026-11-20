# Entorno local

## Requisitos

Docker y [ddev](https://ddev.com). Nada más: PHP, MariaDB y Node viven dentro
de los contenedores.

## Levantar el proyecto

```bash
ddev start
ddev composer install
ddev artisan key:generate
ddev artisan migrate:fresh --seed
```

El sitio queda en `https://hazlolegal.ddev.site`.

| URL | Qué es |
|---|---|
| `https://hazlolegal.ddev.site` | Sitio público |
| `https://hazlolegal.ddev.site/admin` | Panel de administración |
| `https://hazlolegal.ddev.site/portal` | Portal del abogado |
| `ddev launch -m` | Mailpit: los correos que se «envían» |

Credenciales en [Accesos y permisos](../04-panel/03-accesos-y-permisos.md).

## Comandos del día a día

```bash
ddev artisan test                  # toda la suite
ddev artisan test --filter=Checkout
ddev artisan migrate:fresh --seed  # base limpia con datos de demo
ddev artisan optimize:clear        # caché de rutas, vistas y config
ddev artisan tinker
ddev logs -f                       # logs del servidor
ddev ssh                           # consola dentro del contenedor
```

> **Después de tocar un Blade o el CSS:** no hace falta compilar nada. El CSS
> se enlaza con `@asset()`, que le añade la fecha de modificación, así que un
> cambio llega con una recarga normal.

## Compartir con alguien de fuera

```bash
ddev compartir
```

Levanta dos túneles de ngrok (el sitio y Mailpit) con autenticación básica, y
muestra las URL y los accesos. Cómo se construyó ese comando a mano está en
`COMPARTIR-NGROK.md`, en la raíz.

## Correos

En local no sale ningún correo de verdad: todo cae en Mailpit. Es la forma de
revisar cómo quedan las plantillas sin molestar a nadie.

## Trampas conocidas

| Síntoma | Causa | Solución |
|---|---|---|
| Página sin estilos | Una clase nueva sin regla en `site.css` | Escribir la regla. No hay build step que avise |
| `ddev start` falla por puerto ocupado | Otro proyecto ddev corriendo | `ddev poweroff` y volver a arrancar |
| Cambios que no se ven | Caché de config o vistas | `ddev artisan optimize:clear` |
| `LazyLoadingViolationException` | Relación sin `with()` | Es a propósito: añade el eager loading |
| `MassAssignmentException` | Columna que ya no existe | `Model::shouldBeStrict()` lo caza en desarrollo |
| `robots.txt` o `sitemap.xml` vacíos | Un archivo estático en `public/` tapa la ruta | Borrar el estático. Hay un test que lo vigila |

## Variables de entorno que importan

```dotenv
APP_LOCALE=es
APP_TIMEZONE=America/Bogota

HL_WHATSAPP=573001234567
HL_WHATSAPP_DISPLAY="+57 300 123 4567"
HL_EMAIL=hola@hazlolegal.co
HL_CASES_EMAIL=casos@hazlolegal.co
HL_LEGAL_NAME="HazloLegal S.A.S."
HL_NIT=901.000.000-1
```

El resto de la configuración de negocio está en `config/hazlolegal.php` o en el
panel (`Ajustes del sitio`). La regla es: lo que cambia por entorno va por
`.env`; lo que cambia el equipo comercial, al panel.
