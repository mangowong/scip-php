<?php

declare(strict_types=1);

namespace TestData;

/**
 * @property              $d3
 * @property-read  ClassB $d4
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d4.
 * @property-write ClassA $d5
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d5.
 * @property       array<int, array{
 *     ClassA,
 *     ClassB,
 * }>                     $d6
//                          ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d6.
 */
final class ClassD extends ClassA
//            ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#
//                   kind Class
//                   display_name ClassD
//                   signature_documentation
//                   > final class ClassD extends TestData\ClassA
//                   documentation
//                   > @property              $d3<br>@property-read  ClassB $d4<br>@property-write ClassA $d5<br>@property       array<int, array{<br>    ClassA,<br>    ClassB,<br>}>                     $d6<br>
//                           ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassA#
{
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#

    public function __construct(
//                    ^^^^^^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#__construct().
//                               kind Method
//                               display_name __construct
//                               signature_documentation
//                               > public function __construct(public readonly \TestData\ClassF $d1, public readonly int $d2)
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#__construct().
        public readonly ClassF $d1,
//                        ^^^^^^ reference scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassF#
//                               ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d1.
//                                      kind Field
//                                      display_name $d1
//                                      signature_documentation
//                                      > public readonly \TestData\ClassF $d1
        public readonly int $d2,
//                            ^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#$d2.
//                                   kind Field
//                                   display_name $d2
//                                   signature_documentation
//                                   > public readonly int $d2
    ) {
    }
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#__construct()
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassD#
}

final readonly class ClassJ
//                     ^^^^^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#
//                            kind Class
//                            display_name ClassJ
//                            signature_documentation
//                            > final readonly class ClassJ
{
//⌄ enclosing_range_start scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#
    public const J0 = 42;
//                 ^^ definition scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#J0.
//                        kind Constant
//                        display_name J0
//                        signature_documentation
//                        > public J0 = 42
//⌃ enclosing_range_end scip-php composer davidrjenni/scip-php-test 2879a47ba00225b1d0cf31ebe8b9fc7f6cd28be5 TestData/ClassJ#
}
