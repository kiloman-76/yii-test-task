<?php
namespace app\components;
use Yii;
use yii\base\Component;
use app\services\dto\LatestRatesDto;
use app\services\dto\HistoricalRatesDto;

class TestExchangeRatesComponent extends Component
{
    public string $apiKey;
    private string $baseUrl = 'https://openexchangerates.org/api';

    public function init()
    {
        parent::init();
        if (!$this->apiKey) {
            throw new \InvalidArgumentException('API key is required.');
        }
    }

    public function getLatestRates( //Основной метод для вызова эндпойинта с последними курсами валют
        string $base = '',
        string $symbols = '',
        bool $prettyprint = false,
        bool $show_alternative = false
    ): ?LatestRatesDto
    {
        // Здесь может быть дополнительная валидация параметров, например на соответствие строк определенному формату и т.д.

        $params = $this->buildQueryParams($base, $symbols, $prettyprint, $show_alternative);

        $data = $this->fetchData('/latest.json', $params);
        if(!$data){
            return null;
        }

        return new LatestRatesDto(
            $data['base'],
            $data['rates'],
            $data['timestamp']
        );
    }

    public function getHistoricalRates(
        string $date,
        string $base = '',
        string $symbols = '',
        bool $prettyprint = false,
        bool $show_alternative = false
    ): ?HistoricalRatesDto //Метод для получения курса валют за определенный день для демонстрации добавления новых эндпоинтов
    {
        $params = $this->buildQueryParams($base, $symbols, $prettyprint, $show_alternative);

        $data = $this->fetchData('/historical/' . $date . '.json', $params);
        if(!$data){
            return null;
        }

        return new HistoricalRatesDto(
            $data['base'],
            $data['rates'],
            $data['timestamp']
        );
    }


    private function buildQueryParams(
        ?string $base,
        ?string $symbols,
        ?bool $prettyprint,
        ?bool $show_alternative
    ): array {
        $params = [];
        if ($base) {
            $params['base'] = $base;
        }
        if ($symbols) {
            $params['symbols'] = $symbols;
        }
        if ($prettyprint) {
            $params['prettyprint'] = $prettyprint;
        }
        if ($show_alternative) {
            $params['show_alternative'] = $show_alternative;
        }
        return $params;
    }


    private function fetchData(string $endpoint, array $queryParams = []) //Общий метод для отправки запроса
    {
        $queryParams['app_id'] = $this->apiKey;
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($queryParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Yii::error('Failed to make request to ' . $endpoint . ': ' . $error, __METHOD__);
            throw new \yii\base\UserException('Произошла ошибка при получении данных с API.');
        }

        $data = json_decode($response, true);
        if(isset($data['error'])){
            Yii::error('Failed to make request to ' . $endpoint . ':' . $data['message']);
            throw new \yii\base\UserException('Произошла ошибка при получении данных с API.');
        }
        return $data;
    }
}

