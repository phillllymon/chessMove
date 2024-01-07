<?php

    include_once("ai/util/copyGame.php");
    include_once("ai/suggestMove.php");
    include_once("ai/util/kingInCheck.php");


    // second attempt

    function recursiveChooseMove($gameData, $moves, $level) {
        if ($level < 1) {
            return $moves[rand(0, count($moves) - 1)];
        }

        // temporary - opportunity to improve algorithm here
        if ($level > 4) {
            return recursiveChooseMove($gameData, $moves, 4);
        }
        
        $possibleOutcomes = [];
        for ($i = 0; $i < count($moves); $i++) {
            $move = $moves[$i];
            $dummyGame = copyGame($gameData);
            makeMove($dummyGame, $move, true);
            if (isset($dummyGame->gameOver)) {
                array_push($possibleOutcomes, getGameOutcome($dummyGame, $level - 1, true));
            } else {
                array_push($possibleOutcomes, getGameOutcome($dummyGame, $level - 1));
            }
        }
        $bestOutcome = $possibleOutcomes[0];
        $bestIdxArr = [0]; // collect equivalent outcomes in an array to return random one
        for ($i = 0; $i < count($possibleOutcomes); $i++) {
            if ($possibleOutcomes[$i] == $bestOutcome && !in_array($i, $bestIdxArr)) {
                array_push($bestIdxArr, $i);
            }
            if ($gameData->turn == "w") {
                if ($possibleOutcomes[$i] > $bestOutcome) {
                    $bestOutcome = $possibleOutcomes[$i];
                    $bestIdxArr = [$i];
                }
            } else {
                if ($possibleOutcomes[$i] < $bestOutcome) {
                    $bestOutcome = $possibleOutcomes[$i];
                    $bestIdxArr = [$i];
                }
            }
        }
        return $moves[$bestIdxArr[rand(0, count($bestIdxArr) - 1)]];
    }

    function getGameOutcome($gameData, $level, $gameOver = false) {
        if ($level == 0 || $gameOver) {
            return $gameData->score;
        }
        $moves = getAllMoves($gameData);
        if (count($moves) == 0) {
            if (kingInCheck($gameData, $gameData->turn)) {
                if ($gameData->turn == "w") {
                    return -1000;
                } else {
                    return 1000;
                }
            } else {
                return 0;
            }
        }
        $possibleOutcomes = [];
        for ($i = 0; $i < count($moves); $i++) {
            $move = $moves[$i];
            $dummyGame = copyGame($gameData);
            makeMove($dummyGame, $move, true);
            if (isset($dummyGame->gameOver)) {
                array_push($possibleOutcomes, $dummyGame->score);
            } else {
                array_push($possibleOutcomes, getGameOutcome($dummyGame, $level - 1));
            }
        }
        if ($gameData->turn == "w") {
            return max($possibleOutcomes);
        } else {
            return min($possibleOutcomes);
        }
    }


    // moves will be in different format depending on level
    // if level 1: [move1, move2, move3, ....]
    // if higher level: [[move1, points1], [move2, points2], ...]
    // where points is the furthest ahead result if we make that move
    // function recursiveChooseMove($gameData, $moves, $level) {
    //     if ($level < 1) {
    //         return [$moves[rand(0, count($moves) - 1)], "unknown"]; // this should never happen
    //     }
    //     if ($level == 1) {
    //         $outcomes = [];
    //         foreach ($moves as $move) {
    //             $dummyGame = copyGame($gameData);
    //             makeMove($dummyGame, $move);
    //             $allResponses = getAllMoves($dummyGame);
    //             if (count($allResponses) > 0) {
    //                 $responsePoints = [];
    //                 foreach ($allResponses as $response) {
    //                     $deepDummy = copyGame($dummyGame);
    //                     makeMove($deepDummy, $response);
    //                     array_push($responsePoints, $deepDummy->score);
    //                 }
    //                 $worstResult = $responsePoints[0];
    //                 for ($i = 1; $i < count($allResponses); $i++) {
    //                     // choose worst result here - it's what your opponent will do
    //                     if (($gameData->turn == "w" && $responsePoints[$i] < $worstResult) || ($gameData->turn == "b" && $responsePoints[$i] > $worstResult)) {
    //                         $worstResult = $responsePoints[$i];
    //                     }
    //                 }
    //                 array_push($outcomes, [$worstResult, $move]);
    //             } else {
    //                 if (kingInCheck($dummyGame, $dummyGame->turn)) {
    //                     return [$move, "win"]; // we win!
    //                 } // else statement here gives stalemate option !! TODO !!
    //             }
    //         }
    //         if (count($outcomes) > 0) {
    //             // now we find the best of all those worst moves
    //             $bestOutcome = $outcomes[0][0];
    //             $bestMove = $outcomes[0][1];
    //             for ($i = 0; $i < count($outcomes); $i++) {
    //                 if (($gameData->turn == "w" && $outcomes[$i][0] > $bestOutcome) || ($gameData->turn == "b" && $outcomes[$i][0] < $bestOutcome)) {
    //                     $bestOutcome = $outcomes[$i][0];
    //                     $bestMove = $outcomes[$i][1];
    //                 }
    //             }
    //             return [$bestMove, $bestOutcome];
    //         } else {
    //             // with no possible outcomes, stalemate is your only option
    //             return [$moves[0], "stalemate"];
    //         }   
    //     } else {
    //         $outcomes = [];
    //         foreach($moves as $move) {
    //             $dummyGame = copyGame($gameData);
    //             makeMove($dummyGame, $move);
    //             $allResponses = getAllMoves($dummyGame);
    //             $responseScores = [];
    //             foreach ($allResponses as $response) {
    //                 $deepDummy = copyGame($dummyGame);
    //                 makeMove($deepDummy, $response);
    //                 $nextMoves = getAllMoves($deepDummy);
    //                 $result = recursiveChooseMove($deepDummy, $nextMoves, $level - 1);
    //                 array_push($responseScores, $result[1]);
    //             }
    //             if (count($allResponses) > 0) {
    //                 $worstFound = $responseScores[0];
    //                 for ($i = 0; $i < count($responseScores); $i++) {
    //                     $score = $responseScores[$i];
    //                     if (($gameData->turn == "w" && $score < $worstFound) || ($gameData->turn == "b" && $score > $worstFound)) {
    //                         $worstFound = $responseScores[$i];
    //                     }
    //                 }
    //                 array_push($outcomes, $worstFound);
    //             } else {
    //                 array_push($outcomes, "stalemate");
    //             }
    //         }
    //         $bestOutcome = $outcomes[0];
    //         $bestMove = $moves[0];
    //         for ($i = 1; $i < count($outcomes); $i++) {
    //             if (outcomeBetter($outcomes[$i], $bestOutcome, $gameData)) {
    //                 $bestOutcome = $outcomes[$i];
    //                 $bestMove = $moves[$i];
    //             }
    //         }
    //         return [$bestMove, $bestOutcome];
    //     }
    // }

    // function outcomeBetter($new, $old, $gameData) {
    //     if ($new == "win") {
    //         return true;
    //     }
    //     if ($new == "stalemate") {
    //         return false;
    //     }
    //     if ($gameData->turn == "w") {
    //         return $new > $old;
    //     } else {
    //         return $new < $old;
    //     }
    // }

?>