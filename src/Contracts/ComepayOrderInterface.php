<?php

namespace Grechanyuk\Comepay\Contracts;

interface ComepayOrderInterface
{

    /**
     * Получаем ID заказа
     * @return int
     */
    public function getComepayOrderId();

    /**
     * Сумма заказа, которая будет списана с пользователя
     * @return float
     */
    public function getComepayTotalAmount();

    /**
     * Получаем E-mail покупателя
     * @return string
     */
    public function getComepayClientEmail();

    /**
     * Получаем список товаров.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getComepayProducts();
}