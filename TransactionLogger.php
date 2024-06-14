<?php
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
class TransactionLogger
{
    public PDO $db;
    public function __construct($dbFile)
    {
        $absoluteDbFile = realpath(dirname(__FILE__) . '/../' . $dbFile);
        $this->db = new PDO('sqlite:' . $absoluteDbFile);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTable();
    }
    private function createTable(): void
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
    public function logTransaction($type, $symbol, $amount, $price): void
    {
        $date = (new DateTime())->format('Y-m-d H:i:s');
        $query = "INSERT INTO transactions (type, symbol, amount, price, date) VALUES ('$type', '$symbol', '$amount', '$price', '$date')";
        $this->db->query($query);
    }
    public function showTransactions(): void
    {
        $stmt = $this->db->query("SELECT * FROM transactions ORDER BY date DESC");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC); //result no query

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


