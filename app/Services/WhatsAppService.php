<?php

declare(strict_types=1);

namespace App\Services;

/**
 * WhatsApp notifications via Twilio REST API (no SDK, puro cURL).
 *
 * Configuración en .env:
 *   TWILIO_SID     = ACxxxxxxxxxxxxx
 *   TWILIO_TOKEN   = xxxxxxxxxxxxxxx
 *   TWILIO_FROM    = whatsapp:+14155238886   ← Sandbox; en prod tu número aprobado
 *   STUDIO_NAME    = InkManager              ← Aparece en los mensajes
 */
class WhatsAppService
{
    private string $sid;
    private string $token;
    private string $from;
    private string $studioName;
    private bool   $enabled;

    public function __construct()
    {
        $this->sid        = $_ENV['TWILIO_SID']   ?? '';
        $this->token      = $_ENV['TWILIO_TOKEN'] ?? '';
        $this->from       = $_ENV['TWILIO_FROM']  ?? 'whatsapp:+14155238886';
        $this->studioName = $_ENV['STUDIO_NAME']  ?? 'InkManager';
        $this->enabled    = $this->sid !== '' && $this->token !== '';
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    // ── Plantillas de mensajes ─────────────────────────────────────────────────

    public function confirmacion(string $phone, string $nombre, string $fechaInicio, int $duracionMin): bool
    {
        $fecha = date('d/m/Y \a \l\a\s H:i', strtotime($fechaInicio));
        $msg   = "🖤 ¡Hola {$nombre}! Tu turno en *{$this->studioName}* está agendado para el {$fecha}. "
               . "Duración: {$duracionMin} min. ¡Te esperamos!";
        return $this->send($phone, $msg);
    }

    public function estadoCambiado(string $phone, string $nombre, string $estado, string $fechaInicio): bool
    {
        $fecha = date('d/m/Y \a \l\a\s H:i', strtotime($fechaInicio));
        $tpl   = [
            'confirmado' => "✅ ¡Hola {$nombre}! Tu turno del {$fecha} en *{$this->studioName}* está *confirmado*. ¡Te esperamos!",
            'cancelado'  => "❌ ¡Hola {$nombre}! Tu turno del {$fecha} en *{$this->studioName}* fue *cancelado*. Escribinos para reagendar.",
        ];
        if (!isset($tpl[$estado])) return false;
        return $this->send($phone, $tpl[$estado]);
    }

    public function recordatorio(string $phone, string $nombre, string $fechaInicio, int $duracionMin): bool
    {
        $hora = date('H:i', strtotime($fechaInicio));
        $msg  = "📅 ¡Hola {$nombre}! Te recordamos que *hoy a las {$hora}* tenés turno en *{$this->studioName}*. "
              . "Duración: {$duracionMin} min. ¡Nos vemos!";
        return $this->send($phone, $msg);
    }

    // ── Core ───────────────────────────────────────────────────────────────────

    public function send(string $phone, string $body): bool
    {
        if (!$this->enabled) return false;

        $to = $this->formatPhone($phone);
        if ($to === '') return false;

        $url  = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
        $post = http_build_query([
            'From' => $this->from,
            'To'   => 'whatsapp:' . $to,
            'Body' => $body,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_USERPWD        => "{$this->sid}:{$this->token}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) return false;

        $json = json_decode($response, true);
        // Twilio devuelve 201 Created + un SID de mensaje en éxito
        return $code === 201 && !empty($json['sid']);
    }

    /**
     * Normaliza el teléfono a formato E.164 (+XXXXXXXXXXX).
     * Si no empieza con +, asume Argentina (+54).
     */
    private function formatPhone(string $phone): string
    {
        // Quitar espacios, guiones, paréntesis, puntos
        $phone = preg_replace('/[\s\-\(\)\.]/', '', trim($phone));

        if ($phone === '') return '';

        if (!str_starts_with($phone, '+')) {
            // Quitar 0 inicial (Argentina local) y anteponer +54
            $phone = '+54' . ltrim($phone, '0');
        }

        // Debe coincidir con E.164: + seguido de 7–15 dígitos
        return preg_match('/^\+\d{7,15}$/', $phone) ? $phone : '';
    }
}
