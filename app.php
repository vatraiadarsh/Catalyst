<?php

function add_csv_file_to_database($file_name){

    $csv_file = fopen($file_name, 'r');
    $csv_file_data = fgetcsv($csv_file);

    print_r($csv_file_data);

    


}


if(empty($argv[1])){
    echo "Please provide a file name\n";
    exit(1); // Graceful Shutdown
}else{
    add_csv_file_to_database($argv[1]);
}
