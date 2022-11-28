<?php declare(strict_types = 1);

namespace BrandEmbassy\Nette\DI\Extensions;

use Nette\DI\CompilerExtension;
use Nette\DI\InvalidConfigurationException;
use Nette\InvalidStateException;
use Nette\Loaders\RobotLoader;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ReflectionClass;
use ReflectionException;
use stdClass;
use function array_filter;
use function array_keys;
use function array_unique;
use function class_exists;
use function interface_exists;
use function is_dir;
use function is_string;
use function preg_match;
use function sprintf;
use function trait_exists;

/**
 * @final
 */
class AnnotationExtension extends CompilerExtension
{
    public const ANNOTATION_NAME = 'discovery';

    /** @var array<class-string> */
    private array $classes = [];

    private string $tempDir;


    public function __construct(
        string $tempDir
    ) {
        $this->tempDir = $tempDir . '/cache/nette.discovery';
    }


    public function getConfigSchema(): Schema
    {
        return Expect::arrayOf(
            Expect::structure([
                'in' => Expect::string()->required(),
                'files' => Expect::anyOf(Expect::listOf('string'), Expect::string()->castTo('array'))->default([]),
            ]),
        )->before(
            fn($val) => is_string($val['in'] ?? null)
                ? ['default' => $val]
                : $val,
        );
    }


    /**
     * @throws ReflectionException
     */
    public function loadConfiguration(): void
    {
        foreach (array_filter((array)$this->config) as $name => $batch) {
            if (!is_dir($batch->in)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        'Option "%s › %s › in" must be valid directory name, "%s" given.',
                        $this->name,
                        $name,
                        $batch->in,
                    ),
                );
            }

            $this->findClasses($batch);
        }
    }


    /**
     * @throws ReflectionException
     */
    public function findClasses(stdClass $config): void
    {
        $robot = new RobotLoader();
        $robot->setTempDirectory($this->tempDir);
        $robot->addDirectory($config->in);
        $robot->acceptFiles = $config->files ?? ['*.php'];
        $robot->reportParseErrors(false);
        $robot->refresh();
        $classes = array_unique(array_keys($robot->getIndexedClasses()));

        foreach ($classes as $class) {
            if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
                throw new InvalidStateException(
                    sprintf('Class %s was found, but it cannot be loaded by autoloading.', $class),
                );
            }

            $reflectionClass = new ReflectionClass($class);

            if ($reflectionClass->isInstantiable()
                && $reflectionClass->getDocComment() !== false
                && preg_match('#@' . self::ANNOTATION_NAME . '[\n\s]#s', $reflectionClass->getDocComment()) === 1
            ) {
                $this->classes[] = $class;
            }
        }
    }


    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->classes as $class) {
            if ($builder->findByType($class) !== []) {
                unset($this->classes[$class]);
            }
        }

        foreach ($this->classes as $class) {
            class_exists($class)
                ? $builder->addDefinition(null)->setType($class)
                : $builder->addFactoryDefinition(null)->setImplement($class);
        }
    }
}
