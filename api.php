<?php

    include_once("ai/suggestMove.php");
    include_once("ai/util/moveValid.php");

    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");

    $response = new stdClass();
    $inputs = json_decode(file_get_contents('php://input'));

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $response->error = "invalid request";
        echo(json_encode($response));
        die();
    }

    if ($inputs->request == "checkMove") {
        $gameData = unpackString($inputs->gameState);
        $response->prev = $inputs->gameState;
        $move = $inputs->move;
        $response->move = $move;
        $response->moveValid = isMoveValid($gameData, $move);
    }

    if ($inputs->request == "nextMove") {
        $data = suggestMoveAndReturnString($inputs->gameState, $inputs->level);
        $response->prev = $inputs->gameState;
        $next = $data[0];
        $response->score = $data[1];
        $response->level = $inputs->level;
        $response->move = $data[2];

        if (in_array($next, [
            "stalemate",
            "insufficient",
            "w is checkmated",
            "b is checkmated"
        ])) {
            $response->gameOver = $next;
        } else {
            $response->next = $next;
        }
    }

    echo(json_encode($response));
    

?>