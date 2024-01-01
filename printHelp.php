<?php

    function printOnLine($val) {
        if (gettype($val) == "string") {
            echo("\n");
            echo($val);
        } else if (gettype($val) == "integer") {
            echo("\n");
            echo($val);
        } else if (gettype($val) == "boolean") {
            if ($val) {
                printOnLine("true");
            } else {
                printOnLine("false");
            }
        } else if (gettype($val) == "array") {
            printOnLine("[");
            foreach ($val as $ele) {
                if (gettype($ele) == "array") {
                    printOnLine($ele);
                } else {
                    echo " ".$ele;
                }
            }
            printOnLine("]");
        } else {
            printOnLine(gettype($val));
        }
    }

?>