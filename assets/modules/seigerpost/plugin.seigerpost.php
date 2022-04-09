<?php
/**
 * Plugin for Seiger Post Management Module for Evolution CMS admin panel.
 */

use EvolutionCMS\Facades\UrlProcessor;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Facades\Cache;
use sPost\Models\sPostContent;
use sPost\Models\sPostTag;

$e = evo()->event;
$sPost = new sPost();

/**
 * Track a post entry by a resource alias that is a representation of the post type on the front.
 *
 * Available post types sPostContent::listProxies() The resource alias must match the post type in the list.
 */
if ($e->name == 'OnPageNotFound') {
    $pathArr = explode('/', request()->path());
    $path = end($pathArr);
    if (trim($path)) {
        $postList = Cache::get('postList');
        if (isset($postList[$path])) {
            $postId = $postList[$path];
        }
        if (isset($postId) && $postId) {
            $postTypes = Cache::get('postTypes');
            $proxies = Cache::get('proxies');
            if (isset($proxies[$postTypes[$postId]]) && (($postTypes[$postId] == 0 && ($pathArr[0] == 'blog' || $pathArr[1] == 'blog')) || ($postTypes[$postId] == 1 && ($pathArr[0] == 'news' || $pathArr[1] == 'news')))) {
                $error_page = evo()->getConfig('error_page', 1);
                $systemCacheKey = evo()->systemCacheKey;
                evo()->systemCacheKey = str_replace(
                    $error_page . '_',
                    $proxies[$postTypes[$postId]] . '_' . $postId . '_',
                    $systemCacheKey
                );
                evo()->sendForward($proxies[$postTypes[$postId]]);
                exit();
            }
        }

        $tagList = Cache::get('tagList');
        if (isset($tagList[$path]) && in_array('tag', $pathArr)) {
            $tagId = $tagList[$path];
            if ($tagId) {
                array_pop($pathArr);
                $alias = implode('/', $pathArr);
                if (isset(UrlProcessor::getFacadeRoot()->documentListing[$alias])) {
                    $error_page = evo()->getConfig('error_page', 1);
                    $systemCacheKey = evo()->systemCacheKey;
                    evo()->systemCacheKey = str_replace(
                        $error_page . '_',
                        UrlProcessor::getFacadeRoot()->documentListing[$alias] . '_' . $tagId . '_',
                        $systemCacheKey
                    );
                    evo()->sendForward(UrlProcessor::getFacadeRoot()->documentListing[$alias]);
                    exit();
                }
            }
        }
    }
}

/**
 * Get post fields and add to resource fields array
 */
if ($e->name == 'OnAfterLoadDocumentObject') {
    $pathArr = explode('/', request()->path());
    $path = end($pathArr);
    if (trim($path)) {
        $postList = Cache::get('postList');
        if (isset($postList[$path])) {
            $postId = $postList[$path];
            $lang = evo()->getConfig('lang', 'base');
            $post = $sPost->getPostArray($postId, $lang);
            if ($post && (($post['type'] == 0 && ($pathArr[0] == 'blog' || $pathArr[1] == 'blog') || ($post['type'] == 1 && ($pathArr[0] == 'news' || $pathArr[1] == 'news'))))) {
                evo()->documentObject = array_merge($e->params['documentObject'], $post);
            }
        }

        $tagList = Cache::get('tagList');
        if (isset($tagList[$path]) && in_array('tag', $pathArr)) {
            $tagId = $tagList[$path];
            if ($tagId) {
                array_pop($pathArr);
                $alias = implode('/', $pathArr);
                if (isset(UrlProcessor::getFacadeRoot()->documentListing[$alias])) {
                    $lang = evo()->getConfig('lang', 'base');
                    $tag = sPostTag::find($tagId);
                    if ($tag) {
                        $tagArr = [];
                        foreach ($tag->toArray() as $field => $item) {
                            if (in_array($field, ['alias', $lang, $lang . '_content'])) {
                                switch ($field) {
                                    case $lang :
                                        $tagArr['pagetitle'] = $item;
                                        break;
                                    case $lang . '_content' :
                                        $tagArr['content'] = $item;
                                        break;
                                    default :
                                        $tagArr[$field] = $item;
                                        break;
                                }
                            }
                        }
                        evo()->documentObject = array_merge($e->params['documentObject'], $tagArr);
                    }
                }
            }
        }
    }
}

/**
 * Caching basic post data for fast lookups at the front
 *
 * The binding of post type and resource to represent post by type is cached [Post Type => Resource ID]
 * Binding Post Alias and Post ID [Post Alias => Post ID]
 * Binding Post Type and Post ID [Post ID => Post Type]
 */
if ($e->name == 'OnCacheUpdate') {
    $proxies = [];
    $postList = [];
    $postTypes = [];
    $tagList = [];

    $listTypes = sPostContent::listProxies();
    $docs = SiteContent::withoutTrashed()
        ->whereIn('alias', $listTypes)
        ->wherePublished(1)
        ->get();

    if ($docs) {
        $types = array_flip($listTypes);
        foreach ($docs as $doc) {
            $proxies[$types[$doc->alias]] = $doc->id;
        }
    }

    $posts = sPostContent::select('id', 'type', 'alias')->wherePublished(1)->get();
    if ($posts) {
        foreach ($posts as $post) {
            $postList[$post->alias] = $post->id;
            $postTypes[$post->id] = $post->type;
        }
    }

    $tags = sPostTag::select('id', 'alias')->get();
    if ($tags) {
        foreach ($tags as $tag) {
            $tagList[$tag->alias] = $tag->id;
        }
    }

    Cache::forever('proxies', $proxies);
    Cache::forever('postList', $postList);
    Cache::forever('postTypes', $postTypes);
    Cache::forever('tagList', $tagList);
}
