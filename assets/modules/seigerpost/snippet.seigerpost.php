<?php

use EvolutionCMS\Facades\UrlProcessor;
use Illuminate\Support\Facades\View;
use sPost\Models\sPostTag;

require_once MODX_BASE_PATH . 'assets/modules/seigerpost/sPost.class.php';

$sPost = new sPost();

$result = '';
$posts = [];
$lang = evo()->getConfig('lang', 'uk');
$type = $type ?? 0;
$perPage = $perPage ?? 0;
$skip = $skip ?? 0;

if (isset($latest) && (int)$latest) {
    $posts = $sPost->latestPosts($lang, $type, (int)$latest);
}

if (isset($all) && (int)$all) {
    $posts = $sPost->frontPosts($lang, $type, $perPage, $skip);
}

if (isset($allTags) && (int)$allTags) {
    $posts = $sPost->frontTags($lang, (int)$allTags);
}

if (isset($tagPosts) && (int)$tagPosts) {
    $alias = evo()->documentObject['alias'];
    if (isset(UrlProcessor::getFacadeRoot()->documentListing[request()->path()])) {
        $tag = sPostTag::orderBy('alias')->first();
        evo()->sendRedirect(request()->getRequestUri() . $tag->alias . '/');
    }
    $posts = $sPost->tagPosts($alias, $lang);
}

if (isset($blade) && trim($blade)) {
    $result = View::make($blade, ['posts' => $posts]);
}

return $result;