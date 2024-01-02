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

    $response->next = suggestMoveAndReturnString($inputs->gameState);
    echo(json_encode($response));

?>