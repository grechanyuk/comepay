<?php

namespace Grechanyuk\Comepay\Facades;

use Grechanyuk\Comepay\Contracts\ComepayOrderInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static createPayment(ComepayOrderInterface $order)
 * @method static checkAuthorization($header)
 */
class Comepay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'comepay';
    }
}
