<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../app/Libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../app/Libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../app/Libs/PHPMailer/SMTP.php';

function hevelabLogOtp(string $destinatario, int $otp, string $tipo, string $reason): void
{
    $root = dirname(__DIR__);
    $dir = $root . '/storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $line = json_encode([
        'ts' => date('Y-m-d H:i:s'),
        'to' => $destinatario,
        'otp' => (string) $otp,
        'tipo' => $tipo,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'reason' => $reason,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (is_string($line)) {
        @file_put_contents($dir . '/otp.log', $line . PHP_EOL, FILE_APPEND);
    }
}

function hevelabLogMailError(string $destinatario, string $tipo, string $errorInfo): void
{
    $root = dirname(__DIR__);
    $dir = $root . '/storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $line = json_encode([
        'ts' => date('Y-m-d H:i:s'),
        'to' => $destinatario,
        'tipo' => $tipo,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'error' => $errorInfo,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (is_string($line)) {
        @file_put_contents($dir . '/mail_error.log', $line . PHP_EOL, FILE_APPEND);
    }
}

function hevelabReadSmtpFileConfig(): ?array
{
    $path = __DIR__ . '/mailer_credentials.php';
    if (!is_file($path)) {
        return null;
    }
    $cfg = require $path;
    if (!is_array($cfg)) {
        return null;
    }

    $host = trim((string) ($cfg['host'] ?? ''));
    $user = trim((string) ($cfg['user'] ?? ''));
    $pass = (string) ($cfg['pass'] ?? '');
    $port = (int) ($cfg['port'] ?? 587);
    $fromEmail = trim((string) ($cfg['from_email'] ?? ''));
    $fromName = (string) ($cfg['from_name'] ?? '');
    $secureRaw = strtolower(trim((string) ($cfg['secure'] ?? 'starttls')));
    $secure = $secureRaw === 'smtps' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

    if ($host === '' || $user === '' || $pass === '') {
        return null;
    }

    return [
        'host' => $host,
        'user' => $user,
        'pass' => $pass,
        'port' => $port > 0 ? $port : 587,
        'secure' => $secure,
        'from_email' => $fromEmail !== '' ? $fromEmail : $user,
        'from_name' => $fromName !== '' ? $fromName : 'HEVELAB · VIISION ERP',
        'source' => 'file',
    ];
}

function hevelabResolveSmtpConfig(): ?array
{
    $host = (string) getenv('HEVELAB_SMTP_HOST');
    $user = (string) getenv('HEVELAB_SMTP_USER');
    $pass = (string) getenv('HEVELAB_SMTP_PASS');

    if ($host !== '' && $user !== '' && $pass !== '') {
        $port = (int) (getenv('HEVELAB_SMTP_PORT') !== false ? getenv('HEVELAB_SMTP_PORT') : 587);
        $fromEmail = (string) (getenv('HEVELAB_FROM_EMAIL') !== false ? getenv('HEVELAB_FROM_EMAIL') : $user);
        $fromName = (string) (getenv('HEVELAB_FROM_NAME') !== false ? getenv('HEVELAB_FROM_NAME') : 'HEVELAB · VIISION ERP');

        return [
            'host' => $host,
            'user' => $user,
            'pass' => $pass,
            'port' => $port > 0 ? $port : 587,
            'secure' => PHPMailer::ENCRYPTION_STARTTLS,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'source' => 'env',
        ];
    }

    return hevelabReadSmtpFileConfig();
}

