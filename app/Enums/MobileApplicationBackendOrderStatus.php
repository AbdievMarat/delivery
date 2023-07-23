<?php

namespace App\Enums;

enum MobileApplicationBackendOrderStatus: int
{
    case CancelUnpaid = 4;
    case Refund = 5;
    case Delivered = 7;
}
