<div align="center">
  <p>
    <img src="https://github.com/Hernandz09/hevelab2026/blob/structure/public/assets/images/logos/logo_light_01.png" alt="HeveLab" width="96" height="96">
  </p>
  <h1>HeveLab MVC · VIISION ERP</h1>
  <p>Proyecto web en PHP con arquitectura MVC, panel tipo SPA (rutas asíncronas), autenticación con OTP y soporte de login facial.</p>
</div>

<hr>

<h2>Índice</h2>
<ul>
  <li><a href="#resumen">Resumen</a></li>
  <li><a href="#stack">Stack</a></li>
  <li><a href="#arquitectura-mvc">Arquitectura MVC</a></li>
  <li><a href="#estructura-del-proyecto">Estructura del Proyecto</a></li>
  <li><a href="#rutas-y-navegacion">Rutas y Navegación</a></li>
  <li><a href="#api">API</a></li>
  <li><a href="#configuracion">Configuración</a></li>
  <li><a href="#autenticacion">Autenticación (OTP y Face Login)</a></li>
  <li><a href="#diseño-y-paleta-de-colores">Diseño y Paleta de Colores</a></li>
  <li><a href="#instalacion-local">Instalación Local</a></li>
  <li><a href="#tests">Tests</a></li>
  <li><a href="#seguridad">Seguridad</a></li>
</ul>

<hr>

<h2 id="resumen">Resumen</h2>
<p>
  HeveLab MVC es un proyecto web en PHP pensado para un panel administrativo con navegación fluida: la primera carga renderiza un layout completo y
  las transiciones entre vistas se hacen por fetch a un endpoint que devuelve HTML (rutas asíncronas). Además incluye autenticación con OTP por correo,
  recuperación de contraseña y login facial (usando descriptors de 128 floats).
</p>

<h3>Funciones principales</h3>
<ul>
  <li><strong>Auth</strong>: Login, Registro, OTP (login/registro/reset), Forgot/Reset password, Logout.</li>
  <li><strong>Face Login</strong>: Matching de descriptor facial con umbral configurable.</li>
  <li><strong>Dashboard</strong>: métricas (usuarios, verificados, OTP pendientes), serie de 30 días y limpieza de OTP expirados.</li>
  <li><strong>Usuarios</strong>: CRUD, verificación/suspensión, reset de biometría facial.</li>
  <li><strong>Configuración del sistema</strong>: umbral facial, expiración OTP, intentos máximos, persistido en JSON.</li>
  <li><strong>UI</strong>: tema claro/oscuro, toasts, módulos JS con lazy-loading.</li>
</ul>

<hr>

<h2 id="stack">Stack</h2>
<ul>
  <li><strong>Backend</strong>: PHP (Router propio, Controladores, Servicios).</li>
  <li><strong>Base de datos</strong>: MySQL (script en <code>database/sistema_login.sql</code>).</li>
  <li><strong>Frontend</strong>: Vanilla JS (router asíncrono + módulos), CSS modular.</li>
  <li><strong>Email</strong>: PHPMailer embebido en el repo (sin Composer).</li>
</ul>

<hr>

<h2 id="arquitectura-mvc">Arquitectura MVC</h2>
<p>Flujo general de una request:</p>
<ol>
  <li><code>index.php</code> registra el autoloader, declara rutas y despacha.</li>
  <li><code>App\Core\Router</code> matchea patrones (incluye parámetros tipo <code>/api/view/{page}</code>).</li>
  <li>El controller procesa: usa modelos/servicios y renderiza vistas.</li>
  <li><code>App\Core\Response</code> devuelve HTML o JSON, o redirecciona.</li>
</ol>

<p>Renderizado de vistas:</p>
<ul>
  <li><code>App\Core\View</code> resuelve el template como <code>app/Views/&lt;ruta&gt;.php</code> y lo renderiza con <code>extract()</code>.</li>
  <li>El layout principal es <code>app/Views/layouts/app.php</code>: carga CSS/JS y monta el contenedor <code>#app-main</code>.</li>
