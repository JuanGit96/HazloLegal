# PRODUCTO 01 · DERECHO DE PETICIÓN A EPS

> Transcripción del PDF que escribió el equipo. Es la fuente de verdad
> del producto: si algo en el código no coincide con esto, manda esto.

HazloLegal · Versión 1.0 · 12 de noviembre de 2026
Este es el producto modelo. Los demás sectores se construyen copiando esta estructura y
cambiando el directorio, las casillas, el fundamento y el semáforo.
Lo marcado [MAICOL] requiere que un abogado confirme o redacte el contenido antes de
publicar.
1. ALCANCE: QUÉ VENDE Y QUÉ NO
Qué entrega: el escrito de derecho de petición armado con los datos del usuario, el
instructivo para radicarlo ante su EPS, el contador del plazo legal, un aviso a mitad de
término y otro al vencer, y el expediente en PDF con la petición, el radicado y la constancia
d e no respuesta.
Qué NO entrega: no radica por el usuario, no representa al usuario ante la EPS, no
garantiza que la EPS autorice lo pedido, y no reemplaza la asesoría de un abogado.
Advertencia que va visible antes de pagar, en texto plano:
“El derecho de petición es gratuito y usted puede presentarlo solo, sin abogado. Lo que
usted paga aquí es el acompañamiento: el documento armado, el instructivo de radicación,
el conteo del plazo y el aviso cuando se venza.”
Precio: gratis la generación; $19.900 con instructivo, contador y expediente; $129.900 con
revisión de abogado en 48 horas hábiles; $189.900 express el mismo día hábil.
2. FORMULARIO
Paso 1 · Quién pide
—  Calidad en que actúa: nombre propio / representante legal / agente oficioso / apoderado.
—  Nombre completo.
—  Tipo y número de documento.
—  Correo (se valida con enlace antes de generar).
—  Teléfono.
—  Dirección de notificación y ciudad.
—  ¿Acepta notificación electrónica? Sí / No.
Paso 2 · Para quién es el servicio
—  ¿La petición es para usted o para otra persona?
—  Si es para otra persona: nombre, documento y parentesco, más el documento que
respalda la calidad (poder o justificación de agencia oficiosa). Sin ese documento no se
genera.
Paso 3 · A quién va dirigida
—  EPS (lista del directorio) u “Otra entidad” con campo libre.
—  Número de afiliación, si lo tiene.
—  Régimen: contributivo / subsidiado / no sabe.

Paso 4 · Qué pide
—  Casillas de peticiones frecuentes (sección 3).
—  Petición libre, máximo 400 caracteres.
Paso 5 · Qué pasó
—  Bloque repetible: fecha + hecho. Mínimo 1, máximo 15, 300 caracteres cada uno. El
sistema los ordena y numera por fecha.
—  Anexos: lista de nombres.
Paso 6 · Autorizaciones
—  Casilla 1 (necesaria) y casilla 2 de datos sensibles (facultativa), según la pestaña de
Autorizaciones.
Paso 7 · Revisión
—  Se muestra el escrito completo. El usuario lo lee y pulsa “Apruebo este texto”. Queda
registro con fecha y hora. Sin esa aprobación no se genera el PDF.
3. PETICIONES FRECUENTES · TEXTO EXACTO
Este es el texto que entra al escrito palabra por palabra, con las variables entre llaves.
[MAICOL: aprobar o ajustar la redacción de cada una.]
1. Autorizar el procedimiento {procedimiento} ordenado por el médico {nombre del médico}
el {fecha de la orden}, e informarme la fecha, la hora y el lugar en que se practicará.
2. Entregar los medicamentos {medicamentos} formulados el {fecha de la fórmula}, e
indicarme el punto de dispensación y la fecha de entrega.
3. Asignar cita con especialista en {especialidad}, ordenada el {fecha de la orden}, indicando
fecha, hora, profesional y dirección.
4. Autorizar el transporte, y de ser necesario el alojamiento y la alimentación, para el
paciente y un acompañante, con destino a {ciudad o IPS}.
5. Entregar copia íntegra de mi historia clínica, incluyendo {periodo o servicio}, por el medio
que resulte más expedito.
6. Reconocer y pagar las incapacidades correspondientes al periodo comprendido entre el
{fecha inicial} y el {fecha final}, e informarme el estado del trámite.
7. Reconocer y pagar la licencia de {maternidad o paternidad} correspondiente al nacimiento
ocurrido el {fecha}.
8. Reembolsar la suma de {valor} que asumí directamente por {concepto} el {fecha},
conforme a los soportes anexos.

