<?php
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
class TransactionLogger
{
    private $db;

    public function __construct($dbFile)
    {
        $absoluteDbFile = realpath(dirname(__FILE__) . '/../' . $dbFile);
        if ($absoluteDbFile === false)
        {
            mkdir(dirname(__FILE__) . '/../' . dirname($dbFile), 0777, true);
            $absoluteDbFile = realpath(dirname(__FILE__) . '/../' . dirname($dbFile)) . '/' . basename($dbFile);
        }

        $this->db = new PDO('sqlite:' . $absoluteDbFile);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTable();
    }

    private function createTable()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY,
            type TEXT,
            symbol TEXT,
            amount REAL,
            price REAL,
            date TEXT
        )");
    }

    public function logTransaction($type, $symbol, $amount, $price)
    {
        $stmt = $this->db->prepare("INSERT INTO transactions (type, symbol, amount, price, date) VALUES (:type, :symbol, :amount, :price, :date)");
        $stmt->execute([
              ':type' => $type,
              ':symbol' => $symbol,
              ':amount' => $amount,
              ':price' => $price,
              ':date' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    public function showTransactions()
    {
        $stmt = $this->db->query("SELECT * FROM transactions");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders(['TYPE', 'AMOUNT', 'SYMBOL', 'PRICE', 'DATE']);

        foreach ($transactions as $log) {
            $table->addRow([
                  $log['type'],
                  $log['amount'],
                  $log['symbol'],
                  "€ " . $log['price'],
                  $log['date'],

            ]);
        }
        $table->render();

    }
}


