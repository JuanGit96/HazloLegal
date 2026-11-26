# Crecimiento orgánico

Este archivo explica **cómo llega la gente a HazloLegal sin pagar por cada
clic**, y por qué esa es la única vía que tiene sentido para este negocio.

El SEO tiene su propio capítulo: [03-seo/01-estrategia.md](../03-seo/01-estrategia.md).
Aquí está el resto —el motor completo, las redes y el ritmo de trabajo—, y
cómo encajan unos con otros.

## Por qué orgánico y no pauta

No es ideología, es aritmética. Un derecho de petición se vende en $0 (gratis,
como imán) y el pedido medio con adicionales ronda los $70.000. El valor de
vida de un cliente de documentos es bajo y la recompra es esporádica: nadie
necesita una tutela todos los meses.

Con esos números, **un CPA de $25.000 en Meta se come el margen entero**. La
pauta sirve para dos cosas concretas y acotadas —validar un gancho nuevo en
una semana, y defender una búsqueda de marca— no para sostener el volumen.

Lo que sí escala sin costo marginal:

1. Una ficha de trámite que posiciona trae visitas durante años.
2. Un instructivo en PDF circula impreso, se fotografía y se reenvía por
   WhatsApp sin que nosotros hagamos nada.
3. Un abogado suscrito tiene audiencia propia y le conviene compartirnos.

## Los tres bucles que se refuerzan

### Bucle 1 · Búsqueda → documento gratis → correo

Alguien busca «derecho de petición EPS formato». Encuentra la ficha, genera el
documento gratis, y a cambio deja correo con autorización de tratamiento de
datos. Ese correo recibe guías y, cuando tiene un problema que sí se cobra,
vuelve.

**El gratis no es generosidad: es la puerta de entrada.** Lo que lo cierra es
que el documento sirva de verdad, porque si no sirve no hay segunda visita.

### Bucle 2 · El instructivo que circula

Cada documento entregado sale con un instructivo PDF: dónde radicarlo, qué
anexar, qué plazo tiene la entidad. Ese PDF **se imprime y se lleva a una
ventanilla**, se reenvía al hermano que tiene el mismo problema, se sube a un
grupo de WhatsApp de inquilinos.

Es distribución física y no cuesta nada. Por eso el instructivo lleva la marca
y el nombre de un abogado suscrito: es la colocación más valiosa que tenemos y
la que ningún competidor puede copiar sin entregar documentos.

Se mide: `daily_metrics` con la métrica `pdf_placement`.

### Bucle 3 · El abogado como canal

Un abogado suscrito no es solo un cliente: tiene audiencia, firma artículos y
le conviene que su perfil circule.

- Firma una guía → la guía posiciona por su autoría acreditada (señal E-E-A-T)
  → el perfil recibe visitas → el abogado ve la cifra en su portal → renueva.
- Comparte su perfil en sus redes → trae su audiencia a nuestro dominio.

**Este bucle es el que más se descuida y el que más compone.** Cada abogado
que escribe una guía nos da un activo permanente a cambio de visibilidad, que
es justo lo que vino a comprar.

## El contenido en una frase

> Le ponemos nombre y plazo a un problema que la persona ya tiene.

Nadie busca «asesoría jurídica». Busca «me negaron el medicamento», «me llegó
una fotomulta que no era mía», «cuánto me pueden subir el arriendo». El
contenido que funciona **nombra la situación exacta y dice qué se puede hacer
esta semana**.

Tres ingredientes, siempre:

| Ingrediente | Por qué |
|---|---|
| La situación concreta, en las palabras del afectado | Es lo que se busca y lo que se reconoce |
| El nombre jurídico y el plazo | Es lo que no sabían y lo que convierte angustia en tarea |
| Un paso que se puede dar hoy | Sin esto es contenido de divulgación, no de captación |

## El encadenado de reutilización

Se escribe una vez y se corta cinco. Partir de cero cada semana en cinco redes
es lo que hace que un equipo pequeño abandone a los dos meses.

```
1 guía del blog (la pieza madre, con abogado que la firma)
  ├── 1 video largo de YouTube  → el mismo contenido, en pantalla
  ├── 3 videos verticales       → TikTok, Reels y Shorts
  ├── 1 carrusel                → Instagram y Facebook
  ├── 1 publicación de LinkedIn → en clave de red de abogados
  └── 1 correo a la lista       → el gancho, al documento gratis
```

Ritmo sostenible para un equipo pequeño: **una pieza madre por semana**. Eso
da cuatro guías al mes, dieciséis verticales y cuatro videos largos. Es más
que suficiente; el problema nunca es el volumen, es la constancia.

## Qué se mide y dónde

| Pregunta | Dónde mirar |
|---|---|
| ¿De dónde llegan los correos? | `leads.source` y los campos UTM |
| ¿Qué guía trae tráfico? | `posts.views_count` y `daily_metrics` con `article_view` |
| ¿Qué perfiles se abren? | `daily_metrics` con `profile_view` y `directory_impression` |
| ¿Circulan los instructivos? | `daily_metrics` con `pdf_placement` |
| ¿El embudo convierte? | Panel → Resumen del negocio |

Las métricas de `daily_metrics` son **agregados diarios sin identificar a
nadie**: ni IP ni identificador. Eso no es un recorte, es lo que permite
contarlas también para quien rechazó las cookies —la mayoría— y por eso las
cifras del portal del abogado no mienten por la mitad.

Enlaza siempre con UTM cuando publiques fuera:

```
https://hazlolegal.co/documentos/derecho-de-peticion?utm_source=tiktok&utm_medium=organico&utm_campaign=eps-medicamento
```

## Lo que no se hace

Estas reglas no son de estilo. La SIC sanciona varias de ellas, y las demás
destruyen la confianza que es todo el activo:

- **No prometer resultados judiciales.** Ni en un video, ni en un comentario.
  Se garantiza la calidad del documento, nunca la decisión de un juez.
- **No decir que un abogado revisa cada documento.** En los trámites
  automatizados no ocurre. Lo que se afirma es que el *formato* lo escribieron
  abogados.
- **No inventar testimonios ni cifras.** Es publicidad engañosa.
- **No publicar números de tarjeta profesional**, ni en redes.
- **No usar casos de clientes sin autorización escrita**, y aun con ella,
  anonimizados. Hay reserva profesional de por medio.
- **No comprar seguidores ni listas de contactos.** Un contacto que no
  autorizó el tratamiento de sus datos no se puede usar: Ley 1581 de 2012.
- **No usar la tragedia ajena como gancho.** Funciona una vez y quema la marca
  para siempre en un sector donde la confianza es el producto.
- **No automatizar mensajes directos.** Además de que las plataformas lo
  penalizan, un contacto no solicitado es exactamente lo que nos diferencia de
  los que llaman a la gente tras un accidente.

## Dónde seguir

- [Contenido en redes](02-contenido-en-redes.md) — qué publicar en cada una y
  por qué.
- [Estrategia SEO](../03-seo/01-estrategia.md) — el bucle de búsqueda.
- [Reglas de honestidad](../00-contexto/04-reglas-de-honestidad.md) — lo que
  nunca se promete.
