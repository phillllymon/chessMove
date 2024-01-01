<?php

function posOnBoard($pos) {
    if ($pos[0] < 0 || $pos[0] > 7) {
        return false;
    }
    if ($pos[1] < 0 || $pos[1] > 7) {
        return false;
    }
    return true;
}

?>