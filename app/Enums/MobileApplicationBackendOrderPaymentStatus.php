<?php

namespace App\Enums;

enum MobileApplicationBackendOrderPaymentStatus: int
{
    case Paid = 2;
    case Unpaid = 1;
}
