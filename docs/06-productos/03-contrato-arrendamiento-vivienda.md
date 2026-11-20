# PRODUCTO 03 · CONTRATO DE ARRENDAMIENTO DE VIVIENDA URBANA

> Transcripción del PDF que escribió el equipo. Es la fuente de verdad
> del producto: si algo en el código no coincide con esto, manda esto.

HazloLegal · Versión 1.0 · 12 de noviembre de 2026
El producto insignia. Es el de mayor búsqueda mensual, el que más gente necesita cada
año y el que tiene más regulación encima. También es donde un error de plantilla le cuesta
plata real al usuario.
Precio: $89.900 automatizado · $210.000 acompañado · $290.000 express. El instructivo va
incluido.
1. ALCANCE
Qué entrega: el contrato de arrendamiento de vivienda urbana, el inventario de entrega del
inmueble, el instructivo de firma y entrega, y las alertas de fechas clave (vencimiento,
preaviso, incremento anual).
Qué NO entrega: no es arrendamiento de local comercial, que se rige por el Código de
Comercio y no por la Ley 820. No es contrato de vivienda compartida ni de habitación
dentro de una vivienda, que tienen reglas propias. No incluye estudio de arrendatario ni
p óliza.
2. LO QUE LA LEY 820 DE 2003 NO DEJA NEGOCIAR
Estas son las reglas que el motor aplica aunque el usuario pida otra cosa. Cada una es un
error caro que la plantilla evita.
Canon. No puede exceder el 1% del valor comercial del inmueble o de la parte que se
arrienda. El valor comercial no puede exceder el doble del avalúo catastral.
Incremento anual. Al vencimiento de cada año de ejecución, el canon puede reajustarse
hasta en un porcentaje que no exceda el 100% del IPC del año calendario anterior. Para
2026 ese tope es 5,10%.
[DANIEL] El IPC vive en la tabla de configuración anual, nunca escrito dentro de la plantilla.
Depósitos. Están prohibidos los depósitos en dinero y las cauciones reales para garantizar
el cumplimiento. Sí se admiten deudor solidario, póliza de arrendamiento o afianzamiento.
Si el usuario escribe que va a pedir dos meses de depósito, el motor lo bloquea y le explica
p or qué.
Término. A falta de pacto, se entiende por doce meses. Se prorroga automáticamente por
términos iguales si las partes cumplen.
Terminación unilateral por el arrendador en la prórroga. Exige preaviso escrito por servicio
postal autorizado con no menos de tres meses de antelación e indemnización equivalente a
tres cánones. Es la cláusula que más pleitos genera cuando se redacta mal.
Servicios públicos. El contrato debe definir quién los paga y cómo se acredita el paz y salvo
al entregar.

3. FORMULARIO
Paso 1 · Las partes
—  Arrendador: nombre, documento, dirección, correo, teléfono. ¿Actúa por sí o como
apoderado o administrador?
—  Arrendatario: los mismos datos.
—  ¿Hay deudor solidario? Si sí, sus datos.
Paso 2 · El inmueble
—  Dirección completa y ciudad.
—  Matrícula inmobiliaria (opcional).
—  Tipo: casa, apartamento, apartaestudio.
—  ¿Incluye parqueadero, depósito u otros? Cuáles.
—  ¿Está amoblado? Si sí, se activa el inventario detallado.
Paso 3 · El dinero
—  Canon mensual.
—  Avalúo catastral (opcional, activa la calculadora del tope).
—  Día de pago y medio de pago.
—  ¿Quién paga administración, servicios públicos, internet?
Paso 4 · El plazo
—  Fecha de inicio y término en meses.
—  ¿Se pacta prórroga automática? Sí por defecto.
Paso 5 · Garantías
—  Deudor solidario, póliza, afianzamiento o ninguna.
—  Campo de advertencia si el usuario intenta pactar depósito en dinero.
Paso 6 · Reglas de convivencia
—  ¿Se permiten mascotas? ¿Subarriendo? ¿Cesión? ¿Número máximo de ocupantes?
—  ¿Quién asume reparaciones locativas?
Paso 7 · Inventario de entrega
—  Estado del inmueble por espacios, con campo de observaciones.
—  Lectura de contadores.
—  Número de llaves entregadas.
Paso 8 · Revisión y aprobación
—  Se muestra el contrato completo. Las dos partes deben aprobarlo antes de firmar.
4. CALCULADORAS QUE EL PRODUCTO INCLUYE
Tope del canon. Si el usuario ingresa el avalúo catastral, el sistema calcula el valor
comercial máximo (el doble del avalúo) y el canon máximo (1% de ese valor). Si el canon
pactado lo excede, enciende amarillo.

