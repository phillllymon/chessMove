<?php

    function deepCopy($arr) {
        if (gettype($arr) == "array") {
            $answer = [];
            for ($i = 0; $i < count($arr); $i++) {
                $ele = $arr[$i];
                if (gettype($ele) == "array") {
                    array_push($answer, deepCopy($ele));
                } else {
                    array_push($answer, $ele);
                }
            }
            return $answer;
        }
        return $arr;
    }

?>