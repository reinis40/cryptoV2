<?php

class CoinMarketCapApi implements ApiInterface
{
    public string $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getApiData($url, $parameters)
    {
        $headers = [
              'Accepts: application/json',
              'X-CMC_PRO_API_KEY: ' . $this->apiKey
        ];
        $qs = http_build_query($parameters);
        $request = "$url?$qs";
        $curl = curl_init();
        curl_setopt_array($curl, array(
              CURLOPT_URL => $request,
              CURLOPT_HTTPHEADER => $headers,
              CURLOPT_RETURNTRANSFER => 1
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    public function getCryptoListings(): array
    {
        $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
        $parameters = [
              'start' => '1',
              'limit' => '10',
              'convert' => 'EUR'
        ];
        $response = $this->getApiData($url, $parameters);
        $cryptoList = [];

        foreach ($response['data'] as $cryptoData) {
            $crypto = [
                  'name' => $cryptoData['name'],
                  'symbol' => $cryptoData['symbol'],
                  'quote' =>$cryptoData['quote']['EUR']['price'],
            ];
            $cryptoList[] = $crypto;
        }

        return $cryptoList;
    }
}