Incremento anual. Toma el canon vigente y el IPC del año anterior de la tabla de
configuración, y entrega el canon nuevo y la fecha desde la cual aplica.
Fecha de preaviso. A partir de la fecha de vencimiento, calcula hacia atrás los tres meses
de preaviso y marca el último día hábil para enviarlo. Esta es la alerta que más valor
entrega: la gente pierde la prórroga por no avisar a tiempo.
5. SEMÁFORO
CAPA 1 · PREGUNTAS ESTRUCTURADAS
¿El inmueble se destina a local comercial, oficina o actividad económica? →  ROJO
(producto equivocado)
¿Se arrienda solo una habitación dentro de una vivienda ocupada? →  ROJO
¿El inmueble es rural o tiene uso agropecuario? →  ROJO
¿Hay un contrato vigente y lo que quiere es terminarlo o modificarlo? →  ROJO
¿El arrendador actúa como apoderado o administrador de un tercero? →  AMARILLO (exige
poder o contrato de administración)
¿El canon supera el tope calculado sobre el avalúo catastral? →  AMARILLO
¿El inmueble está en propiedad horizontal con restricciones de arriendo? →  AMARILLO
¿Hay más de un arrendatario o más de un propietario? →  AMARILLO
¿Se pacta un término menor a doce meses? →  AMARILLO
CAPA 2 · PALABRAS EN TEXTO LIBRE
Rojo: local, bodega, oficina, consultorio, negocio, finca, lote, desalojo, lanzamiento,
restitución, ya está viviendo, no ha pagado.
Amarillo: depósito, consignación en garantía, dos meses adelantados, inmobiliaria,
sucesión, herederos, copropiedad.
Bloqueo duro: si el usuario insiste en pactar depósito en dinero o caución real, no se genera
hasta que lo retire. No es un amarillo: es una cláusula prohibida por ley y la plataforma no la
puede imprimir.
6. QUÉ HACE CADA COLOR
VERDE: genera y cobra.
AMARILLO: genera con advertencia aceptada expresamente, y ofrece la revisión
acompañada.
Ejemplo de aviso cuando el canon excede el tope:
“El canon que pactó supera el tope legal calculado sobre el avalúo que nos dio. Puede
continuar, pero el arrendatario podría pedir su reajuste. Si quiere revisarlo con un abogado,
aquí puede hacerlo.”
ROJO por producto equivocado: no genera y redirige, sin cobrar.
“Este formato es para vivienda urbana. Lo que usted necesita se rige por otras normas. Por
ahora no lo tenemos automatizado; aquí le explicamos cómo funciona y, si quiere, le
mostramos abogados que lo hacen.”

