<?php

function connect_to_database()
{
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'php_catalyst';
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        die('Connection failed: ' . mysqli_connect_error());
    }
    return $conn;
}

function create_user_table($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `surname` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    )";

    if (mysqli_query($conn, $sql)) {
        echo "Table users created successfully\n";
    } else {
        echo 'Error creating table: ' . mysqli_error($conn) . "\n";
    }
}

// the first row is the header row with the column names so we skip it.
function read_csv_file_and_remove_first_row($file_name)
{
    if (!file_exists($file_name)) {
        echo "File $file_name does not exist";
        exit(1);
    }
    $csv_file = fopen($file_name, 'r');
    $data = [];
    while (($csv_data = fgetcsv($csv_file)) !== false) {
        $data[] = $csv_data;
    }
    fclose($csv_file);
    array_shift($data);
    return $data;
}

function senatize_string($string)
{
    return preg_replace('/[^\w\s]/', '', $string);
}

function validate_email_before_inserting_to_database($email)
{
    $email = trim($email);
    $pattern =
        '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix';
    if (!preg_match($pattern, $email)) {
        echo "Invalid email address: $email\n";
        exit(1);
    }
}

function insert_into_database($conn, $data)
{
    $name = senatize_string(ucfirst($data[0]));
    $surname = senatize_string(ucfirst($data[1]));
    $email = strtolower($data[2]);
    $sql = "INSERT INTO users (name, surname, email) VALUES ('$name', '$surname', '$email')";
    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully\n";
    } else {
        echo 'Error: ' . $sql . '<br>' . mysqli_error($conn);
    }
}

if (empty($argv[1])) {
    echo 'Please provide a file name';
    exit(1); // Graceful shutdown
}
$data = read_csv_file_and_remove_first_row($argv[1]);
if (count($data) > 0) {
    foreach ($data as $row) {
        validate_email_before_inserting_to_database($row[2]);
    }
} else {
    echo "No data to insert\n";
}
$conn = connect_to_database();
create_user_table($conn);

foreach ($data as $row) {
    insert_into_database($conn, $row);
}

echo count($data) . " rows inserted\n";
mysqli_close($conn);