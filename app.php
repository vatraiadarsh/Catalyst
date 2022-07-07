<?php

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

       echo "Name: $name, Surname: $surname, Email: $email\n";
        $csv_file_data = fgetcsv($csv_file);
    }
    fclose($csv_file);  
    
    


}



if(empty($argv[1])){
    echo "Please provide a file name. \n";
    exit(1); // Graceful Shutdown
}else{
    add_csv_file_to_database($argv[1]);
}
