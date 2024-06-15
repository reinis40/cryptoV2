<?php

require 'vendor/autoload.php';
require 'ApiInterface.php';
require 'CoinMarketCapApi.php';
require 'CoingeckoApi.php';
require 'TransactionLogger.php';
require 'CryptoManager.php';

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Carbon\Carbon;

$dbFile = 'storage/database.sqlite';

//$apiKey = "CG-477vkyBvF6onAz2YUz5LWPJ1";
//$api = new CoingeckoApi();

$apiKey = "ccb58a8c-61b0-4c84-8289-5e562a8476a1";
$api = new CoinMarketCapApi($apiKey);

$logger = new TransactionLogger($dbFile);
$cryptoManager = new CryptoManager($api, $logger);

while (true) {
    echo "\n1. List of crypto\n2. Buy\n3. Sell\n4. View wallet\n5. View logs\n6. Get Crypto by Symbol\n7. Exit\n";
    $input = readline("Select an option: ");

    switch ($input) {
        case 1:
            $cryptoManager->showCrypto();
            break;
        case 2:
            $symbol = readline("Enter cryptocurrency symbol: ");
            $amountEUR = readline("Enter amount in EUR to buy: ");
            $cryptoManager->buyCrypto(strtoupper($symbol), (float)$amountEUR);
            break;
        case 3:
            $symbol = readline("Enter cryptocurrency symbol: ");
            $cryptoManager->sellCrypto(strtoupper($symbol));
            break;
        case 4:
            $cryptoManager->showWallet();
            break;
        case 5:
            $logger->showTransactions();
            break;
        case 6:
            $symbol = readline("Enter cryptocurrency symbol: ");
            $crypto = $api->getCryptoBySymbol(strtoupper($symbol));
            if ($crypto) {
                echo "Name: {$crypto['name']}\nSymbol: {$crypto['symbol']}\nPrice: €{$crypto['quote']}\n";
            } else {
                echo "Cryptocurrency with symbol '$symbol' not found.\n";
            }
            break;
        case 7:
            exit("Goodbye!\n");
        default:
            echo "Invalid option, please try again.\n";
    }
}