ROJO por conflicto existente (mora, desalojo, restitución): no genera contrato y ofrece las
tres salidas, con la gratuita primero.
7. DESPUÉS DE GENERAR
Instructivo de firma: cuántos ejemplares, quién firma, qué anexos adjuntar (inventario,
cédulas, certificado de tradición si se tiene), y la recomendación de conservar un ejemplar
firmado cada parte.
Alertas por correo: 90 días antes del vencimiento, con la fecha límite del preaviso; 30 días
antes del cumpleanos del contrato, con el incremento calculado; y el día del vencimiento.
Expediente: contrato firmado, inventario de entrega y registro de las alertas enviadas.
8. PENDIENTES
—  [MAICOL] Redactar o aprobar el articulado completo del contrato.
—  [MAICOL] Definir qué causales de terminación van siempre a revisión acompañada.
—  [MAICOL] Confirmar el tratamiento de la vivienda compartida y de la habitación: ¿rojo o
producto futuro?
—  [DANIEL] Tabla de configuración anual con SMMLV e IPC.
—  [DANIEL] Las tres calculadoras y el árbol de fechas de preaviso.
—  [DANIEL] Generador de inventario por espacios.
9. ENTREGABLES AL CLIENTE
QUÉ RECIBE EN CADA PLAN
$89.900 automatizado: el contrato en PDF y en Word editable; el inventario de entrega en
PDF, listo para diligenciar y firmar el día de la entrega; el instructivo de firma y entrega; las
tres alertas por correo; y el acceso a las calculadoras de incremento y preaviso durante toda
l a vigencia del contrato.
$210.000 acompañado: todo lo anterior, más revisión de abogado en 48 horas hábiles y
sesión de 30 minutos con observaciones escritas.
$290.000 express: lo mismo, el mismo día hábil.
Por qué va en Word además de PDF: un contrato de arrendamiento se ajusta entre las
partes antes de firmar. Entregar solo PDF obliga al usuario a volver a empezar por un
cambio menor. [DANIEL] Confirmar que el generador produce ambos formatos.
EL INSTRUCTIVO DE FIRMA Y ENTREGA · CONTENIDO EXACTO
PDF de una página, con las fechas clave del contrato ya calculadas. Seis bloques:

1. Antes de firmar. Qué verificar: que los nombres y documentos coincidan con las cédulas,
que la dirección del inmueble esté completa, y que el canon y la fecha de pago sean los
acordados.
2. Cuántos ejemplares y quién firma. Un ejemplar por cada parte y uno por el deudor
solidario si lo hay. Firman arrendador, arrendatario y deudor solidario. Cada página va
rubricada.
3. Qué anexar. Copia de los documentos de identidad, el inventario de entrega firmado, el
certificado de tradición si el arrendador lo tiene, y la póliza o el documento de la garantía
pactada.
4. El día de la entrega. Diligenciar el inventario espacio por espacio, tomar fotos, anotar la
lectura de los contadores y contar las llaves. Frase fija: “El inventario es la única prueba de
cómo se entregó el inmueble. Sin él, la discusión al final es palabra contra palabra.”
5. Sus fechas clave. Fecha de vencimiento del contrato. Último día para enviar el preaviso
de terminación, calculado tres meses antes. Fecha del incremento anual y el canon nuevo
calculado con el IPC vigente. Frase fija sobre el preaviso: “Si el arrendador quiere terminar
el contrato en la prórroga, el preaviso debe enviarse por servicio postal autorizado con no
menos de tres meses de antelación e implica una indemnización de tres cánones. Fuera de
6. Qué NO se puede pactar. Depósitos en dinero y cauciones reales están prohibidos. Si
ese plazo, el contrato se prorroga.”
alguien se los pide, no está obligado a darlos.
Frase fija al pie: “HazloLegal no interviene en la firma ni en la entrega. Este instructivo
refleja la ley vigente al {fecha}.”
LAS ALERTAS POR CORREO
Alerta 1, 90 días antes del vencimiento: la fecha límite del preaviso y qué pasa si no se
envía.
Alerta 2, 30 días antes del cumpleaños del contrato: el incremento calculado con el IPC del
año anterior, el canon nuevo y la fecha desde la cual aplica.
Alerta 3, el día del vencimiento: qué pasó con el contrato y qué opciones hay.
Estas tres alertas son el valor que justifica el precio. El contrato lo copia cualquiera; el aviso
de que se le vence el preaviso en ocho días, no.
EL EXPEDIENTE DEL CONTRATO
Contrato firmado, inventario de entrega diligenciado, documentos de las partes, registro de
las alertas enviadas y el histórico de incrementos aplicados. Se conserva durante toda la
vigencia y se puede descargar completo.
