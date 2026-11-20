# El área del cliente

`/mis-tramites` · sin contraseña.

## Por qué sin contraseña

Una contraseña aquí añadiría fricción justo donde no hay nada que ganar:

- **No guardamos medios de pago.** Lo que protege la cuenta son unos PDF que
  ya están en el correo del cliente.
- **El correo ya está verificado.** Ahí le llegaron los documentos; demostrar
  que es suyo demuestra lo mismo que una contraseña.
- **Cada contraseña es una cosa más que se puede filtrar**, y además genera
  soporte: «olvidé mi clave» es el ticket más común de cualquier sitio.

Así que: el cliente escribe su correo, recibe un enlace firmado y entra. Como
Slack o Notion para quien no quiere crear cuenta.

## El flujo

```
/mis-tramites
     │ escribe su correo
     ▼
Se manda un enlace firmado (Laravel), válido 30 minutos
     │
     ▼
/mis-tramites/{correo}?signature=…
     │
     ▼
Lista de sus pedidos, con los PDF para volver a descargar
```

## Decisiones que conviene conocer

- **La respuesta es la misma exista o no el correo.** Decir «ese correo no
  tiene pedidos» dejaría averiguar quién nos compró y quién no.
- **El enlace vence en 30 minutos.** Suficiente para abrir el correo, poco
  para que quede vivo en una bandeja compartida.
- **La firma es de Laravel** (`URL::temporarySignedRoute`), así que no se
  puede fabricar sin la `APP_KEY`.
- **Se ofrece después de comprar**, no antes. Pedir registro antes de entregar
  es la forma más rápida de perder a alguien que venía por algo gratis; una
  vez tiene su documento, guardar el acceso es un favor que le hacemos.

## Dónde aparece

| Sitio | Qué dice |
|---|---|
| Menú superior | «Soy cliente» |
| Pie | «Mis trámites» |
| Confirmación del pedido | «¿Quieres volver a esto más adelante?» |

## Qué cubren los tests

`tests/Feature/CustomerAreaTest.php`:

- Pide el enlace y le llega.
- **Un correo sin pedidos recibe la misma respuesta** y no se manda nada.
- El enlace firmado abre sus trámites.
- Sin firma, 403.
- Vencido, 403.
- **No se ven los trámites de otro.**

## Lo que falta

- Un histórico de los casos complejos, no solo de los pedidos.
- Poder pedir la eliminación de datos desde aquí, en vez de ir al formulario.
