<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Var_
 * @covers ::<private>
 */
class VarTest extends TestCase
{
    /**
     * Call Mockery::close after each test.
     */
    public function tearDown(): void
    {
        m::close();
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Var_::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned(): void
    {
        $fixture = new Var_('myVariable', null, new Description('Description'));

        $this->assertSame('var', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Var_::__construct
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfVariableNameIsOmittedIfEmpty(): void
    {
        $fixture = new Var_('', null, null);

        $this->assertSame('@var', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Var_::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Var_::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter(): void
    {
        $fixture = new Var_('myVariable', new String_(), new Description('Description'));
        $this->assertSame('@var string $myVariable Description', $fixture->render());

        $fixture = new Var_('myVariable', null, new Description('Description'));
        $this->assertSame('@var $myVariable Description', $fixture->render());

        $fixture = new Var_('myVariable');
        $this->assertSame('@var $myVariable', $fixture->render());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Var_::__construct
     *
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter(): void
    {
        $fixture = new Var_('myVariable');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getVariableName
     */
    public function testHasVariableName(): void
    {
        $expected = 'myVariable';

        $fixture = new Var_($expected);

        $this->assertSame($expected, $fixture->getVariableName());
    }

    /**
     * @covers ::__construct
     * @covers ::getType
     */
    public function testHasType(): void
    {
        $expected = new String_();

        $fixture = new Var_('myVariable', $expected);

        $this->assertSame($expected, $fixture->getType());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     *
     * @covers ::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getDescription
     */
    public function testHasDescription(): void
    {
        $expected = new Description('Description');

        $fixture = new Var_('1.0', null, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\Types\String_
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturned(): void
    {
        $fixture = new Var_('myVariable', new String_(), new Description('Description'));

        $this->assertSame('string $myVariable Description', (string) $fixture);
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\Types\String_
     *
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testStringRepresentationIsReturnedWithoutDescription(): void
    {
        $fixture = new Var_('myVariable');

        $this->assertSame('$myVariable', (string) $fixture);

        // ---

        $fixture = new Var_('myVariable', new String_());

        $this->assertSame('string $myVariable', (string) $fixture);

        // ---

        $fixture = new Var_('myVariable', new String_(), new Description(''));

        $this->assertSame('string $myVariable', (string) $fixture);
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Var_::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethod(): void
    {
        $typeResolver       = new TypeResolver();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context            = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Var_::create('string $myVariable My Description', $typeResolver, $descriptionFactory, $context);

        $this->assertSame('string $myVariable My Description', (string) $fixture);
        $this->assertSame('myVariable', $fixture->getVariableName());
        $this->assertInstanceOf(String_::class, $fixture->getType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Param::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithoutType(): void
    {
        $typeResolver       = new TypeResolver();
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $context            = new Context('');

        $fixture = Var_::create(
            '$myParameter My Description',
            $typeResolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('$myParameter My Description', (string) $fixture);
        $this->assertSame('myParameter', $fixture->getVariableName());
        $this->assertNull($fixture->getType());
        $this->assertSame('My Description', $fixture->getDescription() . '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Param::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithType(): void
    {
        $typeResolver       = new TypeResolver();
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $context            = new Context('');

        $fixture = Var_::create(
            'int My Description',
            $typeResolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('int My Description', (string) $fixture);
        $this->assertSame('', $fixture->getVariableName());
        $this->assertInstanceOf(Integer::class, $fixture->getType());
        $this->assertSame('My Description', $fixture->getDescription() . '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Param::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Types\Context
     *
     * @covers ::create
     */
    public function testFactoryMethodWithTypeWithoutComment(): void
    {
        $typeResolver       = new TypeResolver();
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $context            = new Context('');

        $fixture = Var_::create(
            'int',
            $typeResolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('int', (string) $fixture);
        $this->assertSame('', $fixture->getVariableName());
        $this->assertInstanceOf(Integer::class, $fixture->getType());
        $this->assertSame('', $fixture->getDescription() . '');
    }

    /**
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Var_::<public>
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     *
     * @covers ::create
     */
    public function testFactoryMethodFailsIfEmptyBodyIsGiven(): void
    {
        $this->expectException('InvalidArgumentException');
        $descriptionFactory = m::mock(DescriptionFactory::class);
        Var_::create('', new TypeResolver(), $descriptionFactory);
    }

    /**
     * @covers ::create
     */
    public function testFactoryMethodFailsIfResolverIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        Var_::create('body');
    }

    /**
     * @uses \phpDocumentor\Reflection\TypeResolver
     *
     * @covers ::create
     */
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull(): void
    {
        $this->expectException('InvalidArgumentException');
        Var_::create('body', new TypeResolver());
    }
}
