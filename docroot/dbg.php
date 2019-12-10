<?php
function dbg($arr){
    if(getenv('JSON_LOGGING') === 'true') error_log(json_encode($arr));
}