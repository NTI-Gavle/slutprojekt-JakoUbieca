<?php

function sanitize_array(&$data) {
    if (is_array($data)) {
        foreach ($data as $key => &$value) {
            sanitize_array($value);          // make safe every html tag for our webiste and prevent trouble attempts.                                        
        }
    } else if (is_string($data)) {
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    return $data;
}
?>
