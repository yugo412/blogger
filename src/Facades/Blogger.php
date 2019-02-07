<?php
/**
 * Created by PhpStorm.
 * User: yugo
 * Date: 07/02/19
 * Time: 22:26
 */

namespace Yugo\Blogger\Facades;


use Illuminate\Support\Facades\Facade;

class Blogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'blogger';
    }
}
