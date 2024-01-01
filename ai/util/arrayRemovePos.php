<?php

    function array_remove_pos($arr, $pos) {
        $newArr = [];
        for ($i = 0; $i < count($arr); $i++) {
            if (!($arr[$i][0] == $pos[0] && $arr[$i][1] == $pos[1])) {
                array_push($newArr, $arr[$i]);
            }
        }
        $arr = $newArr;
    }

?>