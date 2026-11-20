# Los dominios, uno por uno

Cada contexto en `app/Domain/` resuelve una parte del negocio. Esto es qué hace
cada uno y, sobre todo, **qué servicio llamar** cuando hay que tocar algo.

---

## Catalog — qué se vende y a qué precio

| Clase | Qué hace |
|---|---|
| `Models/LegalDocument` | Un trámite: precio base, horas de entrega, plantilla, SEO, si admite autoservicio |
| `Models/ServiceAddon` | Un adicional («topping») con su precio por defecto |
| `Services/PricingService` | **Calcula el precio.** Única fuente de verdad |
| `Services/AddonOfferService` | Decide qué adicionales ofrecer, en qué orden y con qué argumento |
| `Services/CatalogService` | Única puerta de entrada al catálogo |
| `Data/Quote` | El precio resuelto de una combinación concreta. `final readonly` |
| `Data/AddonOffer` | Un adicional ya resuelto para un trámite, listo para pintarse |
| `Enums/AddonKind` | Familia del adicional; define el orden en que se ofrece |
| `Enums/PracticeArea` | Áreas de práctica |

El detalle del cálculo está en [Precios y adicionales](03-precios-y-adicionales.md).

---

## Orders — el pedido

| Clase | Qué hace |
|---|---|
| `Models/Order` | El pedido, con `access_token`, `tier` y los importes desglosados |
| `Models/OrderAddon` | Un adicional **tal como se vendió**, con precio congelado |
| `Services/OrderService` | Crea el pedido, confirma el pago, genera los PDF, suma adicionales después |
| `Data/OrderDraft` | Lo que llega del formulario |
| `Enums/OrderTier` | `free` / `self_service` / `accompanied`. **Se calcula, no se elige** |
| `Mail/OrderReceivedMail` | Primer correo. Cambia de tono si no hay nada que cobrar |
| `Mail/OrderPaidMail` | Pago confirmado, con los PDF adjuntos |

**Por qué el precio se congela en `order_addons`:** si mañana sube el express,
el pedido de ayer tiene que seguir diciendo lo que el cliente pagó. Un `join`
contra el catálogo cambiaría el histórico.

---

## Documents — lo que el cliente se lleva

| Clase | Qué hace |
|---|---|
| `Services/LegalDocumentPdfBuilder` | Arma el documento que se radica, desde la plantilla del trámite |
| `Services/InstructionsPdfBuilder` | El instructivo: dónde radicar, qué anexar, qué plazo. **También es publicidad** |
| `Services/LawyerPlacementService` | Qué abogado suscrito aparece en el instructivo, rotando |
| `Services/DocumentReviewer` | La revisión de redacción que sostiene el gancho «sin vacíos legales» |
| `Data/ReviewResult` | Qué se corrigió y qué debería revisar el cliente |

`DocumentReviewer` es determinista: normaliza espacios, arregla mayúsculas
sostenidas, quita groserías y muletillas, corrige el inicio de frase y la
puntuación, y avisa de falta de fechas o de radicados. **No inventa contenido
ni cambia los hechos**, y por eso no se promete que lo haga.

El instructivo cumple dos funciones y por eso recibe tanto cuidado: es lo que
el cliente paga en el adicional, y es una pieza que se reenvía, se imprime y se
entrega en ventanilla llevando nuestra marca y el nombre de un abogado
suscrito.

---

## LegalCases — la carrera por un caso

| Clase | Qué hace |
|---|---|
| `Models/LegalCase` | El caso del cliente |
| `Models/CaseOffer` | La invitación a un abogado, con token y estado |
| `Services/CaseIntakeService` | Recibe el caso del formulario |
| `Services/CaseDispatchService` | Invita a los abogados del área |
| `Services/CaseAssignmentService` | **Asigna al primero que acepta**, con `lockForUpdate()` |

La asignación es atómica a propósito: dos abogados aceptando al mismo tiempo no
pueden quedarse los dos con el caso. El que llega tarde ve que ya fue tomado y
**nunca recibe los datos del cliente**. Hay un test dedicado a esto.

---

## Lawyers — quién ejerce

| Clase | Qué hace |
|---|---|
| `Models/Lawyer` | Ficha del abogado: áreas, cupo, perfil público, suscripción |
| `Services/LawyerDirectory` | El directorio público |
| `Services/CurrentLawyer` | **Quién está usando el portal.** Todo el portal se acota con esto |

`lawyers.professional_card` es dato interno. Nunca se publica. Ver
[Reglas de honestidad](../00-contexto/04-reglas-de-honestidad.md).

---

## Subscriptions — la segunda línea de ingreso

| Clase | Qué hace |
|---|---|
| `Models/LawyerPlan` | Un plan: precio mensual/anual, prueba gratis y qué incluye |
| `Models/LawyerSubscription` | La suscripción de un abogado, con estado y fechas |
| `Enums/SubscriptionStatus` | `trialing` / `active` / `past_due` / `cancelled` |
| `Enums/BillingPeriod` | `monthly` / `annual` |

`LawyerSubscription::allows('includes_lead_data')` es lo que decide si ese
abogado ve contactos. Los interruptores del plan cambian lo que el sistema
hace, no solo lo que la landing promete.

---

## Shared — lo transversal

| Clase | Qué hace |
|---|---|
| `Models/Lead` | Un contacto con su registro de consentimiento |
| `Models/LeadAccess` | Qué abogado vio qué contacto y cuándo |
| `Services/LeadCaptureService` | Captura el contacto con lo que el titular autorizó |
| `Services/ReferenceGenerator` | `HL-2026-00042`, `CC-…`, `GAR-…` |
| `Services/WhatsAppLinkBuilder` | Enlaces `wa.me` con mensaje precargado |
| `Concerns/RoundsPrices` | Redondea un precio tachado a una cifra que parezca un precio |

`Lead::scopeShareable()` es la frontera legal del negocio de datos: solo quien
autorizó compartir y no ha revocado.

---

## Seo

| Clase | Qué hace |
|---|---|
| `Services/SeoManager` | Construye la `SeoMeta` de cada página |
| `Services/SchemaGenerator` | Arma los JSON-LD |
| `Services/SitemapBuilder` | El sitemap |
| `Data/SeoMeta` | Título, descripción, canónica, imagen, migas y esquemas |

**Nunca escribir `<title>` ni JSON-LD a mano en una plantilla.** Detalle en
[Implementación SEO](../03-seo/02-implementacion.md).

---

## Settings y Tools

- `Settings/Services/SettingsService` — los ajustes del panel, cacheados.
  `get()`, `bool()`, `int()`, `image()`, `set()`, `flush()`.
- `Tools/Models/Calculator` y `Tools/Services/LaborCalculator` — las
  calculadoras laborales. Los parámetros del año (salario mínimo, auxilio de
  transporte) viven en `config` de cada calculadora y se editan en el panel,
  porque cambian cada enero.
