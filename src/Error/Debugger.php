<?php

declare(strict_types=1);

namespace Chiron\Core\Error;

use Chiron\Injector\InvokerInterface;
use Closure;
use Exception;
use InvalidArgumentException;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use Throwable;
use Psr\Container\ContainerInterface;
use Chiron\Core\Error\Debug\DebugContext;

use Chiron\Core\Error\Debug\Node\ScalarNode;
use Chiron\Core\Error\Debug\Node\ClassNode;
use Chiron\Core\Error\Debug\Node\NodeInterface;
use Chiron\Core\Error\Debug\Node\SpecialNode;
use Chiron\Core\Error\Debug\Node\PropertyNode;
use Chiron\Core\Error\Debug\Node\ArrayItemNode;
use Chiron\Core\Error\Debug\Node\ArrayNode;
use Chiron\Core\Error\Debug\Node\ReferenceNode;

use Chiron\Core\Error\Debug\Formatter\FormatterInterface;
use Chiron\Core\Error\Debug\Formatter\TextFormatter;
use Chiron\Core\Error\Debug\Formatter\ConsoleFormatter;
use Chiron\Core\Error\Debug\Formatter\HtmlFormatter;

//https://github.com/cakephp/cakephp/blob/5.x/src/Error/Debugger.php

// TODO : passer les protected en private
final class Debugger
{
    /**
     * Returns a reference to the Debugger singleton object instance.
     *
     * @param string|null $class Class name.
     * @return static
     */
    // TODO : utilité de garder l'instance ????
    public static function getInstance(?string $class = null): static
    {
        static $instance = []; // TODO : voir pourquoi on utilise un tableau et pas simplement une variable basique !!! eventuellement l'initialiser à null
        if (!empty($class)) {
            if (!$instance || strtolower($class) !== strtolower(get_class($instance[0]))) {
                $instance[0] = new $class();
            }
        }
        if (!$instance) {
            $instance[0] = new Debugger();
        }

        return $instance[0];
    }

    /**
     * Recursively formats and outputs the contents of the supplied variable.
     *
     * @param mixed $var The variable to dump.
     * @param int $maxDepth The depth to output to. Defaults to 3.
     * @return void
     * @see \Cake\Error\Debugger::exportVar()
     * @link https://book.cakephp.org/4/en/development/debugging.html#outputting-values
     */
    public static function dump(mixed $var, int $maxDepth = 3): void
    {
        pr(static::exportVar($var, $maxDepth));
    }

    /**
     * Outputs a stack trace based on the supplied options.
     *
     * ### Options
     *
     * - `depth` - The number of stack frames to return. Defaults to 999
     * - `format` - The format you want the return. Defaults to the currently selected format. If
     *    format is 'array' or 'points' the return will be an array.
     * - `args` - Should arguments for functions be shown? If true, the arguments for each method call
     *   will be displayed.
     * - `start` - The stack frame to start generating a trace from. Defaults to 0
     *
     * @param array<string, mixed> $options Format for outputting stack trace.
     * @return array|string Formatted stack trace.
     * @link https://book.cakephp.org/4/en/development/debugging.html#generating-stack-traces
     */
    public static function trace(array $options = []): array|string
    {
        return Debugger::formatTrace(debug_backtrace(), $options); // TODO : utiliser static:: et non pas Debugger::
    }

