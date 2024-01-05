<?php

    include_once("ai/getPieceMoves.php");
    function isMoveValid($gameData, $move) {
        $allMoves = getAllMoves($gameData);
        for ($i = 0; $i < count($allMoves); $i++) {
            $thisMove = $allMoves[$i];
            if (compareArrays($move, $thisMove)) {
                return true;
            }
        }
        return false;
    }

    function compareArrays($arr1, $arr2) {
        if (gettype($arr1) != gettype($arr2)) {
            return false;
        }
        if (is_array($arr1)) {
            if (count($arr1) != count($arr2)) {
                return false;
            }
            for ($i = 0; $i < count($arr1); $i++) {
                if (!compareArrays($arr1[$i], $arr2[$i])) {
                    return false;
                }   
            }
            return true;
        } else {
            return $arr1 == $arr2;
        }
    }

?>