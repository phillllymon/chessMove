<?php
    include_once("ai/getPieceMoves.php");
    include_once("ai/util/getPiecePoints.php");
    include_once("ai/util/arrayRemovePos.php");
    include_once("ai/util/kingInCheck.php");
    include_once("ai/util/insufficientMaterial.php");
    include_once("ai/recursiveChooseMove.php");
    include_once("ai/util/deepCopy.php");

    // gameState should be 1 long string
    function suggestMoveAndReturnString($gameState, $level = 2) {
        $gameData = unpackString($gameState);
        $move = getMove($gameData, $level);
        if ($move == "w is checkmated" || $move == "b is checkmated" || $move == "stalemate" || $move == "insufficient") {
            return [$move];
        } else {
            return [makeMoveAndReturnString($gameState, $move), $gameData->score, $move];
        }
    }

    function getMove($gameData, $level) {
        if (insufficientMaterial($gameData)) {
            return "insufficient";
        }
        $allMoves = getAllMoves($gameData);

        if (count($allMoves) == 0) {
            if (kingInCheck($gameData, $gameData->turn)) {
                return $gameData->turn." is checkmated";
            } else {
                return "stalemate";
            }
        }

        // $moveToReturn = $allMoves[rand(0, count($allMoves) - 1)];
        $moveToReturn = recursiveChooseMove($gameData, $allMoves, $level);

        // temp
        // makeMove($gameData, $moveToReturn);
        // return [$moveToReturn, $gameData->score];
        // end temp

        return $moveToReturn;
    }

    function getAllMoves($gameData) {
        $allMoves = [];
        $friends = ["p", "r", "n", "b", "q", "k"];
        if ($gameData->turn == "b") {
            $friends = ["P", "R", "N", "B", "Q", "K"];
        }

        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 8; $j++) {
                if (in_array($gameData->grid[$i][$j], $friends)) {
                    array_push($allMoves, ...getPieceMovesFromPos($gameData, [$i, $j]));
                }
            }
        }

        // remove moves that don't get king out of check (pinned pieces already won't move & king won't move into check if not already)
        if (kingInCheck($gameData, $gameData->turn)) {
            $allGoodMoves = [];
            foreach ($allMoves as $move) {
                $newGrid = deepCopy($gameData->grid);
                $newGrid = makeMoveGridOnly($newGrid, $move);
                $newKingPos = $gameData->wKingPos;
                $king = "k";
                if ($gameData->turn == "b") {
                    $newKingPos = $gameData->bKingPos;
                    $king = "K";
                }
                if (!($newGrid[$newKingPos[0]][$newKingPos[1]] == $king)) {
                    // need to find new king position
                    foreach ([
                        [$newKingPos[0]+1, $newKingPos[1]+1],
                        [$newKingPos[0]+1, $newKingPos[1]],
                        [$newKingPos[0]+1, $newKingPos[1]-1],
                        [$newKingPos[0], $newKingPos[1]+1],
                        [$newKingPos[0], $newKingPos[1]-1],
                        [$newKingPos[0]-1, $newKingPos[1]+1],
                        [$newKingPos[0]-1, $newKingPos[1]],
                        [$newKingPos[0]-1, $newKingPos[1]-1]
                    ] as $adjPos) {
                        if (posOnBoard($adjPos)) {
                            if ($newGrid[$adjPos[0]][$adjPos[1]] == $king) {
                                $newKingPos = [$adjPos[0], $adjPos[1]];
                            }
                        }
                    }
                }

                if (!kingInCheckNewGrid($gameData, $gameData->turn, $newGrid, $newKingPos)) {
                    array_push($allGoodMoves, $move);
                }
            }
            $allMoves = $allGoodMoves;
        }

        return $allMoves;
    }

    function makeMoveAndReturnString($gameState, $move) {
        $gameData = unpackString($gameState);
        makeMove($gameData, $move);

        return packGameToString($gameData);
    }

    function packGameToString($gameData) {
        $gameState = "";
        foreach ($gameData->grid as $rowArr) {
            $gameState .= implode("", $rowArr);
        }
        $gameState .= $gameData->turn;
        $gameState .= implode("", $gameData->canCastle);
        if (isset($gameData->enPassant) && $gameData->enPassant != false) {
            $gameState .= implode("", $gameData->enPassant);
        }

        return $gameState;
    }

    // takes $gameState string and returns game object with following keys:
    // - grid: 2D array of rows starting from white going left to right
    // - turn: either "w" or "b"
    // - canCastle: array of 1's and 0's (chars) in order whiteKingside, whiteQueenside, blackKingside, blackQueenside
    // - enPassant: coords [row, col] of pawn that just moved
    function unpackString($gameState) {
        $gameData = new stdclass();
        $gameData->grid = [];
        for ($i = 0; $i < 8; $i++) {
            array_push($gameData->grid, strToArr(substr($gameState, 8 * $i, 8)));
        }
        $gameData->turn = $gameState[64];
        if (strlen($gameState) > 65) {
            $gameData->canCastle = strToArr(substr($gameState, 65, 4));
        } else {
            $gameData->canCastle = [0, 0, 0, 0];
        }
        if (strlen($gameState) > 69) {
            $gameData->enPassant = strToArr(substr($gameState, 69, 2));
        } // if not set then no enPassant possible

        $gameData->score = 0;
        // find kings & knights & collect points
        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 8; $j++) {
                $occupant = $gameData->grid[$i][$j];
                if ($occupant == "k") {
                    $gameData->wKingPos = [$i, $j];
                }
                if ($occupant == "K") {
                    $gameData->bKingPos = [$i, $j];
                }
                $gameData->score = $gameData->score + getPiecePoints($occupant);
            }
        }

        return $gameData;
    }
    function strToArr($str) {
        $answer = [];
        // explode on "**" is hacky - separator cannot be empty and I'm confident I'll never have "**"
        for ($i = 0; $i < strlen($str); $i++) {
            array_push($answer, $str[$i]);
        }
        return $answer;
    }

    function makeMoveGridOnly($grid, $move) {
        $origin = $move[0];
        $dest = $move[1];
        if (isset($move[2])) { // special move
            if ($move[2] == "enPassant") {
                $grid[$dest[0]][$dest[1]] = $grid[$origin[0]][$origin[1]];
                $grid[$origin[0]][$origin[1]] = "-";
                $capturePos = [$origin[0], $dest[1]];
                $grid[$capturePos[0]][$capturePos[1]] = "-";
            } else if ($move[2] == "castle") {
                $grid[$dest[0]][$dest[1]] = $grid[$origin[0]][$origin[1]];
                $grid[$origin[0]][$origin[1]] = "-";
                $rCol = 0;
                if ($dest[1] > $origin[1]) {
                    $rCol = 7;
                }
                $rOrigin = [$origin[0], $rCol];
                $rDest = [$origin[0], ($origin[1] + $dest[1]) / 2];
                $grid[$rDest[0]][$rDest[1]] = $grid[$rOrigin[0]][$rOrigin[1]];
                $grid[$rOrigin[0]][$rOrigin[1]] = "-";
            } else {
                $grid[$origin[0]][$origin[1]] = "-";
                $grid[$dest[0]][$dest[1]] = $move[2];
            }
        } else {
            $grid[$dest[0]][$dest[1]] = $grid[$origin[0]][$origin[1]];
            $grid[$origin[0]][$origin[1]] = "-";
        }
        return $grid;
    }

    function makeMove($gameData, $move, $checkEndgame = false) {

        $origin = $move[0];
        $dest = $move[1];
        if (isset($move[2])) { // special move
            if ($move[2] == "enPassant") {
                $gameData->grid[$dest[0]][$dest[1]] = $gameData->grid[$origin[0]][$origin[1]];
                $gameData->grid[$origin[0]][$origin[1]] = "-";
                $capturePos = [$origin[0], $dest[1]];
                $gameData->score = $gameData->score - getPiecePoints($gameData->grid[$capturePos[0]][$capturePos[1]]);
                $gameData->grid[$capturePos[0]][$capturePos[1]] = "-";
            } else if ($move[2] == "castle") {
                $gameData->grid[$dest[0]][$dest[1]] = $gameData->grid[$origin[0]][$origin[1]];
                $gameData->grid[$origin[0]][$origin[1]] = "-";
                $rCol = 0;
                if ($dest[1] > $origin[1]) {
                    $rCol = 7;
                }
                $rOrigin = [$origin[0], $rCol];
                $rDest = [$origin[0], ($origin[1] + $dest[1]) / 2];
                $gameData->grid[$rDest[0]][$rDest[1]] = $gameData->grid[$rOrigin[0]][$rOrigin[1]];
                $gameData->grid[$rOrigin[0]][$rOrigin[1]] = "-";
                if ($gameData->turn == "w") {
                    $gameData->wKingPos = $dest;
                    $gameData->canCastle[0] = 0;
                    $gameData->canCastle[1] = 0;
                } else {
                    $gameData->bKingPos = $dest;
                    $gameData->canCastle[2] = 0;
                    $gameData->canCastle[3] = 0;
                }
            } else {
                $gameData->score = $gameData->score - getPiecePoints($gameData->grid[$origin[0]][$origin[1]]);
                $gameData->score = $gameData->score + getPiecePoints($move[2]);
                $gameData->grid[$origin[0]][$origin[1]] = "-";
                $gameData->grid[$dest[0]][$dest[1]] = $move[2];
            }
        } else {
            // check for capture
            if ($gameData->grid[$dest[0]][$dest[1]] != "-") {
                $scoreAdjust = getPiecePoints($gameData->grid[$dest[0]][$dest[1]]);
                $gameData->score = $gameData->score - $scoreAdjust;

            }
            // check for moving king
            if ($gameData->grid[$origin[0]][$origin[1]] == "k") {
                $gameData->wKingPos = $dest;
                $gameData->canCastle[0] = 0;
                $gameData->canCastle[1] = 0;
            }
            if ($gameData->grid[$origin[0]][$origin[1]] == "K") {
                $gameData->bKingPos = $dest;
                $gameData->canCastle[2] = 0;
                $gameData->canCastle[3] = 0;
            }

            // check for moving from rooks' starting positions
            if ($origin[0] == 0) {
                if ($origin[1] == 0) {
                    $gameData->canCastle[0] = 0;
                }
            }
            if ($origin[0] == 0) {
                if ($origin[1] == 7) {
                    $gameData->canCastle[1] = 0;
                }
            }
            if ($origin[0] == 7) {
                if ($origin[1] == 0) {
                    $gameData->canCastle[2] = 0;
                }
            }
            if ($origin[0] == 7) {
                if ($origin[1] == 7) {
                    $gameData->canCastle[3] = 0;
                }
            }

            // check for enPassant
            if ($gameData->grid[$origin[0]][$origin[1]] == "p" || $gameData->grid[$origin[0]][$origin[1]] == "P") {
                if ($dest[0] - $origin[0] == 2 || $dest[0] - $origin[0] == -2) {
                    $gameData->enPassant = $dest;
                }
            } else {
                $gameData->enPassant = false;
            }

            // actual move
            $gameData->grid[$dest[0]][$dest[1]] = $gameData->grid[$origin[0]][$origin[1]];
            $gameData->grid[$origin[0]][$origin[1]] = "-";
        }
        if ($gameData->turn == "w") {
            $gameData->turn = "b";
        } else {
            $gameData->turn = "w";
        }

        // check for endgame
        if ($checkEndgame) {
            if (!gameHasMoves($gameData)) {
                $gameData->gameOver = true;
                if (kingInCheck($gameData, $gameData->turn)) {
                    if ($gameData->turn == "w") {
                        $gameData->score = -1000;
                    } else {
                        $gameData->score = 1000;
                    }
                } else {
                    $gameData->score = 0;
                }
            }
        }
    }

    function gameHasMoves($gameData) {
        // return count(getAllMoves($gameData)) > 0; // << this is slow, better below
        $friends = ["p", "r", "n", "b", "q", "k"];
        if ($gameData->turn == "b") {
            $friends = ["P", "R", "N", "B", "Q", "K"];
        }

        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 8; $j++) {
                if (in_array($gameData->grid[$i][$j], $friends)) {
                    if (count(getPieceMovesFromPos($gameData, [$i, $j])) > 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

?>