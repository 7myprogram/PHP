<?php
function read_file(string $filename, int $byte = -1):string{
    $handle = fopen($filename, "r");
    if ($byte < 0){
        $byte = filesize($filename);
    }
    if($handle){
        $readdata = fread($handle, $byte);
        
        fclose($handle);

        return $readdata;
    }
    return "";
}

function write_file(string $filename, string $mode, string $writedata, int $length){
    if($mode !== 'r'){
        $handle = fopen($filename, $mode);
        if($handle){
            fwrite($handle, $writedata."\n", $length);
            fclose($handle);
        }
    }
}
?>