    /**
     * Formats a stack trace based on the supplied options.
     *
     * ### Options
     *
     * - `depth` - The number of stack frames to return. Defaults to 999
     * - `format` - The format you want the return. Defaults to 'text'. If
     *    format is 'array' or 'points' the return will be an array.
     * - `args` - Should arguments for functions be shown? If true, the arguments for each method call
     *   will be displayed.
     * - `start` - The stack frame to start generating a trace from. Defaults to 0
     *
     * @param \Throwable|array $backtrace Trace as array or an exception object.
     * @param array<string, mixed> $options Format for outputting stack trace.
     * @return array|string Formatted stack trace.
     * @link https://book.cakephp.org/4/en/development/debugging.html#generating-stack-traces
     */
    public static function formatTrace(Throwable|array $backtrace, array $options = []): array|string
    {
        if ($backtrace instanceof Throwable) {
            $backtrace = $backtrace->getTrace();
        }

        $self = Debugger::getInstance();
        $defaults = [
            'depth' => 999,
            'format' => 'text',
            'args' => false,
            'start' => 0,
            'scope' => null,
            'exclude' => ['call_user_func_array', 'trigger_error'],
        ];

        //$options = Hash::merge($defaults, $options);
        $options = array_merge($defaults, $options); // TODO : code temporaire, vérifier que ca fonctionne bien comme Hash::merge !!!


        $count = count($backtrace);
        $back = [];

        $_trace = [
            'line' => '??',
            'file' => '[internal]',
            'class' => null,
            'function' => '[main]',
        ];

        for ($i = $options['start']; $i < $count && $i < $options['depth']; $i++) {
            $trace = $backtrace[$i] + ['file' => '[internal]', 'line' => '??'];
            $signature = $reference = '[main]';

            if (isset($backtrace[$i + 1])) {
                $next = $backtrace[$i + 1] + $_trace;
                $signature = $reference = $next['function'];

                if (!empty($next['class'])) {
                    $signature = $next['class'] . '::' . $next['function'];
                    $reference = $signature . '(';
                    if ($options['args'] && isset($next['args'])) {
                        $args = [];
                        foreach ($next['args'] as $arg) {
                            $args[] = Debugger::exportVar($arg);
                        }
                        $reference .= implode(', ', $args);
                    }
                    $reference .= ')';
                }
            }
            if (in_array($signature, $options['exclude'], true)) {
                continue;
            }
            if ($options['format'] === 'points') {
                $back[] = ['file' => $trace['file'], 'line' => $trace['line'], 'reference' => $reference];
            } elseif ($options['format'] === 'array') {
                $back[] = $trace;
            } elseif ($options['format'] === 'text') {
                $path = static::trimPath($trace['file']);
                $reference = $reference;
                $back[] = sprintf('%s - %s, line %d', $reference, $path, $trace['line']);
            } else {
                throw new InvalidArgumentException(
                    'Invalid trace format chosen. Must be one of `array`, `points` or `text`.'
                );
            }
        }

        if ($options['format'] === 'array' || $options['format'] === 'points') {
            return $back;
        }

        /** @psalm-suppress InvalidArgument */
        return implode("\n", $back);
    }

    /**
     * Shortens file paths by replacing the application base path with 'APP', and the CakePHP core
     * path with 'CORE'.
     *
     * @param string $path Path to shorten.
     * @return string Normalized path
     */
    public static function trimPath(string $path): string
    {
        if (defined('APP') && strpos($path, APP) === 0) {
            return str_replace(APP, 'APP/', $path);
        }
        if (defined('CAKE_CORE_INCLUDE_PATH') && strpos($path, CAKE_CORE_INCLUDE_PATH) === 0) {
            return str_replace(CAKE_CORE_INCLUDE_PATH, 'CORE', $path);
        }
        if (defined('ROOT') && strpos($path, ROOT) === 0) {
            return str_replace(ROOT, 'ROOT', $path);
        }

        return $path;
    }

    /**
     * Prints out debug information about given variable.
     *
     * @param mixed $var Variable to show debug information for.
     * @param array $location If contains keys "file" and "line" their values will
     *    be used to show location info.
     * @param bool|null $showHtml If set to true, the method prints the debug
     *    data encoded as HTML. If false, plain text formatting will be used.
     *    If null, the format will be chosen based on the configured exportFormatter, or
     *    environment conditions.
     * @return void
     */
    public static function printVar(mixed $var, array $location = [], ?bool $showHtml = null): void
    {
        $location += ['file' => null, 'line' => null];
        if ($location['file']) {
            $location['file'] = static::trimPath((string)$location['file']);
        }

        $debugger = static::getInstance();
        $restore = null;
        if ($showHtml !== null) {
            $restore = $debugger->getConfig('exportFormatter');
            $debugger->setConfig('exportFormatter', $showHtml ? HtmlFormatter::class : TextFormatter::class);
        }
        $contents = static::exportVar($var, 25);
        $formatter = $debugger->getExportFormatter();

        if ($restore) {
            $debugger->setConfig('exportFormatter', $restore);
        }

        echo $formatter->formatWrapper($contents, $location);
    }

    /**
     * Converts a variable to a string for debug output.
     *
     * *Note:* The following keys will have their contents
     * replaced with `*****`:
     *
     *  - password
     *  - login
     *  - host
     *  - database
     *  - port
     *  - prefix
     *  - schema
     *
     * This is done to protect database credentials, which could be accidentally
     * shown in an error message if CakePHP is deployed in development mode.
     *
     * @param mixed $var Variable to convert.
     * @param int $maxDepth The depth to output to. Defaults to 3.
     * @return string Variable as a formatted string
     */
    public static function exportVar(mixed $var, int $maxDepth = 3): string
    {
        $context = new DebugContext($maxDepth);
        $node = static::export($var, $context);

        return static::getInstance()->getExportFormatter()->dump($node);
    }

