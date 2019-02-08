<?php
/**
 * Created by PhpStorm.
 * User: yugo
 * Date: 07/02/19
 * Time: 22:27
 */

namespace Yugo\Blogger\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class BloggerApi
{
    /**
     * @var string
     */
    private $key = '';

    /**
     * @var \Illuminate\Config\Repository|mixed|string
     */
    private $url = '';

    /**
     * @var string
     */
    private $blogId = '';

    /**
     * @var Client
     */
    private $client;


    /**
     * @var array
     */
    private $headers = [];

    public function __construct()
    {
        $this->key = config('services.blogger.key');
        $this->url = config('services.blogger.url');

        if (empty($this->key)) {
            abort(500, __('API Key is not defined.'));
        }

        $this->client = new Client([
            'base_uri' => 'https://www.googleapis.com/blogger/v3/blogs/',
            'http_errors' => false,
        ]);

        $this->headers = [
            'Accept-Encoding' => 'gzip',
            'User-Agent' => 'Laravel Blogger (gzip)',
        ];
    }

    /**
     * @param string $path
     * @param array $query
     * @return object
     */
    private function getRequest(string $path, array $query = []): object
    {
        $defaultQuery = [
            'key' => $this->key,
        ];

        return $this->client->get($path, [
            'headers' => $this->headers,
            'query' => array_merge($defaultQuery, $query),
        ]);
    }

    /**
     * @param array $post
     * @return string
     */
    private function addPath(array $post): string
    {
        if (! empty($post['url'])) {
            $url = parse_url($post['url']);

            return $url['path'];
        }

        return '';
    }

    /**
     * @param string $blogId
     * @return BloggerApi
     */
    public function setBlog(string $blogId): self
    {
        $this->blogId = $blogId;

        return $this;
    }

    /**
     * @param string $url
     * @return array
     */
    public function blog(string $url = null): array
    {
        if (! empty($url) and !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(500, 'Invalid URL given.');
        }

        $response = $this->getRequest('byurl', [
            'key' => $this->key,
            'url' => $url ?? $this->url,
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (is_null($body)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            Log::error($body['error']['message'] ?? __('Blog not found.'), [
                'url' => $url ?? $this->url,
            ]);
        }

        return $body;
    }

    /**
     * @param string|null $blogId
     * @return array
     */
    public function posts(string $blogId = null): array
    {
        $response = $this->getRequest(($blogId ?? $this->blogId).'/posts');

        $body = json_decode((string) $response->getBody(), true);

        if (is_null($body)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            Log::error($body['error']['message'] ?? __('Posts not found.'), [
                'blog_id' => $blogId ?? $this->blogId,
            ]);
        }

        if (!empty($body['items'])) {
            $items = collect($body['items'])->map(function ($item){
                $item['path'] = $this->addPath($item);

                return $item;
            });

            $body['items'] = $items->toArray();
        }

        return $body;
    }

    /**
     * @param string $postId
     * @param string|null $blogId
     * @return mixed
     */
    public function postById(string $postId, string $blogId = null): array
    {
        $response = $this->getRequest(($blogId ?? $this->blogId).'/posts/'.$postId);

        $body = json_decode((string) $response->getBody(), true);

        if (is_null($body)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            Log::error($body['error']['message'] ?? __('Post not found.'), [
                'post_id' => $postId,
                'blog_id' => $blogId ?? $this->blogId,
            ]);
        }

        if (isset($body['blog'])) {
            $body['path'] = $this->addPath($body);
        }

        return $body;
    }

    /**
     * @param string $path
     * @param string|null $blogId
     * @return array
     */
    public function postByPath(string $path, string $blogId = null): array
    {
        $response = $this->getRequest(($blogId ?? $this->blogId).'/posts/bypath', [
            'path' => $path,
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (empty($body)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            Log::error($response['error']['message'] ?? __('Post not found.'), [
                'path' => $path,
                'blog_id' => $blogId ?? $this->blogId,
            ]);
        }

        if (isset($body['blog'])) {
            $body['path'] = $this->addPath($body);
        }

        return $body;
    }

    /**
     * @param string $keyword
     * @param string|null $blogId
     * @return array
     */
    public function search(string $keyword = '', string $blogId= null): array
    {
        $response = $this->getRequest(($blogId ?? $this->blogId).'/posts/search', [
            'q' => $keyword,
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (empty($body)) {
            return [];
        }

        if (! empty($body['items'])) {
            $items = collect($body['items'])->map(function ($item){
                $item['path'] = $this->addPath($item);

                return $item;
            });

            if (isset($body['items'])) {
                $body['items'] = $items->toArray();
            }
        }

        return $body;
    }

    /**
     * @param string $postId
     * @param string $blogId
     */
    public function comments(string $postId, string $blogId)
    {

    }

    /**
     * @param string $commentId
     * @param string $postId
     * @param string $blogId
     */
    public function comment(string $commentId, string $postId, string $blogId)
    {

    }

    /**
     * @param string|null $blogId
     * @return array
     */
    public function pages(string $blogId = null): array
    {
        $response = $this->getRequest(($blogId ?? $this->blogId).'/pages');

        $pages = json_decode((string) $response->getBody(), true);

        if (empty($pages)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            Log::error($pages['error']['message'] ?? __('Pages not found.'), [
                'blog_id' => $blogId ?? $this->blogId,
            ]);
        }

        if (! empty($pages['items'])) {
            $items = collect($pages['items'])->map(function ($item){
                $item['path'] = $this->addPath($item);

                return $item;
            });

            $pages['items'] = $items->toArray();
        }

        return $pages;
    }

    /**
     * @param string $pageId
     * @param string|null $blogId
     * @return array
     */
    public function page(string $pageId, string $blogId = null): array
    {
        $response = $this->getRequest(($blogId ?? $this->blogId).'/pages/'.$pageId);

        $page = json_decode((string) $response->getBody(), true);

        if (empty($page)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            Log::error($page['error']['message'] ?? __('Page not found.'), [
                'page_id' => $pageId,
                'blog_id' => $blogId ?? $this->blogId,
            ]);
        }

        if (! empty($page['blog'])) {
            $page['path'] = $this->addPath($page);
        }

        return $page;
    }
}
