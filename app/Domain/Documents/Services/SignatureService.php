<?php

declare(strict_types=1);

namespace App\Domain\Documents\Services;

use App\Domain\Documents\Enums\SignatureStatus;
use App\Domain\Documents\Models\SignatureRequest;
use App\Domain\Orders\Models\Order;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

/**
 * Firma electrónica desde el celular.
 *
 * El flujo es: se genera un enlace con token, se pinta como QR junto al
 * documento, el firmante lo abre en el móvil, traza su firma con el dedo y
 * el trazo queda estampado en el PDF.
 *
 * Lo que da validez bajo la Ley 527 de 1999 no es la imagen del trazo: es
 * poder identificar al firmante y demostrar que el documento no cambió
 * después. Por eso se guarda el rastro completo (cuándo se abrió, desde qué
 * IP y con qué navegador) y el hash SHA-256 del PDF firmado, y por eso el
 * certificado de auditoría va impreso en el propio documento.
 */
final class SignatureService
{
    public function __construct(private readonly LegalDocumentPdfBuilder $documents) {}

    /** Crea la petición de firma de un pedido. */
    public function request(Order $order): SignatureRequest
    {
        $order->loadMissing('document');

        $existing = SignatureRequest::query()
            ->where('order_id', $order->id)
            ->where('status', SignatureStatus::Pending)
            ->first();

        if ($existing && ! $existing->hasExpired()) {
            return $existing;
        }

        return SignatureRequest::query()->create([
            'order_id' => $order->id,
            'token' => bin2hex(random_bytes(24)),
            'signer_name' => $order->customer_name,
            'signer_document' => trim($order->customer_document_type.' '.$order->customer_document_number),
            'status' => SignatureStatus::Pending,
            // Una semana es tiempo de sobra y evita que un enlace quede vivo
            // para siempre en la bandeja de alguien.
            'expires_at' => now()->addWeek(),
        ]);
    }

    /** El QR del enlace, como data URI para meterlo en el PDF o en la web. */
    public function qrDataUri(SignatureRequest $request): string
    {
        // PNG en base64: dompdf no sabe pintar un SVG embebido, y el QR
        // tiene que salir también en el propio documento.
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::M,
            'scale' => 6,
            'outputBase64' => true,
            'quietzoneSize' => 2,
        ]);

        return (new QRCode($options))->render($request->url());
    }

    public function markOpened(SignatureRequest $request): void
    {
        if ($request->opened_at === null) {
            $request->update(['opened_at' => now()]);
        }
    }

    /**
     * Estampa la firma y cierra la evidencia.
     *
     * El PDF se regenera con el trazo y el certificado; el hash se calcula
     * sobre ese archivo final, que es el que tiene que poder compararse si
     * alguien discute después que firmó.
     */
    public function sign(SignatureRequest $request, string $signatureSvg, string $ip, string $userAgent): SignatureRequest
    {
        $order = $request->order()->with('document')->firstOrFail();

        $path = "signatures/{$order->reference}.svg";
        Storage::disk('local')->put($path, $signatureSvg);

        $request->update([
            'status' => SignatureStatus::Signed,
            'signature_data' => $signatureSvg,
            'signature_path' => $path,
            'signed_at' => now(),
            'signed_ip' => $ip,
            'signed_user_agent' => substr($userAgent, 0, 255),
        ]);

        $order->update([
            'signature_path' => $path,
            'signed_at' => now(),
        ]);

        // El documento se rehace con la firma dentro.
        $this->documents->build($order->refresh());

        $hash = hash('sha256', (string) Storage::disk('local')->get($order->refresh()->document_path));
        $request->update(['document_hash' => $hash]);

        return $request->refresh();
    }
}
