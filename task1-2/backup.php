
<?php

$database = $argv[1] ?? 'exercise2_db';

$connection = new mysqli("localhost", "root", "", $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$output = "";

$tables = $connection->query("SHOW TABLES");

while ($table = $tables->fetch_array()) {

    $tableName = $table[0];

    $result = $connection->query("SELECT * FROM `$tableName`");

    while ($row = $result->fetch_assoc()) {

        $columns = array_keys($row);

        $values = array_map(function ($value) use ($connection) {
            return "'" . $connection->real_escape_string($value ?? '') . "'";
        }, array_values($row));

        $output .= "INSERT INTO `$tableName` (" .
            implode(", ", $columns) . ") VALUES (" .
            implode(", ", $values) . ");\n";
    }
}

file_put_contents("backup.txt", $output);

echo "Database backup completed successfully!";

$connection->close();

?>
