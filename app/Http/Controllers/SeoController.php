<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Contracts\TagRepository;
use App\Repositories\Contracts\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Mcamara\LaravelLocalization\LaravelLocalization;

class SeoController extends Controller
{
    /**
     * @var LaravelLocalization
     */
    protected $localization;

    /**
     * @var \App\Repositories\Contracts\PostRepository
     */
    protected $posts;

    /**
     * @var \App\Repositories\Contracts\TagRepository
     */
    protected $tags;

    /**
     * @var \App\Repositories\Contracts\UserRepository
     */
    protected $users;

    /**
     * SeoController constructor.
     *
     * @param LaravelLocalization                        $localization
     * @param \App\Repositories\Contracts\UserRepository $users
     * @param \App\Repositories\Contracts\PostRepository $posts
     * @param \App\Repositories\Contracts\TagRepository  $tags
     */
    public function __construct(LaravelLocalization $localization, UserRepository $users, PostRepository $posts, TagRepository $tags)
    {
        $this->localization = $localization;
        $this->users = $users;
        $this->posts = $posts;
        $this->tags = $tags;
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow:',
            'Sitemap: '.url('/').'/sitemap.xml',
        ];

        return Response::make(implode(PHP_EOL, $lines), 200,
            ['Content-Type' => 'text/plain']);
    }

    public function sitemap()
    {
        $sitemap = app('sitemap');

        $sitemap->addItem([
            'loc' => route('home'),
            'lastmod' => Carbon::now(),
            'priority' => '1.0',
            'freq' => 'daily',
        ]);

        $sitemap->addItem([
            'loc' => route('about'),
            'lastmod' => Carbon::now(),
            'priority' => '1.0',
            'freq' => 'daily',
            'translations' => $this->getTranslations('about'),
        ]);

        $sitemap->addItem([
            'loc' => route('contact'),
            'lastmod' => Carbon::now(),
            'priority' => '1.0',
            'freq' => 'daily',
            'translations' => $this->getTranslations('contact'),
        ]);

        $sitemap->addItem([
            'loc' => route('legal-mentions'),
            'lastmod' => Carbon::now(),
            'priority' => '1.0',
            'freq' => 'daily',
            'translations' => $this->getTranslations('legal-mentions'),
        ]);

        $sitemap->addItem([
            'loc' => route('blog.index'),
            'lastmod' => Carbon::now(),
            'priority' => '1.0',
            'freq' => 'daily',
            'translations' => $this->getTranslations('legal-mentions'),
        ]);

        $publishedPosts = $this->posts->published();

        $publishedPosts->each(function (Post $post) use ($sitemap) {
            $item = [
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->published_at,
                'priority' => '1.0',
                'freq' => 'daily',
            ];

            foreach ($this->getLocalesWithoutDefault() as $localeCode => $properties) {
                $item['translations'][] = [
                    'language' => $localeCode,
                    'url' => url("$localeCode/".route('blog.show', $post->translate($localeCode)->slug)),
                ];
            }

            $sitemap->addItem($item);
        });

        $tags = $this->tags->query()->get();

        $tags->each(function (Tag $tag) use ($sitemap) {
            $sitemap->addItem([
                'loc' => route('blog.tag', $tag->slug),
                'lastmod' => Carbon::now(),
                'priority' => '1.0',
                'freq' => 'daily',
                'language' => $tag->locale,
            ]);
        });

        $users = $this->users->query()->get();

        $users->each(function (User $user) use ($sitemap) {
            $sitemap->addItem([
                'loc' => route('blog.owner', $user->slug),
                'lastmod' => $user->created_at,
                'priority' => '1.0',
                'freq' => 'daily',
            ]);
        });

        return $sitemap->render();
    }

    private function getLocalesWithoutDefault()
    {
        $defaultLocale = $this->localization->getDefaultLocale();

        return array_filter($this->localization->getSupportedLocales(), function ($localCode) use ($defaultLocale) {
            return $localCode !== $defaultLocale;
        }, ARRAY_FILTER_USE_KEY);
    }

    private function getTranslations($routeName)
    {
        $translations = [];

        foreach ($this->getLocalesWithoutDefault() as $localeCode => $properties) {
            $translations[] = [
                'language' => $localeCode,
                'url' => url("$localeCode/".trans("routes.$routeName", [], $localeCode)),
            ];
        }

        return $translations;
    }
}