    /**
     * Converts a variable to a plain text string.
     *
     * @param mixed $var Variable to convert.
     * @param int $maxDepth The depth to output to. Defaults to 3.
     * @return string Variable as a string
     */
    public static function exportVarAsPlainText(mixed $var, int $maxDepth = 3): string
    {
        return (new TextFormatter())->dump(
            static::export($var, new DebugContext($maxDepth))
        );
    }

    /**
     * Convert the variable to the internal node tree.
     *
     * The node tree can be manipulated and serialized more easily
     * than many object graphs can.
     *
     * @param mixed $var Variable to convert.
     * @param int $maxDepth The depth to generate nodes to. Defaults to 3.
     * @return \Cake\Error\Debug\NodeInterface The root node of the tree.
     */
    public static function exportVarAsNodes(mixed $var, int $maxDepth = 3): NodeInterface
    {
        return static::export($var, new DebugContext($maxDepth));
    }

    /**
     * Protected export function used to keep track of indentation and recursion.
     *
     * @param mixed $var The variable to dump.
     * @param \Cake\Error\Debug\DebugContext $context Dump context
     * @return \Cake\Error\Debug\NodeInterface The dumped variable.
     */
    protected static function export(mixed $var, DebugContext $context): NodeInterface
    {
        $type = get_debug_type($var);

        if (str_starts_with($type, 'resource ')) {
            return new ScalarNode($type, $var);
        }

        switch ($type) {
            case 'null':
            case 'bool':
            case 'int':
            case 'float':
            case 'string':
                return new ScalarNode($type, $var);
            case 'array':
                return static::exportArray($var, $context->withAddedDepth());
            default:
                return static::exportObject($var, $context->withAddedDepth());
        }
    }

    /**
     * Export an array type object. Filters out keys used in datasource configuration.
     *
     * The following keys are replaced with ***'s
     *
     * - password
     * - login
     * - host
     * - database
     * - port
     * - prefix
     * - schema
     *
     * @param array $var The array to export.
     * @param \Cake\Error\Debug\DebugContext $context The current dump context.
     * @return \Cake\Error\Debug\ArrayNode Exported array.
     */
    protected static function exportArray(array $var, DebugContext $context): ArrayNode
    {
        $items = [];

        $remaining = $context->remainingDepth();
        if ($remaining >= 0) {

            //$outputMask = static::outputMask();
            $outputMask = []; // TODO : code temporaire !!!

            foreach ($var as $key => $val) {
                if (array_key_exists($key, $outputMask)) {
                    $node = new ScalarNode('string', $outputMask[$key]);
                } elseif ($val !== $var) {
                    // Dump all the items without increasing depth.
                    $node = static::export($val, $context);
                } else {
                    // Likely recursion, so we increase depth.
                    $node = static::export($val, $context->withAddedDepth());
                }
                $items[] = new ArrayItemNode(static::export($key, $context), $node);
            }
        } else {
            $items[] = new ArrayItemNode(
                new ScalarNode('string', ''),
                new SpecialNode('[maximum depth reached]')
            );
        }

        return new ArrayNode($items);
    }

    /**
     * Handles object to node conversion.
     *
     * @param object $var Object to convert.
     * @param \Cake\Error\Debug\DebugContext $context The dump context.
     * @return \Cake\Error\Debug\NodeInterface
     * @see \Cake\Error\Debugger::exportVar()
     */
    protected static function exportObject(object $var, DebugContext $context): NodeInterface
    {
        $isRef = $context->hasReference($var);
        $refNum = $context->getReferenceId($var);

        $className = get_class($var);
        if ($isRef) {
            return new ReferenceNode($className, $refNum);
        }
        $node = new ClassNode($className, $refNum);

        $remaining = $context->remainingDepth();
        if ($remaining > 0) {
            if (method_exists($var, '__debugInfo')) {
                try {
                    foreach ($var->__debugInfo() as $key => $val) {
                        $node->addProperty(new PropertyNode("'{$key}'", null, static::export($val, $context)));
                    }

                    return $node;
                } catch (Exception $e) {
                    return new SpecialNode("(unable to export object: {$e->getMessage()})");
                }
            }

            //$outputMask = static::outputMask();
            $outputMask = []; // TODO : code temporaire !!!

            $objectVars = get_object_vars($var);
            foreach ($objectVars as $key => $value) {
                if (array_key_exists($key, $outputMask)) {
                    $value = $outputMask[$key];
                }
                /** @psalm-suppress RedundantCast */
                $node->addProperty(
                    new PropertyNode((string)$key, 'public', static::export($value, $context->withAddedDepth()))
                );
            }

            $ref = new ReflectionObject($var);

            $filters = [
                ReflectionProperty::IS_PROTECTED => 'protected',
                ReflectionProperty::IS_PRIVATE => 'private',
            ];
            foreach ($filters as $filter => $visibility) {
                $reflectionProperties = $ref->getProperties($filter);
                foreach ($reflectionProperties as $reflectionProperty) {
                    $reflectionProperty->setAccessible(true);

                    if (
                        method_exists($reflectionProperty, 'isInitialized') &&
                        !$reflectionProperty->isInitialized($var)
                    ) {
                        $value = new SpecialNode('[uninitialized]');
                    } else {
                        $value = static::export($reflectionProperty->getValue($var), $context->withAddedDepth());
                    }
                    $node->addProperty(
                        new PropertyNode(
                            $reflectionProperty->getName(),
                            $visibility,
                            $value
                        )
                    );
                }
            }
        }

        return $node;
    }

