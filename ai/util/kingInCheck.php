<?php

    include_once("ai/util/posOnBoard.php");

    function kingInCheckNewGrid($gameData, $color, $newGrid, $newKingPos = false) {
        $newGameData = new stdClass();
        $newGameData->grid = $newGrid;
        if ($newKingPos) {
            if ($color == "w") {
                $newGameData->wKingPos = $newKingPos;
            } else {
                $newGameData->bKingPos = $newKingPos;
            }
        } else {
            $newGameData->wKingPos = $gameData->wKingPos;
            $newGameData->bKingPos = $gameData->bKingPos;
        }
        return kingInCheck($newGameData, $color, $newKingPos);
    }

    // $newKingPos is if you want to pretend king as moved
    function kingInCheck($gameData, $color, $newKingPos = false) {
        $kingKey = $color."KingPos";
        $kingPos = $gameData->{$kingKey};
        if ($newKingPos) {
            $kingPos = $newKingPos;
        }
        $kingOcc = "k";
        if ($color == "b") {
            $kingOcc = "K";
        }

        // check for adjacent king
        $enemyKing = "K";
        if ($color == "b") {
            $enemyKing = "k";
        }
        foreach ([
            [$kingPos[0]+1, $kingPos[1]+1],
            [$kingPos[0]+1, $kingPos[1]],
            [$kingPos[0]+1, $kingPos[1]-1],
            [$kingPos[0], $kingPos[1]+1],
            [$kingPos[0], $kingPos[1]-1],
            [$kingPos[0]-1, $kingPos[1]+1],
            [$kingPos[0]-1, $kingPos[1]],
            [$kingPos[0]-1, $kingPos[1]-1]
        ] as $adjPos) {
            if (posOnBoard($adjPos)) {

                if ($gameData->grid[$adjPos[0]][$adjPos[1]] == $enemyKing) {
                    return true;
                }
            }
        }

        // check for knight attacks
        $enemyKnight = "N";
        if ($color == "b") {
            $enemyKnight = "n";
        }
        foreach ([
            [$kingPos[0]+2, $kingPos[1]+1],
            [$kingPos[0]+1, $kingPos[1]+2],
            [$kingPos[0]-1, $kingPos[1]+2],
            [$kingPos[0]-2, $kingPos[1]+1],
            [$kingPos[0]-2, $kingPos[1]-1],
            [$kingPos[0]-1, $kingPos[1]-2],
            [$kingPos[0]+1, $kingPos[1]-1],
            [$kingPos[0]+2, $kingPos[1]-1]
        ] as $attackPos) {
            if (posOnBoard($attackPos)) {
                if ($gameData->grid[$attackPos[0]][$attackPos[1]] == $enemyKnight) {
                    return true;
                }
            }
        }

        // check for rank or file attack
        foreach ([
            [0, 1],
            [0, -1],
            [1, 0],
            [-1, 0]
        ] as $dir) {
            $distance = 1;
            while ($distance < 8) {
                $row = $kingPos[0] + ($distance * $dir[0]);
                $col = $kingPos[1] + ($distance * $dir[1]);
                if (posOnBoard([$row, $col])) {
                    $occupant = $gameData->grid[$row][$col];
                    if ($occupant != "-" && $occupant != $kingOcc) {
                        $enemies = ["Q", "R"];
                        if ($color == "b") {
                            $enemies = ["q", "r"];
                        }
                        if (in_array($occupant, $enemies)) {
                            return true;
                        } else {
                            break;
                        }
                    }
                }
                
                $distance += 1;
            }
        }

        // check for diagonal attack
        foreach ([
            [1, 1],
            [1, -1],
            [-1, -1],
            [-1, 1]
        ] as $dir) {
            $distance = 1;
            while ($distance < 8) {
                $row = $kingPos[0] + ($distance * $dir[0]);
                $col = $kingPos[1] + ($distance * $dir[1]);
                if (posOnBoard([$row, $col])) {
                    $occupant = $gameData->grid[$row][$col];
                    if ($occupant != "-" && $occupant != $kingOcc) {
                        $enemies = ["Q", "B"];
                        if ($color == "b") {
                            $enemies = ["q", "b"];
                        }
                        if (in_array($occupant, $enemies)) {
                            return true;
                        } else {
                            break;
                        }
                    }
                }
                $distance += 1;
            }
        }

        // check for pawn attack
        $dir = 1;
        $enemy = "P";
        if ($color == "b") {
            $dir = -1;
            $enemy = "p";
        }
        foreach([
            [$dir, 1],
            [$dir, -1]
        ] as $step) {
            $enemyPos = [$kingPos[0] + $step[0], $kingPos[1] + $step[1]];
            if (posOnBoard($enemyPos)) {
                if ($gameData->grid[$enemyPos[0]][$enemyPos[1]] == $enemy) {
                    return true;
                }
            }
        }

        return false;
    }

?>