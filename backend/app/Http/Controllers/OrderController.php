<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\UseCases\OrderUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    )
    {

    }

    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Elenco ordini",
     *     @OA\Response(response=200, description="Lista ordini")
     * )
     */
    public function list()
    { /* ... */
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
     *     @OA\Response(response=200, description="Dettaglio ordine"),
     *     @OA\Response(response=404, description="Ordine non trovato")
     * )
     */
    public function find($orderId)
    { /* ... */
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
     */
    public function create(CreateOrderRequest $request): JsonResponse
    {
        $this->orderUseCase->create(
            $request->toDto(
                $request->validated()
            )
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
     *     @OA\Response(response=200, description="Ordine aggiornato"),
     *     @OA\Response(response=404, description="Ordine non trovato")
     * )
     */
    public function patch(Request $request, $orderId)
    { /* ... */
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
     */
    public function delete($orderId)
    { /* ... */
    }
}
