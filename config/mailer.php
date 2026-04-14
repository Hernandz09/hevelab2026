<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../src/Exception.php';
require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';

/**
 * Envía el código OTP al correo del usuario.
 *
 * @param string $destinatario  Email del receptor
 * @param int    $otp           Código de 6 dígitos
 * @param string $tipo          'login' | 'registro' | 'reset'
 */
function enviarOTP(string $destinatario, int $otp, string $tipo = 'login'): bool
{
  $mail = new PHPMailer(true);

  // ── Contenido según tipo ────────────────────────────────────
  $config = match ($tipo) {
      'registro' => [
      'asunto' => '¡Activa tu cuenta! — HEVELAB',
      'titulo' => '¡Bienvenido a VIISION!',
      'subtitulo' => 'Solo un paso más para comenzar',
      'cuerpo' => 'Gracias por unirte a HEVELAB. Ingresa el siguiente código en la aplicación para activar tu cuenta y comenzar a usar VIISION ERP.',
      'expira' => '5 minutos',
      'tag' => 'CÓDIGO DE ACTIVACIÓN',
      'color' => '#3fb950',
    ],
      'reset' => [
      'asunto' => 'Restablece tu contraseña — HEVELAB',
      'titulo' => 'Solicitud de contraseña',
      'subtitulo' => 'Recibimos tu solicitud de recuperación',
      'cuerpo' => 'Recibimos una solicitud para restablecer la contraseña de tu cuenta. Usa el siguiente código para continuar el proceso de recuperación.',
      'expira' => '10 minutos',
      'tag' => 'CÓDIGO DE RECUPERACIÓN',
      'color' => '#f59e0b',
    ],
      default => [
      'asunto' => 'Tu código de acceso — HEVELAB',
      'titulo' => 'Verificación de identidad',
      'subtitulo' => 'Confirmación de inicio de sesión',
      'cuerpo' => 'Detectamos un inicio de sesión en tu cuenta. Si fuiste tú, ingresa el siguiente código para confirmar tu identidad.',
      'expira' => '5 minutos',
      'tag' => 'CÓDIGO DE ACCESO',
      'color' => '#00c4d4',
    ],
    };

  $otpStr = (string)$otp;
  $year = date('Y');
  $color = $config['color'];

  // ── Rutas de imágenes ──────────────────────────────────────
  $logoPath = __DIR__ . '/../assets/img/logodark_02.png';
  $hevyPath = __DIR__ . '/../assets/img/good.png';
  $hasLogo = file_exists($logoPath);
  $hasHevy = file_exists($hevyPath);

  // ── Plantilla HTML ─────────────────────────────────────────
  $htmlBody = <<<HTML


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{$config['asunto']}</title>
</head>
<body style="margin:0;padding:0;background:#0d1117;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background:#0d1117;padding:16px 16px;">
  <tr><td align="center">

    <!-- Tarjeta contenedor -->
    <table width="580" cellpadding="0" cellspacing="0" border="0"
           style="max-width:580px;width:100%;background:#161b22;
                  border-radius:20px;overflow:hidden;
                  box-shadow:0 8px 48px rgba(0,0,0,0.6);
                  border:1px solid #21262d;">

      <!-- ══ HERO HEADER con Hevy ══ -->
      <tr>
        <td style="background:linear-gradient(135deg,#0d1117 0%,#0d2233 40%,#0d1a2e 100%);
                   padding:20px 32px;text-align:center;overflow:hidden;">

          <!-- Encabezado: Logo Izquierda, Hevy Derecha -->
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td width="55%" align="left" style="vertical-align:middle;">
HTML;

  // Logo
  if ($hasLogo) {
    $htmlBody .= '<img src="cid:hv_logo" width="130" height="auto" alt="HEVELAB" style="display:block;border:0;">';
  }
  else {
    $htmlBody .= '<p style="margin:0;font-size:20px;font-weight:800;color:#fff;letter-spacing:1px;">/// HEVELAB</p>';
  }

  $htmlBody .= <<<HTML
              </td>
              <td width="45%" align="right" style="vertical-align:middle;">
HTML;

  // Hevy
  if ($hasHevy) {
    $htmlBody .= '<img src="cid:hv_hevy" width="160" height="auto" alt="Hevy HEVELAB" style="display:block;margin-left:auto;">';
  }

  $htmlBody .= <<<HTML
              </td>
            </tr>
          </table>

          <!-- Barra de acento -->
          <div style="height:3px;background:linear-gradient(90deg,{$color} 0%,#007cba 100%);margin-top:16px;"></div>
        </td>
      </tr>

      <!-- ══ CUERPO ══ -->
      <tr>
        <td style="padding:36px 36px 28px;background:#161b22;">

          <!-- Chip tag -->
          <span style="display:inline-block;
                    padding:4px 14px;border-radius:99px;
                    background:rgba(0,196,212,0.10);
                    border:1px solid rgba(0,196,212,0.25);
                    font-size:10px;font-weight:700;
                    letter-spacing:2px;color:{$color};text-transform:uppercase;
                    margin-bottom:14px;">
            {$config['tag']}
          </span>

          <!-- Título -->
          <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;
                     color:#e6edf3;line-height:1.2;">
            {$config['titulo']}
          </h1>

          <!-- Subtítulo -->
          <p style="margin:0 0 20px;font-size:13px;font-weight:500;
                    color:{$color};letter-spacing:0.3px;">
            {$config['subtitulo']}
          </p>

          <!-- Descripción -->
          <p style="margin:0 0 30px;font-size:14.5px;color:#8b949e;line-height:1.7;">
            {$config['cuerpo']}
          </p>

          <!-- ══ CAJA OTP ══ -->
          <table width="100%" cellpadding="0" cellspacing="0" border="0"
                 style="margin-bottom:28px;">
            <tr>
              <td align="center"
                  style="background:#0d1117;
                         border:1px solid #30363d;
                         border-top:3px solid {$color};
                         border-radius:16px;
                         padding:28px 20px;">

                <p style="margin:0 0 16px;font-size:10px;font-weight:700;
                           letter-spacing:2px;color:#8b949e;text-transform:uppercase;">
                  Tu código de verificación
                </p>

                <!-- Dígitos -->
                <table cellpadding="0" cellspacing="0" border="0"
                       style="margin:0 auto 16px;">
                  <tr>
HTML;

  // Dígitos OTP individuales
  foreach (str_split($otpStr) as $digit) {
    $htmlBody .= '
                    <td style="padding:0 5px;">
                      <div style="width:46px;height:58px;
                                   background:#21262d;
                                   border:1px solid #30363d;
                                   border-bottom:3px solid ' . $color . ';
                                   border-radius:12px;
                                   font-size:30px;font-weight:800;
                                   color:#e6edf3;
                                   font-family:\'Courier New\',monospace;
                                   text-align:center;line-height:58px;">
                        ' . $digit . '
                      </div>
                    </td>';
  }

  $htmlBody .= <<<HTML
                  </tr>
                </table>

                <p style="margin:0;font-size:12px;color:#8b949e;">
                  Expira en <strong style="color:#e6edf3;">{$config['expira']}</strong>
                  &nbsp;·&nbsp; No lo compartas con nadie
                </p>
              </td>
            </tr>
          </table>

          <!-- ══ AVISO DE SEGURIDAD ══ -->
          <table width="100%" cellpadding="0" cellspacing="0" border="0"
                 style="margin-bottom:8px;">
            <tr>
              <td style="background:#1a1400;
                         border-left:4px solid #f59e0b;
                         border-radius:0 10px 10px 0;
                         padding:14px 18px;">
                <p style="margin:0;font-size:13px;color:#d97706;line-height:1.65;">
                  🔒 <strong>HEVELAB nunca te pedirá este código</strong> por teléfono, chat ni correo.<br>
                  Si no realizaste esta acción, ignora este mensaje — tu cuenta está segura.
                </p>
              </td>
            </tr>
          </table>

        </td>
      </tr>

      <!-- ══ FOOTER ══ -->
      <tr>
        <td style="padding:20px 36px;background:#0d1117;
                   border-top:1px solid #21262d;border-radius:0 0 20px 20px;">
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td>
                <p style="margin:0;font-size:11.5px;color:#484f58;line-height:1.8;">
                  &copy; {$year} <strong style="color:#8b949e;">HEVELAB</strong><br>
                  Mensaje automático, por favor no respondas este correo.
                </p>
              </td>
              <td align="right" style="vertical-align:middle;">
                <p style="margin:0;font-size:22px;font-weight:900;
                          color:{$color};letter-spacing:4px;opacity:0.5;">
                  ///
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>

    </table>
    <!-- Fin tarjeta -->

  </td></tr>
</table>

</body>
</html>
HTML;

  $altBody = "Código OTP ({$config['tag']}): {$otpStr}\n\nVálido por {$config['expira']}.\nSi no realizaste esta acción, ignora este mensaje.";

  try {
    // ── SMTP ────────────────────────────────────────────────
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hernandz.j2004@gmail.com';
    $mail->Password = 'fqvf oxty vors utjp';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // ── Remitente y destinatario ────────────────────────────
    $mail->setFrom('hernandz.j2004@gmail.com', 'HEVELAB · VIISION ERP');
    $mail->addAddress($destinatario);

    // ── Imágenes embebidas (CID) ────────────────────────────
    if ($hasLogo) {
      $mail->addEmbeddedImage($logoPath, 'hv_logo', 'logo.png', 'base64', 'image/png');
    }
    if ($hasHevy) {
      $mail->addEmbeddedImage($hevyPath, 'hv_hevy', 'hevy.png', 'base64', 'image/png');
    }

    // ── Contenido ───────────────────────────────────────────
    $mail->isHTML(true);
    $mail->Subject = $config['asunto'];
    $mail->Body = $htmlBody;
    $mail->AltBody = $altBody;

    $mail->send();
    return true;

  }
  catch (Exception $e) {
    error_log("Error al enviar correo: " . $mail->ErrorInfo);
    return false;
  }
}
?>
