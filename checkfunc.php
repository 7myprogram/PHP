<?php
function es($data){
    if(is_array($data)){
        return array_map(__METHOD__, $data);
    }
    else{
        return htmlspecialchars($data, ENT_QUOTES, mb_internal_encoding());
    }
}

function cken($data){
    if(is_array($data)){
        foreach ($data as $key => $value) {
            if(is_array($value)){
                if(!mb_check_encoding(implode("", $value)))
                    return false;
            }
            else{
                if(!mb_check_encoding($value))
                    return false;
            }
        }     
    }
    else {
        if(!mb_check_encoding($data))
            return false;
    }
    return true;
}

function cken_error($data) {
    if(!cken($data)){
        exit("Encoding error. The expected encoding is ".mb_internal_encoding());
    }
}
?>