9. Informar el estado del trámite de calificación de pérdida de capacidad laboral iniciado el
{fecha}, e indicar los pasos y términos que siguen.
10. Autorizar el traslado a la EPS {EPS destino} / autorizar la portabilidad del servicio en el
municipio de {municipio}.
11. Petición libre (máximo 400 caracteres, redactada por el usuario).
Cierre que se agrega siempre, sin importar las casillas marcadas:
“Solicito que la respuesta se me notifique a la dirección y al correo señalados en el acápite
de notificaciones, dentro del término legal.”
4. FUNDAMENTO DE CADA CASILLA
Fundamento común, en todas:
Artículo 23 de la Constitución Política. Artículos 13 a 33 de la Ley 1755 de 2015. Artículo 33
de la Ley 1755, que somete a las entidades del Sistema de Seguridad Social Integral a las
mismas reglas del derecho de petición ante autoridades.
Fundamento adicional según la casilla: [MAICOL: confirmar los artículos y añadir los que
falten]
—  Casillas 1, 2, 3 y 4: Ley 1751 de 2015, estatutaria de salud, en lo relativo a la
oportunidad, la continuidad y la integralidad del servicio.
—  Casilla 5: régimen de la historia clínica. Al ser petición de documentos, el plazo es de 10
días hábiles.
—  Casillas 6 y 7: normas sobre reconocimiento y pago de prestaciones económicas.
—  Casilla 9: normas sobre calificación de pérdida de capacidad laboral.
—  Casilla 10: normas sobre libre elección, traslado y portabilidad.
El motor arma el párrafo de fundamento concatenando el común más los adicionales de las
casillas marcadas, sin repetir normas.
5. SEMÁFORO
El semáforo no adivina. Funciona en dos capas: preguntas estructuradas, que son la capa
principal, y una lista de palabras, que es la red de seguridad sobre el texto libre.
CAPA 1 · PREGUNTAS ESTRUCTURADAS
Van en el paso 3 del formulario, antes de redactar.
¿El paciente está hospitalizado o en urgencias en este momento? →  ROJO
¿El servicio que le negaron lo necesita en menos de 48 horas? →  ROJO
¿Tiene diagnóstico de cáncer, enfermedad huérfana, o requiere diálisis o trasplante? →
ROJO
¿El paciente es menor de 18 años? →  AMARILLO (ROJO si se combina con cualquiera de
las anteriores)
¿Está embarazada o en posparto? →  AMARILLO

¿Lleva más de 180 días de incapacidad acumulada? →  AMARILLO
¿Ya presentó un derecho de petición por estos mismos hechos y no le respondieron? →
AMARILLO
¿El valor del reembolso supera $2.000.000? →  AMARILLO
CAPA 2 · PALABRAS EN EL TEXTO LIBRE
Se revisan los campos de hechos y de petición libre. Una coincidencia no bloquea sola:
dispara la pregunta estructurada correspondiente, para que sea el usuario quien confirme.
Rojo: urgencias, UCI, hospitalizado, cáncer, tumor, quimioterapia, radioterapia, trasplante,
diálisis, cuidados paliativos, riesgo de muerte, se está muriendo, no puede respirar.
Amarillo: embarazo, parto, recaída, 180 días, junta de calificación, tutela, desacato, segunda
vez, ya reclamé.
Bloqueo por respeto: lista de groserías y amenazas, mantenida en archivo aparte. No se
publica aquí. Una coincidencia impide generar hasta que el usuario reescriba.
CALIBRACIÓN
Una señal amarilla: amarillo. Dos o más señales amarillas: rojo. Cualquier señal roja: rojo,
sin importar lo demás. [MAICOL: confirmar esta calibración.]
6. QUÉ HACE CADA COLOR
VERDE
Genera y cobra. Sin advertencias adicionales.
AMARILLO
Genera, pero antes muestra la advertencia y exige que el usuario la acepte expresamente.
Ofrece el plan acompañado como alternativa, sin obligar.
Texto del aviso amarillo:
“Por lo que nos cuenta, su caso tiene elementos que pueden complicarlo. Puede continuar
con el documento estándar si lo prefiere. También puede pedir que un abogado lo revise
antes de enviarlo.”
ROJO
No genera el documento estándar. Muestra las tres salidas, siempre las tres y en este
orden:
“Por lo que nos cuenta, su caso necesita algo más rápido que un derecho de petición.
Cuando hay riesgo para la vida o la salud, la acción de tutela es el camino, y el juez debe
fallar en 10 días.
1. Puede presentarla usted mismo. La tutela es gratuita, no necesita abogado y puede
presentarse por escrito o de forma verbal, sin autenticación.
2. Puede acudir gratis a la Defensoría del Pueblo, a la personería de su municipio o a un
consultorio jurídico universitario, que lo ayudan sin costo.

