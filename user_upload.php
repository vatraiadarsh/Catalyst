<?php

$options = getopt('', ['file:', 'create_table', 'dry_run', 'u:', 'p:', 'h:', 'help']);
if (isset($options['help'])) {
    echo "\nUsage: php user_upload.php --file [csv file name] --create_table -u [username] -p [password] -h [host]\n\n";
    echo "--file [csv file name] - this is the name of the CSV to be parsed\n";
    echo "--create_table - this will cause the MySQL users table to be built (and no further action will be taken)\n";
    echo "--dry_run - this will be used with the --file directive in case we want to run the script but not \r\n";
    echo "All other functions will be executed, but the database won't be altered\n";
    echo "-u - MySQL username\n";
    echo "-p - MySQL password\n";
    echo "-h - MySQL host\n\n";
    echo "--help - which will output the above list of directives with details.\n";
    exit(0);
}

function connect_to_database($argv)
{
   
    $servername = $argv[9];
    $username = $argv[5];
    $password = $argv[7];
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


function main($argv){

    if (!isset($argv[1])) {
        echo "No file name provided\n";
        echo "please use --help for more information\n";
        echo "eg: php user_upload.php --help\n\n";
        exit(1);
    }

    if($argv[1] == '--file' ){
        if(count($argv) < 3){
            echo "Use this format to perform the parse file and do the DRY run \n";
            echo "php user_upload.php --file [filename] --dry_run\n";
            echo "eg: php user_upload.php --file users.csv --dry_run\n\n";
            exit(1);
        }
    }

    if($argv[1] == '--file' && $argv[3] == '--dry_run'){
        if(count($argv) < 3){
            echo "Use this format to perform the DRY run \n";
            echo "php user_upload.php --file [filename] --dry_run\n";
            echo "eg: php user_upload.php --file users.csv --dry_run\n\n";
            exit(1);
        }
        $file_name = $argv[2];
        $data = read_csv_file_and_remove_first_row($file_name);
        foreach($data as $row){
            validate_email_before_inserting_to_database($row[2]);
        }
        if(count($data) > 0){
            echo "-----------PERFORMING DRY RUN--------------:\n";
            foreach($data as $row){
                // echo "Name:" . $row[0] . " " . "Surname:" . $row[1] . " " . "Email:" . $row[2] . "\n";
                printf("%s     %s     %s\n", $row[0], $row[1], $row[2]);
            }
        }
        echo "-----------END OF DRY RUN--------------:\n";
        echo "Dry run successful\n";
        exit(0);
    }

    if($argv[1] === "--create_table"){
       if(count($argv)<6){
        echo "Use this format to create table with your username password host and database \n";
        echo "php user_upload.php --create_table [username] [password] [host] [database]\n\n";
        echo 'eg: php user_upload.php --create_table root "" localhost apple';
        echo "\n";
        echo 'NOTE: FOR BLANK PASSWORD OR ANY OTHER BLANK FIELDS, USE  "" ';
        exit(1);
       }
        $username = $argv[2];
        $password = $argv[3];
        $host = $argv[4];
        $dbname = $argv[5];
        $connection = mysqli_connect($host, $username, $password, $dbname);
        if(!$connection){
            die("Database connection failed: " . mysqli_connect_error());
        }
        else{
            create_user_table($connection);
        }
        exit(0);
    } 
    if(count($argv)<10){
        echo "Please use this fomat:";
        echo "php user_upload.php --file [csv file name] --create_table -u [username] -p [password] -h [host]\n";
        echo "\n";
        echo 'Usage example: php user_upload.php --file users.csv --create_table -u root -p "" -h localhost';
        echo "\n";
        echo 'NOTE: FOR BLANK PASSWORD OR ANY OTHER BLANK FIELDS, USE  "" ';
        echo "\n";
        echo "TO PERFORM MORE OPERATIONS USE THE --help (eg: php user_upload.php --help) \n";
        exit(1);
    } 
    
    $file_name = $argv[2];
    $conn = connect_to_database($argv);
    create_user_table($conn);
    $data = read_csv_file_and_remove_first_row($file_name);
    if (count($data) > 0) {
        foreach ($data as $row) {
        validate_email_before_inserting_to_database($row[2]);
        }
    }else{
        echo "No data to insert\n";
    }

    foreach ($data as $row) {
        insert_into_database($conn, $row);
    }
    echo count($data) . " rows inserted\n";
    mysqli_close($conn);

}


main($argv);