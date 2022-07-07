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

function add_csv_file_to_database($file_name){

    if (!file_exists($file_name)) {
        echo "File $file_name does not exist";
        exit(1); // Graceful Shutdown
    }
    $csv_file = fopen($file_name, 'r');
    $csv_file_data = fgetcsv($csv_file);
    while($csv_file_data !== false){
        $name = $csv_file_data[0];
        $surname = $csv_file_data[1];
        $email = $csv_file_data[2];

     // insert into database
    }
    fclose($csv_file);  


    
    


}



if(empty($argv[1])){
    echo "Please provide a file name. \n";
    exit(1); // Graceful Shutdown
}else{
    connect_to_database();
    add_csv_file_to_database($argv[1]);
}
