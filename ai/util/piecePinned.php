<?php

function piecePinned($gameData, $pos, $color, $kingPos) {
    if ($kingPos[0] == $pos[0] && $kingPos[1] == $pos[1]) {
        return false; // this means you're asking if the king is pinned - technically false
    }
    $dirs = [[1, 1], [1, -1], [-1, -1], [-1, 1], [1, 0], [0, 1], [-1, 0], [0, -1]];
    $enemies = getEnemiesArr($color);
    foreach($dirs as $dir) {
        $distance = 1;
        while ($distance < 7) {
            $dest = [$kingPos[0] + ($distance * $dir[0]), $kingPos[1] + ($distance * $dir[1])];
            if (!posOnBoard($dest)) {
                break;
            }
            if ($dest[0] == $pos[0] && $dest[1] == $pos[1]) {
                // piece may be pinned - keep going this direction
                $beyond = 1;
                while ($beyond < 7) {
                    $spySpace = [$pos[0] + ($beyond * $dir[0]), $pos[1] + ($beyond * $dir[1])];
                    if (!posOnBoard($spySpace)) {
                        return false; // nothing beyond - piece can't be pinned
                    }
                    $spyOcc = $gameData->grid[$spySpace[0]][$spySpace[1]];
                    if ($spyOcc != "-") {
                        if (in_array($spyOcc, $enemies)) {
                            if ($spyOcc == "q" || $spyOcc == "Q") {
                                return true;
                            }
                            // check if rank or file matches - if yes then check for rook, otherwise bishop
                            if ($spySpace[0] == $kingPos[0] || $spySpace[1] == $kingPos[1]) {
                                if ($spyOcc == "r" || $spyOcc == "R") {
                                    return true;
                                }
                            } else {
                                if ($spyOcc == "b" || $spyOcc == "B") {
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                    $beyond += 1;
                }
            } else {
                $occupant = $gameData->grid[$dest[0]][$dest[1]];
                if ($occupant != "-") {
                    break;
                }
            }
            $distance += 1;
        }
    }
    return false;
}

?>