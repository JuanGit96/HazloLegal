# Reglas de honestidad del producto

Esto no es una guía de estilo. Es la exposición legal de la empresa y la de los
abogados que aparecen en el sitio. `tests/Feature/PublicSeoTest.php` tiene
pruebas que fallan si alguna de estas reglas se rompe.

## 1. Nunca prometer que un abogado revisa cada documento

En los trámites automatizados **no ocurre**. Lo que sí se afirma, y es cierto:

> El **formato** lo escribieron abogados colombianos y se mantiene actualizado
> cuando cambia la norma.

Prometer una revisión individual que no se hace es publicidad engañosa, y
además comprometería personalmente al abogado nombrado ante una eventual
reclamación.

**Dónde se vigila:** `test_el_sitio_no_promete_revision_de_cada_documento`.
El correo de entrega de un documento gratuito lo dice explícitamente:
«Este trámite es automatizado: no pasa por la revisión individual de un
abogado».

## 1 bis. Nunca llamar «IA» al corrector de redacción

`DocumentReviewer` es PHP con reglas deterministas: expresiones regulares y
listas. No hay ningún modelo detrás.

Mientras siga así, ningún texto del sitio puede decir que una inteligencia
artificial revisa el documento. Lo que sí se afirma, y es cierto:

> Revisamos ortografía, tono y puntuación.

El propio servicio lleva el aviso en el encabezado del archivo, y el campo del
panel lo repite en su texto de ayuda. Si algún día se integra un modelo de
verdad, hay además una consecuencia legal: el texto del caso del cliente
saldría hacia un tercero, y eso **tiene que estar en la política de
tratamiento de datos** antes de encenderlo.

## 2. Nunca publicar números de tarjeta profesional

El campo `lawyers.professional_card` existe **solo para control interno**.
Sacarlo al sitio comprometería al abogado nombrado con una revisión individual
que no hace.

**Dónde se vigila:** `test_la_tarjeta_profesional_nunca_se_publica` recorre la
home, «Nosotros», el formulario de caso complejo y un artículo del blog, y
comprueba que no aparezca ni el número ni las frases «Consejo Superior de la
Judicatura» ni «tarjeta profesional».

En el panel, la columna está marcada «(interno)» con un tooltip que lo recuerda,
y en el portal del abogado **ni siquiera es editable**.

## 3. Nunca prometer un resultado judicial

Se garantiza la calidad del documento, no la decisión de un juez o de una
entidad. El Certificado de Garantía dice explícitamente qué no cubre:

> cambios de opinión sobre lo que querías pedir, información que no nos diste,
> y el resultado de la decisión de un juez o de una entidad — eso no lo
> controla ningún abogado.

## 4. Decir qué NO incluye cada trámite, antes de cobrar

Cada documento tiene `not_included`, que se pinta en su ficha bajo «⚠️ Qué no
incluye» con la línea «Lo decimos antes de que pagues, no después».

## 5. No inventar testimonios, cifras ni abogados

Ley 1480 de 2011 (Estatuto del Consumidor). La SIC sanciona la publicidad
engañosa. Los datos de demostración del seeder van marcados como tales en el
propio archivo (`DemoActivitySeeder`, `DemoSubscriptionSeeder`).

Un precio tachado solo es legítimo si ese precio fue real alguna vez. El campo
de descuento en el panel lo dice en su texto de ayuda, y el interruptor
`promo_enabled` permite apagar todos los precios tachados de un golpe.

## 6. No nombrar alternativas gratuitas dentro del embudo

Mencionar la IA o las plantillas gratis en la home le planta la idea a quien no
la traía. Ese contenido va en el blog, donde llega quien ya la está buscando.

**Dónde se vigila:** `test_la_home_no_nombra_alternativas_gratuitas`.

## 7. Tratamiento de datos: autorización expresa y separada

Ley 1581 de 2012. En la práctica:

- Para entregar cualquier documento —gratis o pago— se pide la autorización de
  tratamiento de datos, con enlace a la política. Es obligatoria.
- «Quiero que un abogado me contacte» y «quiero recibir guías por correo» son
  **dos casillas distintas y opcionales**. Autorizar una no autoriza la otra.
- Se guarda `consented_at` y `consent_policy_version`: qué autorizó y bajo qué
  versión de la política.
- Revocar es un clic desde el panel y deja al contacto fuera de todo.
- Cada vez que un abogado abre los datos de un contacto queda registrado en
  `lead_accesses` con fecha e IP.

**Dónde se vigila:** `test_el_consentimiento_de_abogados_es_una_casilla_aparte`,
`test_el_consentimiento_de_datos_queda_registrado_tal_como_lo_marco_el_cliente`
y `test_solo_ve_contactos_que_autorizaron_y_de_sus_areas`.

## 8. Los datos del cliente no se filtran a quien no ganó el caso

En la carrera por un caso complejo, los datos de contacto del cliente solo
existen para el abogado que aceptó primero. Los que llegaron tarde ven que ya
fue tomado y nada más.

**Dónde se vigila:**
`test_el_segundo_abogado_que_llega_ve_que_ya_fue_tomado_y_no_recibe_datos` y,
en el portal, `test_solo_ve_los_casos_que_el_mismo_gano`.

## 9. Un pedido no se abre con la referencia sola

`HL-2026-00042` es correlativa y por tanto adivinable. Las páginas de pedido y
las descargas exigen el token de acceso del enlace o haber hecho el pedido en
esa sesión.

**Dónde se vigila:** `test_la_referencia_sola_no_abre_el_pedido_de_otro`.

---

## Qué hacer si una de estas reglas estorba

Hablarlo, no esquivarlo. Si el negocio decide cambiar una, se cambia aquí, se
cambia el test, y queda constancia de que fue una decisión y no un descuido.
