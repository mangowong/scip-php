<?php

declare(strict_types=1);

namespace TestData
{
    use Exception;
//        ^^^^^^^^^ reference scip-php composer php 8.5.4 Exception#

    final class ClassH extends Exception
//                ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#
//                       kind Class
//                       display_name ClassH
//                       signature_documentation
//                       > final class ClassH extends Exception
//                               ^^^^^^^^^ reference scip-php composer php 8.5.4 Exception#
    {
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#

        public function __construct()
//                        ^^^^^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#__construct().
//                                   kind Method
//                                   display_name __construct
//                                   signature_documentation
//                                   > public function __construct()
        {
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#__construct().
            parent::__construct();
//            ^^^^^^ reference scip-php composer php 8.5.4 Exception#
//                    ^^^^^^^^^^^ reference scip-php composer php 8.5.4 Exception#__construct().
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#__construct().
        }

        public function h1(): int
//                        ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#h1().
//                               kind Method
//                               display_name h1
//                               signature_documentation
//                               > public function h1(): int
        {
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#h1().
            $x = \TestData4\fun2()->f2()->a2();
//                 ^^^^^^^^^^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData4/fun2().
//                                    ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f2().
//                                          ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#a2().
            return $this->getCode() + fun2()->a2() * $x;
//                          ^^^^^^^ reference scip-php composer php 8.5.4 Exception#getCode().
//                                      ^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 fun2().
//                                              ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#a2().
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#h1().
        }
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassH#
    }
}

// https://www.php.net/manual/en/language.namespaces.definitionmultiple.php

namespace TestData2
{
    final class ClassJ
//                ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#
//                       kind Class
//                       display_name ClassJ
//                       signature_documentation
//                       > final class ClassJ
    {
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#
        public const J2 = 42;
//                     ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#J2.
//                            kind Constant
//                            display_name J2
//                            signature_documentation
//                            > public J2 = 42
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData2/ClassJ#
    }
}

namespace
{
    final class ClassJ
//                ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#
//                       kind Class
//                       display_name ClassJ
//                       signature_documentation
//                       > final class ClassJ
    {
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#
        public const J3 = 42;
//                     ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#J3.
//                            kind Constant
//                            display_name J3
//                            signature_documentation
//                            > public J3 = 42
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 ClassJ#
    }
}
