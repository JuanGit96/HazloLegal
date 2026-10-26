# HazloLegal

Plataforma de documentos y trámites legales para Colombia: catálogo con precio
fijo, embudo de compra, reparto de casos complejos entre abogados y blog como
motor de adquisición orgánica.

**Estado: prototipo funcional.** Todo el sitio se puede recorrer de principio a
fin. Lo que todavía no es real está listado en «Qué es dummy» más abajo.

---

## 1. Levantar el proyecto

Requisito: Docker Desktop abierto.

```bash
cd /Volumes/Innspira/LaravelProjects/HazloLegal
ddev start
ddev launch
```

| | |
|---|---|
| Sitio | https://hazlolegal.ddev.site |
| Panel | https://hazlolegal.ddev.site/admin |
| Correos (Mailpit) | https://hazlolegal.ddev.site:8026 |
| Usuario de demo | `admin@hazlolegal.co` / `hazlolegal2026` |

Reconstruir la base con datos de demostración:

```bash
ddev artisan migrate:fresh --seed
```

Tests:

```bash
ddev artisan test
```

**Stack:** Laravel 13 · PHP 8.5 · MariaDB 11.8 · Filament 5 · sin build step
(el CSS del sitio público se escribe a mano y se sirve tal cual).

---

## 2. Arquitectura

Arquitectura basada en servicios, con dominios acotados. La regla es simple:
**los controladores y Filament orquestan, los Services deciden.**

```
app/
├── Domain/
│   ├── Catalog/      Trámites, planes y precios
│   ├── Orders/       Pedidos de documentos
│   ├── LegalCases/   Casos complejos y reparto entre abogados
│   ├── Lawyers/      La red de abogados
│   ├── Content/      Blog, testimonios y FAQ
│   ├── Seo/          Metadatos, JSON-LD y sitemap
│   └── Shared/       Referencias, leads, garantías, WhatsApp
│
│   Cada dominio: Models/ Services/ Data/ Enums/ Mail/
│
├── Http/Controllers/Web/   Reciben, delegan, devuelven vista
├── Http/Requests/          Validación
└── Filament/               Panel: Resources, Widgets, Pages
```

### Dónde vive cada decisión

| Decisión | Archivo |
|---|---|
| Cuánto cuesta algo | `Domain/Catalog/Services/PricingService` |
| Quién recibe un caso | `Domain/Lawyers/Services/LawyerDirectory` |
| Quién gana la carrera por un caso | `Domain/LegalCases/Services/CaseAssignmentService` |
| Qué correos salen y cuándo | Los Services del dominio, nunca los controladores |
| Datos del negocio (precios, contacto, garantía) | `config/hazlolegal.php` |

### La pieza con más cuidado: la carrera por un caso

`CaseAssignmentService::accept()` resuelve una condición de carrera real —varios
abogados pueden hacer clic en el mismo segundo—. Bloquea la fila del caso con
`lockForUpdate()` dentro de una transacción, así solo un request puede asignarlo;
los demás leen que ya tiene dueño y reciben «lo tomó otro». Está cubierto por
tests que simulan la carrera.

---

## 3. Los dos tipos de trámite

El catálogo distingue dos fases, y esa distinción se muestra al cliente:

- **Fase 1 · Automatizado.** Estructura estable. Se arma sobre un formato que
  escribieron y mantienen los abogados del equipo. **El documento no se revisa
  caso por caso**, y el sitio lo dice en cada ficha. NDA, Datacrédito, promesa
  de compraventa, arrendamiento, fotomultas.
- **Fase 2 · Redacción manual.** Exige narrar hechos, calcular o escribir
  cláusulas condicionales. Lo redacta un abogado desde cero. Tutela, liquidación
  laboral, prestación de servicios, SAS, derecho de petición general.

---

## 4. Qué funciona y qué es dummy

### Funciona de verdad

- Catálogo, landings SEO por trámite y por área, buscador.
- Embudo completo: plan → trámite → velocidad y datos → pago → confirmación.
- **Casos complejos de punta a punta**: formulario → acuse al cliente → correo a
  todos los abogados del área → el primero que acepta se lo queda → aviso al
  cliente con el perfil del abogado → aviso a los demás.
- Blog con categorías, autoría acreditada y captura de leads.
- Certificado de Garantía con formulario de reclamación.
- Panel completo con 12 recursos, 4 widgets y 11 páginas de hoja de ruta.
- SEO técnico: sitemap, robots, JSON-LD, Open Graph, canonical.

### Es dummy y hay que reemplazar

