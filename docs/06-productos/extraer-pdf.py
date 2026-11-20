"""Extrae el texto de un PDF generado, con su disposición en líneas.

Tres cosas hay que resolver para leer uno de estos archivos:

1. **Las fuentes van en subconjunto.** El contenido no trae letras sino
   identificadores de glifo (`<0033> Tj`); la traducción está en el CMap
   `/ToUnicode` que el propio PDF incrusta.
2. **La posición vertical no está en el texto.** Viene del operador `cm`,
   que transforma el sistema de coordenadas antes de cada bloque, con su
   pila `q`/`Q`. Sin seguirlo, todas las líneas caen en la misma altura y el
   documento sale como una sopa de palabras.
3. **No hay espacios.** El PDF coloca cada palabra en su coordenada; los
   espacios se deducen de los saltos horizontales.

No sirve para PDF escaneados: ahí no hay texto, hay una imagen.
"""
import re
import sys
import zlib


def _streams(raw):
    for m in re.finditer(rb'stream\r?\n(.*?)endstream', raw, re.S):
        try:
            yield zlib.decompress(m.group(1))
        except Exception:
            continue


def _cmap(data):
    mapa = {}

    for bloque in re.findall(rb'beginbfchar(.*?)endbfchar', data, re.S):
        for src, dst in re.findall(rb'<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>', bloque):
            mapa[int(src, 16)] = _texto_hex(dst)

    for bloque in re.findall(rb'beginbfrange(.*?)endbfrange', data, re.S):
        for lo, hi, dst in re.findall(
            rb'<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>', bloque
        ):
            base = int(dst, 16)
            for i in range(int(lo, 16), int(hi, 16) + 1):
                mapa[i] = chr(base + i - int(lo, 16))

    return mapa


def _texto_hex(hexstr):
    try:
        return bytes.fromhex(hexstr.decode()).decode('utf-16-be')
    except Exception:
        return ''


NUM = r'-?[\d.]+'
RE_CM = re.compile(rb'(' + NUM.encode() + rb') (' + NUM.encode() + rb') ('
                   + NUM.encode() + rb') (' + NUM.encode() + rb') ('
                   + NUM.encode() + rb') (' + NUM.encode() + rb') cm')


def _piezas(data, mapa):
    """Recorre el stream siguiendo la pila gráfica y devuelve (x, y, texto)."""
    pila = []
    tx = ty = 0.0
    salida = []

    for m in re.finditer(rb'q|Q|' + RE_CM.pattern + rb'|BT(.*?)ET', data, re.S):
        token = m.group(0)

        if token == b'q':
            pila.append((tx, ty))
            continue

        if token == b'Q':
            if pila:
                tx, ty = pila.pop()
            continue

        if token.endswith(b'cm'):
            partes = token.split()
            tx += float(partes[4])
            ty += float(partes[5])
            continue

        bloque = m.group(m.lastindex) if m.lastindex else b''
        for x, y, texto, ancho in _bloque(bloque, mapa):
            salida.append((tx + x, ty + y, texto, ancho))

    return salida


def _bloque(bloque, mapa):
    m = re.search(
        rb'(' + NUM.encode() + rb') (' + NUM.encode() + rb') (' + NUM.encode()
        + rb') (' + NUM.encode() + rb') (' + NUM.encode() + rb') ('
        + NUM.encode() + rb') Tm', bloque)
    bx = float(m.group(5)) if m else 0.0
    by = float(m.group(6)) if m else 0.0

    # La matriz suele llevar un -1 en la vertical: el texto crece hacia
    # arriba y la página hacia abajo. Sin respetar ese signo, los párrafos
    # salen con las líneas al revés.
    signo = -1.0 if (m and float(m.group(4)) < 0) else 1.0

    # Ancho medio de glifo, para saber dónde acaba una palabra y empieza la
    # siguiente: el PDF no guarda espacios, solo posiciones.
    tf = re.search(rb'/\w+ (' + NUM.encode() + rb') Tf', bloque)
    ancho = float(tf.group(1)) * 0.52 if tf else 6.0

    x = y = 0.0
    for op in re.finditer(
        rb'(' + NUM.encode() + rb') (' + NUM.encode() + rb') Td|<([0-9A-Fa-f]+)>\s*Tj|\[(.*?)\]\s*TJ',
        bloque, re.S,
    ):
        if op.group(1) is not None:
            x += float(op.group(1))
            y += float(op.group(2))
            continue

        crudo = op.group(3) or b''
        if op.group(4):
            crudo = b''.join(re.findall(rb'<([0-9A-Fa-f]+)>', op.group(4)))

        texto = ''.join(
            mapa.get(int(crudo[i:i + 4], 16), '') for i in range(0, len(crudo) - 3, 4)
        )
        if texto:
            yield bx + x, signo * (by + y), texto, ancho


def extraer(path):
    raw = open(path, 'rb').read()

    mapa = {}
    contenidos = []

    for data in _streams(raw):
        if b'beginbfchar' in data or b'beginbfrange' in data:
            mapa.update(_cmap(data))
        if b'BT' in data:
            contenidos.append(data)

    paginas = []

    for data in contenidos:
        piezas = _piezas(data, mapa)
        if piezas:
            paginas.append(_componer(piezas))

    return re.sub(r'\n{3,}', '\n\n', '\n\n'.join(paginas))


def _componer(piezas):
    lineas = {}
    for x, y, texto, ancho in piezas:
        lineas.setdefault(round(y, 0), []).append((x, texto, ancho))

    salida = []
    for y in sorted(lineas):
        trozos = sorted(lineas[y], key=lambda p: p[0])
        linea = ''
        fin = None
        for x, texto, ancho in trozos:
            if fin is not None and x - fin > ancho * 0.9:
                linea += ' '
            linea += texto
            fin = x + len(texto) * ancho
        if linea.strip():
            salida.append(linea.strip())

    return '\n'.join(salida)


if __name__ == '__main__':
    print(extraer(sys.argv[1]))
