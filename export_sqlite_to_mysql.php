<?php
// One-off script: dumps all rows from database/database.sqlite as MySQL-compatible INSERT statements.
// Usage: php export_sqlite_to_mysql.php > sqlite_data_dump.sql

$sqlitePath = __DIR__ . '/database/database.sqlite';
$outputPath = __DIR__ . '/sqlite_data_dump.sql';

$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
    ->fetchAll(PDO::FETCH_COLUMN);

function mysqlEscape(string $value): string
{
    return str_replace(
        ["\\", "\0", "\n", "\r", "'", "\x1a"],
        ["\\\\", "\\0", "\\n", "\\r", "\\'", "\\Z"],
        $value
    );
}

$out = fopen($outputPath, 'w');
fwrite($out, "SET FOREIGN_KEY_CHECKS=0;\n\n");

foreach ($tables as $table) {
    if ($table === 'migrations') {
        continue; // schema already created by php artisan migrate on the MySQL side
    }

    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        continue;
    }

    $columns = array_keys($rows[0]);
    $columnList = '`' . implode('`, `', $columns) . '`';

    fwrite($out, "-- Table: $table\n");
    fwrite($out, "DELETE FROM `$table`;\n");

    foreach ($rows as $row) {
        $values = array_map(function ($value) {
            if ($value === null) {
                return 'NULL';
            }
            if (is_int($value) || is_float($value)) {
                return $value;
            }
            return "'" . mysqlEscape((string) $value) . "'";
        }, array_values($row));

        fwrite($out, "INSERT INTO `$table` ($columnList) VALUES (" . implode(', ', $values) . ");\n");
    }

    fwrite($out, "\n");
}

fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($out);

echo "Done. Wrote " . count($tables) . " table(s) (minus migrations) to $outputPath\n";
