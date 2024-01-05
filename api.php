<?php

    include_once("ai/suggestMove.php");

    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");

    $response = new stdClass();
    $inputs = json_decode(file_get_contents('php://input'));

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $response->error = "invalid request";
        echo(json_encode($response));
        die();
    }


    // $next = suggestMoveAndReturnString($inputs->gameState);

    // temp
    $data = suggestMoveAndReturnString($inputs->gameState, $inputs->level);
    $next = $data[0];
    $response->score = $data[1];
    
    // end temp

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
    echo(json_encode($response));

?>