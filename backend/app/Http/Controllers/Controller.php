<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Stock Manager API",
 *     version="1.0.0",
 *     description="Service for handling stock products and orders"
 * )
 *
 * @OA\Components(
 *
 *     @OA\Schema(
 *         schema="OrderResponse",
 *         @OA\Property(
 *             property="id",
 *             type="string",
 *             format="ulid",
 *             description="Order unique identifier",
 *             example="01HV5R2K3M4N5P6Q7R8S9T0U1V"
 *         ),
 *         @OA\Property(
 *             property="name",
 *             type="string",
 *             description="Order name",
 *             example="Urgent delivery"
 *         ),
 *         @OA\Property(
 *             property="description",
 *             type="string",
 *             description="Order description",
 *             example="Order for Mr.Rossi in Catanzaro"
 *         ),
 *         @OA\Property(
 *             property="status",
 *             type="string",
 *             description="Current order status",
 *             example="pending",
 *             enum={"pending", "processing", "shipped", "delivered", "cancelled"}
 *         ),
 *         @OA\Property(
 *             property="createdAt",
 *             type="string",
 *             format="date-time",
 *             description="Order creation timestamp",
 *             example="2024-01-15T10:30:00Z"
 *         ),
 *         @OA\Property(
 *             property="updatedAt",
 *             type="string",
 *             format="date-time",
 *             description="Order last update timestamp",
 *             example="2024-01-15T15:45:00Z"
 *         ),
 *         @OA\Property(
 *             property="orderItems",
 *             type="array",
 *             description="Order items (included when withDetails=true)",
 *             @OA\Items(ref="#/components/schemas/OrderItemResponse")
 *         )
 *     ),
 *
 *     @OA\Schema(
 *         schema="OrderItemResponse",
 *         @OA\Property(
 *             property="name",
 *             type="string",
 *             description="Product name",
 *             example="Premium Widget"
 *         ),
 *         @OA\Property(
 *             property="quantity",
 *             type="integer",
 *             minimum=1,
 *             description="Quantity ordered",
 *             example=2
 *         ),
 *         @OA\Property(
 *             property="createdAt",
 *             type="string",
 *             format="date-time",
 *             description="Order item creation timestamp",
 *             example="2024-01-15T10:30:00Z"
 *         ),
 *         @OA\Property(
 *             property="updatedAt",
 *             type="string",
 *             format="date-time",
 *             description="Order item last update timestamp",
 *             example="2024-01-15T10:30:00Z"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="ValidationError",
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="The given data was invalid"
 *             ),
 *             @OA\Property(
 *                 property="error",
 *                 type="object",
 *                 description="Detailed validation errors",
 *             ),
 *             @OA\Property(
 *                 property="type",
 *                 type="string",
 *                 example="validation_error"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="InvalidArgument",
 *         description="Invalid argument provided",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Invalid argument provided"
 *             ),
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 example="Product with ID 01HV5R2K3M4N5P6Q7R8S9T0U1V not found"
 *             ),
 *             @OA\Property(
 *                 property="type",
 *                 type="string",
 *                 example="invalid_argument"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response="ServerError",
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Internal server error"
 *             )
 *         )
 *     )
 * )
 */
abstract class Controller
{
    //
}
