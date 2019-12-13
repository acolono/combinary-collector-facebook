<?php
function dbg(){
    static $logging = null;
    if($logging === null){
       $logging = getenv('JSON_LOGGING') === 'true'; 
    }
    if($logging) {
        $log = func_get_args();
        if(count($log) === 1) $log = $log[0];
        error_log(json_encode($log));
    }
}