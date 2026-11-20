# Firma electrónica y pagos

## Firma electrónica por QR

El adicional `firma_digital` ($19.900) entrega esto:

```
Documento listo
      │
      ▼
Se crea una petición de firma con token y vencimiento a 7 días
      │
      ├─► En la página del pedido: un QR
      └─► En el correo de entrega: un enlace
                    │
                    ▼
      /firmar/{token}  ← pantalla solo para celular
                    │
         traza la firma con el dedo
                    │
                    ▼
      El PDF se regenera con la firma + certificado
```

### Por qué así

- **El QR es para quien está en el computador.** Firmar con el ratón produce
  un garabato; el dedo sobre la pantalla es lo que se parece a una firma.
- **La pantalla no tiene navegación.** Quien llega ya decidió: cada enlace
  sería una oportunidad de abandonar.
- **El trazo viaja como SVG**, no como PNG: pesa menos, se imprime nítido y
  se puede dibujar sobre el PDF sin perder calidad.

### Qué le da validez

La Ley 527 de 1999 no exige un formato de firma: exige poder **identificar al
firmante** y comprobar que el documento **no cambió después**. De ahí que se
guarde:

| Dato | Para qué |
|---|---|
| Fecha y hora | Cuándo se firmó |
| Dirección IP | Desde dónde |
| Navegador y dispositivo | Con qué |
| Cuándo se abrió el enlace | Que hubo una persona delante |
| **Huella SHA-256 del PDF firmado** | Que el archivo no se tocó después |

Todo eso va impreso en el propio documento, en un recuadro al final. Un PDF
que no lleva esa información es solo una imagen de una firma.

### Lo que falta

OTP por SMS antes de firmar, firma de varias partes en un mismo documento y
sello de tiempo de una entidad certificadora. Ver
[Pendientes](../05-operacion/03-pendientes.md).

---

## Pagos con Wompi

Integrado **en sandbox**, para las dos cosas que se cobran: las compras de
clientes y las suscripciones de abogados.

### Cómo funciona

Se usa el **Checkout Web**: el cliente sale al dominio de Wompi y vuelve.
Aquí nunca entra un número de tarjeta, que es exactamente lo que no queremos
tener que custodiar.

```
/pago/{referencia}
      │  se abre un PaymentTransaction con referencia propia
      ▼
checkout.wompi.co  ← formulario firmado con la firma de integridad
      │
      ├─► el usuario vuelve a /wompi/retorno/{referencia}
      │        (se consulta el estado real a Wompi, no se cree a la URL)
      │
      └─► Wompi llama a /wompi/eventos  ← este es el que manda
                 │
                 ▼
         PaymentProcessor: entrega el pedido o activa la suscripción
```

### Las dos firmas, que no son la misma

| Firma | Cuándo | Qué impide |
|---|---|---|
| **Integridad** | Al salir hacia Wompi | Que alguien edite el monto en el enlace antes de pagar |
| **Eventos** | Al recibir el webhook | Que cualquiera que conozca la URL nos escriba «aprobado» |

La de integridad es `sha256(referencia + monto_en_centavos + moneda + secreto)`.
La de eventos la define Wompi en cada evento: dice qué propiedades firmó y en
qué orden.

### Decisiones que conviene conocer

- **El webhook manda, no el navegador.** El usuario puede cerrar la pestaña
  antes de volver, o volver con la URL editada a mano. El estado real se
  escribe cuando Wompi lo confirma con su firma.
- **Cada visita a la pantalla de pago abre una transacción nueva.** Wompi no
  admite dos transacciones con la misma referencia, así que un pago rechazado
  se puede reintentar sin inventar otro pedido.
- **`PaymentProcessor` es idempotente.** Un webhook puede llegar dos veces, y
  entregarle dos veces a alguien por un reintento de la pasarela sería culpa
  nuestra.
- **El webhook está fuera de CSRF** (`bootstrap/app.php`) porque no viene de
  un formulario nuestro. Lo que prueba que es legítimo es la firma.

### Las suscripciones

El abogado paga desde su portal (`/portal` → Mi suscripción → Pagar), no desde
la landing pública. Es deliberado: **primero se verifica la tarjeta
profesional, después se cobra**. Cobrarle a alguien por aparecer como abogado
antes de comprobar que lo es sería el problema contrario.

Un cobro aprobado activa la suscripción, extiende el periodo (desde la fecha
de renovación si todavía no venció, para que pagar antes no quite días) y
publica el perfil si el plan lo incluye.

**No hay cobro recurrente automático.** Cada periodo se cobra con un enlace y
el abogado decide cada vez. Es más trabajo, pero no hay cargos sorpresa.

### Modo de pruebas

Con `WOMPI_MODE=sandbox` ningún cobro es real y la pantalla lo dice, con la
tarjeta de prueba a la vista. Las llaves van por `.env`; `wompi.md` está en el
`.gitignore` porque ni las de prueba deben ir al repositorio.

### Qué cubren los tests

`tests/Feature/WompiPaymentTest.php`:

- La pantalla de pago sale hacia Wompi con la firma de integridad correcta.
- Un evento aprobado pone el pedido en marcha.
- **Un evento sin firma válida no cambia nada** (401).
- Un evento repetido no entrega dos veces.
- Un pago rechazado deja el pedido sin pagar.
