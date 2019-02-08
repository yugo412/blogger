# Blogger API for Laravel
Unoffical Blogger (REST API) package for Laravel.

## Features

- Get blog information.
- Retrieve posts.
- Retrieve post by ID or path.
- Search posts. 

## Installation

Add Blogger package to your Laravel application by running command:

```
composer require yugo/blogger
```

## Configuration

If you using Laravel 5.5 and up, provider is automatically registered using auto package discovery from Laravel. If you get error to indicate provider not found, you can register new provider via `config/app.php` file.

```
/*
 * Package Service Providers...
 */
Yugo\Moota\BloggerServiceProvider::class,
```

You can set up alias for Blogger package from the same config file.

```
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

For security reason, you can define `blogger.key` config from `.env` file using sample below.

```
BLOGGER_KEY="secret-api-key"
BLOGGER_URL="https://yourblog.blogspot.com"
```

## Usage

Before retrieving posts, pages, and comments, you know your blog idd first. To get retrieve blog id data, you can use `blog` method from `Blogger` facade. For example:

```
$blog = Blogger::blog();
```



## Available Methods

```
$blog = Blogger::blog();

$posts = Blogger::posts($blog['id']);

$results = Blogger::search($keyword, $blogId);
$singlePost = Blogger::postById($postId, $blogId);
$singlePost = Blogger::postByPath($path, $blogId);
```

## Best Practice

```php
<?php

namespace App\Http\Controllers;

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

        $this->blogger = Blogger::setBlogId($blog['id']);
    }

    public function blog()
    {
        $blog = Blogger::blog();

        return $blog;
    }

    public function posts()
    {
        return $this->blogger->posts();
    }

    public function post(string $postId)
    {
        return $this->blogger->post($postId);
    }

    public function search(Request $request)
    {
        return $this->blogger->search($request->keyword);
    }
}
```

## License

MIT license.
