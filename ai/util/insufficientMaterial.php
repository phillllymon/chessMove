<?php

    function insufficientMaterial($gameData) {
        $minorPieces = 0;
        foreach ($gameData->grid as $row) {
            foreach ($row as $piece) {
                if ($piece != "-" && $piece != "k" && $piece != "K") {
                    // only keep track of bishops and knights - if any other pieces found, there is sufficient material
                    if (in_array($piece, ["n", "b", "N", "B"])) {
                        $minorPieces += 1;
                        if ($minorPieces > 1) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }
            }
        }
        if ($minorPieces > 1 ) {
            return false;
        }
        return true;
    }

?>