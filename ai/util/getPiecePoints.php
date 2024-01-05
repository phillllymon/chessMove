<?php

    function getPiecePoints($piece) {
        return [
            "-" => 0,
            // "k" => 1000, // hacky, but we'll try to maximize or minimize points in ai and this will be helpful and simple
            // "K" => -1000,
            "k" => 0,
            "K" => 0,
            "r" => 5,
            "R" => -5,
            "n" => 3,
            "N" => -3,
            "b" => 3,
            "B" => -3,
            "q" => 9,
            "Q" => -9,
            "p" => 1,
            "P" => -1
        ][$piece];
    }

?>