| Qué | Dónde |
|---|---|
| **El pago no cobra.** Al confirmar se simula un pago aprobado | `OrderService::confirmPayment()` |
| **No se genera PDF.** El pedido se marca entregado a mano | `orders.delivered_file_path` |
| **No hay firma OTP.** Solo se registra la intención | `orders.wants_signature` |
| **Abogados ficticios** con tarjetas profesionales inventadas | `LawyerSeeder` |
| **Testimonios ficticios** | `TestimonialSeeder` |
| **Pedidos y casos de ejemplo** | `DemoActivitySeeder` |
| **Textos legales sin revisar** | `pages/terms`, `pages/privacy` |
| **Datos de contacto de relleno** (WhatsApp, NIT, dirección) | `.env` / `config/hazlolegal.php` |

> ⚠️ **Antes de publicar**: los testimonios inventados son publicidad engañosa y
> la SIC puede sancionarlos (Ley 1480 de 2011, art. 30).

### Qué NO promete el sitio, a propósito

El sitio **no publica números de tarjeta profesional** y **no afirma que un
abogado revise cada documento**. Es una decisión deliberada: en los trámites
automatizados nadie revisa caso por caso, y prometerlo expondría tanto a la
empresa como al abogado concreto que se nombrara.

Lo que sí se afirma, y es verificable: los formatos los escriben abogados
colombianos del equipo, se actualizan cuando cambia la norma, y el resultado
está cubierto por el Certificado de Garantía. La tarjeta profesional se guarda
en la ficha de cada abogado como **control interno** y no sale en ninguna
página ni correo. Hay tests que lo verifican (`PublicSeoTest`).

---

## 5. Configuración por entorno

Lo que cambia entre entornos va en `.env`:

```dotenv
HL_WHATSAPP=573001234567          # formato internacional, sin "+"
HL_WHATSAPP_DISPLAY="+57 300 123 4567"
HL_EMAIL=hola@hazlolegal.co
HL_CASES_EMAIL=casos@hazlolegal.co
HL_NIT=901.000.000-1
HL_ADDRESS="Calle 100 # 19-61, Oficina 802"

HL_CASE_MAX_LAWYERS=8             # a cuántos abogados se reparte un caso
HL_CASE_OFFER_TTL=48              # horas antes de que venza la invitación

HL_GOOGLE_VERIFICATION=           # Search Console
HL_GA_ID=                         # Google Analytics 4
HL_META_PIXEL=

HL_FACEBOOK=https://facebook.com/hazlolegal
HL_INSTAGRAM=...                  # y linkedin, tiktok, youtube
```

Lo que cambia por decisión comercial (precios, plazos, textos de los planes,
FAQ, contenido de los trámites) se edita **desde el panel**, no en código.

---

## 6. SEO

La estrategia está montada sobre tres capas:

1. **Landings transaccionales** (`/documentos/{slug}`): una por trámite, con
   contenido largo, FAQ con marcado `FAQPage`, precio y `Offer` en JSON-LD.
2. **Clusters informativos** (`/blog`): guías que atacan la búsqueda informativa
   («cómo hacer una tutela») y enlazan al servicio de pago correspondiente.
3. **E-E-A-T**: el contenido legal es YMYL y Google exige autoría acreditada.
   Cada artículo declara autor y revisor en el JSON-LD (`BlogPosting` →
   `author` / `reviewedBy` → `Person` con especialidad y formación). Se dejó
   fuera `hasCredential` para no publicar la tarjeta profesional; la autoría
   nominal con experiencia declarada conserva la mayor parte de la señal.

Datos estructurados implementados: `LegalService`, `WebSite` con `SearchAction`,
`Service` + `Offer`, `FAQPage`, `BreadcrumbList`, `BlogPosting`, `ItemList`,
`Person` con credenciales, `HowTo`.

El embudo (`/contratar`, `/pago`, `/pedido`) va con `noindex` para no diluir las
landings, y bloqueado en `robots.txt`.

### Pendiente de configurar al publicar

- Verificar la propiedad en Google Search Console (`HL_GOOGLE_VERIFICATION`).
- Enviar `sitemap.xml`.
- Crear y verificar el perfil de Google Business.
- Conseguir las primeras reseñas reales.

---

## 7. Hoja de ruta

Las once páginas del grupo «Próximos módulos» del panel documentan cada módulo
pendiente: qué debe hacer, por qué importa, de qué depende y qué parte ya está
construida. Por prioridad:

**Alta** — Pasarela de pagos · Generación de PDF · Plantillas y automatización ·
Firma electrónica OTP · WhatsApp Business · Equipo y permisos
**Media** — Analítica y embudo · Facturación electrónica DIAN · Portal del
abogado · Herramientas de SEO
**Baja** — Portal del cliente
