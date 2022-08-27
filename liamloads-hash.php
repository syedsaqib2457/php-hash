<?php
    function _0_0($_0_0, $_0_1) {
        $_0_1 = $_0_1 + 3;
        return ((($_0_0 + (($_0_1 << 2) + ($_0_1 >> 1) & 65535)) & 65535) + ($_0_1 & 3)) + 1;
    }

    function liamloadsHash($_0_1) {
        $_0_1 = $_0_1 . "\0";
        // In development.
        return "";
    }
?>
