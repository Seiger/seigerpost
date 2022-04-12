<?php
/**
 * News management module
 */

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE != 'true') die("No access");

require_once MODX_BASE_PATH . 'assets/modules/seigerpost/sPost.class.php';

$sPost = new sPost();
$data['editor'] = '';
$data['get'] = $_REQUEST['get'] ?? "posts";
$data['sPost'] = $sPost;
$data['lang_default'] = $sPost->langDefault();
$data['url'] = $sPost->url;

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
    case "postAdd":
        $data['tags'] = $tags ?? [];
        $data['editor'] = $sPost->textEditor("content,epilog");
        break;
    case "postSave":
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