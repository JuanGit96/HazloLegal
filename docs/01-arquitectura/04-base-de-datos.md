# Base de datos

Migraciones con clase anónima. Modelos con `protected $guarded = ['id']` y
`protected function casts()`. Toda columna con semántica de negocio se castea a
su enum.

## Las tablas que importan

### Catálogo y precios

| Tabla | Qué guarda | Detalle que importa |
|---|---|---|
| `legal_documents` | Los trámites | `base_price` puede ser 0; `self_service_enabled = false` lo deja solo con abogado; `template_body` es el cuerpo del PDF con marcadores |
| `service_addons` | Los adicionales | `default_price` 0 = incluido |
| `legal_document_service_addon` | **El cruce** | `price` nulo = por defecto; `price` 0 = incluido. `is_preselected` llega marcado |

### Pedidos

| Tabla | Qué guarda | Detalle que importa |
|---|---|---|
| `orders` | El pedido | `access_token` es la credencial real; la referencia es correlativa. `base_amount` / `addons_amount` / `amount` desglosados. `ai_review` guarda qué se corrigió |
| `order_addons` | Los adicionales vendidos | `key`, `name` y `price` **congelados**. No se hace join contra el catálogo |

### Casos complejos

| Tabla | Detalle que importa |
|---|---|
| `legal_cases` | `assigned_lawyer_id` se escribe una sola vez, bajo `lockForUpdate()` |
| `case_offers` | Un token por invitación. Es la credencial del enlace del correo |

### Abogados y suscripciones

| Tabla | Detalle que importa |
|---|---|
| `lawyers` | `professional_card` **nunca se publica**. `user_id` enlaza con el acceso al portal. `last_placement_at` rota quién sale en los instructivos |
| `lawyer_plans` | Los `includes_*` cambian lo que el sistema hace |
| `lawyer_subscriptions` | `status`, `trial_ends_at`, `featured_until` |

### Datos personales

| Tabla | Detalle que importa |
|---|---|
| `leads` | `consent_marketing` y `consent_share_with_lawyers` son **dos cosas distintas**. `consented_at`, `consent_policy_version`, `consent_revoked_at` |
| `lead_accesses` | Qué abogado vio qué contacto, cuándo y desde qué IP. Es la prueba de diligencia |

### Resto

`site_settings` (clave/grupo/valor/tipo, con el formulario del panel construido
desde aquí), `calculators`, `posts`, `post_categories`, `testimonials`,
`faq_entries`, `guarantee_claims`, `users` (`is_active`, `is_staff`).

## Migraciones del cambio de modelo

El pivote de noviembre de 2026 está en estas migraciones, en orden:

```
2026_11_12_100000  create_service_addons_table
2026_11_12_100100  rework_legal_documents_pricing
2026_11_12_100200  rework_orders_for_addons
2026_11_12_100300  create_lawyer_subscriptions_tables
2026_11_12_100400  extend_lawyers_for_directory
2026_11_12_100500  create_site_settings_table
2026_11_12_100600  create_calculators_table
2026_11_12_100700  extend_leads_for_lawyer_network
2026_11_12_100800  add_placement_and_steps
2026_11_12_100900  add_templates_and_order_access
2026_11_12_101000  drop_service_plans          ← irreversible a propósito
2026_11_12_101100  add_staff_flag_to_users
```

`drop_service_plans` no tiene `down()`: reconstruir los planes exigiría
inventar los precios que tenían, y eso falsearía el histórico de pedidos.

## Seeders

| Seeder | Qué siembra |
|---|---|
| `SiteSettingSeeder` | 19 ajustes en 4 grupos |
| `ServiceAddonSeeder` | Los 5 adicionales |
| `LawyerSeeder` | Los abogados |
| `AdminUserSeeder` | Acceso al panel y al portal |
| `LegalDocumentSeeder` | Los 10 trámites |
| `PricingSeeder` | **La matriz completa** de precios por trámite |
| `LawyerPlanSeeder` | Los 3 planes |
| `CalculatorSeeder` | Las 4 calculadoras |
| `FaqSeeder`, `TestimonialSeeder`, `BlogSeeder` | Contenido |
| `DemoActivitySeeder`, `DemoSubscriptionSeeder` | **Datos de demostración**, marcados como tales |

Antes de producción: `php artisan db:wipe` y sembrar solo lo que no sea demo.