    /**
     * Get the configured export formatter or infer one based on the environment.
     *
     * @return \Cake\Error\Debug\FormatterInterface
     * @unstable This method is not stable and may change in the future.
     * @since 4.1.0
     */
    public function getExportFormatter(): FormatterInterface
    {
        $instance = static::getInstance();

        //$class = $instance->getConfig('exportFormatter');
        $class = null; // TODO : code temporaire !!!!

        if (!$class) {
            if (ConsoleFormatter::environmentMatches()) {
                $class = ConsoleFormatter::class;
            } elseif (HtmlFormatter::environmentMatches()) {
                $class = HtmlFormatter::class;
            } else {
                $class = TextFormatter::class;
            }
        }
        $instance = new $class();
        if (!$instance instanceof FormatterInterface) {
            throw new RuntimeException(
                "The `{$class}` formatter does not implement " . FormatterInterface::class
            );
        }

        return $instance;
    }

    /**
     * Grabs an excerpt from a file and highlights a given line of code.
     *
     * Usage:
     *
     * ```
     * Debugger::excerpt('/path/to/file', 100, 4);
     * ```
     *
     * The above would return an array of 8 items. The 4th item would be the provided line,
     * and would be wrapped in `<span class="code-highlight"></span>`. All the lines
     * are processed with highlight_string() as well, so they have basic PHP syntax highlighting
     * applied.
     *
     * @param string $file Absolute path to a PHP file.
     * @param int $line Line number to highlight.
     * @param int $context Number of lines of context to extract above and below $line.
     * @return array<string> Set of lines highlighted
     * @see https://secure.php.net/highlight_string
     * @link https://book.cakephp.org/4/en/development/debugging.html#getting-an-excerpt-from-a-file
     */
    public static function excerpt(string $file, int $line, int $context = 2): array
    {
        $lines = [];
        if (!file_exists($file)) {
            return [];
        }
        $data = file_get_contents($file);
        if (empty($data)) {
            return $lines;
        }
        if (str_contains($data, "\n")) {
            $data = explode("\n", $data);
        }
        $line--;
        if (!isset($data[$line])) {
            return $lines;
        }
        for ($i = $line - $context; $i < $line + $context + 1; $i++) {
            if (!isset($data[$i])) {
                continue;
            }
            $string = str_replace(["\r\n", "\n"], '', static::highlight($data[$i]));
            if ($i === $line) {
                $lines[] = '<span class="code-highlight">' . $string . '</span>';
            } else {
                $lines[] = $string;
            }
        }

        return $lines;
    }

    /**
     * Wraps the highlight_string function in case the server API does not
     * implement the function as it is the case of the HipHop interpreter
     *
     * @param string $str The string to convert.
     * @return string
     */
    protected static function highlight(string $str): string
    {
        if (function_exists('hphp_log') || function_exists('hphp_gettid')) {
            return htmlentities($str);
        }
        $added = false;
        if (!str_contains($str, '<?php')) {
            $added = true;
            $str = "<?php \n" . $str;
        }
        $highlight = highlight_string($str, true);
        if ($added) {
            $highlight = str_replace(
                ['&lt;?php&nbsp;<br/>', '&lt;?php&nbsp;<br />'],
                '',
                $highlight
            );
        }

        return $highlight;
    }


    /**
     * Format an exception message to be HTML formatted.
     *
     * Does the following formatting operations:
     *
     * - HTML escape the message.
     * - Convert `bool` into `<code>bool</code>`
     * - Convert newlines into `<br />`
     *
     * @param string $message The string message to format.
     * @return string Formatted message.
     */
    public static function formatHtmlMessage(string $message): string
    {
        $message = e($message);
        $message = preg_replace('/`([^`]+)`/', '<code>$1</code>', $message);
        $message = nl2br($message);

        return $message;
    }
}
