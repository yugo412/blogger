<?php
/**
 * Created by PhpStorm.
 * User: yugo
 * Date: 07/02/19
 * Time: 22:24
 */

namespace Yugo\Blogger;


use Illuminate\Support\ServiceProvider;
use Yugo\Blogger\Services\BloggerApi;

class BloggerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

    }


    public function register(): void
    {
        $this->app->bind('blogger', function ($config){
            return new BloggerApi;
        });
    }
}
