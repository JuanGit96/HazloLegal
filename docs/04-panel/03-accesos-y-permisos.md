# Accesos y permisos

## Credenciales de demostración

> ⚠️ Son del prototipo. **Cambiarlas antes de exponer nada a internet.**
> Se siembran en `database/seeders/AdminUserSeeder.php`.

| Panel | URL | Usuario | Contraseña |
|---|---|---|---|
| Administración | `/admin` | `admin@hazlolegal.co` | `hazlolegal2026` |
| Portal del abogado | `/portal` | `daniela.restrepo@hazlolegal.co` | `abogado2026` |

El segundo es un abogado real del directorio sembrado, con su `user_id`
enlazado. Entra al portal y **no ve nada que no sea suyo**.

## Cómo se decide quién entra a qué

`App\Models\User::canAccessPanel(Panel $panel)`:

```php
if (! $this->is_active) {
    return false;
}

return match ($panel->getId()) {
    'lawyer' => $this->lawyer()->exists(),
    default  => (bool) $this->is_staff,
};
```

Dos columnas en `users`:

| Columna | Qué significa |
|---|---|
| `is_active` | La cuenta está habilitada. Sin esto, ningún panel |
| `is_staff` | Es del equipo. Da acceso a `/admin` |

Y la relación `lawyers.user_id` da acceso a `/portal`.

**Por qué hacía falta `is_staff`:** hasta que existió el portal, cualquier
usuario activo entraba al panel de administración. Con dos públicos en la
misma tabla, eso significaba que un abogado suscrito habría visto los pedidos,
los ingresos y los clientes de los demás. La migración
`2026_11_12_101100_add_staff_flag_to_users` marca `true` todas las filas que ya
existían, porque son del equipo.

Un miembro del equipo **puede** ser además abogado del directorio: tendría
`is_staff = true` y una ficha enlazada, y entraría a los dos.

> Filament solo salta `canAccessPanel()` en entorno local. En producción, un
> usuario sin ella recibe 403 en todo el panel.

## Qué ve cada perfil

### Equipo (`is_staff`)
Todo: pedidos con datos de clientes, ingresos, casos, abogados, suscripciones,
leads con sus consentimientos, catálogo, precios, contenido y ajustes.

Hoy **no hay roles dentro del equipo**: quien entra, entra a todo. La
separación por roles está documentada como pendiente en el propio panel
(«Próximos módulos → Equipo y permisos»).

### Abogado suscrito
Solo lo suyo. Detalle en [Portal del abogado](02-portal-del-abogado.md):

| Recurso | Alcance |
|---|---|
| Resumen | Sus métricas |
| Mis casos | Solo los que ganó |
| Contactos | Solo autorizados, de sus áreas, si su plan lo incluye |
| Mi perfil | Su ficha, sin la tarjeta profesional ni los criterios del negocio |
| Mi suscripción | Solo lectura |

### Cliente
No tiene cuenta. Accede a su pedido por el enlace con `access_token`, que llega
por correo. Un portal de cliente está en el roadmap.

### Abogado invitado a un caso
No necesita cuenta. El enlace del correo lleva un token por invitación, que es
la credencial. Si acepta primero, ve los datos; si llega tarde, no.

## Cómo se garantiza el aislamiento

No por esconder menús, sino acotando las consultas en el origen:

| Dónde | Cómo |
|---|---|
| `MyCaseResource::getEloquentQuery()` | `where('assigned_lawyer_id', $lawyerId)`, y `1 = 0` si no hay abogado |
| `ViewMyCase::resolveRecord()` | Vuelve a pasar por la consulta acotada |
| `MyLeadResource::getEloquentQuery()` | `shareable()` + áreas del abogado + característica del plan |
| `MyLeadResource::canViewAny()` | 403 sin la característica |
| `MyProfile::save()` | Solo los campos del formulario |
| `CheckoutController::authorizeOrder()` | Token del enlace o sesión del comprador |
| `CaseAssignmentService` | `lockForUpdate()`: un solo ganador |

**Un `whereNull` en lugar de `1 = 0` habría sido un desastre:** devolvería los
casos sin asignar, que es justo lo que no puede pasar.

## Tests que lo vigilan

`tests/Feature/LawyerPortalTest.php`:

- El abogado entra a su portal y recibe 403 en el de administración.
- El usuario del equipo recibe 403 en el portal.
- Solo ve los casos que ganó, ni listándolos ni abriéndolos por URL.
- Sin la característica en el plan, no hay contactos.
- No ve los que no autorizaron ni los que revocaron.
- Ni el que sí autorizó enseña su correo en el listado.
- No puede editar su tarjeta profesional.

`tests/Feature/CheckoutFlowTest.php`:

- La referencia sola no abre el pedido de otro (403 en la página y en la
  descarga).

`tests/Feature/ComplexCaseFlowTest.php`:

- El segundo abogado que llega ve que ya fue tomado y **no recibe datos**.

## Checklist antes de producción

- [ ] Cambiar las dos contraseñas de demostración
- [ ] `php artisan db:wipe` y sembrar **sin** `DemoActivitySeeder` ni `DemoSubscriptionSeeder`
- [ ] Revisar que todo usuario del equipo tenga `is_staff = true` y nadie más
- [ ] Comprobar que `APP_DEBUG=false` y `APP_ENV=production`
- [ ] Verificar que `/admin` y `/portal` solo se sirven por HTTPS
- [ ] Revisar `robots.txt`: `/admin` bloqueado (`/portal` también debería)