function enviarOTP(string $destinatario, int $otp, string $tipo = 'login'): bool
{
    $smtp = hevelabResolveSmtpConfig();
    if ($smtp === null) {
        $GLOBALS['hevelab_mail_delivery'] = 'log';
        hevelabLogOtp($destinatario, $otp, $tipo, 'smtp_not_configured');
        return true;
    }

    $mail = new PHPMailer(true);

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

    $otpStr = (string) $otp;
    $year = date('Y');
    $color = $config['color'];

    $logoPath = __DIR__ . '/../public/assets/images/logos/logodark_02.png';
    $hevyPath = __DIR__ . '/../public/assets/images/illustrations/good.png';
    $maxEmbedBytes = 180000;
    $hasLogo = is_file($logoPath) && (int) @filesize($logoPath) > 0 && (int) @filesize($logoPath) <= $maxEmbedBytes;
    $hasHevy = is_file($hevyPath) && (int) @filesize($hevyPath) > 0 && (int) @filesize($hevyPath) <= $maxEmbedBytes;

    $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{$config['asunto']}</title>
</head>
<body style="margin:0;padding:0;background:#0d1117;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0d1117;padding:32px 16px;">
  <tr><td align="center">
    <table width="580" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;width:100%;background:#161b22;border-radius:20px;overflow:hidden;box-shadow:0 8px 48px rgba(0,0,0,0.6);border:1px solid #21262d;">
      <tr>
        <td style="background:linear-gradient(135deg,#0d1117 0%,#0d2233 60%,#0d1a2e 100%);padding:0;text-align:center;overflow:hidden;">
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td style="padding:24px 32px 0;text-align:left;">
HTML;

    if ($hasLogo) {
        $htmlBody .= '<img src="cid:hv_logo" width="130" height="auto" alt="HEVELAB" style="display:block;border:0;margin-bottom:4px;">';
        $htmlBody .= '<p style="margin:0;font-size:9px;font-weight:700;color:' . $color . ';letter-spacing:2.5px;text-transform:uppercase;">VIISION ERP</p>';
    } else {
        $htmlBody .= '<p style="margin:0;font-size:20px;font-weight:800;color:#fff;letter-spacing:1px;">/// HEVELAB</p>';
        $htmlBody .= '<p style="margin:2px 0 0;font-size:9px;font-weight:700;color:' . $color . ';letter-spacing:2.5px;text-transform:uppercase;">VIISION ERP</p>';
    }

    $htmlBody .= <<<HTML
              </td>
            </tr>
          </table>

          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td align="center" style="padding:16px 32px 0;">
HTML;

    if ($hasHevy) {
        $htmlBody .= '<img src="cid:hv_hevy" width="210" height="auto" alt="Hevy HEVELAB" style="display:block;margin:0 auto;">';
    }

    $htmlBody .= <<<HTML
              </td>
            </tr>
          </table>
          <div style="height:3px;background:linear-gradient(90deg,{$color} 0%,#007cba 100%);margin-top:0;"></div>
        </td>
      </tr>

      <tr>
        <td style="padding:36px 36px 28px;background:#161b22;">
          <span style="display:inline-block;padding:4px 14px;border-radius:99px;background:rgba(0,196,212,0.10);border:1px solid rgba(0,196,212,0.25);font-size:10px;font-weight:700;letter-spacing:2px;color:{$color};text-transform:uppercase;margin-bottom:14px;">
            {$config['tag']}
          </span>
          <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;color:#e6edf3;line-height:1.2;">{$config['titulo']}</h1>
          <p style="margin:0 0 20px;font-size:13px;font-weight:500;color:{$color};letter-spacing:0.3px;">{$config['subtitulo']}</p>
          <p style="margin:0 0 30px;font-size:14.5px;color:#8b949e;line-height:1.7;">{$config['cuerpo']}</p>

          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
            <tr>
              <td align="center" style="background:#0d1117;border:1px solid #30363d;border-top:3px solid {$color};border-radius:16px;padding:28px 20px;">
                <p style="margin:0 0 16px;font-size:10px;font-weight:700;letter-spacing:2px;color:#8b949e;text-transform:uppercase;">Tu código de verificación</p>
                <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 16px;">
                  <tr>
HTML;

    foreach (str_split($otpStr) as $digit) {
        $htmlBody .= '<td style="padding:0 5px;"><div style="width:46px;height:58px;background:#21262d;border:1px solid #30363d;border-bottom:3px solid ' . $color . ';border-radius:12px;font-size:30px;font-weight:800;color:#e6edf3;font-family:\'Courier New\',monospace;text-align:center;line-height:58px;">' . htmlspecialchars($digit, ENT_QUOTES, 'UTF-8') . '</div></td>';
    }

    $htmlBody .= <<<HTML
                  </tr>
                </table>
                <p style="margin:0;font-size:12px;color:#8b949e;">Expira en <strong style="color:#e6edf3;">{$config['expira']}</strong>&nbsp;·&nbsp; No lo compartas con nadie</p>
              </td>
            </tr>
          </table>

          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:8px;">
            <tr>
              <td style="background:#1a1400;border-left:4px solid #f59e0b;border-radius:0 10px 10px 0;padding:14px 18px;">
                <p style="margin:0;font-size:13px;color:#d97706;line-height:1.65;">
                  <strong>HEVELAB nunca te pedirá este código</strong> por teléfono, chat ni correo.<br>
                  Si no realizaste esta acción, ignora este mensaje — tu cuenta está segura.
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <tr>
        <td style="padding:20px 36px;background:#0d1117;border-top:1px solid #21262d;border-radius:0 0 20px 20px;">
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td>
                <p style="margin:0;font-size:11.5px;color:#484f58;line-height:1.8;">
                  &copy; {$year} <strong style="color:#8b949e;">HEVELAB</strong> &mdash; VIISION ERP<br>
                  Mensaje automático, por favor no respondas este correo.
                </p>
              </td>
              <td align="right" style="vertical-align:middle;">
                <p style="margin:0;font-size:22px;font-weight:900;color:{$color};letter-spacing:4px;opacity:0.5;">///</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </td></tr>
</table>

</body>
</html>
HTML;

    $altBody = "Código OTP ({$config['tag']}): {$otpStr}\n\nVálido por {$config['expira']}.";

    try {
        $GLOBALS['hevelab_mail_delivery'] = (string) ($smtp['source'] ?? 'smtp');
        $mail->isSMTP();
        $mail->Host = (string) $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string) $smtp['user'];
        $mail->Password = (string) $smtp['pass'];
        $mail->SMTPSecure = $smtp['secure'];
        $mail->Port = (int) $smtp['port'];
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 10;

        $mail->setFrom((string) $smtp['from_email'], (string) $smtp['from_name']);
        $mail->addAddress($destinatario);

        if ($hasLogo) {
            $mail->addEmbeddedImage($logoPath, 'hv_logo', 'logo.png', 'base64', 'image/png');
        }
        if ($hasHevy) {
            $mail->addEmbeddedImage($hevyPath, 'hv_hevy', 'hevy.png', 'base64', 'image/png');
        }

        $mail->isHTML(true);
        $mail->Subject = $config['asunto'];
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();
        return true;
    } catch (Exception) {
        $GLOBALS['hevelab_mail_delivery'] = 'fail';
        hevelabLogOtp($destinatario, $otp, $tipo, 'smtp_send_failed');
        hevelabLogMailError($destinatario, $tipo, (string) ($mail->ErrorInfo ?? ''));
        return false;
    }
}
