<?php

namespace Collector\Test\Package;

use Collector\Package\PackageInterface;
use Collector\Test\TestCase;
use Collector\Package\Link;
use Composer\Semver\Constraint\ConstraintInterface;
use PHPUnit_Framework_MockObject_MockObject;

class LinkTest extends TestCase
{
    /** @type ConstraintInterface|PHPUnit_Framework_MockObject_MockObject */
    private $constraint;

    /** @type PackageInterface|PHPUnit_Framework_MockObject_MockObject */
    private $package;

    protected function setUp()
    {
        $this->constraint = $this
            ->getMockBuilder('Composer\Semver\Constraint\ConstraintInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->package = $this
            ->getMockBuilder('Collector\Package\PackageInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSuccess()
    {
        $sourcePackage = 'source\package';
        $targetPackage = 'target\package';
        $description = 'is modeled after';
        $prettyConstraint = 'with some constraint';
        $amazingPackage = 'amazing\package';

        $this->constraint->expects($this->once())
            ->method('getPrettyString')
            ->willReturn($prettyConstraint);

        $this->package->expects($this->once())
            ->method('getPrettyString')
            ->willReturn($amazingPackage);

        $link = new Link($sourcePackage, $targetPackage, $this->constraint, $description, $prettyConstraint);

        $this->assertEquals($sourcePackage, $link->getSource());
        $this->assertEquals($targetPackage, $link->getTarget());
        $this->assertEquals($description, $link->getDescription());
        $this->assertEquals($this->constraint, $link->getConstraint());
        $this->assertEquals($prettyConstraint, $link->getPrettyConstraint());
        $this->assertEquals(
            $amazingPackage . ' ' . $description . ' ' . $targetPackage . ' ' . $prettyConstraint,
            $link->getPrettyString($this->package)
        );
    }

    public function testGetPrettyConstraintFailsOnMissingPrettyConstraint()
    {
        $sourcePackage = 'source\package';
        $targetPackage = 'target\package';
        $description = 'is modeled after';
        $prettyConstraint = 'with some constraint';

        $this->setExpectedException(
            'UnexpectedValueException',
            'Link ' . $sourcePackage . ' ' . $description . ' ' . $targetPackage . ' () has been misconfigured and had no prettyConstraint given.'
        );

        $link = new Link($sourcePackage, $targetPackage, $this->constraint, $description);

        $this->assertEquals($prettyConstraint, $link->getPrettyConstraint());
    }
}
