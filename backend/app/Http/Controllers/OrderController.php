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
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="List orders with optional filtering and pagination",
     *     description="Retrieve a list of orders with optional filters for name, description, creation date and pagination support",
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersPage"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersRowsPerPage"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersName"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersDescription"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersCreationDate"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersWithDetails"),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Array of orders",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="ulid", description="Order ID", example="01HV5R2K3M4N5P6Q7R8S9T0U1V"),
     *                     @OA\Property(property="name", type="string", description="Order name", example="Urgent delivery"),
     *                     @OA\Property(property="description", type="string", description="Order description", example="Order for Mr.Rossi in Catanzaro"),
     *                     @OA\Property(property="status", type="string", description="Order status", example="delivered"),
     *                     @OA\Property(property="createdAt", type="string", format="date-time", description="Creation timestamp", example="2024-01-15 10:30:00"),
     *                     @OA\Property(property="updatedAt", type="string", format="date-time", description="Last update timestamp", example="2024-01-15 10:30:00"),
     *                     @OA\Property(
     *                         property="orderItems",
     *                         type="array",
     *                         description="Order items (included when withDetails=true)",
     *                         @OA\Items(
     *                             @OA\Property(property="productId", type="string", format="ulid", description="Product ID", example="01HW6S3L4N5O6P7Q8R9S0T1U2W"),
     *                             @OA\Property(property="quantity", type="integer", description="Quantity ordered", example=2),
     *                             @OA\Property(property="productName", type="string", description="Product name", example="Laptop Dell XPS")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=422, ref="#/components/responses/InvalidArgument"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
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
                    withDetails: $request->toDto()->withDetails
                )
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Get a specific order by ID",
     *     description="Retrieve detailed information about a specific order using its unique identifier",
     *     @OA\Parameter(
     *         name="orderId",
     *         in="path",
     *         required=true,
     *         description="The unique identifier of the order",
     *         @OA\Schema(type="string", format="ulid", example="01HV5R2K3M4N5P6Q7R8S9T0U1V")
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/FindOrderWithDetails"),
     *     @OA\Response(
     *         response=200,
     *         description="Order details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Order details",
     *                 @OA\Property(property="id", type="string", format="ulid", description="Order ID", example="01HV5R2K3M4N5P6Q7R8S9T0U1V"),
     *                 @OA\Property(property="name", type="string", description="Order name", example="Urgent delivery"),
     *                 @OA\Property(property="description", type="string", description="Order description", example="Order for Mr.Rossi in Catanzaro"),
     *                 @OA\Property(property="status", type="string", description="Order status", example="delivered"),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", description="Creation timestamp", example="2024-01-15 10:30:00"),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", description="Last update timestamp", example="2024-01-15 10:30:00"),
     *                 @OA\Property(
     *                     property="orderItems",
     *                     type="array",
     *                     description="Order items (included when withDetails=true)",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", format="ulid", description="Order item ID", example="01HW6S3L4N5O6P7Q8R9S0T1U2W"),
     *                         @OA\Property(property="quantity", type="integer", description="Quantity ordered", example=2),
     *                         @OA\Property(property="createdAt", type="string", format="date-time", description="Item creation timestamp", example="2024-01-15 10:30:00"),
     *                         @OA\Property(property="updatedAt", type="string", format="date-time", description="Item update timestamp", example="2024-01-15 10:30:00")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
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
            data: $order->toArrayResponse(
                withDetails: $request->toDto()->withDetails
            )
        );

    }

    /**
     * @OA\Post(
     *     path="/api/orders",
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
     *     path="/api/orders/{orderId}",
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
     *     path="/api/orders/{orderId}",
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
