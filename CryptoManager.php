<?php

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class CryptoManager
{
    private ApiInterface $api;
    public array $wallet;
    public TransactionLogger $logger;
    public int $initialEur = 1000;

    public function __construct(ApiInterface $api, TransactionLogger $logger)
    {
        $this->api = $api;
        $this->logger = $logger;
        $this->wallet = ['EUR' => $this->initialEur];
    }

    public function showCrypto(): void
    {
        $data = $this->api->getCryptoListings();
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Name', 'Symbol', 'Price per 1 coin (EUR)']);

        foreach ($data as $crypto) {
            $table->addRow([
                  $crypto['name'],
                  $crypto['symbol'],
                  "€" . $crypto['quote']
            ]);
        }
        $table->render();
    }
    public function buyCrypto($symbol, $amountEUR): void
    {
        $data = $this->api->getCryptoListings();
        //var_dump($amountEUR);
        $price=0;
            foreach ($data as $crypto) {
                if ($crypto['symbol'] == strtoupper($symbol)) {

                    $price = $crypto['quote'];
                    break;
                }
            }
        if (is_numeric($price)) {
            if ($this->wallet['EUR'] >= $amountEUR) {
                $this->wallet['EUR'] -= $amountEUR;
                $amountCrypto = $amountEUR / $price;

                if (!isset($this->wallet[$symbol])) {
                    $this->wallet[$symbol] = 0;
                }
                $this->wallet[$symbol] += $amountCrypto;

                $this->logger->logTransaction('buy', $symbol, $amountCrypto, $amountEUR);
                echo "Bought $amountCrypto of $symbol at €$price each.\n";
            } else {
                echo "Insufficient funds to buy €$amountEUR of $symbol.\n";
            }
        }
    }
    public function sellCrypto($symbol): void
    {
        $data = $this->api->getCryptoListings();
        $price = null;
        foreach ($data as $crypto)
        if ($crypto['symbol'] === strtoupper($symbol)) {
            {
                $price = $crypto['quote'];
                break;
            }
        }
        if ($price !== null) {
            if (isset($this->wallet[$symbol]) && $this->wallet[$symbol] > 0) {
                $amountCrypto = $this->wallet[$symbol];
                $this->wallet['EUR'] += $amountCrypto * $price;
                unset($this->wallet[$symbol]);

                $this->logger->logTransaction('sell', $symbol, $amountCrypto, $amountCrypto * $price);
                echo "Sold $amountCrypto of $symbol at €$price each.\n";
            } else {
                echo "Insufficient $symbol to sell.\n";
            }
        } else {
            echo "Error: Crypto quote not found for symbol '$symbol'.\n";
        }
    }
    public function showWallet(): void
    {

        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['Currency', 'Amount', 'Value in EUR']);

        foreach ($this->wallet as $symbol => $amount) {
            $valueInEur = $symbol === 'EUR' ? $amount : $this->convertToEUR($symbol, $amount);
            $table->addRow([
                  $symbol,
                  $amount,
                  number_format($valueInEur, 2)
            ]);
        }
        $table->render();
    }

    private function convertToEUR($symbol, $amount)
    {
        if ($symbol == 'EUR') {
            return $amount;
        } else {
            $data = $this->api->getCryptoListings();
            foreach ($data as $crypto)
            {
                if ($crypto['symbol'] === ($symbol))
                {
                    $priceInEUR = $crypto['quote'];
                }
            }

            return $amount * $priceInEUR;
        }
    }
}



