# Manual de Usuario y Operaciones • Starlink Control Manager

> **Versión del Sistema:** 2026.11  
> **Seguridad:** Cifrado SSL/TLS 256-bit • 2FA TOTP • Sesión Única Concurrente  
> **Plataforma:** PWA Responsive (Escritorio, Android, iOS / APK)

---

## Tabla de Contenidos

1. [Introducción y Roles de Usuario](#1-introducción-y-roles-de-usuario)
2. [Acceso, Autenticación y Seguridad](#2-acceso-autenticación-y-seguridad)
3. [Módulo de Antenas Starlink](#3-módulo-de-antenas-starlink)
4. [Centro de Pagos y Cobranzas](#4-centro-de-pagos-y-cobranzas)
5. [Directorio de Clientes](#5-directorio-de-clientes)
6. [Dashboard Financiero y Calendario](#6-dashboard-financiero-y-calendario)
7. [Catálogo y Mantenimiento del Sistema](#7-catálogo-y-mantenimiento-del-sistema)
8. [Auditoría y Trazabilidad (Logs)](#8-auditoría-y-trazabilidad-logs)
9. [Atajos Rápidos y Productividad](#9-atajos-rápidos-y-productividad)
10. [Instalación como App Móvil (PWA / APK)](#10-instalación-como-app-móvil-pwa--apk)

---

## 1. Introducción y Roles de Usuario

**Starlink Control Manager** es una plataforma centralizada para la gestión operativa, comercial y financiera de antenas satelitales Starlink. Permite controlar terminales, calcular automáticamente estados de solvencia y mora, conciliar comprobantes de pago, aplicar cortes técnicos preventivos y auditar todas las operaciones de forma inmutable.

### Matriz de Roles y Permisos

| Módulo / Acción | Administrador | Moderador | Expectador (Cliente) |
| :--- | :---: | :---: | :---: |
| **Dashboard y Calendario** | Completo (Finanzas + Antenas) | Completo | Solo sus terminales |
| **Antenas: Crear / Editar / Eliminar** | Sí | No | No |
| **Corte Técnico y Reactivación** | Sí | No | No |
| **Exoneración de Deuda previa** | Sí | No | No |
| **Registrar Pagos** | Sí | Sí | Sí (Reportar) |
| **Validar / Aprobar / Rechazar Pagos** | Sí | Sí | No |
| **Directorio de Clientes** | Lectura y Edición | Lectura y Edición | No |
| **Gestión de Usuarios y Roles** | Sí | Solo Lectura | No |
| **Base de Datos (Respaldos SQL)** | Sí | No | No |
| **Auditoría e Historial de Logs** | Sí (Consultar y Limpiar) | Solo Lectura | No |

---

## 2. Acceso, Autenticación y Seguridad

### Inicio de Sesión
1. Ingresa tu **Cédula de Identidad** (solo números) y tu **Contraseña**.
2. Puedes marcar la casilla **"Recordar mi cédula"** para que el navegador autocomplete tu identificación en futuros accesos.
3. Puedes hacer clic en el ícono de ojo para verificar que la contraseña escrita sea correcta.
4. Presiona **"Ingresar al Sistema"**.

### Autenticación en Dos Pasos (2FA / TOTP)
Si el usuario tiene activado el segundo factor de autenticación:
1. Tras ingresar la contraseña, el sistema solicitará el **código de 6 dígitos**.
2. Abre tu app de autenticación (**Google Authenticator**, **Microsoft Authenticator** o **Authy**) e ingresa el token dinámico de 30 segundos.

### Preguntas de Seguridad y Recuperación de Clave
* Si olvidaste tu contraseña, haz clic en **"¿Olvidaste tu clave?"** en la pantalla de inicio de sesión.
* El sistema te solicitará tu cédula y las 3 respuestas a tus preguntas de seguridad registradas. Al responder correctamente, podrás definir una nueva contraseña.

### Protección contra Sesiones Concurrentes
Por motivos de seguridad operativa, un mismo usuario no puede tener dos sesiones abiertas al mismo tiempo en dispositivos distintos. Si el sistema detecta que inicias sesión en otro equipo, te solicitará responder tus preguntas secretas para autorizar el traspaso de sesión y revocar automáticamente la sesión previa.

---

## 3. Módulo de Antenas Starlink

El inventario de antenas es el núcleo del sistema (`index.php?url=antenas`).

### Estados de Solvencia
El sistema calcula automáticamente el estado financiero de cada antena evaluando el día de corte asignado y los pagos aprobados:
* **🟢 Al día:** La mensualidad del ciclo actual está pagada.
* **🟡 Pendiente:** El pago del mes actual aún no ha sido registrado, pero el ciclo está dentro del plazo.
* **🔴 Atrasado (Mora):** Se ha superado la fecha de corte. La etiqueta indica exactamente el número de meses adeudados y el monto total calculado (ej. `2 meses atrasados • Deuda: $130.00`).

### Acciones Operativas en Cada Antena
* **💵 Cobrar:** Abre el Centro de Pagos preseleccionando automáticamente el serial de la antena y calculando el importe del plan.
* **🚫 Corte Técnico (Suspender):** Aplica una suspensión administrativa visible con el badge `SUSPENDIDA`. Ideal para marcar terminales desconectadas por falta de pago sin borrarlas de la base de datos.
* **▶️ Activar:** Reactiva el servicio de la antena cuando el cliente regulariza su deuda.
* **📲 Recordar (WhatsApp):** Abre WhatsApp Web o la App móvil con un mensaje personalizado que incluye nombre del cliente, serial, nickname, monto de la deuda y fecha de vencimiento.
* **✏️ Editar:** Modifica el apodo (*nickname*), kit, país, plan o cuenta vinculada.
* **🗑️ Eliminar:** Da de baja la antena previa confirmación de seguridad.

### Exoneración de Deuda Histórica
En situaciones especiales (ej. antena suspendida temporalmente por viaje o cliente con acuerdo comercial previo):
1. Haz clic en **"Exonerar Deuda"**.
2. Selecciona la fecha límite hasta la cual se perdonan los meses vencidos y añade una nota explicativa.
3. El sistema no exigirá los meses previos a dicha fecha, recalculando la deuda a partir del nuevo ciclo.

---

## 4. Centro de Pagos y Cobranzas

Ubicado en `index.php?url=payments`, centraliza la auditoría de cobros y conciliación de transferencias.

### Registro de un Pago
1. Presiona el botón **"Registrar Pago"** (o usa el botón rápido **"Cobrar"** desde una antena).
2. Selecciona una o varias antenas del listado (puedes buscar por cliente o serial).
3. Selecciona la cantidad de **meses a abonar**:
   * Opciones rápidas con un clic: `1m`, `2m`, `3m`, `6m` o `1 año`.
   * El total en USD se calcula automáticamente multiplicando el valor mensual del plan por los meses seleccionados.
4. Escoge la moneda (`USD`, `Bolívares` o `USDT`).
5. Adjunta el **comprobante de pago** (imagen JPG/PNG o documento PDF de hasta 5 MB).
6. Presiona **"Guardar Pago"**.

### Conciliación y Aprobación
Los operadores administradores y moderadores pueden revisar los pagos reportados:
* **Píldoras de Filtro Rápido:** Puedes filtrar la tabla haciendo clic en `Todos`, `Pendientes`, `Aprobados` o `Rechazados`.
* **Ver Comprobante:** Haz clic en el ícono de comprobante para inspeccionar la captura de la transferencia bancaria o archivo adjunto.
* **Aprobar:** Cambia el estado a `Aprobado` y actualiza inmediatamente la solvencia de la antena vinculada.
* **Rechazar:** Marca la transacción como rechazada si los fondos no coinciden o el comprobante es inválido.

### Emisión de Recibos Digitales con Código QR
* Cada pago aprobado dispone de un botón **"Recibo"**.
* Genera un comprobante formal con membrete de la empresa, desglose de meses cubiertos, serial, kit, cliente y un **código QR de verificación criptográfica** que cualquier persona puede escanear con su teléfono para validar la autenticidad del recibo.
* Incluye botón para imprimir en formato ticket/carta y enlace directo para compartir por WhatsApp.

---

## 5. Directorio de Clientes

En `index.php?url=clients` se gestiona la cartera de abonados.

* **Contacto Inmediato:** Cada fila incluye botones directos para:
  * <i class="bi bi-whatsapp"></i> Iniciar chat de WhatsApp con el cliente.
  * <i class="bi bi-telephone"></i> Realizar una llamada telefónica directa.
  * <i class="bi bi-clipboard"></i> Copiar el número o la cédula al portapapeles.
* **Antenas Asociadas:** Al hacer clic en la insignia `X antenas`, se despliega un visor con todas las terminales que pertenecen a ese abonado.
* **Historial / Pagos:** El botón **"Pagos"** te redirige al historial filtrando automáticamente todas las transacciones de ese cliente para auditar su estado de cuenta completo.

---

## 6. Dashboard Financiero y Calendario

El panel de control (`index.php?url=dashboard`) ofrece una vista panorámica del negocio en tiempo real.

### Tarjetas de Telemetría
* **Ingresos Mensuales:** Recaudación acumulada del mes en curso comparada contra el total histórico.
* **Deuda Activa en Calle:** Sumatoria exacta de todos los meses impagos de antenas activas.
* **Terminales Activas vs. Suspendidas:** Porcentaje de solvencia de la red.

### Uso del Calendario de Vencimientos
El calendario agrupa las antenas según su **día de corte mensual**:
* **Vista Cuadrícula:** Muestra la cuadrícula tradicional del mes. Cada día con cobros tiene un indicador con la cantidad de terminales.
* **Vista Agenda (Recomendada para Móviles):** Organiza los cobros en tarjetas cronológicas día por día con clientes, seriales y botones de cobro rápido.
* **Detalle al Clic:** Al presionar cualquier día del calendario, se abre una ventana modal con el listado detallado de todas las antenas que vencen esa fecha.

---

## 7. Catálogo y Mantenimiento del Sistema

Acceso reservado para administradores:
* **Cuentas Starlink (`index.php?url=accounts`):** Organización de correos y cuentas maestras de Starlink asignadas a cada lote de antenas.
* **Planes de Servicio (`index.php?url=plans`):** Definición de tarifas en USD (Prioridad, Itinerante ilimitado, Estándar).
* **Países (`index.php?url=countries`):** Gestión geográfica y divisas de facturación local.
* **Gestión de Usuarios (`index.php?url=users`):** Creación de operadores, asignación de roles y reseteo de claves.
* **Base de Datos y Respaldos (`index.php?url=database`):**
  * Descarga con un clic de respaldos completos en formato `.sql`.
  * Restauración segura ante contingencias.

---

## 8. Auditoría y Trazabilidad (Logs)

El módulo de auditoría (`index.php?url=logs`) registra cronológica e inmutablemente cada evento crítico:
* Quién realizó la acción (nombre del operador y rol).
* Tipo de evento (`LOGIN`, `REGISTRAR_PAGO`, `SUSPENDER`, `ACTIVAR`, `ELIMINAR`).
* Módulo afectado y descripción detallada del cambio.
* Dirección IP y fecha/hora exacta.

> [!NOTE]
> Los registros no pueden ser editados manualmente. Los administradores solo pueden purgar registros antiguos con una antigüedad mayor a 90 días para liberar espacio.

---

## 9. Atajos Rápidos y Productividad

### Búsqueda Global Universal (<kbd>Ctrl</kbd> + <kbd>K</kbd>)
* Presiona <kbd>Ctrl</kbd> + <kbd>K</kbd> (o <kbd>Cmd</kbd> + <kbd>K</kbd> en Mac) desde cualquier pantalla.
* Escribe el nombre de cualquier módulo o acción (ej. `antenas`, `pagos`, `clientes`, `seguridad`).
* Navega con las flechas <kbd>↑</kbd> <kbd>↓</kbd> y pulsa <kbd>Enter</kbd> para acceder instantáneamente.

### Exportación a Excel (.csv con BOM UTF-8)
En los módulos de **Antenas**, **Pagos** y **Clientes**, encontrarás el botón **"Excel (.csv)"**:
* Genera una descarga limpia con codificación UTF-8 internacional.
* Compatible de forma nativa con **Microsoft Excel**, **Google Sheets** y **LibreOffice Calc** sin caracteres corruptos en acentos ni signos monetarios.

### Copiado Rápido al Portapapeles
Cualquier serial, kit, cédula o teléfono tiene a su lado un ícono de portapapeles <i class="bi bi-clipboard"></i>. Al tocarlo, el dato se copia inmediatamente y una alerta verde te confirma la copia sin necesidad de seleccionar texto a mano.

---

## 10. Instalación como App Móvil (PWA / APK)

La plataforma está diseñada como una **Progressive Web App (PWA)** de grado nativo, con soporte fuera de línea y navegación táctil inferior.

### Instalación en Android (Google Chrome)
1. Abre Google Chrome en tu teléfono e ingresa a la URL del sistema.
2. Toca el menú de tres puntos (`⋮`) en la esquina superior derecha.
3. Selecciona **"Instalar aplicación"** o **"Agregar a la pantalla principal"**.
4. La app aparecerá en tu menú de aplicaciones con el ícono oficial y abrirá a pantalla completa sin barra de navegación del navegador.

### Instalación en iPhone / iPad (Safari)
1. Abre Safari y navega al sistema.
2. Toca el botón **Compartir** (ícono de cuadrado con flecha hacia arriba).
3. Desplázate hacia abajo y selecciona **"Agregar al inicio"**.
4. Confirma el nombre y presiona **"Agregar"**.

---

*Manual elaborado y actualizado por el Equipo de Ingeniería de Starlink Control Manager.*

