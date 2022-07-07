<?php

function add_csv_file_to_database($file_name){

    $csv_file = fopen($file_name, 'r');
    $csv_file_data = fgetcsv($csv_file);

    print_r($csv_file_data);

    


}

add_csv_file_to_database('users.csv');