<?php
// app/Http/Controllers/ReturnRequestController.php
namespace App\Http\Controllers;

use App\Models\ReturnRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:1000',
        ]);

        $order = Order::find($request->order_id);

        // Validar elegibilidad del pedido
        if (!$this->isEligible($order)) {
            return response()->json(['message' => 'El pedido no es elegible para devolución'], 422);
        }

        // Verificar si ya existe una solicitud para ese pedido
        $existing = ReturnRequest::where('order_id', $order->id)->first();
        if ($existing) {
            return response()->json(['message' => 'Ya existe una solicitud de devolución para este pedido'], 422);
        }

        // Lógica para aprobar o enviar a revisión manual
        $status = $this->autoApprove($order) ? ReturnRequest::STATUS_APPROVED : ReturnRequest::STATUS_MANUAL_REVIEW;

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'reason' => $request->reason,
            'status' => $status,
        ]);

        // Aquí puedes generar la guía si fue aprobado automáticamente
        if ($status == ReturnRequest::STATUS_APPROVED) {
            // Generar PDF o guía (implementación pendiente)
            $guideUrl = $this->generateGuide($returnRequest);
            $returnRequest->guide_url = $guideUrl;
            $returnRequest->save();
        }

        return response()->json($returnRequest, 201);
    }

    private function isEligible(Order $order): bool
    {
        // Ejemplo: pedido debe estar entregado y no mayor a 30 días
        $maxDays = 30;
        if ($order->status !== 'delivered') {
            return false;
        }
        return $order->delivered_at->diffInDays(now()) <= $maxDays;
    }

    private function autoApprove(Order $order): bool
    {
        // Ejemplo simple: aprueba automáticamente si el pedido es reciente
        return $order->delivered_at->diffInDays(now()) <= 15;
    }

    private function generateGuide(ReturnRequest $returnRequest): string
    {
        // Implementar generación de PDF o guía y devolver URL o path
        // Por ejemplo, guardar archivo en storage/app/returns/ y retornar ruta pública
        return '/storage/returns/guide123.pdf'; // placeholder
    }
}