</ul>

<hr>

<h2 id="estructura-del-proyecto">Estructura del Proyecto</h2>
<pre><code>.
├─ app/
│  ├─ Controllers/         Controllers (Auth, Dashboard, Admin, APIs)
│  ├─ Core/                Router, Request, View, Response, Autoloader
│  ├─ Libs/PHPMailer/      PHPMailer embebido (sin Composer)
│  ├─ Models/              Acceso a datos (UserModel, ProductModel)
│  ├─ Services/            Servicios (PDO, config, feature flags)
│  └─ Views/               Layouts, pages y components (PHP)
├─ config/
│  ├─ app.php              Config general (nombre, base_url)
│  ├─ database.php         Config DB (host, user, password, charset)
│  ├─ hooks.php            Flags por defecto de hooks (front)
│  ├─ mailer.php           Envío OTP por correo (SMTP/env/archivo) + fallback a log
│  ├─ mailer_credentials.php.example  Ejemplo de credenciales SMTP
│  └─ system_config.json   Config del sistema (umbral facial, expiración OTP, etc)
├─ database/
│  └─ sistema_login.sql    Script MySQL (tabla usuarios + admin seed)
├─ public/
│  └─ assets/              CSS/JS/imagenes/modelos (face-api)
├─ routes/
│  └─ web.php              Rutas adicionales
├─ storage/
│  └─ logs/                Logs (OTP cuando SMTP no está configurado)
├─ tests/
│  └─ run.php              Validaciones rápidas del proyecto
├─ .htaccess               Rewrite a index.php
└─ index.php               Front controller + registro de rutas
</code></pre>

<hr>

<h2 id="rutas-y-navegacion">Rutas y Navegación</h2>

<h3>Rutas backend</h3>
<ul>
  <li><strong>Definición principal</strong>: <code>index.php</code> y <code>routes/web.php</code>.</li>
  <li><strong>Patrones con parámetros</strong>: el Router soporta <code>{param}</code> y los pasa como argumentos al handler.</li>
  <li><strong>Fallback</strong>: si la ruta no existe y empieza con <code>/api/</code>, responde JSON 404; si no, HTML 404.</li>
</ul>

<h3>Navegación tipo SPA (panel)</h3>
<p>
  El panel usa un router asíncrono en JS:
</p>
<ol>
  <li><code>public/assets/js/app.js</code> inicializa hooks, tema y router.</li>
  <li><code>public/assets/js/core/router.js</code> intercepta links con <code>data-link</code> y hace fetch del HTML vía <code>/api/view/&lt;page&gt;</code>.</li>
  <li>Después del HTML, hace lazy-load del módulo JS de la página (por ejemplo <code>productsModule.js</code>) y ejecuta <code>mount()</code>.</li>
</ol>

<hr>

<h2 id="api">API</h2>

<h3>HTML de vistas (para navegación asíncrona)</h3>
<table>
  <thead>
    <tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr>
  </thead>
  <tbody>
    <tr><td><code>/api/view/{page}</code></td><td>GET</td><td>Devuelve <code>{ page, html }</code> para <code>home|dashboard|products|users|settings</code>.</td></tr>
  </tbody>
</table>

<h3>Dashboard</h3>
<table>
  <thead>
    <tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr>
  </thead>
  <tbody>
    <tr><td><code>/api/dashboard/overview</code></td><td>GET</td><td>Métricas + serie 30 días (requiere sesión).</td></tr>
    <tr><td><code>/api/dashboard/cleanup-expired-otp</code></td><td>POST</td><td>Elimina usuarios no verificados con OTP expirado (requiere sesión).</td></tr>
  </tbody>
</table>

