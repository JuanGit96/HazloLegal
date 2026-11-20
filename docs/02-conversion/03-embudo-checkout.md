# Embudo 2 · El checkout con adicionales

Una sola pantalla para armar el trámite y dejar los datos. No hay paso de
«elige plan»: desapareció con el cambio de modelo.

## La pantalla (`/contratar/{slug}`)

Dos columnas en escritorio, una en móvil:

```
┌─────────────────────────────┬──────────────────┐
│ 1. Arma tu trámite          │  Tu pedido       │
│    [ ] Instructivo  $19.900 │  Trámite  $59.900│
│    [x] Firma        $19.900 │  Firma    $19.900│
│    [ ] Express      $40.000 │  Entrega  24 h   │
│    [ ] Con abogado  $90.100 │  ─────────────── │
│                             │  Total   $79.800 │
│ 2. Cuéntanos tu caso        │  [Continuar]     │
│    [textarea]               │                  │
│    [datos]                  │  🛡️ Garantía      │
│    [consentimientos]        │                  │
└─────────────────────────────┴──────────────────┘
      ▲ en móvil, barra fija abajo con total y CTA
```

## Qué se ve en cada adicional

| Elemento | Para qué |
|---|---|
| Emoji y nombre | Reconocimiento inmediato |
| Precio, con el tachado si hay descuento | Nunca hay sorpresas en la factura |
| El argumento (`tagline`) | Qué gana, no qué es |
| Hasta 3 viñetas | Qué incluye exactamente |
| Etiqueta (`badge`) | «MÁS ELEGIDO» |
| Prueba social | «8 de cada 10 lo agregan» |

Todo eso se edita desde el panel. Si un adicional no tiene `tagline`,
`AddonOfferService` pone un respaldo por familia para que nunca salga una
tarjeta muda.

## El total en vivo

JavaScript puro, sin peticiones al servidor: el precio de cada adicional viaja
en `data-price`. Al marcar o desmarcar:

- Se recalcula el total (entero, no acumulando).
- Se añade o quita su línea en el resumen.
- La entrega cambia a «Ahora mismo» si marcó express.
- El texto del botón cambia entre «Generar mi documento gratis» y «Continuar al
  pago».

**El servidor vuelve a calcularlo al guardar.** Lo del navegador es solo la
vista: un precio que viene del cliente no se cobra nunca.

## El problema del móvil

El resumen va arriba en móvil, así que al terminar el formulario el usuario se
quedaba sin botón. Se resolvió con una barra fija al fondo
(`.checkout-bar`) que lleva el total y el CTA, más un espaciador para que la
barra no tape el último campo.

## Qué pasa al enviar

```
StoreOrderRequest valida
        ↓
OrderService::place()
        ↓
  ┌─ total = 0 ─→ estado Paid, PDF generados → /pedido
  └─ total > 0 ─→ estado AwaitingPayment     → /pago
```

La sesión guarda la referencia del pedido para que quien acaba de comprar no
tenga que llevar el token en la URL.

## El pago (`/pago/{referencia}`)

Pantalla de demostración: no cobra todavía. Lo dice con un aviso explícito, no
en letra pequeña. Ofrece Nequi, PSE o transferencia, y muestra los datos de
transferencia manual con la referencia del pedido.

Debajo del botón: «🔒 Nunca pedimos datos de tarjeta por WhatsApp ni por
teléfono. Si alguien lo hace en nuestro nombre, no somos nosotros».

Al confirmar, `OrderService::confirmPayment()`:

1. Marca pagado (**es idempotente**: un segundo intento no vuelve a cobrar ni
   reenvía el correo; hay un test).
2. Arranca el reloj del plazo: `promised_at` se calcula **desde el pago**, no
   desde el pedido.
3. Genera los PDF.
4. Manda el correo con los adjuntos.

## La confirmación (`/pedido/{referencia}?t=...`)

Igual que en el embudo gratuito: archivos, lo que se revisó de la redacción,
resumen, y **un** adicional ofrecido.

Esa página **no se abre con la referencia sola**. `HL-2026-00042` es
correlativa: sin el token del enlace o la sesión del comprador, devuelve 403.
Lo mismo las descargas.

## Dónde termina

| Resultado | Qué queda |
|---|---|
| Paga | Pedido, PDF entregados, lead capturado |
| No paga | Pedido en `AwaitingPayment` y un correo con el enlace |
| Suma un adicional después | El mismo pedido vuelve a cobro |
