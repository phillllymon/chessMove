<?php
    function getEnemiesArr($color) {
        if ($color == "w") {
            return ["P", "R", "N", "B", "K", "Q"];
        } else {
            return ["p", "r", "n", "b", "k", "q"];
        }
    }

?>