<?php

declare(strict_types=1);

namespace Chiron\Core;

use ArrayIterator;
use Chiron\Container\SingletonInterface;
use Chiron\Filesystem\Filesystem;
use Chiron\Core\Exception\PublishException;
use Countable;
use IteratorAggregate;
use Transversable;

final class Publisher implements SingletonInterface
{
    /**
     * The paths that should be published.
     * @var array
     */
    private $publishes = [];
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var bool
     */
    private $force; // TODO : renommer en $forceCopy

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    // TODO : renommer en addItem() ????
    public function add(string $source, string $destination)
    {
        $source = $this->filesystem->normalizePath($source);
        $destination = $this->filesystem->normalizePath($destination);

        $this->publishes[$source] = $destination;
    }

    // TODO : renommer en publishAll ou publishItems ???
    public function publish(bool $force): void
    {
        $this->force = $force;

        foreach ($this->publishes as $from => $to) {
            if ($this->filesystem->isDirectory($from)) {
                $this->publishDirectory($from, $to);
            } elseif ($this->filesystem->isFile($from)) {
                $this->publishFile($from, $to);
            } else {
                throw new PublishException(sprintf('Can\'t locate path: "%s".', $from));
            }
        }
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param string $from
     * @param string $to
     */
    public function publishDirectory(string $from, string $to): void
    {
        if (! $this->filesystem->exists($to) || $this->force) {
            $this->status($from, $to, 'Directory');
        }

        // TODO : ajouter un booléen à la méthode "->files()" pour savoir si le retour est un tableau d'object SPlFileInfo ou on son cast le retour en un tableau de string !!!!
        foreach ($this->filesystem->files($from) as $fileInfo) {
            // cast SplFileInfo object to string.
            $file = (string) $fileInfo;
            // copy file or folder.
            $this->publishFile($file, $to . '/' . $this->filesystem->basename($file));
        }
    }

    /**
     * Publish the file to the given path.
     *
     * @param string $from
     * @param string $to
     */
    public function publishFile(string $from, string $to): void
    {
        if (! $this->filesystem->exists($to) || $this->force) {
            $this->createParentDirectory(dirname($to));
            $this->filesystem->copy($from, $to);

            $this->status($from, $to, 'File');
        }
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param string $directory
     */
    private function createParentDirectory(string $directory): void
    {
        if (! $this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }
    }


    private function status(string $from, string $to, string $type): void
    {
        call_user_func_array($this->callback, [$from, $to, $type]);
    }
}
