<?php


namespace app\models;

use Yii;
use yii\base\ErrorException;
use yii\base\Model;
use app\controllers\SiteController;


class apiModel extends Model
{
    /**
     * Получаем данные с blockchain.info и переводим их в массив
     */
    public static function getTicker()
    {
        try {
            return json_decode(
                file_get_contents('https://blockchain.info/ticker'),
                true
            );
        } catch (ErrorException $e) {
            return false;
        }

    }
    /**
     * конвертация полученного массива в нужную форму
     * на выходе массив с курсами валют +2%
     */
    public static function getAllData($input)
    {
        foreach ($input as $first_key => $array) {
            foreach ($array as $key => $value)
                if ($key == 'last') {
                    $data[$first_key] = round($value * 1.02, 2);
                }
        }
        asort($data);
        return $data;
    }
    /**
     *получение текущего курса валюты из blockchain.info без учета комиссии
     */
    public static function getClearData($input, $currency){
        foreach ($input as $first_key => $array) {
            foreach ($array as $key => $value)
                if ($key == 'last'&$first_key==$currency) {
                    $data[$first_key] = $value;
                }
        }
        return $data[$currency];
    }
    /**
     * конвертация из BTC на другую валюту и обратно
     */
    public static function CurrencyConvert($input, $currency_to, $currency_from, $value)
    {
        if ($currency_from=='BTC') {
            $rate = round(ApiModel::getClearData($input, $currency_to) * 1.02, 2);
            return [
                'currency_from' => $currency_from,
                'currency_to' => $currency_to,
                'value' => $value,
                'converted_value' => number_format(round($value * $rate, 2), 2, ".", ""),
                'rate' => $rate
            ];
        }
        $rate = round(1 / (self::getClearData($input, $currency_from)) * 0.98, 10);
        return [
            'currency_from' => $currency_from,
            'currency_to' => $currency_to,
            'value' => $value,
            'converted_value' => number_format(round($rate * $value, 10), 10, ".", ""),
            'rate' => number_format($rate, 10)
        ];
    }

    public static function checkToken($token)
    {
          return  $token=='USe9JAB8T1DK_osubjVNXvzCcGyiqnpr3PQYx27h-0LIaw6Olt4d5ZgkRHfEmWFM';
    }
}