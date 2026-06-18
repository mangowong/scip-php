<?php

declare(strict_types=1);

namespace ScipPhp;

use RuntimeException;
use Scip\Document;
use Scip\Index;
use Scip\Language;
use Scip\Metadata;
use Scip\PositionEncoding;
use Scip\TextEncoding;
use Scip\ToolInfo;
use ScipPhp\Composer\Composer;
use ScipPhp\Composer\ProjectFiles;
use ScipPhp\File\Reader;
use ScipPhp\Parser\Parser;
use ScipPhp\Types\Types;

use function array_values;
use function count;
use function function_exists;
use function is_array;
use function json_decode;
use function posix_isatty;
use function str_replace;

use const JSON_THROW_ON_ERROR;
use const STDOUT;

final readonly class Indexer
{
    private Metadata $metadata;

    private Parser $parser;

    private Composer $composer;

    private SymbolNamer $namer;

    private Types $types;

    /** @var list<non-empty-string> */
    private array $projectFiles;

    /**
     * @param  non-empty-string        $projectRoot
     * @param  non-empty-string        $version
     * @param  list<non-empty-string>  $args
     */
    public function __construct(
        private string $projectRoot,
        string $version,
        array $args,
    ) {
        $this->metadata = new Metadata([
            'version'                => 1,
            'project_root'           => "file://{$projectRoot}",
            'text_document_encoding' => TextEncoding::UTF8,
            'tool_info'              => new ToolInfo([
                'name'      => 'scip-php',
                'version'   => $version,
                'arguments' => $args,
            ]),
        ]);

        $this->parser = new Parser();

        $json = $this->parseComposerJson();
        $autoload = is_array($json['autoload'] ?? null) ? $json['autoload'] : [];
        $autoloadDev = is_array($json['autoload-dev'] ?? null) ? $json['autoload-dev'] : [];
        $bin = is_array($json['bin'] ?? null) ? $json['bin'] : [];

        $projectFiles = new ProjectFiles($this->projectRoot, $autoload, $autoloadDev, $bin);
        $this->projectFiles = $projectFiles->projectFiles();

        $this->composer = new Composer($this->projectRoot, $this->projectFiles);
        $this->namer = new SymbolNamer($this->composer);
        $this->types = new Types($this->composer, $this->namer);
    }

    /** @return array<array-key, mixed> */
    private function parseComposerJson(): array
    {
        $content = Reader::read($this->projectRoot . '/composer.json');
        $json = json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            throw new RuntimeException("Cannot parse composer.json.");
        }
        return $json;
    }

    public function index(): Index
    {
        $this->types->collect(...$this->projectFiles);

        $documents = [];
        $extSymbols = [];
        $total = count($this->projectFiles);
        $lastPct = -1;
        $isTty = function_exists('posix_isatty') && posix_isatty(STDOUT);
        foreach ($this->projectFiles as $i => $filename) {
            $n = $i + 1;
            $pct = $total > 0 ? (int) ($n / $total * 100) : 100;
            if ($isTty && $pct !== $lastPct) {
                echo "Indexing: {$pct}% ({$n}/{$total})\r";
                $lastPct = $pct;
            }
            $indexer = new DocIndexer($this->composer, $this->namer, $this->types);
            $this->parser->traverse($filename, $indexer, $indexer->index(...));
            $documents[] = new Document([
                'language'          => Language::PHP,
                'relative_path'     => str_replace($this->projectRoot . '/', '', $filename),
                'occurrences'       => $indexer->occurrences,
                'symbols'           => array_values($indexer->symbols),
                'position_encoding' => PositionEncoding::UTF8CodeUnitOffsetFromLineStart,
            ]);
            foreach ($indexer->extSymbols as $symbol => $info) {
                $extSymbols[$symbol] = $info;
            }
        }
        if ($isTty) {
            echo "Indexing: 100% ({$total}/{$total}) - done          \n";
        }

        return new Index([
            'documents'        => $documents,
            'metadata'         => $this->metadata,
            'external_symbols' => $extSymbols,
        ]);
    }
}
