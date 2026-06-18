<?php

declare(strict_types=1);

namespace ScipPhp\Parser;

use Closure;
use Override;
use PhpParser\Error as ParseError;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser as PhpParser;
use PhpParser\ParserFactory;
use ScipPhp\File\Reader;
use Throwable;

use function fwrite;

use const STDERR;

final readonly class Parser
{
    private ParentConnectingVisitor $parentConnectingVisitor;

    private NameResolver $nameResolver;

    private PhpParser $parser;

    public function __construct()
    {
        $this->parentConnectingVisitor = new ParentConnectingVisitor();
        $this->nameResolver = new NameResolver();
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * @param  non-empty-string  $filename
     * @param  Closure(PosResolver, Node): void  $visitor
     */
    public function traverse(string $filename, object $newThis, Closure $visitor): void
    {
        $code = Reader::read($filename);
        if ($code === '') {
            return;
        }

        $stmts = null;
        try {
            $stmts = $this->parser->parse($code);
        } catch (ParseError $e) {
            fwrite(STDERR, "Warning: parse error in {$filename}: {$e->getMessage()}\n");
            return;
        } catch (Throwable $e) {
            fwrite(STDERR, "Warning: failed to parse {$filename}: {$e->getMessage()}\n");
            return;
        }
        if ($stmts === null) {
            fwrite(STDERR, "Warning: cannot parse file: {$filename}\n");
            return;
        }

        $pos = new PosResolver($code);

        $t = new NodeTraverser(
            $this->nameResolver,
            $this->parentConnectingVisitor,
            new class ($pos, $newThis, $visitor) extends NodeVisitorAbstract
            {
                public function __construct(
                    private readonly PosResolver $pos,
                    private readonly object $newThis,
                    private readonly Closure $visitor,
                ) {
                }

                #[Override]
                public function leaveNode(Node $n): ?Node
                {
                    $this->visitor->call($this->newThis, $this->pos, $n);
                    return null;
                }
            },
        );

        $t->traverse($stmts);
    }
}
