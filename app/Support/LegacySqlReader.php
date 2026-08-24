<?php

namespace App\Support;

use RuntimeException;

/**
 * Tolerant reader for `INSERT INTO` statements of a MySQL dump file.
 *
 * Parses the extended insert tuples with a small state machine so quoted
 * strings containing commas, parentheses, or escaped quotes are handled.
 */
class LegacySqlReader
{
    /** @var array<string, list<array<string, string|null>>>|null */
    private ?array $tables = null;

    public function __construct(private readonly string $path)
    {
        //
    }

    /**
     * Get all parsed rows for the given table.
     *
     * @return list<array<string, string|null>>
     */
    public function rows(string $table): array
    {
        return $this->parse()[$table] ?? [];
    }

    /**
     * Parse the dump file once and index the rows by table.
     *
     * @return array<string, list<array<string, string|null>>>
     */
    private function parse(): array
    {
        if ($this->tables !== null) {
            return $this->tables;
        }

        if (! is_file($this->path)) {
            throw new RuntimeException("Arquivo de dump não encontrado: {$this->path}");
        }

        $sql = (string) file_get_contents($this->path);
        $this->tables = [];

        preg_match_all('/INSERT INTO `(\w+)` \(([^)]+)\) VALUES/', $sql, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            $table = $match[1][0];
            $columns = array_map(fn (string $column) => trim($column, '` '), explode(',', $match[2][0]));
            $offset = $match[0][1] + strlen($match[0][0]);

            foreach ($this->parseTuples($sql, $offset) as $tuple) {
                if (count($tuple) === count($columns)) {
                    $this->tables[$table][] = array_combine($columns, $tuple);
                }
            }
        }

        return $this->tables;
    }

    /**
     * Consume value tuples from the given offset until the statement terminator.
     *
     * @return list<list<string|null>>
     */
    private function parseTuples(string $sql, int $offset): array
    {
        $rows = [];
        $current = [];
        $value = '';
        $quoted = false;
        $inTuple = false;
        $inString = false;
        $length = strlen($sql);

        for ($i = $offset; $i < $length; $i++) {
            $char = $sql[$i];

            if (! $inTuple) {
                if ($char === '(') {
                    $inTuple = true;
                    $current = [];
                    $value = '';
                    $quoted = false;
                } elseif ($char === ';') {
                    break;
                }

                continue;
            }

            if ($inString) {
                if ($char === '\\') {
                    $value .= self::unescape($sql[$i + 1] ?? '');
                    $i++;
                } elseif ($char === "'") {
                    if (($sql[$i + 1] ?? '') === "'") {
                        $value .= "'";
                        $i++;
                    } else {
                        $inString = false;
                    }
                } else {
                    $value .= $char;
                }

                continue;
            }

            if ($char === "'") {
                if (! $quoted && trim($value) === '') {
                    $value = '';
                }

                $inString = true;
                $quoted = true;
            } elseif ($char === ',') {
                $current[] = self::finalize($value, $quoted);
                $value = '';
                $quoted = false;
            } elseif ($char === ')') {
                $current[] = self::finalize($value, $quoted);
                $rows[] = $current;
                $inTuple = false;
            } else {
                $value .= $char;
            }
        }

        return $rows;
    }

    /**
     * Convert a raw token into its value (NULL handling for unquoted tokens).
     */
    private static function finalize(string $value, bool $quoted): ?string
    {
        if ($quoted) {
            return $value;
        }

        $value = trim($value);

        return strcasecmp($value, 'NULL') === 0 ? null : $value;
    }

    /**
     * Resolve a MySQL escape sequence to its character.
     */
    private static function unescape(string $char): string
    {
        return match ($char) {
            'n' => "\n",
            'r' => "\r",
            't' => "\t",
            '0' => "\0",
            'Z' => "\x1a",
            default => $char,
        };
    }
}
