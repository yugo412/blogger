# Blogger API for Laravel
Unoffical Blogger (REST API) package for Laravel.

## Features

- Get blog information.
- Retrieve posts.
- Retrieve post by ID or path.
- Search posts. 
- Retrieve pages.
- Retrieve single page.

## Installation

Add Blogger package to your Laravel application by running command:

```
composer require yugo/blogger
```

## Configuration

If you are using Laravel 5.5 and upper, provider is automatically registered using auto package discovery from Laravel. If you get error to indicate provider not found, you can register new provider via `config/app.php` file.

```
/*
 * Package Service Providers...
 */
Yugo\Blogger\BloggerServiceProvider::class,
```

You can set up alias for Blogger package from the same config file.

```
// facade alias
'Blogger' => Yugo\Blogger\Facades\Blogger::class,
```

Before using `Blogger` facade, you must provide API key and full URL from your blog. API Key can be generated via [this url](https://developers.google.com/blogger/docs/3.0/using#APIKey).

Add two configs called `key` and `url` to file `config/services.php`.

```
'blogger' => [
    'key' => env('BLOGGER_KEY'),
    'url' => env('BLOGGER_URL', 'https://yourblog.blogspot.com'),
],
```

For security reason, you can define `blogger.key` and `blogger.url` config from `.env` file using sample below.

```
BLOGGER_KEY="secret-api-key"
BLOGGER_URL="https://yourblog.blogspot.com"
```

## Usage

Before retrieving posts, pages, and comments, you must know your blog id first. To retrieve blog id data, you can use `blog` method from `Blogger` facade. For example:

```
$blog = Blogger::blog();
```



## Available Methods

```php
$blog = Blogger::blog();


// retrieving posts
$posts = Blogger::posts($blog['id']);
$posts = Blogger::search($keyword, $blogId);
$post = Blogger::postById($postId, $blogId);
$post = Blogger::postByPath($path, $blogId);

// retrieving pages
$pages = Blogger::pages($blog['id']);
$page = Blogger::page($pageId, $blogId);
```

## Best Practice
When you retrieving posts or pages, you must define blog id in every method. If you only have one blog and static blog id, you can follow this sample code for best practice.
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Yugo\Blogger\Facades\Blogger;

class BloggerController extends Controller
{
    /**
     * @var Blogger
     */
    private $blogger;

    public function __construct()
    {
        $blog = Cache::remember('blogger.blog', now()->addDays(7), function (){
           return Blogger::blog();
        });

        abort_if(empty($blog), 404, __('Blog not found.'));

        $this->blogger = Blogger::setBlog($blog['id']);
    }

    /**
     * @return JsonResponse
     */
    public function blog(): JsonResponse
    {
        return response()->json($this->blogger->blog());
    }

    /**
     * @return JsonResponse
     */
    public function posts(): JsonResponse
    {
        return response()->json($this->blogger->posts());
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function post(string $id): JsonResponse
    {
        return response()->json($this->blogger->postById($id));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $this->validate($request, [
            'keyword' => ['required', 'string'],
        ]);

        return response()->json($this->blogger->search($request->keyword));
    }

    /**
     * @return JsonResponse
     */
    public function pages(): JsonResponse
    {
        return response()->json($this->blogger->pages());
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function page(string $id): JsonResponse
    {
        return response()->json($this->blogger->page($id));
    }
}
```

Next, you can define routes like this example in case you want access it directly via browser.

```php
Route::get('blogger/blog', 'BloggerController@blog');
Route::get('blogger/posts', 'BloggerController@posts');
Route::get('blogger/post/{id}', 'BloggerController@post');
Route::get('blogger/search', 'BloggerController@search');
Route::get('blogger/pages', 'BloggerController@pages');
Route::get('blogger/page/{id}', 'BloggerController@page');
```

## License

MIT license.
