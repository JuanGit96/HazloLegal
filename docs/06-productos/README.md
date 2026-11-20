# Los nueve productos

Transcripción de las especificaciones que escribió el equipo. **Son la
fuente de verdad del producto**: si el código no coincide con esto, manda
esto.

Los nueve están construidos: cada uno con su formulario propio, su
documento —casillas de petición o plantilla, según el caso— y su
instructivo.

| # | Producto | Documento | Campos | Pasos del instructivo |
|---|---|---|---|---|
| [01](01-derecho-de-peticion-eps.md) | Derecho de petición a EPS | 11 casillas | 3 | 6 |
| [02](02-derecho-de-peticion-pensiones.md) | Petición a fondos de pensiones | 10 casillas | 2 | 5 |
| [03](03-contrato-arrendamiento-vivienda.md) | Arrendamiento de vivienda urbana | Plantilla | 15 | 5 |
| [04](04-contrato-prestacion-de-servicios.md) | Prestación de servicios | Plantilla | 11 | 5 |
| [05](05-acuerdo-de-confidencialidad.md) | Acuerdo de confidencialidad | Plantilla | 11 | 5 |
| [06](06-poder-especial-por-mensaje-de-datos.md) | Poder especial por mensaje de datos | Plantilla | 8 | 4 |
| [07](07-reclamacion-por-garantia-legal.md) | Reclamación por garantía legal | 6 casillas | 4 | 5 |
| [08](08-pqr-servicios-publicos.md) | PQR de servicios públicos | 6 casillas | 6 | 6 |
| [09](09-paquete-cumplimiento-pymes.md) | Cumplimiento de datos para pymes | Plantilla | 15 | 7 |

Las definiciones viven en `database/seeders/data/productos/` y se cargan
con `ProductSeeder`. Para ver cómo queda uno:

```
ddev artisan hl:demo derecho-de-peticion
```

> **La redacción jurídica es un primer borrador.** Las especificaciones
> marcan con `[MAICOL]` todo lo que un abogado tiene que aprobar antes de
> publicarlo, así que las casillas entran con `needs_lawyer_review` en
> true y el panel las muestra en ámbar hasta que alguien las apruebe.

## Dos decisiones sobre los documentos

**El semáforo vive en el instructivo, no en el checkout.** Avisarle a
alguien que su caso necesita una tutela cuando ya tiene el documento en la
mano —en la hoja que se va a llevar a la ventanilla— le sirve más que una
alerta que cerró de paso mientras compraba.

**La información de precio de las especificaciones se ignoró.** Los nueve
documentos traen su propia propuesta comercial; el modelo de precios del
sitio es el de base más adicionales y no se tocó.

## Qué pide cada especificación, y dónde vive

| Lo que pide la especificación | Dónde está |
|---|---|
| Casillas de petición con su texto exacto y variables | `document_clauses` |
| Fundamento legal por casilla, concatenado sin repetir | `ClauseComposer::compose()` |
| Fundamento común y cierre | `legal_documents.clause_common_basis` / `clause_closing` |
| Semáforo: preguntas y palabras | `document_risk_rules` + `RiskAssessor` |
| Plazo que cambia según la casilla | `document_clauses.response_deadline_days` |
| Campos del formulario propios del trámite | `legal_documents.template_fields` |
| Precios por plan | Precio base + adicionales |

## Lo que falta

- Pintar las casillas en el checkout: hoy se arman por código, no desde la
  pantalla de compra.
- Contador del plazo legal con calendario de festivos, prórroga y
  requerimiento.
- Expediente PDF (petición + radicado + constancia de no respuesta).
- Avisos a mitad de plazo y al vencer.
- Lista de palabras prohibidas en la interfaz, verificada automáticamente.

## Sobre `extraer-pdf.py`

El script con el que se transcribieron estos PDF. Resuelve tres cosas que
hacen ilegible un PDF generado: las fuentes en subconjunto (el texto son
identificadores de glifo, no letras), la posición vertical que vive en el
operador `cm` y no en el texto, y los espacios, que no existen y hay que
deducir de las coordenadas.

**No sirve para PDF escaneados**: ahí no hay texto, hay una imagen.
