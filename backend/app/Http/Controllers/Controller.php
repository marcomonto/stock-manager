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
 *      @OA\Response(
 *          response="ValidationError",
 *          description="Validation error",
 *
 *          @OA\JsonContent(
 *
 *              @OA\Property(
 *                  property="message",
 *                  type="string",
 *                  example="Invalid argument provided"
 *              ),
 *              @OA\Property(
 *                  property="error",
 *                  type="strinh",
 *                  description="Error Message",
 *              ),
 *              @OA\Property(
 *                  property="type",
 *                  type="string",
 *                  example="invalid_argument"
 *              )
 *          )
 *      ),
 *
 *      @OA\Response(
 *          response="InvalidArgument",
 *          description="Invalid argument provided",
 *
 *          @OA\JsonContent(
 *
 *              @OA\Property(
 *                  property="message",
 *                  type="string",
 *                  example="The given data was invalid"
 *              ),
 *              @OA\Property(
 *                  property="error",
 *                  type="string",
 *                  example="Product with ID 123 not found"
 *              ),
 *              @OA\Property(
 *                  property="type",
 *                  type="string",
 *                  example="validation_error"
 *              )
 *          )
 *      ),
 *
 *      @OA\Response(
 *          response="ServerError",
 *          description="Internal server error",
 *
 *          @OA\JsonContent(
 *
 *              @OA\Property(
 *                  property="message",
 *                  type="string",
 *                  example="Internal server error"
 *              )
 *          )
 *      )
 *  )
 */
abstract class Controller
{
    //
}
