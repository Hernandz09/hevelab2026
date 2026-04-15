# Proyecto HeveLab 2026

Este proyecto es un sistema de gestión basado en la arquitectura **MVC (Modelo-Vista-Controlador)** escrito en PHP.

## 📂 Estructura con Ejemplos Prácticos

### 🌐 `api/` (El "Cerebro Silencioso")
*Se encarga de procesos rápidos que ocurren sin que la página se recargue.*
- **Ejemplo:** Cuando te pones frente a la cámara para el **Login Facial**, los datos de tu rostro se envían a `api/face_login.php`. Este archivo procesa la imagen y le responde al sistema si eres tú o no.

### 🏢 `controllers/` (El "Director de Orquesta")
*Decide qué debe pasar según lo que el usuario haga.*
- **Ejemplo:** Cuando haces clic en el botón "Ingresar", el controlador `authorizationController.php` recibe tus datos, verifica con el modelo si son correctos y decide si te deja pasar al Dashboard o te muestra un error.

### 📊 `models/` (El "Bibliotecario")
*Es el único que sabe buscar y guardar cosas en la base de datos.*
- **Ejemplo:** Si quieres cambiar tu nombre de perfil, el controlador le pide a `userModel.php`: "Oye, busca al usuario con ID 14 y cambia su nombre por 'Juan'". El modelo hace el cambio en la base de datos.

### 🖥️ `views/` (Lo que tú ves)
*Contiene la parte visual, los formularios y botones.*
- **Ejemplo:** Todo lo que ves cuando entras a la página de registro (cuadros de texto, colores de fondo, el botón de "Registrar") está definido en `views/register/registrar.php`.

### ⚙️ `config/` (Los "Ajustes de Fábrica")
*Donde se guarda la información técnica para que todo funcione.*
- **Ejemplo:** Como mencionaste con **`mailer.php`**, este archivo configura el correo para que cuando el sistema te envíe un código, llegue correctamente a tu Gmail. Otro ejemplo es `conexion.php`, que guarda la "llave" para entrar a la base de datos.

### 🛠️ `assets/libs/` (La "Caja de Herramientas")
*Contiene herramientas complejas compradas o descargadas que ayudan al proyecto.*
- **Ejemplo:** En la carpeta `assets/libs/PHPMailer.php` está el "motor" interno que sabe cómo hablar con los servidores de Google para enviar correos. Es una herramienta técnica que `mailer.php` usa para trabajar.

### 🎨 `assets/` (La "Decoración y Estética")
*Contiene los archivos que hacen que la web se vea bonita y moderna.*
- **Ejemplo:** Si los botones son de color azul o tienen sombras elegantes, esas reglas están guardadas en los archivos dentro de `assets/css/`. Si el sistema tiene animaciones, están en `assets/js/`.

## 🚀 Archivos Principales en la Raíz
- **`index.php`**: Es la puerta de entrada. Si no has iniciado sesión, te manda al Login; si ya iniciaste, te manda al Dashboard.
- **`Dashboard.php`**: Es el "Panel Principal" que ves apenas entras al sistema, donde seguramente aparecen las estadísticas o el resumen de tu cuenta.

---
*Este documento sirve como guía técnica simplificada para entender el funcionamiento del sistema.*
