<?php

class Environment
{
    const VARNAME_REGEX = '(?i:[A-Z][A-Z0-9_]*+)';
    const STATE_VARNAME = 0;
    const STATE_VALUE = 1;

    protected static $path = ROOT_DIR;
    protected static $envBag = null;

    public static function get($key, $default = null)
    {
        if (is_null(static::$envBag)) {
            self::load();
        }

        return isset(static::$envBag[$key]) ? static::$envBag[$key] : $default;
    }

    private static function load()
    {
        static::$envBag = [];
        $path = static::$path . '/.env';
        if (!is_readable($path) || is_dir($path)) {
            echo "File not readable!";
            exit;
        }

        static::$envBag = self::parse(file_get_contents($path));
    }

    private static function parse($data)
    {
        $data = str_replace(["\r\n", "\r"], "\n", $data);
        $lineno = 1;
        $cursor = 0;
        $end = \strlen($data);

        if (preg_match('/(?:\s*+(?:#[^\n]*+)?+)++/A', $data, $match, 0, $cursor)) {
            $cursor += \strlen($match[0]);
            $lineno += substr_count($match[0], "\n");
        }

        $state = 0;

        $values = [];
        $name = '';

        while ($cursor < $end) {
            switch ($state) {
                case self::STATE_VARNAME:
                    $name = self::lexVarname($data, $cursor, $lineno, $end);
                    $state = 1;
                    break;
                case self::STATE_VALUE:
                    $values[$name] = self::lexValue($data, $cursor, $end, $lineno);
                    $state = 0;
                    break;
            }
        }

        if (self::STATE_VALUE === $state) {
            $values[$name] = '';
        }

        return $values;
    }

    private static function lexVarname(&$data, &$cursor, &$lineno, &$end)
    {
        // var name + optional export
        if (!preg_match('/(export[ \t]++)?(' . self::VARNAME_REGEX . ')/A', $data, $matches, 0, $cursor)) {
            echo 'Invalid character in variable name';
            exit;
        }

        $cursor += \strlen($matches[0]);
        $lineno += substr_count($matches[0], "\n");


        if ($cursor === $end || "\n" === $data[$cursor] || '#' === $data[$cursor]) {
            if ($matches[1]) {
                echo 'Unable to unset an environment variable';
                exit;
            }

            echo 'Missing = in the environment variable declaration';
            exit;
        }

        if (' ' === $data[$cursor] || "\t" === $data[$cursor]) {
            echo 'Whitespace characters are not supported after the variable name';
            exit;
        }

        if ('=' !== $data[$cursor]) {
            echo 'Missing = in the environment variable declaration';
            exit;
        }

        ++$cursor;

        return $matches[2];
    }

    private static function lexValue(&$data, &$cursor, &$end, &$lineno): string
    {

        if (preg_match('/[ \t]*+(?:#.*)?$/Am', $data, $matches, 0, $cursor)) {
            $cursor += \strlen($matches[0]);
            $lineno += substr_count($matches[0], "\n");


            if (preg_match('/(?:\s*+(?:#[^\n]*+)?+)++/A', $data, $match, 0, $cursor)) {
                $cursor += \strlen($matches[0]);
                $lineno += substr_count($matches[0], "\n");
            }

            return '';
        }

        if (' ' === $data[$cursor] || "\t" === $data[$cursor]) {
            echo 'Whitespace are not supported before the value';
            exit;
        }

        $v = '';

        do {
            if ("'" === $data[$cursor]) {
                $len = 0;

                do {
                    if ($cursor + ++$len === $end) {
                        $cursor += $len;

                        echo 'Missing quote to end the value';
                        exit;
                    }
                } while ("'" !== $data[$cursor + $len]);

                $v .= substr($data, 1 + $cursor, $len - 1);
                $cursor += 1 + $len;
            } elseif ('"' === $data[$cursor]) {
                $value = '';

                if (++$cursor === $end) {
                    echo 'Missing quote to end the value';
                    exit;
                }

                while ('"' !== $data[$cursor] || ('\\' === $data[$cursor - 1] && '\\' !== $data[$cursor - 2])) {
                    $value .= $data[$cursor];
                    ++$cursor;

                    if ($cursor === $end) {
                        echo 'Missing quote to end the value';
                        exit;
                    }
                }
                ++$cursor;
                $value = str_replace(['\\"', '\r', '\n'], ['"', "\r", "\n"], $value);
                $resolvedValue = $value;
                $resolvedValue = str_replace('\\\\', '\\', $resolvedValue);
                $v .= $resolvedValue;
            } else {
                $value = '';
                $prevChr = $data[$cursor - 1];
                while ($cursor < $end && !\in_array($data[$cursor], ["\n", '"', "'"], true) && !((' ' === $prevChr || "\t" === $prevChr) && '#' === $data[$cursor])) {
                    if ('\\' === $data[$cursor] && isset($data[$cursor + 1]) && ('"' === $data[$cursor + 1] || "'" === $data[$cursor + 1])) {
                        ++$cursor;
                    }

                    $value .= $prevChr = $data[$cursor];

                    if ('$' === $data[$cursor] && isset($data[$cursor + 1]) && '(' === $data[$cursor + 1]) {
                        ++$cursor;
                        $value .= '(' . self::lexNestedExpression($cursor, $data, $end) . ')';
                    }

                    ++$cursor;
                }
                $value = rtrim($value);
                $resolvedValue = $value;
                $resolvedValue = str_replace('\\\\', '\\', $resolvedValue);

                if ($resolvedValue === $value && preg_match('/\s+/', $value)) {
                    echo 'A value containing spaces must be surrounded by quotes';
                    exit;
                }

                $v .= $resolvedValue;

                if ($cursor < $end && '#' === $data[$cursor]) {
                    break;
                }
            }
        } while ($cursor < $end && "\n" !== $data[$cursor]);

        // skip Empty Lines
        if (preg_match('/(?:\s*+(?:#[^\n]*+)?+)++/A', $data, $match, 0, $cursor)) {
            // move cursor
            $cursor += \strlen($match[0]);
            $lineno += substr_count($match[0], "\n");
        }

        return $v;
    }

    private static function lexNestedExpression(&$cursor, &$data, &$end): string
    {
        ++$cursor;
        $value = '';

        while ("\n" !== $data[$cursor] && ')' !== $data[$cursor]) {
            $value .= $data[$cursor];

            if ('(' === $data[$cursor]) {
                $value .= self::lexNestedExpression($cursor, $data, $end) . ')';
            }

            ++$cursor;

            if ($cursor === $end) {
                echo 'Missing closing parenthesis.';
                exit;
            }
        }

        if ("\n" === $data[$cursor]) {
            echo 'Missing closing parenthesis.';
            exit;
        }

        return $value;
    }
}
