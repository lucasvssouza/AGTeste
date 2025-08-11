<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    'customer_name' => ['required', 'string', 'max:255'],
                    'order_date'    => ['required', 'date'],
                    'delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
                    'status'        => ['required', Rule::in(['pendente', 'entregue', 'cancelado'])],
                ],
                [
                    'customer_name.required'       => 'O campo cliente é obrigatório.',
                    'customer_name.string'         => 'O nome do cliente deve ser um texto válido.',
                    'customer_name.max'            => 'O nome do cliente não pode ter mais que :max caracteres.',
                    'order_date.required'          => 'A data do pedido é obrigatória.',
                    'order_date.date'              => 'A data do pedido deve ser uma data válida.',
                    'delivery_date.date'           => 'A data de entrega deve ser uma data válida.',
                    'delivery_date.after_or_equal' => 'A data de entrega deve ser igual ou posterior à data do pedido.',
                    'status.required'              => 'O status do pedido é obrigatório.',
                    'status.in'                    => 'O status informado é inválido. Use: pendente, entregue ou cancelado.',
                ]
            );

            $order = Order::create($data)->fresh();

            $payload = $this->payload($order);
            return response()->json(['success' => true, 'msg' => 'Pedido criado com sucesso!'] + $payload, 201);

        } catch (ValidationException $e) {
            $first = collect($e->errors())->flatten()->first() ?? 'Dados inválidos.';
            return response()->json([
                'success' => false,
                'message' => $first,
                'errors'  => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Erro ao criar pedido', ['e' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao processar sua solicitação.',
            ], 500);
        }
    }

    public function update(Request $request, Order $order)
    {
        try {
            $data = $request->validate(
                [
                    'customer_name' => ['required', 'string', 'max:255'],
                    'order_date'    => ['required', 'date'],
                    'delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
                    'status'        => ['required', Rule::in(['pendente', 'entregue', 'cancelado'])],
                ]
            );

            $order->update($data);
            $order->refresh();

            $payload = $this->payload($order);
            return response()->json(['success' => true, 'msg' => 'Pedido atualizado com sucesso!'] + $payload, 200);

        } catch (ValidationException $e) {
            $first = collect($e->errors())->flatten()->first() ?? 'Dados inválidos.';
            return response()->json([
                'success' => false,
                'message' => $first,
                'errors'  => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Erro ao atualizar pedido', ['id' => $order->id ?? null, 'e' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao processar sua solicitação.',
            ], 500);
        }
    }

    public function destroy(Request $request, Order $order)
    {
        try {
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pedido apagado com sucesso.',
            ]);

        } catch (Throwable $e) {
            Log::error('Erro ao apagar pedido', ['id' => $order->id ?? null, 'e' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao processar sua solicitação.',
            ], 500);
        }
    }

    private function payload(Order $o): array
    {
        return [
            'id'                      => $o->id,
            'customer_name'           => $o->customer_name,
            'status'                  => $o->status,
            'order_date'              => $this->toDateString($o->order_date),
            'delivery_date'           => $this->toDateString($o->delivery_date),
            'order_date_formatted'    => $this->formatBR($o->order_date),
            'delivery_date_formatted' => $this->formatBR($o->delivery_date),
        ];
    }

    private function toDateString($value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function formatBR($value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('d/m/Y');
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (Throwable) {
            return null;
        }
    }
}
