<?php

namespace App\Enums;

enum Dtos: string
{
    case CreateOrder = 'CreateOrder';
    case EditOrder = 'UpdateOrder';
    case DeleteOrder = 'DeleteOrder';
    case FindOrder = 'FindOrder';
    case ListOrders = 'ListOrders';
}