<h3>Usuarios</h3>
<p>Acciones via <code>/api/usuarios/{action}</code>. Por defecto: <code>listar</code>.</p>
<table>
  <thead>
    <tr><th>Action</th><th>Método</th><th>Payload</th><th>Notas</th></tr>
  </thead>
  <tbody>
    <tr><td><code>listar</code></td><td>GET</td><td>-</td><td>Devuelve lista de usuarios (requiere sesión).</td></tr>
    <tr><td><code>crear</code></td><td>POST</td><td><code>{ usuario, email, password }</code></td><td>Crea usuario verificado (requiere sesión).</td></tr>
    <tr><td><code>editar</code></td><td>POST</td><td><code>{ id, usuario, email }</code></td><td>Actualiza datos (requiere sesión).</td></tr>
    <tr><td><code>eliminar</code></td><td>POST</td><td><code>{ id }</code></td><td>No permite borrar el usuario en sesión (requiere sesión).</td></tr>
    <tr><td><code>toggle_verificado</code></td><td>POST</td><td><code>{ id, verificado }</code></td><td>Verifica o suspende (requiere sesión).</td></tr>
    <tr><td><code>resetear_facial</code></td><td>POST</td><td><code>{ id }</code></td><td>Elimina descriptor facial (requiere sesión).</td></tr>
    <tr><td><code>guardar_facial</code></td><td>POST</td><td><code>{ id, descriptor: number[128] }</code></td><td>Guarda biometría facial (requiere sesión).</td></tr>
    <tr><td><code>actualizar_etapa</code></td><td>POST</td><td><code>{ id, etapa }</code></td><td>Acción pública.</td></tr>
    <tr><td><code>cancelar_registro</code></td><td>POST</td><td><code>{ id }</code></td><td>Acción pública (elimina solo no verificados).</td></tr>
  </tbody>
</table>

<h3>Configuración</h3>
<table>
  <thead>
    <tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr>
  </thead>
  <tbody>
    <tr><td><code>/api/system-config</code></td><td>GET</td><td>Lee <code>config/system_config.json</code> (requiere sesión).</td></tr>
    <tr><td><code>/api/system-config</code></td><td>POST</td><td>Guarda config (requiere sesión).</td></tr>
    <tr><td><code>/api/config/flags</code></td><td>GET</td><td>Lee flags de hooks (mezcla <code>config/hooks.php</code> + cache) .</td></tr>
    <tr><td><code>/api/config/flags</code></td><td>POST</td><td>Guarda flags en <code>storage/cache/hook-flags.json</code>.</td></tr>
  </tbody>
</table>

<h3>Face Login</h3>
<table>
  <thead>
    <tr><th>Endpoint</th><th>Método</th><th>Descripción</th></tr>
  </thead>
  <tbody>
    <tr><td><code>/api/face-login</code></td><td>POST</td><td>Recibe <code>{ descriptor: number[128] }</code>, busca el match y dispara OTP.</td></tr>
    <tr><td><code>/api/legacy/face-login</code></td><td>POST</td><td>Alias legacy del endpoint anterior.</td></tr>
  </tbody>
</table>

<hr>

<h2 id="configuracion">Configuración</h2>

<h3>Base de datos</h3>
<p>Archivo: <code>config/database.php</code></p>
<pre><code>return [
  'driver' =&gt; 'mysql',
  'host' =&gt; 'localhost',
  'port' =&gt; 3306,
  'database' =&gt; 'sistema_login',
  'username' =&gt; 'root',
  'password' =&gt; '',
  'charset' =&gt; 'utf8mb4',
];
</code></pre>

<p>Script SQL: <code>database/sistema_login.sql</code> crea la tabla <code>usuarios</code> (con OTP, estado verificado y descriptor facial).</p>

<h3>Config del sistema (JSON)</h3>
<p>Archivo: <code>config/system_config.json</code></p>
<ul>
  <li><code>face_threshold</code>: umbral para match facial (0.10 a 1.00).</li>
  <li><code>otp_expiracion_min</code>: minutos de expiración OTP (1 a 60).</li>
  <li><code>max_intentos_login</code>: intentos máximos (1 a 20).</li>
