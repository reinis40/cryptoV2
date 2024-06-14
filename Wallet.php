<?php

class Wallet
{
    private $walletFile;

    public function __construct($walletFile)
    {
        $this->walletFile = $walletFile;
        if (!file_exists($walletFile)) {
            file_put_contents($walletFile, json_encode(['EUR' => 1000]));
        }
    }

    public function loadWallet()
    {
        return json_decode(file_get_contents($this->walletFile), true);
    }

    public function saveWallet($wallet)
    {
        file_put_contents($this->walletFile, json_encode($wallet, JSON_PRETTY_PRINT));
    }

    public function convertToEUR($api, $symbol, $amount)
    {
        if ($symbol == 'EUR') {
            return $amount;
        } else {
            $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
            $parameters = [
                  'symbol' => $symbol,
                  'convert' => 'EUR'
            ];

            $data = $api->getApiData($url, $parameters);
            $priceInEUR = $data['data'][$symbol]['quote']['EUR']['price'];
            return $amount * $priceInEUR;
        }
    }
}


