<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Nette\DI\Extensions;

use BrandEmbassy\Nette\DI\Extensions\AnnotationExtension;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\DoNotDiscoverInterface;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\NotToBeDiscovered;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\NotToBeDiscoveredTest;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\subDir\NotToBeDiscoveredFromSubDirTest;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\subDir\ToBeDiscoveredFromSubDir;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\subDir\ToBeDiscoveredFromSubDirWithInlineAnnotation;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\ToBeDiscovered;
use Iterator;
use Nette\DI\Compiler;
use Nette\DI\MissingServiceException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

/**
 * @final
 */
class AnnotationExtensionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testRegisteringDiscoveredServices(): void
    {
        $annotationExtension = $this->createAndSetupExtension();
        $builder = $annotationExtension->getContainerBuilder();

        $toBeDiscovered = $builder->getDefinitionByType(ToBeDiscovered::class);
        $toBeDiscoveredFromSubDir = $builder->getDefinitionByType(ToBeDiscoveredFromSubDir::class);
        $toBeDiscoveredFromSubDirWithInlineAnnotation = $builder->getDefinitionByType(ToBeDiscoveredFromSubDirWithInlineAnnotation::class);

        Assert::assertSame($toBeDiscovered->getType(), ToBeDiscovered::class);
        Assert::assertSame($toBeDiscoveredFromSubDir->getType(), ToBeDiscoveredFromSubDir::class);
        Assert::assertSame($toBeDiscoveredFromSubDirWithInlineAnnotation->getType(), ToBeDiscoveredFromSubDirWithInlineAnnotation::class);
    }


    /**
     * @dataProvider notDiscoverableClassesProvider
     *
     * @throws Throwable
     */
    public function testNotRegisteringClassWithoutDiscoveryAnnotation(string $type): void
    {
        $annotationExtension = $this->createAndSetupExtension();
        $builder = $annotationExtension->getContainerBuilder();

        $this->expectException(MissingServiceException::class);

        $builder->getDefinitionByType($type);
    }


    /**
     * @return Iterator<array{type: string}>
     */
    public function notDiscoverableClassesProvider(): Iterator
    {
        yield 'not discoverable class' => [
            'type' => NotToBeDiscovered::class,
        ];

        yield 'not discoverable interface' => [
            'type' => DoNotDiscoverInterface::class,
        ];
    }


    /**
     * @dataProvider excludedClassesProvider
     *
     * @throws Throwable
     */
    public function testNotRegisteringClassEndingOnTest(string $type): void
    {
        $setup = new stdClass();
        $setup->in = __DIR__ . '/fixtures';
        $setup->excludeClasses = ['/.*Test$/'];
        $annotationExtension = $this->createAndSetupExtension($setup);
        $builder = $annotationExtension->getContainerBuilder();

        $this->expectException(MissingServiceException::class);

        $builder->getDefinitionByType($type);
    }


    /**
     * @return Iterator<array{type: string}>
     */
    public function excludedClassesProvider(): Iterator
    {
        yield 'with test suffix' => [
            'type' => NotToBeDiscoveredTest::class,
        ];

        yield 'with test suffix from sub directory' => [
            'type' => NotToBeDiscoveredFromSubDirTest::class,
        ];
    }


    /**
     * @throws Throwable
     */
    private function createAndSetupExtension(?stdClass $setup = null): AnnotationExtension
    {
        if ($setup === null) {
            $setup = new stdClass();
            $setup->in = __DIR__ . '/fixtures';
            $setup->excludeClasses = [];
        }

        $annotationExtension = new AnnotationExtension(__DIR__ . '/../../temp');
        $annotationExtension->setConfig([
            'default' => $setup,
        ]);

        $annotationExtension->setCompiler(new Compiler(), 'discovery');
        $annotationExtension->loadConfiguration();
        $annotationExtension->beforeCompile();

        return $annotationExtension;
    }
}