3. Si prefiere que alguien la redacte y haga el seguimiento, aquí tiene abogados en su
ciudad.”
La opción gratuita nunca se oculta, nunca aparece de última y nunca se muestra en letra
más pequeña que la del directorio.
7. PALABRAS PROHIBIDAS EN TODA LA INTERFAZ
No se puede escribir, en ninguna pantalla, correo ni pieza publicitaria: “sin riesgo”,
“blindado”, “garantizado”, “100% legal”, “su abogado revisa todo”, “asegura la respuesta”, “la
EPS tiene que autorizar”, “necesita un abogado para la tutela”.
Se usa en cambio: “indicios”, “puede aumentar el riesgo”, “el término legal es”, “puede
presentarlo usted mismo”.
8. DESPUÉS DE GENERAR
Instructivo: enlace a la página oficial de PQRS de la EPS, el canal que esa página indica, y
la fecha de última verificación visible. Frase fija: “HazloLegal no radica por usted.”
Radicación: campo para registrar el número de radicado y la fecha real. Aviso en pantalla:
sin ese registro no corre el contador.
Contador: 15 días hábiles como regla general; 10 días hábiles si la única casilla marcada es
la 5, historia clínica. Se cuenta desde el día siguiente a la radicación, con calendario de
festivos. El usuario puede registrar una prórroga comunicada por la EPS, que no puede
exceder el doble del plazo inicial, y un requerimiento para completar, que suspende el
término.
Avisos: uno a mitad del plazo y otro al vencer. El del vencimiento ofrece el expediente
armado y repite las tres salidas del rojo.
Expediente: PDF con la petición, la constancia de radicación, la fecha de vencimiento y la
constancia de no respuesta.
9. REGLAS QUE NO SE PUEDEN ROMPER
Uno. El usuario aprueba el texto final antes de generar. El contenido es suyo.
Dos. Si actúa por otro y no adjunta poder ni justifica la agencia oficiosa, no se genera.
Tres. Si no autoriza datos sensibles, el formulario permite pedir el servicio sin diagnóstico y
el documento se genera igual.
Cuatro. El contenido del documento no entra a la base de marketing.
Cinco. En rojo, la salida gratuita va primero.
Seis. El plazo que muestre el contador sale de la modalidad, nunca de un número fijo.
Siete. Si el instructivo manda a un canal que no existe, el usuario lo reporta con un botón y
esa EPS pasa a revisión.
10. PENDIENTES DE ESTE PRODUCTO

—  [MAICOL] Aprobar la redacción de las 10 peticiones frecuentes.
—  [MAICOL] Confirmar el fundamento adicional de cada casilla.
—  [MAICOL] Confirmar la calibración del semáforo y la lista de palabras.
—  [MAICOL] Decidir si la petición libre existe en el plan gratuito o solo en el pago.
—  [DANIEL] Directorio inicial de EPS con enlace a su página de PQRS y fecha de
verificación.
—  [DANIEL] Calendario de días hábiles con festivos.
—  [AMBOS] Si se admiten menores de edad como usuarios, que es la única decisión de la
política de datos que toca este producto.
11. ENTREGABLES AL CLIENTE
QUÉ RECIBE EN CADA PLAN
Gratis: el escrito en PDF, con marca de agua discreta, y la página “Qué sigue” con el enlace
de radicación de su EPS. Nada más. Sin contador, sin avisos, sin expediente.
$19.900: el escrito en PDF sin marca de agua; el instructivo de radicación en PDF; el
contador del plazo; dos correos de seguimiento; y el expediente final en PDF.
$129.900: todo lo anterior, más la revisión de un abogado en 48 horas hábiles y una sesión
de 30 minutos. El abogado entrega observaciones escritas sobre el documento.
$189.900: lo mismo, con la revisión y la sesión el mismo día hábil.
EL INSTRUCTIVO DE RADICACIÓN · CONTENIDO EXACTO
Es un PDF de una página, con el nombre del usuario y la fecha de generación. Siete
bloques, siempre en este orden:
1. A quién va dirigido. Nombre de la EPS tal como aparece en el escrito.
2. Dónde radicar. Enlace a la página oficial de PQRS de esa EPS, con la fecha de última
verificación visible. Si la EPS tiene varios canales, se listan todos con su enlace.
3. Qué llevar o adjuntar. El escrito firmado, copia del documento de identidad, los anexos
que mencionó, y el poder o la justificación de agencia oficiosa si actúa por otro.
4. Qué pedir al radicar. Número de radicado, fecha y hora, y constancia de recibido. Frase
fija: “Sin el número de radicado no hay cómo probar que lo presentó.”
5. Cuánto tiene que esperar. El plazo que corresponde a su caso, en días hábiles, con la
fecha exacta de vencimiento calculada.
6. Qué hacer cuando reciba respuesta. Cómo registrarla en la plataforma y qué revisar: si
responde todo lo pedido, si llega dentro del plazo, si es de fondo o es una respuesta
evasiva.
7. Qué hacer si no responden. Las tres salidas, con la gratuita primero.
Frase fija al pie: “HazloLegal no radica por usted. Este instructivo se verificó el {fecha}. Si el
enlace no funciona, avísenos con el botón de reporte.”

LOS CORREOS
Correo 1, al generar: el escrito y el instructivo adjuntos, con el recordatorio de registrar el
radicado.
Correo 2, a mitad del plazo: “¿Ya le respondieron? Si sí, registre la respuesta aquí.”
Correo 3, al vencer: aviso de vencimiento, expediente adjunto y las tres salidas.
[DANIEL] Los tres correos usan plantilla propia y no pasan por la base de marketing. Un
usuario que no autorizó publicidad igual recibe estos, porque son parte del servicio
contratado.
EL EXPEDIENTE
Un PDF con portada e índice: la petición radicada, la constancia de radicación con su
número y fecha, el cálculo del vencimiento, y la constancia de que no hubo respuesta dentro
del término. Es lo que el usuario entrega a un abogado o anexa a una tutela, y es el
v erdadero producto.
