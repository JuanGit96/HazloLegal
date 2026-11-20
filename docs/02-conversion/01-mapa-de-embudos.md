# Mapa de embudos

Hay cuatro caminos de entrada y todos terminan en uno de tres sitios: un
documento entregado, un caso en manos de un abogado, o un abogado suscrito.

```
                    ┌─ Búsqueda en Google ─┐
                    │                      │
      «derecho de petición gratis»   «cómo hacer una tutela»
                    │                      │
                    ▼                      ▼
            /gratis o la ficha          /blog/...
                    │                      │
                    └──────────┬───────────┘
                               ▼
                    ┌──── /contratar/{slug} ────┐
                    │  arma el trámite          │
                    │  + adicionales            │
                    └──────────┬────────────────┘
                               │
               ┌───────────────┴───────────────┐
         total = 0                        total > 0
               │                               │
               ▼                               ▼
      documento entregado              /pago → /pedido
               │                               │
               └───────────┬───────────────────┘
                           ▼
                  oferta del siguiente adicional
                           │
                           ▼
                  lead capturado (con permiso)
                           │
                           ▼
              ┌─ si autorizó ser contactado ─┐
              ▼                               ▼
     abogado suscrito lo ve            /caso-complejo
                                              │
                                     carrera entre abogados
```

## Los cuatro caminos

| Camino | Quién llega | Dónde entra | Dónde termina |
|---|---|---|---|
| **1. Documento gratis** | Busca el formato, no quiere pagar | `/gratis`, home, ficha | Documento entregado + lead |
| **2. Documento de pago** | Sabe lo que necesita | `/documentos`, ficha | Pedido pagado + PDF |
| **3. Caso complejo** | Su problema no cabe en un formato | `/caso-complejo` | Abogado asignado |
| **4. Abogado** | Quiere clientes | `/abogados/registro` | Suscripción |

Cada uno tiene su documento:

- [Embudo del documento gratis](02-embudo-documento-gratis.md)
- [Embudo del checkout](03-embudo-checkout.md)
- [Embudo del caso complejo](04-embudo-caso-complejo.md)
- [Embudo de abogados](05-embudo-abogados.md)

## Los puentes entre embudos

Lo que hace que el modelo funcione no son los embudos sueltos, sino los puentes
entre ellos:

| Puente | Desde | Hacia | Cómo |
|---|---|---|---|
| Calculadora → trámite | `/gratis/calculadoras/...` | Checkout | `related_document_id` ofrece el trámite tras el resultado |
| Artículo → trámite | `/blog/...` | Ficha | El artículo enlaza el trámite que explica |
| Gratis → de pago | Confirmación | Mismo pedido | `nextBestOffer()` ofrece **un** adicional después de entregar |
| Documento → abogado | Checkout | Directorio | Casilla «quiero que un abogado me contacte» |
| Instructivo → abogado | PDF | Directorio | El PDF lleva el nombre de un abogado suscrito |
| Área → directorio | `/tramites/{area}` | `/abogados` | Bloque de abogados del área |

## Dónde se mide

Panel → Tablero → «El embudo del modelo»:

- Documentos gratis del mes y **% que acabó pagando** — si no sube, lo gratuito
  es solo costo.
- Ingresos por adicionales — si no suben, el checkout cobra pero no persuade.
- MRR de suscripciones y cuántos están en prueba.
- Contactos compartibles.
