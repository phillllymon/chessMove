<?php

    include_once("ai/util/getEnemiesArr.php");
    include_once("ai/util/piecePinned.php");
    include_once("ai/util/posOnBoard.php");
    include_once("ai/util/kingInCheck.php");
    include_once("printHelp.php");

    // $pos is coordinates of the pawn, e.g. [2, 3] in [row, col] format
    
    function getPieceMovesFromPos($gameData, $pos) {
        $piece = $gameData->grid[$pos[0]][$pos[1]];
        
        // return array(
        //     "-" => [],
        //     "p" => getPawnMoves($gameData, $pos, "w"),
        //     "P" => getPawnMoves($gameData, $pos, "b"),
        //     "r" => getRookMoves($gameData, $pos, "w"),
        //     "R" => getRookMoves($gameData, $pos, "b"),
        //     "n" => getKnightMoves($gameData, $pos, "w"),
        //     "N" => getKnightMoves($gameData, $pos, "b"),
        //     "b" => getBishopMoves($gameData, $pos, "w"),
        //     "B" => getBishopMoves($gameData, $pos, "b"),
        //     "q" => getQueenMoves($gameData, $pos, "w"),
        //     "Q" => getQueenMoves($gameData, $pos, "b"),
        //     "k" => getKingMoves($gameData, $pos, "w"),
        //     "K" => getKingMoves($gameData, $pos, "b"),
        // )[$piece];

        // ^that^ thing didn't work so exact same thing but more explicit:
        if ($piece == "-") {
            return [];
        }
        if ($piece == "P") {
            return getPawnMoves($gameData, $pos, "b");
        }
        if ($piece == "p") {
            return getPawnMoves($gameData, $pos, "w");
        }
        if ($piece == "R") {
            return getRookMoves($gameData, $pos, "b");
        }
        if ($piece == "r") {
            return getRookMoves($gameData, $pos, "w");
        }
        if ($piece == "N") {
            return getKnightMoves($gameData, $pos, "b");
        }
        if ($piece == "n") {
            return getKnightMoves($gameData, $pos, "w");
        }
        if ($piece == "B") {
            return getBishopMoves($gameData, $pos, "b");
        }
        if ($piece == "b") {
            return getBishopMoves($gameData, $pos, "w");
        }
        if ($piece == "Q") {
            return getQueenMoves($gameData, $pos, "b");
        }
        if ($piece == "q") {
            return getQueenMoves($gameData, $pos, "w");
        }
        if ($piece == "K") {
            return getKingMoves($gameData, $pos, "b");
        }
        if ($piece == "k") {
            return getKingMoves($gameData, $pos, "w");
        }
    }
    function getQueenMoves($gameData, $pos, $color) {
        $dirs = [[1, 1], [1, -1], [-1, -1], [-1, 1], [1, 0], [0, 1], [-1, 0], [0, -1]];
        return getSlidableMoves($gameData, $pos, $color, $dirs);
    }
    function getBishopMoves($gameData, $pos, $color) {
        $dirs = [[1, 1], [1, -1], [-1, -1], [-1, 1]];
        return getSlidableMoves($gameData, $pos, $color, $dirs);
    }
    function getRookMoves($gameData, $pos, $color) {
        $dirs = [[1, 0], [0, 1], [-1, 0], [0, -1]];
        return getSlidableMoves($gameData, $pos, $color, $dirs);
    }

    function getKingMoves($gameData, $pos, $color) {
        $steps = [[1, 0], [0, 1], [-1, 0], [0, -1], [1, 1], [1, -1], [-1, -1], [-1, 1]];
        $dests = getSteppableMoves($gameData, $pos, $color, $steps);
        $kingMoves = [];
        foreach ($dests as $kingMove) {
            if (!kingInCheck($gameData, $color, $kingMove[1])) {
                array_push($kingMoves, $kingMove);
            }
        }
        array_push($kingMoves, ...getCastleMoves($gameData, $pos, $color));
        return $kingMoves;
    }
    function getKnightMoves($gameData, $pos, $color) {
        $steps = [[-1, 2], [1, 2], [2, 1], [2, -1], [1, -2], [-1, -2], [-2, 1], [-2, -1]];
        return getSteppableMoves($gameData, $pos, $color, $steps);
    }
    function getPawnMoves($gameData, $pos, $color) {
        $row = $pos[0];
        $col = $pos[1];
        
        $moves = [];

        // $enemies = ["p", "r", "n", "b", "k", "q"]; // if piece is black
        $dir = -1;
        if ($color == "w") {
            // $enemies = ["P", "R", "N", "B", "K", "Q"];
            $dir = 1;
        }
        if ($row > 0 && $row < 7) {
                $kingPos = $gameData->wKingPos;
            if ($color == "b") {
                $kingPos = $gameData->bKingPos;
            }

            $captures = [[$row + $dir, $col + 1], [$row + $dir, $col - 1]];
            $diagonalPin = false;
            if (piecePinned($gameData, $pos, $color, $kingPos)) { // figure out pin direction and deal accordingly
                // if side, no moves possible
                if ($kingPos[0] == $pos[0]) {
                    return [];
                } else if ($kingPos[1] == $pos[1]) {
                    // front or back pin - no captures possible
                    $captures = [];
                } else {
                    // must be diagonal pin - no forward possible and only capture in one direction
                    $diagonalPin = true;

                    // if pawn behind king - can only capture toward king
                    if (($color == "w" && $kingPos[0] > $pos[0]) || ($color == "b" && $kingPos[0] < $pos[0])) {
                        if ($kingPos[1] > $pos[1]) {
                            $captures = [[$row + $dir, $col + 1]];
                        } else {
                            $captures = [[$row + $dir, $col - 1]];
                        }
                    } else { // pawn is in front of king (diagonally)
                        // can only capture away from king
                        if ($kingPos[1] > $pos[1]) {
                            $captures = [[$row + $dir, $col - 1]];
                        } else {
                            $captures = [[$row + $dir, $col + 1]];
                        }
                    }
                }
            }


            // check one move forward
            if (!$diagonalPin) {
                $dest = [$row + $dir, $col];
                $occupant = $gameData->grid[$dest[0]][$dest[1]];

                if ($occupant == "-") {
                    array_push($moves, [$pos, $dest]);
                    // check two moves forward
                    if (($color == "w" && $row == 1) || ($color == "b" && $row == 6)) {
                        $nextDest = [$dest[0] + $dir, $col];
                        if ($gameData->grid[$nextDest[0]][$nextDest[1]] == "-") {
                            array_push($moves, [$pos, $nextDest]);
                        }
                    }
                }
            }
            
            // check diagonals
            foreach ($captures as $capDest) {
                if ($capDest[1] > -1 && $capDest[1] < 8) {
                    if (in_array($gameData->grid[$capDest[0]][$capDest[1]], getEnemiesArr($color))) {
                        array_push($moves, [$pos, $capDest]);
                    }
                }
            }
        }

        // add en passant moves
        if (isset($gameData->enPassant)) {
            $enemyPos = $gameData->enPassant;
            if ($pos[0] == $enemyPos[0]) {
                if ($pos[1] == $enemyPos[1] + 1 || $pos[1] == $enemyPos[1] - 1) {
                    array_push($moves, [$pos, [$pos[0] + $dir], $enemyPos[1], "enPassant"]);
                }
            }
        }

        // return promotion moves if pawn is approaching last row
        $promotions = [];
        for ($i = 0; $i < count($moves); $i++) {
            $thisMove = $moves[$i];
            if ($thisMove[1][0] == 0) { // we got to white side
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "Q"]);
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "R"]);
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "N"]);
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "B"]);
            }
            if ($thisMove[1][0] == 7) { // we got to black side
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "q"]);
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "r"]);
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "n"]);
                array_push($promotions, [[$thisMove[0][0], $thisMove[0][1]], [$thisMove[1][0], $thisMove[1][1]], "b"]);
            }
        }
        if (count($promotions) > 0) {
            return $promotions;
        }

        return $moves;
    }

    // helpers
    function getCastleMoves($gameData, $pos, $color) {
        $castleRow = 0;
        $kingSideAllowed = $gameData->canCastle[0];
        $queenSideAllowed = $gameData->canCastle[1];
        $rook = "r";
        if ($color == "b") {
            $castleRow = 7;
            $kingSideAllowed = $gameData->canCastle[2];
            $queenSideAllowed = $gameData->canCastle[3];
            $rook = "R";
        }
        // king in place?
        if ($pos[0] != $castleRow || $pos[1] != 4) {
            return [];
        }
        // in check?
        if (kingInCheck($gameData, $color)) {
            return [];
        }

        $castleMoves = [];
        // check for kingSide
        if ($kingSideAllowed) {
            // rook in place?
            if ($gameData->grid[$castleRow][7] == $rook) {
                // empty between?
                if ($gameData->grid[$castleRow][6] == "-" && $gameData->grid[$castleRow][5] == "-") {
                    // doesn't pass through check?
                    if (!kingInCheck($gameData, $color, [$castleRow, 5])) {
                        // doesn't land in check?
                        if (!kingInCheck($gameData, $color, [$castleRow, 6])) {
                            array_push($castleMoves, [$pos, [$castleRow, 6], "castle"]);
                        }
                    }
                }
            }
        }

        // check for queenSide
        if ($queenSideAllowed) {
            // rook in place?
            if ($gameData->grid[$castleRow][0] == $rook) {
                // empty between?
                if ($gameData->grid[$castleRow][1] == "-" && $gameData->grid[$castleRow][2] == "-" && $gameData->grid[$castleRow][3] == "-") {
                    // doesn't pass through check?
                    if (!kingInCheck($gameData, $color, [$castleRow, 3])) {
                        // doesn't land in check?
                        if (!kingInCheck($gameData, $color, [$castleRow, 2])) {
                            array_push($castleMoves, [$pos, [$castleRow, 2], "castle"]);
                        }
                    }
                }
            }
        }
        return $castleMoves;
    }

    function getSteppableMoves($gameData, $pos, $color, $steps) {
        $kingPos = $gameData->wKingPos;
        if ($color == "b") {
            $kingPos = $gameData->bKingPos;
        }
        if (piecePinned($gameData, $pos, $color, $kingPos)) {
            return [];
        }

        $moves = [];
        $enemies = getEnemiesArr($color);
        foreach ($steps as $step) {
            $dest = [$pos[0] + $step[0], $pos[1] + $step[1]];
            if (posOnBoard($dest)) {
                $occupant = $gameData->grid[$dest[0]][$dest[1]];
                if ($occupant == "-") {
                    array_push($moves, [$pos, $dest]);
                } else if (in_array($occupant, $enemies)) {
                    array_push($moves, [$pos, $dest]);
                }
            }
        }
        return $moves;
    }

    function getSlidableMoves($gameData, $pos, $color, $dirs) {
        $moves = [];
        $enemies = getEnemiesArr($color);
        $kingPos = $gameData->wKingPos;
        if ($color == "b") {
            $kingPos = $gameData->bKingPos;
        }
        $dirsWeCanMove = $dirs; // we will restrict this only if pinned
        if (piecePinned($gameData, $pos, $color, $kingPos)) {
            $thisPiece = $gameData->grid[$pos[0]][$pos[1]];
            $isRook = ($thisPiece == "r" || $thisPiece == "R");
            $isBishop = ($thisPiece == "b" || $thisPiece == "B");

            // determine $dirs to move
            if ($pos[0] == $kingPos[0]) {
                if ($isBishop) {
                    $dirsWeCanMove = [];
                } else {
                    $dirsWeCanMove = [[0, 1], [0, -1]];
                }
            } else if ($pos[1] == $kingPos[1]) {
                if ($isBishop) {
                    $dirsWeCanMove = [];
                } else {
                    $dirsWeCanMove = [[1, 0], [-1, 0]];
                }
            } else if (($pos[0] > $kingPos[0] && $pos[1] > $kingPos[1]) || ($pos[0] < $kingPos[0] && $pos[1] < $kingPos[1])) {
                if ($isRook) {
                    $dirsWeCanMove = [];
                } else {
                    $dirsWeCanMove = [[1, 1], [-1, -1]];
                }
            } else {
                if ($isRook) {
                    $dirsWeCanMove = [];
                } else {
                    $dirsWeCanMove = [[1, -1], [-1, 1]];
                }
            }

        } 
        foreach ($dirsWeCanMove as $dir) {
            $distance = 1;
            while ($distance < 7) {
                $dest = [$pos[0] + ($distance * $dir[0]), $pos[1] + ($distance * $dir[1])];
                if (!posOnBoard($dest)) {
                    break;
                }
                $occupant = $gameData->grid[$dest[0]][$dest[1]];

                if ($occupant == "-") {
                    array_push($moves, [$pos, $dest]);
                } else if (in_array($occupant, $enemies)) {
                    array_push($moves, [$pos, $dest]);
                    break;
                } else {
                    break;
                }
                $distance += 1;
            }
        }
        return $moves;
    }
?>