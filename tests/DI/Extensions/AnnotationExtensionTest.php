<?php declare(strict_types = 1);

namespace BrandEmbassy\Nette\DI\Extensions;

use BrandEmbassyTest\Nette\DI\Extensions\fixtures\DoNotDiscoverInterface;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\NotToBeDiscovered;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\subDir\ToBeDiscoveredFromSubDir;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\subDir\ToBeDiscoveredFromSubDirWithInlineAnnotation;
use BrandEmbassyTest\Nette\DI\Extensions\fixtures\ToBeDiscovered;
use Nette\DI\Compiler;
use Nette\DI\MissingServiceException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

final class AnnotationExtensionTest extends TestCase
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
     * @throws Throwable
     */
    public function testNotRegisteringClassWithoutDiscoveryAnnotation(): void
    {
        $annotationExtension = $this->createAndSetupExtension();
        $builder = $annotationExtension->getContainerBuilder();

        $this->expectException(MissingServiceException::class);

        $builder->getDefinitionByType(NotToBeDiscovered::class);
    }


    /**
     * @throws Throwable
     */
    public function testNotRegisteringInterfacesEvenWithDiscoveryAnnotation(): void
    {
        $annotationExtension = $this->createAndSetupExtension();
        $builder = $annotationExtension->getContainerBuilder();

        $this->expectException(MissingServiceException::class);

        $builder->getDefinitionByType(DoNotDiscoverInterface::class);
    }


    /**
     * @throws Throwable
     */
    private function createAndSetupExtension(): AnnotationExtension
    {
        $extensionSetup = new stdClass();
        $extensionSetup->in = __DIR__ . '/fixtures';

        $annotationExtension = new AnnotationExtension(__DIR__ . '/../../temp');
        $annotationExtension->setConfig([
            'in' => $extensionSetup
        ]);

        $annotationExtension->setCompiler(new Compiler(), 'discovery');
        $annotationExtension->loadConfiguration();
        $annotationExtension->beforeCompile();

        return $annotationExtension;
    }
}
