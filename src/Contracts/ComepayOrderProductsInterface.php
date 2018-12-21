<?php
namespace Grechanyuk\Comepay\Contracts;

interface ComepayOrderProductsInterface {

    /**
     * Получаем количество товаров
     * @return float
     */

    public function getComepayQuantity();

    /**
     * Получаем цену за единицу или всю сумму (см.CalcBySubTotal)
     * @return float
     */

    public function getComepayAmount();

    /**
     * Получаем описание товара
     * @return string
     */

    public function getComepayDescription();

    /**
     * Тип НДС (1 - НДС не облагается; 2 - НДС 10%; 3 - НДС 18%.). Если false, будет взят из конфигурационного файла
     * @return int|boolean
     */

    public function getComepayVat();

    /**
     * флаг для расчета всей суммы.
     * Если флаг CalcBySubTotal не указан или указано значение false – то полная сумма считается автоматически
     * как (кол-во) * (цена за еденицу).
     * Если  CalcBySubTotal = true, то в поле Amount указывается полная сумма,
     * а цена за еденицу автоматически считается как (сумма) / (кол-во).
     *
     * @return boolean
     */

    public function getComepayCalcBySubTotal();
}