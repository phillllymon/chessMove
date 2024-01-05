<?php

    include_once("ai/util/deepCopy.php");

    function copyGame($oldGame) {
        $newGame = new stdClass();
        $newGame->grid = deepCopy($oldGame->grid);
        $newGame->turn = $oldGame->turn;
        $newGame->canCastle = deepCopy($oldGame->canCastle);
        if (isset($oldGame->enPassant)) {
            $newGame->enPassant = deepCopy($oldGame->enPassant);
        }
        $newGame->bKingPos = deepCopy($oldGame->bKingPos);
        $newGame->wKingPos = deepCopy($oldGame->wKingPos);
        $newGame->score = $oldGame->score;
        return $newGame;
    }

?>