<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\DeleteOrderRequest;
use App\Http\Requests\FindOrderRequest;
use App\Http\Requests\ListOrdersRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\UseCases\OrderUseCase;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="Orders API"
 * )
 */
class OrderController extends Controller
{
    public function __construct(
        protected OrderUseCase $orderUseCase,
    ){}

    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Elenco ordini",
     *     @OA\Response(response=200, description="Lista ordini")
     * )
     * @throws \DateMalformedStringException
     */
    public function list(ListOrdersRequest $request): JsonResponse
    {
        $orders = $this->orderUseCase->list(
            $request->toDto()
        );
        return response()->json(
            data: $orders->map(
                fn($order) => $order->toArrayResponse(
                    !empty($request->validated('withDetails')
                    )
                )
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Visualizza un ordine",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order Details"),
     *     @OA\Response(response=404, description="Ordine non trovato")
     * )
     */
    public function find(FindOrderRequest $request): JsonResponse
    {
        $order = $this->orderUseCase->get(
            $request->toDto()
        );
        if (empty($order)) {
            return response()->json(
                ['error' => 'Order not found'],
                404
            );
        }
        return response()->json(
            data: $order->toArrayResponse(!empty($request->validated('withDetails')))
        );

    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Create new order",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateOrderRequest")
     *     ),
     *     @OA\Response(response=201, description="Order created successfully"),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=422, ref="#/components/responses/InvalidArgument"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     * @throws \Exception
     */
    public function create(CreateOrderRequest $request): JsonResponse
    {
        $this->orderUseCase->create(
            $request->toDto()
        );
        return response()->json(
            status: 201
        );

    }

    /**
     * @OA\Patch(
     *     path="/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Aggiorna un ordine",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="shipped")
     *         )
     *     ),
     *      @OA\Response(response=200, description="Order updated successfully"),
     *      @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *      @OA\Response(response=422, ref="#/components/responses/InvalidArgument"),
     *      @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     * @throws \Exception
     */
    public function update(UpdateOrderRequest $request): JsonResponse
    {
        $this->orderUseCase
            ->update(
                $request->toDto()
            );
        return response()->json(
            status: 201
        );
    }

    /**
     * @OA\Delete(
     *     path="/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Elimina un ordine",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Ordine eliminato"),
     *     @OA\Response(response=404, description="Ordine non trovato")
     * )
     * @throws \Exception
     */
    public function delete(DeleteOrderRequest $request): JsonResponse
    {
        $this->orderUseCase->delete(
            $request->toDto()
        );
        return response()->json();
    }
}
