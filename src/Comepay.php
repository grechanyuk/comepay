<?php

namespace Grechanyuk\Comepay;

use Grechanyuk\Comepay\Contracts\ComepayOrderInterface;
use Grechanyuk\Comepay\Exceptions\ComepayException;
use Grechanyuk\Comepay\Exceptions\ComepayFatalException;
use Grechanyuk\Comepay\Models\ComepayPayment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class Comepay
{
    private $client;
    private $baseUrl;
    private $login;

    public function __construct()
    {
        if (config('comepay.testing.enable')) {
            $this->baseUrl = 'https://moneytest.comepay.ru:449/';
            $this->login = config('comepay.testing.shopNumber');
            $password = config('comepay.testing.shopPassword');
        } else {
            $this->baseUrl = 'https://shop.comepay.ru/';
            $this->login = config('comepay.shopNumber');
            $password = config('comepay.shopPassword');
        }

        $this->client = new Client(['base_uri' => $this->baseUrl, 'auth' => [
            $this->login, $password
        ]]);
    }

    /**
     * @param ComepayOrderInterface $order
     * @param bool|string $user
     * @param bool|string $comment
     * @param bool|string $ccy
     * @param bool|int $payAttribute
     * @return bool|\Exception|ComepayException|ComepayFatalException|string
     */
    public function createPayment(ComepayOrderInterface $order, $user = false, $comment = false, $ccy = false, $payAttribute = false)
    {
        $paymentId = str_random(40);
        $url = 'api/prv/' . config('comepay.shopId') . '/bills/' . $paymentId;
        $products = $order->getComepayProducts()->get();

        $prd = [];
        foreach ($products as $product) {
            $prd[] = [
                'Count' => $product->getComepayQuantity(),
                'Amount' => $product->getComepayAmount(),
                'Description' => $product->getComepayDescription(),
                'Vat' => $product->getComepayVat() ? $product->getComepayVat() : config('comepay.vat'),
                'CalcBySubTotal' => $product->getComepayCalcBySubTotal()
            ];
        }

        try {
            $response = $this->client->put($url, [
                'form_params' => [
                    'user' => $user ? $user : config('comepay.user'),
                    'amount' => (float) $order->getComepayTotalAmount(),
                    'ccy' => $ccy ? $ccy : config('comepay.ccy'),
                    'сomment' => $comment ? $comment : sprintf(config('comepay.comment'), $order->getComepayOrderId()),
                    'lifetime' => date(DATE_ISO8601, strtotime(config('comepay.lifetime'))),
                    'email' => $order->getComepayClientEmail(),
                    'infos' => json_encode($prd),
                    'payattribute' => $payAttribute ? $payAttribute : config('comepay.payattribute')
                ]
            ]);
        } catch (ClientException $e) {
            Log::warning('Ошибка Comepay при формировании счета', ['error' => $e]);

            return false;
        }


        if ($response->getStatusCode() == 200) {
            //$response = $response->getBody()->getContents();
            $response = json_decode($response->getBody()->getContents());

            if (!$response->response->result_code) {
                ComepayPayment::create([
                    'order_id' => $order->getComepayOrderId(),
                    'payment_id' => $paymentId,
                    'status' => $response->response->bill->status
                ]);

                return $this->baseUrl . 'Order/Accept?shop=' . config('comepay.shopId') . '&transaction=' . $paymentId . '&successUrl=' . config('comepay.successURL') . '&failUrl=' . config('comepay.failURL');
            } else {
                try {
                    $this->errorResponse($response->response->result_code);
                } catch (ComepayFatalException $e) {
                    Log::warning('Получена фатальная ошибка модуля Comepay. ', ['error' => $e]);
                    return false;
                } catch (ComepayException $e) {
                    Log::debug('Поймано исключение Comepay', ['error' => $e]);
                    return $this->createPayment($order);
                }
            }
        }

        return false;
    }

    /**
     * @param $header
     * @return bool
     */
    public function checkAuthorization($header) {
        if($header == 'Basic ' . base64_encode($this->login . ':' . config('comepay.callbackPassword'))) {
            return true;
        }

        return false;
    }

    /**
     * @param $errorCode
     */
    private function errorResponse($errorCode)
    {
        if ($errorCode == 5) {
            throw new ComepayFatalException('Неверные параметры');
        } else if ($errorCode == 13) {
            throw new ComepayException('Сервер занят');
        } else if ($errorCode == 150) {
            throw new ComepayFatalException('Ошибка авторизации');
        } else if ($errorCode == 210) {
            throw new ComepayFatalException('Запрос не найден');
        } else if ($errorCode == 215) {
            throw new ComepayFatalException('Запрос уже существует');
        } else if ($errorCode == 241) {
            throw new ComepayFatalException('Сумма меньше минимума');
        } else if ($errorCode == 242) {
            throw new ComepayFatalException('Сумма больше максимума');
        } else if ($errorCode == 298) {
            throw new ComepayFatalException('Покупатель не зарегистрирован');
        } else if ($errorCode == 299) {
            throw new ComepayFatalException('Магазин не зарегистрирован');
        } else if ($errorCode == 300) {
            throw new ComepayException('Ошибка сервера');
        }
    }
}