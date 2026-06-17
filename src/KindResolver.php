<?php

declare(strict_types=1);

namespace ScipPhp;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Scip\SymbolInformation\Kind;

use function is_string;

final readonly class KindResolver
{
    public static function kind(Const_|ClassLike|ClassMethod|EnumCase|Function_|Param|PropertyItem $n): int
    {
        if ($n instanceof Const_) {
            return Kind::Constant;
        }
        if ($n instanceof Class_) {
            return Kind::PBClass;
        }
        if ($n instanceof Interface_) {
            return Kind::PBInterface;
        }
        if ($n instanceof Trait_) {
            return Kind::PBTrait;
        }
        if ($n instanceof Enum_) {
            return Kind::Enum;
        }
        if ($n instanceof EnumCase) {
            return Kind::EnumMember;
        }
        if ($n instanceof Function_) {
            return Kind::PBFunction;
        }
        if ($n instanceof ClassMethod) {
            if ($n->name->toString() === '__construct') {
                return Kind::Constructor;
            }
            if ($n->isStatic()) {
                return Kind::StaticMethod;
            }
            return Kind::Method;
        }
        if ($n instanceof PropertyItem) {
            return Kind::Field;
        }
        if ($n instanceof Param) {
            return Kind::Parameter;
        }
        return Kind::UnspecifiedKind;
    }

    /** @return ?non-empty-string */
    public static function displayName(Const_|ClassLike|ClassMethod|EnumCase|Function_|Param|PropertyItem $n): ?string
    {
        if ($n instanceof ClassLike) {
            $name = $n->name?->toString();
            return $name !== null && $name !== '' ? $name : null;
        }
        if ($n instanceof ClassMethod || $n instanceof Function_) {
            $name = $n->name->toString();
            return $name !== '' ? $name : null;
        }
        if ($n instanceof Const_ || $n instanceof EnumCase) {
            $name = $n->name->toString();
            return $name !== '' ? $name : null;
        }
        if ($n instanceof PropertyItem) {
            $name = $n->name->toString();
            return $name !== '' ? "\${$name}" : null;
        }
        // Param is the remaining type in the union.
        if (!$n->var instanceof Variable || !is_string($n->var->name)) {
            return null;
        }
        $name = $n->var->name;
        return $name !== '' ? "\${$name}" : null;
    }
}
