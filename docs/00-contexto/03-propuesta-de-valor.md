# Propuesta de valor y ganchos

## La promesa, en una frase

> El documento legal que necesitas, escrito por abogados colombianos, listo
> para radicar, con el precio a la vista antes de empezar.

Todo lo demás son variaciones de eso adaptadas a quién llega y con qué
intención.

## Las cuatro objeciones y qué las responde

Quien llega a un sitio legal no está comparando funcionalidades: está
desconfiando. Cada elemento del sitio responde a una objeción concreta.

| Lo que piensa | Qué lo responde | Dónde está |
|---|---|---|
| «¿Esto sirve de verdad o es una plantilla de internet?» | Fundamento legal citado, formato escrito por abogados y actualizado cuando cambia la norma | Ficha del trámite, `legal_basis` |
| «¿Cuánto me va a costar al final?» | Precio publicado antes de pedir datos, total en vivo en el checkout | `/documentos`, `.price-live` |
| «¿Y si lo rechazan?» | Certificado de Garantía: corrección sin costo o devolución del 100 % | `/garantia`, `config/hazlolegal.php` |
| «¿Y después qué hago con esto?» | Instructivo de radicación, con plazos y qué hacer si no responden | Adicional `instructivo` |

## Los ganchos

Un gancho es la frase corta que hace que alguien dé el primer paso. Los de
HazloLegal son estos, y todos son administrables desde el panel
(`Ajustes del sitio → Ganchos y textos`) porque son lo primero que hay que
probar cuando la conversión no sube.

### «Gratis, de verdad»

No es una prueba de 7 días ni una versión recortada: el documento sale completo
y es del cliente. Esto se dice explícitamente porque el escepticismo por
defecto es que «gratis» esconde algo.

El contrapeso honesto va en la misma pantalla: para entregar el documento se
pide el correo y la autorización de tratamiento de datos. Se dice antes, no
después.

### «Sin vacíos legales · Nuestra revisión corrige ortografía y redacción»

El texto que escribe el cliente pasa por `DocumentReviewer`
(`app/Domain/Documents/Services/DocumentReviewer.php`), que normaliza espacios,
arregla el texto en mayúsculas sostenidas, quita groserías y muletillas,
corrige el uso de mayúsculas al inicio de frase y la puntuación, y avisa de
problemas de contenido (falta de fechas, falta de números de radicado, texto
demasiado corto).

Lo que **no** hace, y por eso no se promete: no inventa contenido ni cambia los
hechos que el cliente contó. El correo de entrega lo dice con esas palabras.

Este gancho se puede apagar entero desde `Ajustes → hook_ai_enabled`.

### «Tú armas el trámite. Pagas solo lo que le sumes»

Contra la sospecha de que los paquetes traen cosas que uno no necesita. Cada
adicional muestra su precio antes de pagar y quitarlo es un clic — hay un test
que comprueba que el precio se ve (`PublicSeoTest`).

### «Precio fijo publicado»

La comparación de la home es **solo contra la vía tradicional**, nunca contra
plantillas gratis ni contra IA. Mencionar una alternativa gratuita dentro del
embudo le planta la idea a quien no la traía. Ese contenido va en el blog,
donde llega quien ya la está buscando. Hay un test que lo vigila
(`test_la_home_no_nombra_alternativas_gratuitas`).

## Prueba social

Toda cifra que se muestre tiene que ser real:

- `hook_free_count` (documentos generados) se edita en el panel y su propia
  ayuda dice: «Debe reflejar el número real. Un dato inflado es publicidad
  engañosa».
- Los testimonios del seeder están marcados como datos de demostración.
- La prueba social de un adicional («7 de cada 10 lo agregan») es un campo
  libre, pero la ayuda del formulario advierte que debe reflejar un número
  real.

Inventarlas es publicidad engañosa bajo la Ley 1480 de 2011 y la SIC la
sanciona. Ver [Reglas de honestidad](04-reglas-de-honestidad.md).

## El tono

Español de Colombia, directo, sin jerga jurídica cuando se le habla al cliente
y con la norma citada cuando aporta confianza. Se tutea. Se dice «te
respondemos», no «se le dará respuesta».

Y se dice lo que **no** incluye cada trámite antes de cobrar, no después: cada
documento tiene un campo `not_included` que se pinta en su ficha bajo el título
«Qué no incluye».
