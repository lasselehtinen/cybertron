<?php

namespace lasselehtinen\Cybertron\Tests;

use Artisan;
use Illuminate\Filesystem\Filesystem;
use lasselehtinen\Cybertron\Commands\TransformerMakeCommand;
use lasselehtinen\Cybertron\Tests\TestModel;
use Orchestra\Testbench\TestCase;

class PackageTest extends TestCase
{
    /**
     * Command instance
     * @var lasselehtinen\Cybertron\Commands\TransformerMakeCommand
     */
    protected $command;

    /**
     * Model used for testing
     * @var lasselehtinen\Cybertron\Tests\TestModel
     */
    protected $model;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->command = new TransformerMakeCommand(new Filesystem);
        $this->model = new TestModel;

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/migrations'),
        ]);
    }

    /**
     * Load the packages service provider
     * @param  Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['lasselehtinen\Cybertron\CybertronServiceProvider'];
    }

    /**
     * Test that the command is initiliazed correctly
     * @return void
     */
    public function testCommandIsInitiliazed()
    {
        $this->assertInstanceOf('lasselehtinen\Cybertron\Commands\TransformerMakeCommand', $this->command);
    }

    /**
     * Test that the model used for testing is initiliazed correctly
     * @return void
     */
    public function testModelIsInitiliazed()
    {
        $this->assertInstanceOf('lasselehtinen\Cybertron\Tests\TestModel', $this->model);
    }

    /**
     * Tests that the relationships are parsed correctly from the model
     * @return void
     */
    public function testRelationshipAreParsedCorrectly()
    {
        // Get relationships from the model
        $relationships = $this->command->getRelationships($this->model);

        $expectedResult = [
            'method' => 'hasManySomethings',
            'relationType' => 'hasMany',
            'relationCountType' => 'collection',
            'relatedClass' => 'SomeOtherModel',
        ];

        $this->assertEquals($expectedResult, $relationships->where('method', 'hasManySomethings')->first());
    }

    /**
     * Test that the column types are parsed correctly
     * @return void
     */
    public function testGettingColumnTypes()
    {
        $expectedResult = [
            0 => [
                'name' => 'id_field',
                'type' => 'integer',
                'cast' => true,
            ],
            1 => [
                'name' => 'boolean_field',
                'type' => 'boolean',
                'cast' => true,
            ],
            2 => [
                'name' => 'string_field',
                'type' => 'string',
                'cast' => false,
            ],
        ];

        $this->assertEquals($expectedResult, $this->command->getColumnTypes($this->model)->toArray());
    }

    /**
     * Tests parsing the relationship method contents
     * @return void
     */
    public function testGettingMethodContents()
    {
        $methodContents = $this->command->getMethodContents($this->model, 'hasManySomethings');

        $this->assertEquals('public function hasManySomethings(){return $this->hasMany(SomeOtherModel::class);}', $methodContents);
    }

    /**
     * Test parsing the class name that the relationship is referring to
     * @return void
     */
    public function testGettingRelationshipsClassName()
    {
        $className = $this->command->getRelationshipClassName($this->model, 'hasManySomethings');

        $this->assertEquals('SomeOtherModel', $className);
    }

    /**
     * Tests that running the command generates the expected output
     * @return void
     */
    public function testGeneratedClassIsCorrect()
    {
        // Generate path for the generated Transformer
        $filePath = __DIR__ . '/../vendor/orchestra/testbench/fixture/app/TestTransformer.php';

        // Delete previous one if exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Use Artisan to generate new Transformer
        Artisan::call('make:transformer', [
            'name' => 'TestTransformer', '--model' => '\\lasselehtinen\\Cybertron\\Tests\\TestModel',
        ]);

        // Check that the generated Transformer exists
        $this->assertFileExists($filePath);

        // Check that the contents matches the expected result
        $this->assertEquals(md5_file(__DIR__ . '/GeneratedTransformer.php'), md5_file($filePath));
    }
}
