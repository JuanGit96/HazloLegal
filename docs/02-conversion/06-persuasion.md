# Persuasión: qué se usa, dónde, y por qué es legítimo

Este sitio está construido para vender. Eso no está reñido con la honestidad,
pero sí exige ser explícito sobre qué técnicas se usan, porque la línea entre
persuadir y manipular existe y aquí importa más que en otros negocios: el
cliente llega con un problema legal, que es un estado de vulnerabilidad.

## La regla que separa las dos cosas

> Una técnica es legítima si el cliente **tomaría la misma decisión sabiendo
> exactamente cómo funciona**.

Mostrar primero el adicional barato es legítimo: si el cliente supiera que el
orden es deliberado, seguiría queriendo el instructivo. Un contador falso de
«quedan 3 unidades» no lo es: si supiera que es falso, se iría.

## Lo que se usa

### 1. Orden de las opciones (anclaje al revés)

**Dónde:** el checkout, vía `AddonKind::offerOrder()`.

Primero lo barato y concreto (instructivo, $19.900), al final lo caro
(acompañamiento, $129.900).

**Por qué funciona:** abrir con lo caro hace que todo lo demás parezca un gasto
añadido; abrir con lo barato construye el hábito de ir sumando, y cada «sí»
hace más probable el siguiente.

**Por qué es legítimo:** todos los precios se ven a la vez. No se oculta el
caro, se pone después.

### 2. Una oferta a la vez

**Dónde:** `nextBestOffer()`, en la confirmación.

**Por qué:** una lista de seis casillas abruma y la respuesta por defecto pasa
a ser «ninguna». Uno solo, con su motivo, es una decisión que se puede tomar.

**Por qué es legítimo:** reduce la carga, no la información. El resto sigue
disponible.

### 3. Venta después de entregar

**Dónde:** la confirmación ofrece un adicional **después** de que el cliente
tiene su documento.

**Por qué:** a estas alturas ya vio que el producto sirve.

**Por qué es legítimo:** es justo el momento en que la oferta es más honesta,
porque el cliente ya puede juzgar la calidad de lo que compraría.

### 4. Premarcado

**Dónde:** `is_preselected` en la tabla pivote. Por ejemplo, el acompañamiento
en una tutela.

**Por qué:** la opción por defecto se acepta mucho más que la que hay que
elegir.

**Por qué es legítimo, y solo si se cumplen las tres:**
1. Se ve (la casilla está marcada a la vista, no escondida).
2. Se explica (lleva su argumento y su precio).
3. Se quita de un clic.

Si falta cualquiera de las tres, deja de ser un valor por defecto razonable y
pasa a ser un cargo deslizado. El formulario del panel lo dice en el texto de
ayuda del campo.

### 5. Precios tachados

**Dónde:** `compare_at_price` o `discount_percentage`.

**Por qué:** el contraste hace legible el valor.

**Por qué es legítimo — con una condición dura:** solo si ese precio fue real
alguna vez. Inventarlo es publicidad engañosa (Ley 1480 de 2011) y la SIC lo
sanciona. El panel lo advierte en el campo, y el ajuste `promo_enabled` los
apaga todos de golpe.

Cuando el tachado se deriva de un porcentaje, el resultado se redondea a una
cifra que parezca un precio: un tachado de $70.471 delata que lo calculó una
máquina.

### 6. Prueba social

**Dónde:** `social_proof` en los adicionales, `hook_free_count` en la home,
testimonios.

**Por qué es legítimo:** solo si las cifras son reales. El campo del panel lo
dice: «Debe reflejar el número real. Un dato inflado es publicidad engañosa».
Los datos de demostración del seeder están marcados como tales.

### 7. Enmarcado en pérdida

**Dónde:** `missingOut()` — lo que el cliente se pierde si no añade nada.

**Por qué funciona:** la pérdida mueve más que la ganancia equivalente.

**Por qué es legítimo:** solo si lo que se enumera es cierto y ya estaba dicho
en la ficha. No se inventa una consecuencia.

### 8. Nombrar la acción, no el destino

**Dónde:** todos los CTA. «Generar el mío gratis», no «Ver más». «Continuar al
pago», no «Siguiente».

**Por qué es legítimo:** es simplemente escribir mejor.

### 9. Reducción de fricción

- El total se recalcula sin recargar.
- El checkout es una sola pantalla.
- Un trámite gratuito **no pasa por la pantalla de pago**.
- Un adicional posterior se suma al mismo pedido: «No vuelves a escribir nada».

### 10. Autoridad y confianza

- Fundamento legal citado en cada trámite.
- Certificado de Garantía por escrito, con lo que **no** cubre.
- «Nunca pedimos datos de tarjeta por WhatsApp» en la pantalla de pago.
- Normas citadas: Ley 1581 de 2012, Ley 527 de 1999.

## Lo que NO se usa, y por qué

| Técnica | Por qué no |
|---|---|
| Cuentas regresivas falsas | El cliente tomaría otra decisión si supiera que el reloj es decorativo |
| «Quedan X plazas» inventado | Igual |
| Testimonios inventados | Publicidad engañosa; la SIC sanciona |
| Cifras infladas | Igual |
| Confirmshaming («No, prefiero perder mi caso») | Avergonzar a alguien con un problema legal |
| Costos revelados al final | Todo el precio se ve antes de pedir datos |
| Suscripción difícil de cancelar | No hay suscripciones de cliente |
| Nombrar alternativas gratuitas en el embudo | Le planta la idea a quien no la traía; ese contenido va al blog |

Las primeras cuatro tienen test o regla explícita en
[Reglas de honestidad](../00-contexto/04-reglas-de-honestidad.md).

## Qué revisar antes de añadir una técnica nueva

1. ¿El cliente tomaría la misma decisión sabiendo cómo funciona?
2. ¿Lo que se afirma es verificable?
3. ¿Se puede deshacer de un clic?
4. ¿Sobreviviría a que un periodista lo publique?

Si alguna respuesta es no, no entra.
