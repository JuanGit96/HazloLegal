# Checklist antes de publicar

## Un trámite nuevo

- [ ] `name`, `short_name`, `slug` en español y sin tildes en el slug
- [ ] `practice_area` correcta: decide en qué landing de área aparece
- [ ] `tagline`: la promesa en una línea
- [ ] `summary`: 2-3 frases, es lo que se lee en las tarjetas
- [ ] `legal_basis`: la norma que lo sustenta
- [ ] `base_price` y, si aplica, `compare_at_price` **real**
- [ ] `self_service_enabled`: ¿se puede pedir sin abogado sin hacer daño?
- [ ] `delivery_hours`
- [ ] `what_you_get` — qué recibe
- [ ] `required_info` — qué hay que pedirle
- [ ] `not_included` — **qué NO incluye**, obligatorio
- [ ] `use_cases` — situaciones concretas, en primera persona
- [ ] `faqs` — al menos 4, respondiendo en la primera frase
- [ ] `body` — mínimo 600 palabras. Sin esto no posiciona
- [ ] `seo_title` (≤ 60-65 caracteres) y `seo_description` (~155)
- [ ] `focus_keyword` — la búsqueda exacta que se quiere ganar
- [ ] `template_body` — el cuerpo del PDF con sus marcadores
- [ ] `filing_steps`, `filing_entity`, `response_deadline_days` — para el instructivo
- [ ] Adicionales asociados con su precio (Matriz de precios)
- [ ] ¿Está en el sitemap? (los trámites sí, automáticamente)

## Un artículo

- [ ] `author_lawyer_id` — **obligatorio**. Es la señal E-E-A-T principal
- [ ] `title` que contenga la búsqueda real
- [ ] `excerpt` de 1-2 frases
- [ ] Primer párrafo que responda la pregunta **sin preámbulo**
- [ ] Normas citadas con número y año
- [ ] Enlace al trámite que resuelve lo que explica
- [ ] `seo_title`, `seo_description`
- [ ] Categoría asignada
- [ ] Si son pasos, considerar marcado `HowTo`

## Una calculadora

- [ ] `key` con fórmula implementada en `LaborCalculator`. Una clave sin
      fórmula no calcula nada
- [ ] `config` con los parámetros **del año en curso**
- [ ] `related_document_id` — el puente hacia lo que se cobra
- [ ] `body` explicando la fórmula y citando la norma
- [ ] `faqs`

## Un abogado en el directorio

- [ ] `headline` — especialidad en una línea
- [ ] `bio` corta y `long_bio` larga
- [ ] `practice_areas` — decide qué casos y contactos recibe
- [ ] `city`
- [ ] Foto
- [ ] `is_published` solo si su plan incluye perfil público
- [ ] **NUNCA** publicar `professional_card`

## Revisión final de cualquier texto de cara al cliente

- [ ] ¿Promete que un abogado revisa cada documento? → **quitar**
- [ ] ¿Promete un resultado judicial? → **quitar**
- [ ] ¿Menciona alternativas gratuitas y está dentro del embudo? → **mover al blog**
- [ ] ¿Alguna cifra que no sea real? → **quitar**
- [ ] ¿Español de Colombia, tuteando?
- [ ] ¿Los montos en pesos enteros y con separador de miles?
- [ ] `ddev artisan test` en verde
