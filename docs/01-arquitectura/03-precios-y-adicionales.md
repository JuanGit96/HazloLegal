# El motor de precios: base + adicionales

Esta es la pieza central del producto. Si solo vas a entender una cosa del
código, que sea esta.

## La idea

No hay planes cerrados. El precio de un pedido es:

```
precio base del trámite  +  suma de los adicionales elegidos
```

El precio base **puede ser cero**. El «nivel» del pedido (`OrderTier`) no se
elige: se deduce de lo que el cliente sumó.

Esto reemplazó un modelo anterior de planes (`ServicePlan`: «documento»,
«asesoría»…) que se eliminó por completo. Mantener las dos cosas habría dejado
dos fuentes de verdad para el mismo número, que es la forma más segura de
cobrar mal.

## Las piezas

```
LegalDocument          base_price, compare_at_price, discount_percentage,
                       self_service_enabled, is_free, delivery_hours,
                       express_delivery_hours

ServiceAddon           key, kind, default_price, discount_percentage,
                       badge, social_proof, bullets

legal_document_        price (sobrescribe), is_available, is_recommended,
service_addon          is_preselected, sort_order
```

La tabla pivote es la que hace que **el mismo adicional cueste distinto en cada
trámite**: acompañar una tutela ($280.000) no es acompañar un NDA ($90.100).

Una celda vacía en `price` significa «usa el precio por defecto del adicional»;
un `0` significa «va incluido sin costo». Son cosas distintas y no se
normalizan a lo mismo.

## `PricingService::quote()`

```php
$quote = $pricing->quote($document, ['instructivo', 'express']);
```

Devuelve un `Quote` inmutable con:

| Campo | Qué es |
|---|---|
| `baseAmount` | El precio base del trámite |
| `addonsAmount` | La suma de los adicionales |
| `total` | Lo que se cobra |
| `compareAtTotal` | El precio tachado, si hay descuento configurado |
| `discountAmount` | La diferencia |
| `deliveryHours` | Horas de entrega, ya resueltas con o sin express |
| `tier` | `free`, `self_service` o `accompanied` |

Se **recalcula entero en cada cambio**, nunca se va sumando sobre el anterior.
Un total que se acumula acaba desincronizado con sus líneas.

## Ejemplos reales (con los datos sembrados)

**Derecho de petición**, precio base $0:

| Lo que elige | Total |
|---|---|
| Nada | Gratis |
| + Instructivo | $19.900 |
| + Instructivo + Express | $79.900 |
| Todo (instructivo, firma, express, acompañamiento, sesión) | $309.600 |

**Promesa de compraventa de vehículo**, base $59.900 con 15 % de descuento:

- Tachado: $70.900 · Real: $59.900
- + Firma digital ($19.900) + Express ($40.000) = **$119.800**
- El instructivo va a precio 0: incluido.

**Acción de tutela en salud**, sin autoservicio:

- No se puede pedir sola. El acompañamiento ($280.000) llega **premarcado**.
- Se anuncia como «Desde $280.000», no como «Gratis», porque
  `self_service_enabled = false` cambia cómo se calcula el `entryPrice()`.

## `AddonOfferService`: el orden no es estético

```php
$offers = $addonOffers->offersFor($document, $seleccionados);
```

Los adicionales se ordenan por `AddonKind::offerOrder()`:

| Orden | Familia | Ejemplo |
|---|---|---|
| 1 | `guidance` | Instructivo |
| 2 | `signature` | Firma digital |
| 3 | `delivery` | Express |
| 4 | `review` | Acompañamiento |
| 5 | `session` | Sesión con abogado |

**Primero lo barato y concreto, al final lo caro.** Abrir con el abogado de
$129.900 hace que todo lo demás parezca un gasto añadido; abrir con el
instructivo de $19.900 construye el hábito de ir sumando, y cada «sí» hace más
probable el siguiente.

Nada de esto es un truco: cada adicional entrega lo que promete, el precio se
muestra siempre y quitar uno es tan fácil como ponerlo. Ver
[Persuasión](../02-conversion/06-persuasion.md).

Otros métodos:

- `nextBestOffer()` — el siguiente adicional a proponer, **de uno en uno**. Una
  lista de seis casillas abruma y la respuesta por defecto pasa a ser
  «ninguna».
- `preselectedKeys()` — los que llegan marcados por defecto.
- `missingOut()` — lo que el cliente se está perdiendo, para enmarcar en
  pérdida.

## Combinaciones que no se pueden cumplir

La entrega inmediata y «lo escribe un abogado» se excluyen: nadie redacta
sobre los hechos de otro al instante, así que cobrar las dos sería cobrar por
algo que no va a pasar.

En la pantalla **las dos siguen visibles**. Al marcar una se desmarca la otra
y aparece un aviso diciendo cuál se quitó y por qué; esconder la opción sin
explicación se lee como un error de la página. Gana la última que se marcó, y
volver atrás es un clic.

En el servidor manda `PricingService::withoutIncompatible()`, que descarta la
entrega aunque lleguen las dos en el POST. La pantalla puede equivocarse o
alguien puede saltársela: lo que se cobra se decide aquí.

## Precios tachados

Dos formas de configurarlos:

1. **`compare_at_price`** — el precio anterior real. Es la forma correcta.
2. **`discount_percentage`** — un atajo: el tachado se deriva. El resultado se
   redondea a una cifra que parezca un precio (`Shared\Concerns\RoundsPrices`),
   porque un tachado de $70.471 delata que lo calculó una máquina.

Todos los tachados se apagan de golpe con el ajuste `promo_enabled`.

> Un precio tachado solo es legítimo si ese precio fue real alguna vez.
> Inventarlo es publicidad engañosa bajo la Ley 1480 de 2011.

## Dónde se administra

- **Por trámite:** `/admin` → Trámites → pestaña «Adicionales de este trámite».
- **Por adicional:** `/admin` → Adicionales → pestaña «Trámites donde se ofrece».
- **Todo de golpe:** `/admin` → **Matriz de precios**, el cruce completo de
  trámites × adicionales con el precio editable y las marcas de recomendado y
  premarcado.
