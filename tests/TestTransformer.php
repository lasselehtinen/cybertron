<?php

namespace App;

use League\Fractal;
use \lasselehtinen\Cybertron\Tests\TestModel;

class TestTransformer extends Fractal\TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var  array
     */
    protected $defaultIncludes = [
        'hasManySomethings',
    ];

    /**
     * Transform TestModel
     *
     * @param    TestModel $testModel
     * @return  array
     */
    public function transform(TestModel $testModel)
    {
        return [
            'id_field' => (integer) $testModel->id_field,
            'boolean_field' => (boolean) $testModel->boolean_field,
            'string_field' => $testModel->string_field,
        ];
    }

    /**
     * Include HasManySomethings
     *
     * @param  TestModel $testModel
     * @return  \League\Fractal\Resource\Collection
     */
    public function includeHasManySomethings(TestModel $testModel)
    {
        return $this->collection($testModel->hasManySomethings, new SomeOtherModelTransformer);
    }

}