</ul>

<h3>SMTP (env o archivo)</h3>
<p>
  El envío de OTP está en <code>config/mailer.php</code>. La configuración se resuelve así:
</p>
<ol>
  <li>Primero variables de entorno: <code>HEVELAB_SMTP_HOST</code>, <code>HEVELAB_SMTP_USER</code>, <code>HEVELAB_SMTP_PASS</code> (opcional: <code>HEVELAB_SMTP_PORT</code>, <code>HEVELAB_FROM_EMAIL</code>, <code>HEVELAB_FROM_NAME</code>).</li>
  <li>Si no hay env, intenta leer <code>config/mailer_credentials.php</code>.</li>
  <li>Si no está configurado, hace fallback a log: guarda el OTP en <code>storage/logs/otp.log</code> (útil en local).</li>
</ol>

<p>Ejemplo de archivo (no se versiona): copia <code>config/mailer_credentials.php.example</code> a <code>config/mailer_credentials.php</code> y completa:</p>
<pre><code>return [
  'host' =&gt; 'smtp.tudominio.com',
  'port' =&gt; 587,
  'user' =&gt; 'no-reply@tudominio.com',
  'pass' =&gt; '***',
  'secure' =&gt; 'starttls', // o 'smtps'
  'from_email' =&gt; 'no-reply@tudominio.com',
  'from_name' =&gt; 'HEVELAB · VIISION ERP',
];
</code></pre>

<hr>

<h2 id="autenticacion">Autenticación (OTP y Face Login)</h2>

<h3>OTP</h3>
<ul>
  <li>El OTP se genera con <code>random_int(100000, 999999)</code> y expira según <code>otp_expiracion_min</code>.</li>
  <li>Se guarda en DB (tabla <code>usuarios</code>): <code>otp_code</code> y <code>otp_expiracion</code>.</li>
  <li>El flujo <code>registro</code> marca usuario como verificado al validar OTP.</li>
  <li>En local, si SMTP no está configurado, el OTP se registra en <code>storage/logs/otp.log</code>.</li>
</ul>

<h3>Face Login</h3>
<ul>
  <li>El frontend captura un descriptor facial (128 dimensiones).</li>
  <li>El backend compara distancias euclidianas contra usuarios verificados con descriptor guardado.</li>
  <li>Si la distancia &lt; <code>face_threshold</code>, inicia el flujo OTP (login) para reforzar seguridad.</li>
  <li>Los modelos para face-api están en <code>public/assets/models/face-api</code>.</li>
</ul>

<hr>

<h2 id="diseño-y-paleta-de-colores">Diseño y Paleta de Colores</h2>
<p>
  El sistema de diseño se basa en variables CSS en <code>public/assets/css/layouts/base.css</code> y se adapta a modo oscuro con la clase <code>body.theme-dark</code>.
</p>

<h3>Tokens (Light)</h3>
<table>
  <thead>
    <tr><th>Token</th><th>Hex</th><th>Preview</th></tr>
  </thead>
  <tbody>
    <tr><td><code>--bg</code></td><td><code>#f4f7fb</code></td><td><img alt="#f4f7fb" src="https://via.placeholder.com/80x18/f4f7fb/000000?text=+" /></td></tr>
    <tr><td><code>--surface</code></td><td><code>#ffffff</code></td><td><img alt="#ffffff" src="https://via.placeholder.com/80x18/ffffff/000000?text=+" /></td></tr>
    <tr><td><code>--text</code></td><td><code>#0a0c10</code></td><td><img alt="#0a0c10" src="https://via.placeholder.com/80x18/0a0c10/ffffff?text=+" /></td></tr>
    <tr><td><code>--primary</code></td><td><code>#008ba8</code></td><td><img alt="#008ba8" src="https://via.placeholder.com/80x18/008ba8/ffffff?text=+" /></td></tr>
    <tr><td><code>--primary-2</code></td><td><code>#00c4d4</code></td><td><img alt="#00c4d4" src="https://via.placeholder.com/80x18/00c4d4/000000?text=+" /></td></tr>
    <tr><td><code>--warning</code></td><td><code>#f59e0b</code></td><td><img alt="#f59e0b" src="https://via.placeholder.com/80x18/f59e0b/000000?text=+" /></td></tr>
    <tr><td><code>--danger</code></td><td><code>#ef4444</code></td><td><img alt="#ef4444" src="https://via.placeholder.com/80x18/ef4444/ffffff?text=+" /></td></tr>
  </tbody>
