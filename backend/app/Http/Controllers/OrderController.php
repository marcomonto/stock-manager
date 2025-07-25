<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\DeleteOrderRequest;
use App\Http\Requests\FindOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
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
    public function find(FindOrderRequest $request): JsonResponse
    {
        $this->orderUseCase->find(
            $request->toDto()
        );
        //TODO add eloquent models to response
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
