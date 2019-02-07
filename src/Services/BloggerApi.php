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

    private $url = '';

    private $blogId = '';

    private $client;

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

        $response = $this->client->get('byurl', [
            'query'=> [
                'key' => $this->key,
                'url' => $url ?? $this->url,
            ]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (is_null($body)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            if (! empty($body['error']['message'])) {
                Log::error($body['error']['message'], [
                    'url' => $url ?? $this->url,
                ]);
            }
        }

        return $body;
    }

    /**
     * @param string|null $blogId
     * @return array
     */
    public function posts(string $blogId = null): array
    {
        $response = $this->client->get(($blogId ?? $this->blogId).'/posts', [
            'query' => [
                'key' => $this->key,
            ]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (is_null($body)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            if (! empty($body['error']['message'])) {
                Log::error($body['error']['message'], [
                    'blog_id' => $blogId ?? $this->blogId,
                ]);
            }
        }

        $items = collect($body['items'])->map(function ($item){
            $item['blog']['path'] = $this->addPath($item);

            return $item;
        });

        $body['items'] = $items->toArray();

        return $body;
    }

    /**
     * @param string $postId
     * @param string|null $blogId
     * @return mixed
     */
    public function postById(string $postId, string $blogId = null): array
    {
        $response = $this->client->get(($blogId ?? $this->blogId).'/posts/'.$postId, [
            'query' => [
                'key' => $this->key,
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (is_null($body)) {
            return [];
        }

        if ($response->getStatusCode() != 200) {
            if (! empty($body['error']['message'])) {
                Log::error($body['error']['message'], [
                    'post_id' => $postId,
                    'blog_id' => $blogId ?? $this->blogId,
                ]);
            }
        }

        if (isset($body['blog'])) {
            $body['blog']['path'] = $this->addPath($body);
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
        $response = $this->client->get(($blogId ?? $this->blogId).'/posts/bypath', [
            'query' => [
                'key' => $this->key,
                'path' => $path
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (empty($body)) {
            return [];
        }

        if ($body['blog']) {
            $body['blog']['path'] = $this->addPath($body);
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
        $response = $this->client->get(($blogId ?? $this->blogId).'/posts/search', [
            'query' => [
                'key' => $this->key,
                'q' => $keyword,
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);

        if (empty($body)) {
            return [];
        }

        if (! empty($body['items'])) {
            $items = collect($body['items'])->map(function ($item){
                $item['blog']['path'] = $this->addPath($item);

                return $item;
            });

            if (isset($body['items'])) {
                $body['items'] = $items->toArray();
            }
        }

        return $body;
    }

    public function comments(string $postId, string $blogId)
    {

    }

    public function comment(string $commentId, string $postId, string $blogId)
    {

    }

    public function pages(string $blogId)
    {
        
    }

    public function page(string $pageId, string $blogId = null)
    {

    }
}
