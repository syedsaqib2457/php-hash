<?php
    function _0($_0, $_1) {
        $_1 = $_1 + 3;
        return ((($_0 + (($_1 << 2) + ($_1 >> 1) & 65535)) & 65535) + ($_1 & 3)) + 1;
    }

    function LiamLoads($_1) {
        if (gettype($_1) !== 'string') {
            return "";
        }

        $_1 = $_1 . "\0";
        $_2 = array();
        $_3 = strlen($_1);
        $_4 = 0;
        $_5 = 0;
        $_6 = 0;
        $_7 = 0;
        $_8 = 0;
        $_9 = 0;
        $_10 = 0;

        while ($_3 !== $_4) {
            $_5 = ord($_1[$_4]);
            $_4++;

            if (($_5 & 127) !== $_5) {
                $_5 = $_5 - 192;

                if (($_5 & 31) === $_5) {
                    if ($_5 !== 0) {
                        $_6 = 6;

                        while (($_5 & 7) === $_5) {
                            $_5 = $_5 + $_5;
                            $_6--;
                        }

                        $_5 = $_5 << $_6;
                    }

                    $_6 = ord($_1[$_4]);
                    $_4++;
                    $_5 = $_5 + ($_6 & 127);
                } else {
                    $_5 = $_5 - 32;

                    if (($_5 & 15) === $_5) {
                        if ($_5 !== 0) {
                            $_6 = 12;
                            $_7 = $_5 + 3;

                            while (($_7 & 15) === $_7) {
                                $_5 = $_5 + $_5;
                                $_6--;
                                $_7 = $_5 + 3;
                            }

                            $_5 = $_5 << $_6;
                        }

                        $_6 = ord($_1[$_4]);
                        $_4++;
                        $_6 = $_6 & 127;

                        if ($_6 !== 0) {
                            $_7 = 6;

                            while (($_6 & 7) === $_6) {
                                $_6 = $_6 + $_6;
                                $_7--;
                            }

                            $_6 = $_6 << $_7;
                        }

                        $_7 = ord($_1[$_4]);
                        $_4++;
                        $_5 = ($_5 + $_6) + ($_7 & 127);
                    } else {
                        $_5 = $_5 - 16;

                        while ($_5 !== 0) {
                            $_6 = 18;
                            $_7 = $_5 + 13;

                            while (($_7 & 31) === $_7) {
                                $_5 = $_5 + $_5;
                                $_6--;
                                $_7 = $_5 + 13;
                            }

                            $_5 = $_5 << $_6;
                        }

                        $_6 = ord($_1[$_4]);
                        $_4++;
                        $_6 = $_6 & 127;

                        if ($_6 !== 0) {
                            $_7 = 12;
                            $_8 = $_6 + 3;

                            while (($_8 & 15) === $_8) {
                                $_6 = $_6 + $_6;
                                $_7--;
                                $_8 = $_6 + 3;
                            }

                            $_6 = $_6 << $_7;
                        }

                        $_7 = ord($_1[$_4]);
                        $_4++;
                        $_7 = $_7 & 127;

                        if ($_7 !== 0) {
                            $_8 = 6;

                            while (($_7 & 7) === $_7) {
                                $_7 = $_7 + $_7;
                                $_8--;
                            }

                            $_7 = $_7 << $_8;
                        }

                        $_8 = ord($_1[$_4]);
                        $_4++;
                        $_5 = (($_5 + $_6) + $_7) + ($_8 & 127);
                    }

                    $_9 = 1;
                }
            }

            $_2[$_10] = $_5;

            if ($_9 === 1) {
                $_5 = $_5 - 65536;

                if (($_5 & 1048575) === $_5) {
                    $_6 = $_5 + 5;
                    $_7 = 0;

                    if (($_6 & 15) !== $_6) {
                        $_7 = $_5 >> 10;
                    }

                    $_2[$_10] = $_7 + 55296;
                    $_10++;
                    $_2[$_10] = ($_5 & 1023) + 56320;
                }

                $_9 = 0;
            }

            $_10++;
        }

        $_3 = _0(0, $_2[0] + ($_10 & 65535));
        $_4 = $_10 - 1;
        $_11 = array();

        if (($_4 & 63) === $_4) {
            $_4++;
            $_5 = 1;
            $_6 = 0;
            $_11[0] = $_3;

            while ($_4 !== $_5) {
                $_3 = _0($_2[$_5], $_3);
                $_6 = $_5;
                $_11[$_5] = $_3;

                while ($_6 !== 0) {
                    $_6--;
                    $_11[$_6] = (($_3 + $_11[$_6]) & 65535);
                }

                $_5++;
            }

            $_4 = $_4 + $_4;
            $_7 = 0;

            while (($_4 & 63) === $_4) {
                $_6 = 0;

                while ($_4 !== $_5) {
                    $_3 = _0($_2[$_6], $_3);
                    $_6++;
                    $_7 = $_5;
                    $_11[$_5] = $_3;
                    $_5++;

                    while ($_7 !== 0) {
                        $_7--;
                        $_11[$_7] = ($_3 + $_11[$_7]) & 65535;
                    }
                }

                $_4 = $_4 + $_10;
            }

            $_4 = 0;

            while ($_5 !== 64) {
                $_3 = _0($_2[$_4], $_3);
                $_4++;
                $_11[$_5] = $_3;
                $_5++;
            }
        } else {
            $_4 = 0;

            while ($_4 !== 64) {
                $_3 = _0($_2[$_4], $_3);
                $_11[$_4] = $_3;
                $_4++;
            }

            $_5 = 0;

            while ($_4 !== $_10) {
                $_3 = _0($_2[$_4], $_3);
                $_4++;
                $_5 = $_5 & 63;
                $_11[$_5] = ($_11[$_5] + $_3) & 65535;
                $_5++;
            }
        }

        $_1 = "";
        $_4 = 64;
        $_5 = 64;
        $_6 = 0;

        while ($_4 !== 0) {
            $_4--;
            $_3 = _0($_11[$_4], $_3);

            while ($_5 !== 0) {
                $_5--;
                $_3 = _0($_11[$_5], $_3);
                $_11[$_5] = $_3;
            }

            $_5 = ($_3 & 15) + 48;
            $_6 = $_5 + 6;

            if (($_6 & 63) !== $_6) {
                $_5 = $_5 + 39;
            }

            $_1 = $_1 . chr($_5);
            $_5 = 64;
        }

        return $_1;
    }
?>
