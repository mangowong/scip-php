<?php

declare(strict_types=1);

namespace TestData;

use function strlen;
//             ^^^^^^ reference scip-php composer php 8.5.4 strlen().

/** @method ClassA m1(int $p1, $p2, bool $p3) */
//                   ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#m1().
final class ClassF
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
//                   kind Class
//                   display_name ClassF
//                   signature_documentation
//                   > final class ClassF
//                   documentation
//                   > @method ClassA m1(int $p1, $p2, bool $p3)
{
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
    public readonly int $f1;
//                        ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f1.
//                               kind Field
//                               display_name $f1
//                               signature_documentation
//                               > public readonly int $f1

    public EnumG $f2;
//           ^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/EnumG#
//                 ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f2.
//                        kind Field
//                        display_name $f2
//                        signature_documentation
//                        > public \TestData\EnumG $f2

    private static ClassA $f3;
//                   ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f3.
//                                 kind Field
//                                 display_name $f3
//                                 signature_documentation
//                                 > private static \TestData\ClassA $f3

    public function f1(): int
//                    ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f1().
//                           kind Method
//                           display_name f1
//                           signature_documentation
//                           > public function f1(): int
    {
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f1().
        return $this->f1 + 42 + strlen('ABC');
//                      ^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f1.
//                                ^^^^^^ reference scip-php composer php 8.5.4 strlen().
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f1().
    }

    public static function f2(): ClassA
//                           ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f2().
//                                  kind Method
//                                  display_name f2
//                                  signature_documentation
//                                  > public static function f2(): \TestData\ClassA
//                                        ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
    {
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f2().
        return self::$f3;
//               ^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
//                     ^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#$f3.
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#f2().
    }
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
}

namespace TestData3;

final class ClassJ
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#
//                   kind Class
//                   display_name ClassJ
//                   signature_documentation
//                   > final class ClassJ
{
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#
    public const J1 = 42;
//                 ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#J1.
//                        kind Constant
//                        display_name J1
//                        signature_documentation
//                        > public J1 = 42
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData3/ClassJ#
}
