<?php

namespace lasselehtinen\Cybertron\Tests;

use Illuminate\Database\Eloquent\Model;
use lasselehtinen\Cybertron\Tests\SomeOtherModel;

class TestModel extends Model
{
    /**
     * Example hasMany relationship
     */
    public function hasManySomethings()
    {
        return $this->hasMany(SomeOtherModel::class);
    }
}
