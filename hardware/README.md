# Prototipo RFID

Sistema Inteligente de Gestión de Inventarios

---

# Descripción

Este directorio contiene toda la documentación correspondiente al prototipo RFID desarrollado para el Sistema Inteligente de Gestión de Inventarios.

El lector RFID fue construido utilizando un ESP32 y un módulo RC522 para identificar activos institucionales y transmitir la información hacia el sistema web mediante WiFi.

---

# Objetivo

Permitir la identificación automática de activos mediante etiquetas RFID, enviando la información al sistema Laravel para su procesamiento y registro.

---

# Componentes

- ESP32 Dev Module

- RC522 RFID Reader

- Tarjeta RFID

- Llavero RFID

- Cable USB

- Cables Dupont

---

# Tecnologías

- ESP32

- RC522

- Arduino IDE

- HTTP/HTTPS

- Laravel

- MySQL

---

# Estructura

```
hardware/

firmware/

diagramas/

documentos/

README.md
```

---

# Firmware

El programa del ESP32 se encuentra en:

```
firmware/

esp32_rfid.ino
```

---

# Diagramas

Este proyecto incluye tres diagramas técnicos.

## Diagrama de Conexión

```
diagramas/

01_Diagrama_Conexion_ESP32_RC522.png
```

Describe la conexión física entre el ESP32 y el módulo RC522 utilizando comunicación SPI.

---

## Arquitectura del Sistema

```
diagramas/

02_Arquitectura_Sistema_RFID.png
```

Describe el flujo completo desde la lectura RFID hasta la actualización del sistema web.

---

## Flujo de Funcionamiento

```
diagramas/

03_Flujo_Funcionamiento_RFID.png
```

Explica paso a paso el proceso completo desde la detección de la tarjeta RFID hasta la generación de notificaciones en el sistema.

---

# Comunicación

```
Tarjeta RFID

↓

RC522

↓

ESP32

↓

WiFi

↓

Laravel API

↓

MySQL

↓

Dashboard

↓

Notificaciones
```

---

# Seguridad

El dispositivo utiliza:

- HTTPS

- X-Device-Key

- Validación del dispositivo

---

# Resultado esperado

Al acercar una tarjeta RFID:

- Se detecta el UID.

- Se transmite la información.

- Laravel procesa la solicitud.

- Se registra el movimiento.

- Se genera una notificación.

- El Dashboard actualiza la información.

---

# Proyecto

Sistema Inteligente de Gestión de Inventarios con RFID e Inteligencia Artificial

ITCA-FEPADE

2026