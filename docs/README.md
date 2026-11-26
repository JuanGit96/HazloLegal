# Documentación de HazloLegal

Esta carpeta existe para que alguien que llega sin contexto —una persona nueva
en el equipo o un modelo de lenguaje al que le pasan el repositorio— pueda
entender **qué es este negocio, cómo gana dinero y por qué el código está
escrito como está**, sin tener que deducirlo leyendo controladores.

No describe un plan: describe lo que hay construido a 19 de noviembre de 2026.
Donde algo está pendiente, lo dice.

## Por dónde empezar

Si solo vas a leer tres archivos, que sean estos, en este orden:

1. [Qué es HazloLegal](00-contexto/01-que-es-hazlolegal.md) — el producto en una página.
2. [Modelo de negocio](00-contexto/02-modelo-de-negocio.md) — de dónde sale el dinero.
3. [Arquitectura](01-arquitectura/01-vision-general.md) — cómo está organizado el código.

Y antes de tocar nada que se le muestre a un cliente, lee
[Reglas de honestidad](00-contexto/04-reglas-de-honestidad.md). No son estilo:
son exposición legal de la empresa, y hay tests que fallan si se rompen.

## Índice completo

### 00 · Contexto del negocio
| Archivo | Qué responde |
|---|---|
| [01-que-es-hazlolegal.md](00-contexto/01-que-es-hazlolegal.md) | Qué vende, a quién y en qué se diferencia |
| [02-modelo-de-negocio.md](00-contexto/02-modelo-de-negocio.md) | Las tres líneas de ingreso y por qué lo gratis se regala |
| [03-propuesta-de-valor.md](00-contexto/03-propuesta-de-valor.md) | La propuesta, los ganchos y las objeciones que responde |
| [04-reglas-de-honestidad.md](00-contexto/04-reglas-de-honestidad.md) | Lo que **nunca** se promete, y la norma colombiana detrás |

### 01 · Arquitectura
| Archivo | Qué responde |
|---|---|
| [01-vision-general.md](01-arquitectura/01-vision-general.md) | Stack, la regla que ordena todo, dónde vive cada cosa |
| [02-dominios.md](01-arquitectura/02-dominios.md) | Qué hace cada contexto y cada servicio |
| [03-precios-y-adicionales.md](01-arquitectura/03-precios-y-adicionales.md) | El motor de precios base + adicionales, con ejemplos |
| [04-base-de-datos.md](01-arquitectura/04-base-de-datos.md) | Las tablas que importan y por qué están así |
| [05-convenciones.md](01-arquitectura/05-convenciones.md) | Cómo se escribe el código aquí |

### 02 · Conversión
| Archivo | Qué responde |
|---|---|
| [01-mapa-de-embudos.md](02-conversion/01-mapa-de-embudos.md) | Todos los caminos de entrada, de inicio a fin |
| [02-embudo-documento-gratis.md](02-conversion/02-embudo-documento-gratis.md) | El imán: paso a paso, qué se captura y con qué permiso |
| [03-embudo-checkout.md](02-conversion/03-embudo-checkout.md) | El checkout con adicionales, pantalla por pantalla |
| [04-embudo-caso-complejo.md](02-conversion/04-embudo-caso-complejo.md) | La carrera por un caso entre abogados |
| [05-embudo-abogados.md](02-conversion/05-embudo-abogados.md) | Cómo se capta y se retiene a un abogado suscrito |
| [06-persuasion.md](02-conversion/06-persuasion.md) | Qué técnicas se usan, dónde, y por qué son legítimas |
| [07-firma-y-pagos.md](02-conversion/07-firma-y-pagos.md) | Firma electrónica por QR y cobros con Wompi |
| [08-area-de-cliente.md](02-conversion/08-area-de-cliente.md) | Por qué el acceso del cliente no tiene contraseña |

### 03 · SEO
| Archivo | Qué responde |
|---|---|
| [01-estrategia.md](03-seo/01-estrategia.md) | Cómo se pretende llegar al primer lugar y por qué |
| [02-implementacion.md](03-seo/02-implementacion.md) | Dónde está cada pieza en el código |
| [03-busquedas-con-ia.md](03-seo/03-busquedas-con-ia.md) | Aparecer en respuestas de ChatGPT, Perplexity y AI Overviews |
| [04-checklist-de-contenido.md](03-seo/04-checklist-de-contenido.md) | Qué revisar antes de publicar |

### 04 · Panel y accesos
| Archivo | Qué responde |
|---|---|
| [01-manual-del-panel.md](04-panel/01-manual-del-panel.md) | Todo lo que el panel de administración hace |
| [02-portal-del-abogado.md](04-panel/02-portal-del-abogado.md) | Qué ve y qué no ve un abogado suscrito |
| [03-accesos-y-permisos.md](04-panel/03-accesos-y-permisos.md) | Credenciales, quién entra a qué, y cómo se aísla |

### 06 · Los productos
| Archivo | Qué responde |
|---|---|
| [README.md](06-productos/README.md) | Las nueve especificaciones y qué parte está construida |

### 05 · Operación
| Archivo | Qué responde |
|---|---|
| [01-entorno-local.md](05-operacion/01-entorno-local.md) | Levantar el proyecto, comandos, compartir por ngrok |
| [02-pruebas.md](05-operacion/02-pruebas.md) | Qué cubren los tests y qué significa cada uno |
| [03-pendientes.md](05-operacion/03-pendientes.md) | Qué falta, con honestidad |

### 07 · Crecimiento
| Archivo | Qué responde |
|---|---|
| [01-crecimiento-organico.md](07-crecimiento/01-crecimiento-organico.md) | Por qué orgánico y no pauta, los tres bucles y qué se mide |
| [02-contenido-en-redes.md](07-crecimiento/02-contenido-en-redes.md) | Qué publicar en TikTok, Instagram, Facebook, LinkedIn y YouTube |

## Convención de estas páginas

- Todo lo que el usuario final lee va en **español de Colombia**; los
  identificadores de código, en inglés.
- Los montos son pesos enteros: `90000` es $90.000, no 900 pesos.
- Cuando un documento dice «ver `app/Domain/...`», esa ruta existe. Si no la
  encuentras, el código cambió y el documento quedó viejo: arréglalo.