</table>

<h3>Tokens (Dark)</h3>
<p>Modo oscuro se activa con <code>body.theme-dark</code>. Los tokens clave cambian, por ejemplo:</p>
<ul>
  <li><code>--bg</code>: <code>#0b0f14</code></li>
  <li><code>--surface</code>: <code>#111827</code></li>
  <li><code>--text</code>: <code>#e6edf3</code></li>
  <li><code>--primary</code>: <code>#00c4d4</code></li>
</ul>

<h3>Componentes UI</h3>
<ul>
  <li><strong>Layout</strong>: <code>app/Views/layouts/app.php</code> + estilos base.</li>
  <li><strong>Componentes</strong>: topbar/sidebar/footer en <code>app/Views/components</code> con CSS en <code>public/assets/css/components</code>.</li>
  <li><strong>Páginas</strong>: CSS por vista en <code>public/assets/css/pages</code>.</li>
  <li><strong>Toasts</strong>: <code>public/assets/js/legacy/toast.js</code> + <code>public/assets/css/components/toast.css</code>.</li>
</ul>

<hr>

<h2 id="instalacion-local">Instalación Local</h2>

<h3>Requisitos</h3>
<ul>
  <li>PHP 8+ (recomendado: 8.1/8.2)</li>
  <li>MySQL/MariaDB</li>
  <li>Apache (opcional, si quieres usar <code>.htaccess</code>)</li>
</ul>

<h3>1) Base de datos</h3>
<ol>
  <li>Crea la DB y tabla importando <code>database/sistema_login.sql</code>.</li>
  <li>Configura credenciales en <code>config/database.php</code>.</li>
</ol>

<h3>2) SMTP (opcional pero recomendado)</h3>
<ol>
  <li>Copia <code>config/mailer_credentials.php.example</code> a <code>config/mailer_credentials.php</code>.</li>
  <li>Completa host/user/pass/puerto.</li>
</ol>

<h3>3) Ejecutar</h3>
<p><strong>Opción A: Apache (mod_rewrite)</strong></p>
<ul>
  <li>Apunta el DocumentRoot al proyecto (o a una carpeta virtual) y asegúrate de tener <code>AllowOverride All</code> para que funcione <code>.htaccess</code>.</li>
</ul>

<p><strong>Opción B: Servidor embebido de PHP</strong></p>
<pre><code>php -S localhost:8000 index.php</code></pre>
<p>Abre: <code>http://localhost:8000</code></p>

<hr>

<h2 id="tests">Tests</h2>
<p>El repo incluye un runner simple de validaciones:</p>
<pre><code>php tests/run.php</code></pre>
<p>Este script valida que existan archivos clave y que el router soporte match básico y navegación asíncrona.</p>

<hr>

<h2 id="seguridad">Seguridad</h2>
<ul>
  <li><strong>Secrets</strong>: <code>config/mailer_credentials.php</code> está ignorado en Git para evitar subir credenciales.</li>
  <li><strong>OTP</strong>: en local puede ir a log si no hay SMTP; en producción se recomienda SMTP obligatorio.</li>
  <li><strong>Face Login</strong>: aun con match facial, se exige OTP como segundo factor.</li>
  <li><strong>Sesiones</strong>: el panel protege endpoints sensibles validando <code>$_SESSION['user_id']</code>.</li>
</ul>

