<?php
    include_once("ai/suggestMove.php");



    // show rows left to right and list them in order from white to black
    // $gameState = "rnbqkbnrpppppppp--------------------------------PPPPPPPPRNBQKBNRw1111";

    $gameState = arrToBoardStr([
        ["r", "n", "b", "-", "k", "b", "-", "r"],
        ["p", "p", "p", "p", "-", "p", "p", "p"],
        ["-", "-", "-", "-", "p", "q", "-", "n"],
        ["P", "-", "-", "-", "-", "-", "-", "-"],
        ["-", "-", "-", "-", "-", "-", "-", "-"],
        ["-", "-", "-", "-", "-", "-", "-", "-"],
        ["-", "P", "P", "P", "P", "P", "P", "P"],
        ["R", "N", "B", "Q", "K", "B", "N", "R"]
        // ["K", "-", "-", "-", "-", "-", "-", "-"],
        // ["-", "p", "p", "-", "-", "-", "-", "-"],
        // ["-", "k", "-", "-", "-", "-", "-", "-"],
        // ["-", "r", "-", "-", "-", "-", "-", "-"],
        // ["-", "-", "-", "-", "-", "-", "-", "-"],
        // ["-", "-", "-", "-", "-", "-", "-", "-"],
        // ["-", "-", "-", "-", "-", "-", "-", "-"],
        // ["-", "-", "-", "-", "-", "-", "-", "-"]
    ]);

    

    // echo(implode("X", $myArr));


    
    printBoard($gameState);
    $newBoard = suggestMoveAndReturnString($gameState, 2)[0];
    if ($newBoard == "w is checkmated" || $newBoard == "b is checkmated" || $newBoard == "stalemate" || $newBoard == "insufficient") {
        printOnLine($newBoard);
    } else {
        printBoard($newBoard);
    }
    // printBoard(suggestMoveAndReturnString($gameState));

    // printBoard($gameState);
    // suggestMove($gameState);

    echo("\n");
    echo("\n");

    function printBoard($gameState) {
        // print rows in reverse so board looks like white's perspective
        for ($i = 7; $i > -1; $i--) {
            echo("\n");
            echo($i);
            $row = substr($gameState, 8 * $i, 8);
            for ($j = 0; $j < 8; $j++) {
                echo(" ");
                echo($row[$j]);
            }
        }
        echo("\n");
        echo("  0 1 2 3 4 5 6 7");
        echo("\n");
        echo("\n");
    }

    function arrToBoardStr($arr) {
        $answer = "";
        foreach ($arr as $row) {
            $rowStr = "";
            foreach ($row as $ele) {
                $rowStr .= $ele;
            }
            $answer .= $rowStr;
        }
        $answer .= "w1111";
        return $answer;
    }

?>