<?php
    function _0_0($_0_0, $_0_1) {
        $_0_1 = $_0_1 + 3;
        return ((($_0_0 + (($_0_1 << 2) + ($_0_1 >> 1) & 65535)) & 65535) + ($_0_1 & 3)) + 1;
    }

    function liamloadsHash($_0_1) {
        if (gettype($_0_1) !== 'string') {
            return "";
        }

        $_0_1 = $_0_1 . "\0";
        $_0_2 = array();
        $_0_3 = strlen($_0_1);
        $_0_4 = 0;
        $_0_5 = 0;
        $_0_6 = 0;
        $_0_7 = 0;
        $_0_8 = 0;
        $_0_9 = 0;
        $_0_10 = 0;

        while ($_0_3 !== $_0_4) {
            $_0_5 = ord($_0_1[$_0_4]);
            $_0_4++;

            if (($_0_5 & 127) !== $_0_5) {
                $_0_5 = $_0_5 - 192;

                if (($_0_5 & 31) === $_0_5) {
                    if ($_0_5 !== 0) {
                        $_0_6 = 6;

                        while (($_0_5 & 7) === $_0_5) {
                            $_0_5 = $_0_5 + $_0_5;
                            $_0_6--;
                        }

                        $_0_5 = $_0_5 << $_0_6;
                    }

                    $_0_6 = ord($_0_1[$_0_4]);
                    $_0_4++;
                    $_0_5 = $_0_5 + ($_0_6 & 127);
                } else {
                    $_0_5 = $_0_5 - 32;

                    if (($_0_5 & 15) === $_0_5) {
                        if ($_0_5 !== 0) {
                            $_0_6 = 12;
                            $_0_7 = $_0_5 + 3;

                            while (($_0_7 & 15) === $_0_7) {
                                $_0_5 = $_0_5 + $_0_5;
                                $_0_6--;
                                $_0_7 = $_0_5 + 3;
                            }

                            $_0_5 = $_0_5 << $_0_6;
                        }

                        $_0_6 = ord($_0_1[$_0_4]);
                        $_0_4++;
                        $_0_6 = $_0_6 & 127;

                        if ($_0_6 !== 0) {
                            $_0_7 = 6;

                            while (($_0_6 & 7) === $_0_6) {
                                $_0_6 = $_0_6 + $_0_6;
                                $_0_7--;
                            }

                            $_0_6 = $_0_6 << $_0_7;
                        }

                        $_0_7 = ord($_0_1[$_0_4]);
                        $_0_4++;
                        $_0_5 = ($_0_5 + $_0_6) + ($_0_7 & 127);
                    } else {
                        $_0_5 = $_0_5 - 16;

                        while ($_0_5 !== 0) {
                            $_0_6 = 18;
                            $_0_7 = $_0_5 + 13;

                            while (($_0_7 & 31) === $_0_7) {
                                $_0_5 = $_0_5 + $_0_5;
                                $_0_6--;
                                $_0_7 = $_0_5 + 13;
                            }

                            $_0_5 = $_0_5 << $_0_6;
                        }

                        $_0_6 = ord($_0_1[$_0_4]);
                        $_0_4++;
                        $_0_6 = $_0_6 & 127;

                        if ($_0_6 !== 0) {
                            $_0_7 = 12;
                            $_0_8 = $_0_6 + 3;

                            while (($_0_8 & 15) === $_0_8) {
                                $_0_6 = $_0_6 + $_0_6;
                                $_0_7--;
                                $_0_8 = $_0_6 + 3;
                            }

                            $_0_6 = $_0_6 << $_0_7;
                        }

                        $_0_7 = ord($_0_1[$_0_4]);
                        $_0_4++;
                        $_0_7 = $_0_7 & 127;

                        if ($_0_7 !== 0) {
                            $_0_8 = 6;

                            while (($_0_7 & 7) === $_0_7) {
                                $_0_7 = $_0_7 + $_0_7;
                                $_0_8--;
                            }

                            $_0_7 = $_0_7 << $_0_8;
                        }

                        $_0_8 = ord($_0_1[$_0_4]);
                        $_0_4++;
                        $_0_5 = (($_0_5 + $_0_6) + $_0_7) + ($_0_8 & 127);
                    }

                    $_0_9 = 1;
                }
            }

            $_0_2[$_0_10] = $_0_5;

            if ($_0_9 === 1) {
                $_0_5 = $_0_5 - 65536;

                if (($_0_5 & 1048575) === $_0_5) {
                    $_0_6 = $_0_5 + 5;
                    $_0_7 = 0;

                    if (($_0_6 & 15) !== $_0_6) {
                        $_0_7 = $_0_5 >> 10;
                    }

                    $_0_2[$_0_10] = $_0_7 + 55296;
                    $_0_10++;
                    $_0_2[$_0_10] = ($_0_5 & 1023) + 56320;
                }

                $_0_9 = 0;
            }

            $_0_10++;
        }

        $_0_3 = _0_0(0, $_0_2[0] + ($_0_10 & 65535));
        $_0_4 = $_0_10 - 1;
        $_0_11 = array();

        if (($_0_4 & 63) === $_0_4) {
            $_0_4++;
            $_0_5 = 1;
            $_0_6 = 0;
            $_0_11[0] = $_0_3;

            while ($_0_4 !== $_0_5) {
                $_0_3 = _0_0($_0_2[$_0_5], $_0_3);
                $_0_6 = $_0_5;
                $_0_11[$_0_5] = $_0_3;

                while ($_0_6 !== 0) {
                    $_0_6--;
                    $_0_11[$_0_6] = (($_0_3 + $_0_11[$_0_6]) & 65535);
                }

                $_0_5++;
            }

            $_0_4 = $_0_4 + $_0_4;
            $_0_7 = 0;

            while (($_0_4 & 63) === $_0_4) {
                $_0_6 = 0;

                while ($_0_4 !== $_0_5) {
                    $_0_3 = _0_0($_0_2[$_0_6], $_0_3);
                    $_0_6++;
                    $_0_7 = $_0_5;
                    $_0_11[$_0_5] = $_0_3;
                    $_0_5++;

                    while ($_0_7 !== 0) {
                        $_0_7--;
                        $_0_11[$_0_7] = ($_0_3 + $_0_11[$_0_7]) & 65535;
                    }
                }

                $_0_4 = $_0_4 + $_0_10;
            }

            $_0_4 = 0;

            while ($_0_5 !== 64) {
                $_0_3 = _0_0($_0_2[$_0_4], $_0_3);
                $_0_4++;
                $_0_11[$_0_5] = $_0_3;
                $_0_5++;
            }
        } else {
            $_0_4 = 0;

            while ($_0_4 !== 64) {
                $_0_3 = _0_0($_0_2[$_0_4], $_0_3);
                $_0_11[$_0_4] = $_0_3;
                $_0_4++;
            }

            $_0_5 = 0;

            while ($_0_4 !== $_0_10) {
                $_0_3 = _0_0($_0_2[$_0_4], $_0_3);
                $_0_4++;
                $_0_5 = $_0_5 & 63;
                $_0_11[$_0_5] = ($_0_11[$_0_5] + $_0_3) & 65535;
                $_0_5++;
            }
        }

        $_0_1 = "";
        $_0_4 = 64;
        $_0_5 = 64;
        $_0_6 = 0;

        while ($_0_4 !== 0) {
            $_0_4--;
            $_0_3 = _0_0($_0_11[$_0_4], $_0_3);

            while ($_0_5 !== 0) {
                $_0_5--;
                $_0_3 = _0_0($_0_11[$_0_5], $_0_3);
                $_0_11[$_0_5] = $_0_3;
            }

            $_0_5 = ($_0_3 & 15) + 48;
            $_0_6 = $_0_5 + 6;

            if (($_0_6 & 63) !== $_0_6) {
                $_0_5 = $_0_5 + 39;
            }

            $_0_1 = $_0_1 . chr($_0_5);
            $_0_5 = 64;
        }

        return $_0_1;
    }
?>
