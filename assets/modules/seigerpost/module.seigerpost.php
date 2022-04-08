<?php
/**
 *	News management module
 */

use sAuthors\Model\sAuthors;
use PageNavigation\Model\PageNavigation;

if(!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') die("No access");

require_once MODX_BASE_PATH . 'assets/modules/seigerpost/sPost.class.php';
require_once MODX_BASE_PATH . 'assets/modules/sAuthors/src/sAuthors.class.php';
require_once MODX_BASE_PATH . 'assets/tvs/PageNav/src/PageNavigation.php';

$sPost  = new sPost();
$pageNav = new PageNavigation();
$data['editor'] = '';
$data['get'] = $_REQUEST['get'] ?? "posts";
$data['sPost'] = $sPost;
$data['lang_default'] = evolutionCMS()->getConfig('s_lang_default');
$data['url'] = $sPost->url;
$data['tvId'] = 3;
$data['tvId_epilog'] = 4;

$sAuthors = new sAuthors();
$data['authors'] = $sAuthors->authors();

switch ($data['get']) {
    default:
        break;
    case "post":
        $texts = [];
        $tags = [];
        $post = $sPost->getPost(request()->i);
        $pTexts = $post->texts->toArray();
        foreach ($pTexts as $pText) {
            $texts[$pText['lang']] = $pText;
        }
        $pTags = $post->tags->toArray();
        foreach ($pTags as $pTag) {
            $tags[] = $pTag['id'];
        }

        $data['post'] = $post;
        $data['texts'] = $texts;
        $data['heading'] = [];
        $data['heading'] += $pageNav->getHeadersFromContent($texts['en']['content']);
        array_unshift($data['heading'],$texts['en']['pagetitle']);
        $data['heading_epilog'] = $pageNav->getHeadersFromContent($texts['en']['epilog']);
        $data['heading_epilog'][] = 'Latest News';
        $data['selected'] = $pageNav->getNavigation($data['tvId'],$post['post']);
        $data['selectEpilog'] = $pageNav->getNavigation($data['tvId_epilog'],$post['post']);
    case "postAdd":
        $data['tags'] = $tags ?? [];
        $data['editor'] = $sPost->textEditor("content,epilog");
        break;
    case "postSave":
        $pageNav->setModuleNav(request(), $data['tvId']);
        $pageNav->setModuleNav(request(), $data['tvId_epilog']);
        $sPost->save(request());
        $data['tags'] = $tags ?? [];
        break;
    case "postDelete":
        $sPost->delete(request()->i);
        break;
    case "tags":
        $data['editor'] = $sPost->textEditor("tagContent");
        break;
    case "tagTexts":
        $result = $sPost->getTagTexts($_POST['tagId'], $_POST['lang']);
        die(json_encode($result));
    case "tagSetTexts":
        $result = $sPost->setTagTexts($_POST['tagId'], $_POST['lang'], $_POST['texts']);
        die($result);
    case "translate":
        $result = $sPost->getAutomaticTranslateTag($_POST['source'], $_POST['target']);
        die($result);
    case "update":
        $result = $sPost->updateTranslateTag($_POST['source'], $_POST['target'], $_POST['value']);
        die($result);
    case "addTag":
        $sPost->addTag(request()->get('value'));
        die(json_encode(['status' => 1]));
}

$sPost->view('index', $data);