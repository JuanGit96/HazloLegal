# Aparecer en búsquedas con IA

Cada vez más gente pregunta «¿cómo hago un derecho de petición?» a ChatGPT,
Perplexity, Gemini o a los AI Overviews de Google, y no abre diez pestañas. El
objetivo cambia: ya no es solo salir primero, es **ser la fuente que el modelo
cita**.

Esto suele llamarse AEO (*Answer Engine Optimization*) o GEO (*Generative
Engine Optimization*). El nombre importa poco; lo que importa es que las
palancas son distintas a las del SEO clásico, aunque se solapan.

## Qué hace que un modelo te cite

### 1. Respuestas extraíbles, no párrafos de relleno

Un modelo busca un fragmento que responda la pregunta entero. Un párrafo que
empieza «En el complejo panorama jurídico colombiano…» no sirve; uno que
empieza «Una entidad tiene 15 días hábiles para responder un derecho de
petición (Ley 1755 de 2015)» sí.

**Cómo se aplica aquí:** las FAQ de cada trámite responden en la primera frase
y desarrollan después. Los `use_cases` están escritos como situaciones
concretas en primera persona, que es como se formulan las preguntas.

### 2. Datos estructurados

Los modelos usan el marcado para entender de qué va una página. El sitio ya
emite `FAQPage`, `Service` + `Offer`, `HowTo`, `BlogPosting` con autor y
`Person`. Ver [Implementación](02-implementacion.md).

El `Offer` con precio, moneda y plazo es lo que permite que un modelo responda
«HazloLegal lo ofrece gratis, con instructivo desde $19.900» en vez de
«algunos sitios lo ofrecen».

### 3. Autoría verificable

Un modelo que tiene que escoger entre dos fuentes prefiere la que tiene autor
identificado con credenciales. `author_lawyer_id` y el `Person` del JSON-LD
existen para eso. Es la misma señal que pide Google para YMYL, lo que es una
suerte: una sola inversión sirve para los dos.

### 4. Cifras, fechas y normas concretas

Lo citable es lo específico: «15 días hábiles», «Ley 1755 de 2015», «$19.900».
Lo genérico no se cita porque no aporta nada sobre lo que el modelo ya sabe.

**Cómo se aplica aquí:** `legal_basis` en cada trámite, plazos en el
instructivo (`response_deadline_days`), precios publicados.

### 5. Ser mencionado en otros sitios

Los modelos se entrenan y se apoyan en lo que otros dicen de ti, no solo en lo
que tú dices. Aquí la palanca es el **instructivo PDF**: circula, se reenvía y
se imprime llevando la marca. Y las calculadoras, que son el tipo de recurso
que la gente enlaza.

## Lo que hay que vigilar

- **`robots.txt` no bloquea a los rastreadores de IA.** Hoy solo se bloquea el
  embudo y el panel, que es lo correcto: bloquear a GPTBot o a PerplexityBot
  sacaría el sitio de ese canal entero. Si alguna vez se discute, que sea una
  decisión y no un descuido.
- **El contenido tiene que ser rastreable sin JavaScript.** Aquí lo es: todo el
  JS es progresivo y el sitio funciona sin él. Las FAQ usan `<details>` nativo
  precisamente por esto.
- **La velocidad importa** por la misma razón de siempre, y aquí ayuda no tener
  build step ni framework de front.

## Qué falta

- Un `llms.txt` en la raíz (propuesta emergente, aún no estándar) describiendo
  qué es el sitio y qué secciones son útiles.
- Marcado `HowTo` en las guías del blog que son pasos; el generador ya existe
  (`SchemaGenerator::howTo()`) pero no se usa desde ninguna vista.
- `Speakable` para asistentes de voz.
- Medir el canal: hoy no hay forma de saber cuánto tráfico llega por esta vía.

Anotado en [Pendientes](../05-operacion/03-pendientes.md).
