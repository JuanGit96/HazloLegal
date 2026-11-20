# Cómo está hecho `ddev compartir`

Este documento explica, paso a paso, cómo se construyó el comando que publica
el sitio y la bandeja de correo por ngrok. Sirve para dos cosas: entender qué
hace antes de confiar en él, y poder rehacerlo a mano si algún día falla o
quieres montarlo en otro proyecto.

El comando vive en `.ddev/commands/host/compartir` y **sí va al repositorio**.
Lo único que no va son las contraseñas, que se leen de `.env`.

---

## 1. Qué problema resuelve

Para enseñarle el proyecto a alguien que no está en tu máquina hacen falta dos
cosas publicadas a internet:

- **El sitio y el panel**, que corren en el contenedor de ddev.
- **Mailpit**, el buzón que intercepta todos los correos en local. Sin él, quien
  prueba el formulario de caso complejo no ve ningún correo: se quedan en tu Mac.

Y hay tres fricciones que el comando resuelve:

1. **Los puertos cambian en cada arranque.** Docker publica el contenedor en un
   puerto distinto cada vez que lo recreas. Un script con el puerto escrito a
   mano deja de funcionar al segundo reinicio.
2. **El plan gratuito de ngrok permite un solo proceso**, pero sí varios túneles
   dentro de ese proceso. Hay que añadirlos por su API local, no lanzando dos
   `ngrok` separados.
3. **La bandeja no puede quedar abierta.** Tiene correos con direcciones y datos
   de las pruebas; se protege con usuario y contraseña.

---

## 2. Hacerlo a mano, en cuatro pasos

Si quieres reproducirlo sin el comando, esto es exactamente lo que hace.

### Paso 1 · Averiguar los puertos reales

El nombre del contenedor es `ddev-<proyecto>-web`. Dentro, el sitio escucha en
el puerto 80 y Mailpit en el 8025; Docker los publica fuera en puertos
aleatorios:

```bash
docker port ddev-hazlolegal-web
# 80/tcp   -> 127.0.0.1:63761    <- el sitio
# 443/tcp  -> 127.0.0.1:63760
# 8025/tcp -> 127.0.0.1:63762    <- Mailpit
```

Para quedarte solo con el número:

```bash
WEB_PORT=$(docker port ddev-hazlolegal-web 80/tcp | head -1 | cut -d: -f2)
MAIL_PORT=$(docker port ddev-hazlolegal-web 8025/tcp | head -1 | cut -d: -f2)
```

> **Por qué no se usa `https://hazlolegal.ddev.site`**: ese dominio lo resuelve
> el router de ddev mirando la cabecera `Host`. Si lo tuneleas, ngrok manda su
> propio dominio en esa cabecera y el router no sabe a qué proyecto enviarlo.
> Yendo al puerto directo del contenedor te saltas el router entero.

### Paso 2 · Abrir el primer túnel

```bash
ngrok http "$WEB_PORT" --host-header=rewrite --log=stdout > /tmp/ngrok.log 2>&1 &
```

- `--host-header=rewrite` reescribe la cabecera `Host` a la del destino. Sin
  esto, nginx dentro del contenedor recibe un `Host` que no reconoce.
- `&` lo deja en segundo plano: cierras la terminal y el túnel sigue vivo.

### Paso 3 · Añadir el segundo túnel por la API del agente

ngrok abre una API local en el puerto **4040**. Ahí se le pide un túnel más,
con autenticación básica para la bandeja:

```bash
curl -s -X POST http://127.0.0.1:4040/api/tunnels \
  -H 'Content-Type: application/json' \
  -d '{
    "name": "mailpit",
    "proto": "http",
    "addr": "63762",
    "host_header": "rewrite",
    "basic_auth": ["socio:tu-clave-aqui"]
  }'
```

Esto es lo que permite **añadir un túnel sin reiniciar el que ya está abierto**,
y por tanto sin invalidar un enlace que ya hayas compartido.

