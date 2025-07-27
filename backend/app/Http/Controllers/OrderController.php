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
    ) {}

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="List orders with optional filtering and pagination",
     *     description="Retrieve a list of orders with optional filters for name, description, creation date and pagination support",
     *     operationId="listOrders",
     *
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersPage"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersRowsPerPage"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersName"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersDescription"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersCreationDate"),
     *     @OA\Parameter(ref="#/components/parameters/ListOrdersWithDetails"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of orders retrieved successfully",
     *
     *         @OA\JsonContent(
     *             type="array",
     *             description="Array of orders",
     *
     *             @OA\Items(ref="#/components/schemas/OrderResponse")
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     */
    public function list(ListOrdersRequest $request): JsonResponse
    {
        $orders = $this->orderUseCase->list(
            $request->toDto()
        );

        return response()->json(
            data: $orders->map(
                fn ($order) => $order->toArrayResponse(
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
     *     operationId="findOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/FindOrderId"),
     *     @OA\Parameter(ref="#/components/parameters/FindOrderWithDetails"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order details retrieved successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/OrderResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Order not found")
     *         )
     *     ),
     *
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
     *     summary="Create a new order",
     *     description="Create a new order with the specified products and quantities. Stock quantities will be decremented automatically.",
     *     operationId="createOrder",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/CreateOrderRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             description="Empty response body on successful creation"
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=422, ref="#/components/responses/InvalidArgument"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     *
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
     * @OA\Put(
     *     path="/api/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Update an existing order",
     *     description="Update an existing order with new products, quantities, name or description. Stock quantities will be adjusted automatically based on the changes.",
     *     operationId="updateOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/UpdateOrderId"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateOrderRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             description="Empty response body on successful update"
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=422, ref="#/components/responses/InvalidArgument"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     *
     * @throws \Exception
     */
    public function update(UpdateOrderRequest $request): JsonResponse
    {
        $this->orderUseCase->update(
            $request->toDto()
        );

        return response()->json(
            status: 200
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{orderId}",
     *     tags={"Orders"},
     *     summary="Delete an order",
     *     description="Delete an order and restore the product stock quantities. The order status will be set to 'cancelled' and the order will be soft deleted.",
     *     operationId="deleteOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/DeleteOrderId"),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *             description="Empty response body on successful deletion"
     *         )
     *     ),
     *
     *     @OA\Response(response=400, ref="#/components/responses/ValidationError"),
     *     @OA\Response(response=422, ref="#/components/responses/InvalidArgument"),
     *     @OA\Response(response=500, ref="#/components/responses/ServerError")
     * )
     *
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