### Paso 4 · Leer las URLs públicas

```bash
curl -s http://127.0.0.1:4040/api/tunnels | python3 -m json.tool
```

Cada túnel trae su `public_url`. El del sitio se llama `command_line` (lo creó
la línea de comandos) y el otro, `mailpit`.

Para cerrarlo todo:

```bash
pkill -f "ngrok http"
```

---

## 3. Las dos trampas que costaron depurar

Si lo rehaces a mano y algo no sale, lo más probable es que sea una de estas.

### La API responde antes de que el túnel exista

El agente levanta su API en el 4040 **antes** de haber registrado el túnel. Si
preguntas por las URLs en cuanto la API contesta, te devuelve una lista vacía.

Mal:

```bash
curl -sf http://127.0.0.1:4040/api/tunnels && leer_urls   # puede salir vacío
```

Bien: esperar al túnel, no a la API.

```bash
for _ in $(seq 1 40); do
    URL=$(curl -s http://127.0.0.1:4040/api/tunnels | python3 -c "...")
    [ -n "$URL" ] && break
    sleep 0.5
done
```

### `set -e` mata el script durante la espera

Mientras ngrok arranca, `curl` no puede conectar y sale con código 7. Con
`set -euo pipefail`, ese 7 se propaga a la asignación y el script muere en
mitad del bucle de espera, sin mensaje.

La función que consulta la API tiene que no fallar nunca:

```bash
url_de() {
    local salida=""
    salida="$(curl -s --max-time 2 "$API" | python3 -c "..." )" || salida=""
    printf '%s' "$salida"
    return 0
}
```

---

## 4. De dónde salen las contraseñas

Se leen de `.env`, que está ignorado por git:

```dotenv
HL_SHARE_PASSWORD=v8mxo8vcoy        # protege la bandeja de correo
HL_DEMO_ADMIN_EMAIL=admin@hazlolegal.co
HL_DEMO_ADMIN_PASSWORD=hazlolegal2026
```

`HL_SHARE_PASSWORD` se guarda ahí **a propósito, para que no cambie**: si se
generara una nueva en cada arranque, habría que volver a avisarle a quien ya
tiene el enlace. Si el archivo no la tiene, el comando genera una y la escribe.

Las dos de admin son solo para imprimirlas en el bloque de accesos. Si cambias
la contraseña real del panel, actualiza también esa variable o el bloque mentirá.

---

## 5. Convertirlo en comando de ddev

Un comando de ddev es un script normal en `.ddev/commands/host/<nombre>`, con
permisos de ejecución y unas cabeceras en comentarios que ddev lee para
`ddev help`:

```bash
#!/usr/bin/env bash

## Description: Publica el sitio y la bandeja de correo por ngrok
## Usage: compartir [--stop]
## Example: "ddev compartir" | "ddev compartir --stop"
```

```bash
chmod +x .ddev/commands/host/compartir
```

Dentro del script tienes disponibles, entre otras:

- `DDEV_PROJECT` — el nombre del proyecto (`hazlolegal`).
- `DDEV_APPROOT` — la ruta absoluta de la raíz del proyecto.

Van en `host/` porque necesitan `docker` y `ngrok`, que viven en tu Mac. Los que
van en `web/` se ejecutan dentro del contenedor, donde no existe ninguno de los dos.

---

## 6. Limitaciones

- **Las URLs cambian en cada arranque.** Es la restricción del plan gratuito.
  Se resuelve con un dominio reservado de ngrok (de pago) o desplegando en un
  servidor con tu dominio.
- **Mailpit se vacía al recrear el contenedor.** Los correos viven dentro de él.
  La base de datos no: está en un volumen aparte y sobrevive.
- **El panel queda accesible para cualquiera que tenga el enlace.** No se le
  puede poner autenticación aparte porque comparte túnel con el sitio y ngrok
  gratuito no distingue por ruta. La protección real es cambiar la contraseña
  del usuario administrador.
