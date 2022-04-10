<?php
/**
 * Class SeigerPost - Seiger Post Management Module for Evolution CMS admin panel.
 */

require_once MODX_BASE_PATH . 'assets/modules/seigerpost/models/sPostContent.php';
require_once MODX_BASE_PATH . 'assets/modules/seigerpost/models/sPostTranslate.php';
require_once MODX_BASE_PATH . 'assets/modules/seigerpost/models/sPostTag.php';

use Carbon\Carbon;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SiteModule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use sPost\Models\sPostContent;
use sPost\Models\sPostTag;
use sPost\Models\sPostTranslate;

if (!class_exists('sPost')) {
    class sPost
    {
        public $evo;
        public $url;
        public $perPage = 30;
        protected $basePath = MODX_BASE_PATH . 'assets/modules/seigerpost/';

        public function __construct()
        {
            $this->url = $this->moduleUrl();
            Paginator::defaultView('pagination');
        }

        /**
         * List posts with default language
         *
         * @return array
         */
        public function posts(): array
        {
            $posts = sPostContent::lang($this->langDefault())->orderByDesc('s_post_contents.updated_at')->get()->toArray();

            return $posts ?? [];
        }

        /**
         * List of posts for the frontend
         *
         * @param $lang
         * @param $type
         * @param $perPage
         * @param $skip
         * @return array
         */
        public function frontPosts($lang, $type = 0, $perPage = 0, $skip = 0): object
        {
            $posts = (object)[];
            $skipped = [0];
            $type = (int)$type;

            if (!$perPage) {
                $perPage = $this->perPage;
            }

            if ($skip) {
                $skipped = sPostContent::lang($lang)
                    ->whereType($type)
                    ->wherePublished(1)
                    ->orderByDesc('s_post_contents.pub_date')
                    ->limit($skip)
                    ->get('s_post_contents.id')
                    ->pluck('id')
                    ->toArray();
            }

            $posts = sPostContent::lang($lang)
                ->whereType($type)
                ->wherePublished(1)
                ->whereNotIn('post', $skipped)
                ->orderByDesc('s_post_contents.pub_date')
                ->paginate($perPage);

            return $posts;
        }

        /**
         * List recent entries
         *
         * @param $lang
         * @param $type
         * @param $limit
         * @return array
         */
        public function latestPosts($lang, $type = 0, $limit = 0): array
        {
            $posts = [];
            $type = (int)$type;

            if (!$limit) {
                $limit = $this->perPage;
            }

            $array = sPostContent::lang($lang)->whereType($type)->wherePublished(1)->orderByDesc('s_post_contents.pub_date')->limit($limit + 1)->get();

            if ($array) {
                foreach ($array as $item) {
                    $posts[] = $this->extendAttributes($item)->toArray();
                }
            }

            return $posts;
        }

        /**
         * @param string $lang
         * @param array $ids
         * @return array
         */
        public function onlyPosts(string $lang, array $ids): array
        {
            $posts = [];
            $ids = array_filter($ids, 'intval');
            $array = sPostContent::lang($lang)->whereIn('post', $ids)->wherePublished(1)->orderByDesc('s_post_contents.pub_date')->get();

            if ($array) {
                foreach ($array as $item) {
                    $posts[] = $this->extendAttributes($item)->toArray();
                }
            }

            return $posts;
        }

        /**
         * Get post object with translation
         *
         * @param $postId
         * @param string $lang
         * @return mixed
         */
        public function getPost($postId, $lang = '')
        {
            if (!trim($lang)) {
                $lang = $this->langDefault();
            }

            $post = sPostContent::lang($lang)->wherePost($postId)->first();

            return $post;
        }

        /**
         * Get post as an array
         *
         * @param $postId
         * @param $lang
         * @return array
         */
        public function getPostArray($postId, $lang = '')
        {
            $post = $this->getPost($postId, $lang);

            if ($post) {
                $post = $this->extendAttributes($post);
            }

            return $post->toArray() ?? [];
        }

        /**
         * @param $post
         * @return mixed
         */
        public function extendAttributes($post)
        {
            $post->authorName = $post->authorName;
            $post->authorLink = $post->authorLink;
            $post->pubFormat = $post->pubFormat;
            $post->agoFormat = $post->agoFormat;
            $post->link = $post->link;

            return $post;
        }

        /**
         * @param $lang
         * @param $postId
         * @return array
         */
        public function frontTags($lang, $postId)
        {
            $tags = [];
            $post = $this->getPost($postId, $lang);
            foreach ($post->tags as $tag) {
                $tags[$tag->alias] = $tag->{$lang};
            }

            return $tags;
        }

        /**
         * Delete post and its translations by ID
         *
         * @param $postId
         * @return void
         */
        public function delete($postId)
        {
            $post = sPostContent::whereId($postId)->first();
            if ($post) {
                $texts = sPostTranslate::wherePost($postId)->get();
                foreach ($texts as $text) {
                    $text->delete();
                }
                $post->delete();
            }

            return header('Location: ' . $this->moduleUrl());
        }

        /**
         * Save post
         *
         * @param Request $request
         * @return void
         */
        public function save(Request $request)
        {
            global $_lang;

            $pub_date = $this->validateDate($request->pub_date);
            $alias = $this->validateAlias($request);

            $post = false;
            if ((int)$request->post) {
                $post = sPostContent::find($request->post);
            }
            if (!$post) {
                $post = new sPostContent();
            }

            $post->published = (int)$request->published;
            $post->pub_date = $pub_date;
            $post->alias = $alias;
            $post->cover = evo()->db->escape($request->cover);
            $post->type = (int)$request->type;
            $post->author = (int)$request->author;
            $post->save();

            foreach ($this->langTabs() as $lang => $label) {
                if ($request->has($lang)) {
                    $this->setContent($post->id, $lang, $request->input($lang));
                }
            }

            if ($request->has('tags')) {
                $post->post = $post->id;
                $post->tags()->sync($request->get('tags'));
            }

            return header('Location: ' . $this->moduleUrl() . '&get=post&i=' . $post->id);
        }

        /**
         * Default language
         *
         * @return string
         */
        public function langDefault(): string
        {
            return evo()->getConfig('s_lang_default', 'base');
        }

        /**
         * Content Tabs
         *
         * @return array
         */
        public function langTabs(): array
        {
            global $_lang;
            $tabs = [];
            $s_lang = evo()->getConfig('s_lang_config', '');

            if (trim($s_lang)) {
                $s_lang = explode(',', $s_lang);
                foreach ($s_lang as $item) {
                    $tabs[$item] = $_lang['spost_texts'] . ' ' . $item;
                }
            } else {
                $tabs['base'] = $_lang['spost_texts'];
            }

            return $tabs;
        }

        /**
         * Saving texts
         *
         * @param int $postId
         * @param string $lang
         * @param array $fields
         * @return void
         */
        public function setContent(int $postId, string $lang, array $fields): void
        {
            sPostTranslate::updateOrCreate(['post' => $postId, 'lang' => $lang], $fields);
        }

        /**
         * Tag table field synchronization
         *
         * @return void
         */
        public function checkTagsTable(): void
        {
            $tagsColumns = sPostTag::getTableColumns();

            $s_lang = evo()->getConfig('s_lang_config', 'base');
            if (trim($s_lang)) {
                $s_lang = explode(',', $s_lang);
            }

            foreach ($s_lang as $item) {
                if (!in_array($item . '_content', $s_lang)) {
                    $s_lang[] = $item . '_content';
                }
            }

            $missing = array_diff($s_lang, $tagsColumns);

            if (count($missing)) {
                foreach ($missing as $item) {
                    $columnType = 'string';
                    if (substr_count($item, 'content')) {
                        $columnType = 'text';
                    }
                    Schema::table(sPostTag::getTableName(), function (Blueprint $table) use ($columnType, $item) {
                        $table->$columnType($item);
                    });
                }
            }
        }

        /**
         * Tag List
         *
         * @return mixed
         */
        public function listTags()
        {
            $tags = sPostTag::orderBy($this->langDefault())->paginate(15);
            $tags->withPath($this->url);

            return $tags;
        }

        /**
         * Add tag
         *
         * @param $value
         * @return void
         */
        public function addTag($value)
        {
            if (!empty($value) && $value = trim($value)) {
                $alias = Str::slug($value);
                $tag = sPostTag::whereAlias($alias)->first();
                if (!$tag) {
                    $tag = new sPostTag();
                    $tag->alias = $alias;
                    $tag->{$this->langDefault()} = $value;
                    $tag->save();
                } else {
                    $tag->{$this->langDefault()} = $value;
                    $tag->update();
                }
            }
        }

        /**
         * Single tag posts
         *
         * @param $alias
         * @param $lang
         * @return array
         */
        public function tagPosts($alias, $lang)
        {
            $posts = [];

            $tag = sPostTag::whereAlias($alias)->first();
            if ($tag->posts) {
                $postIds = [];
                foreach ($tag->posts as $post) {
                    $postIds[] = $post->id;
                }
            }

            $array = sPostContent::lang($lang)->whereIn('post', $postIds)->wherePublished(1)->orderByDesc('s_post_contents.pub_date')->get();

            if ($array) {
                foreach ($array as $item) {
                    $posts[] = $this->extendAttributes($item)->toArray();
                }
            }

            return $posts;
        }

        /**
         * Image resizing
         *
         * @param $image
         * @param $width
         * @param $height
         * @param $cacheFolder
         * @return array|string
         */
        public function imgResize($image, $width, $height) {
            $resize = evo()->runSnippet('phpthumb', ['input' => $image, 'options' => 'w='.$width.',h='.$height.',zc=1']);
            return empty($resize) ? $image : $resize;
        }

        /**
         * Display render
         *
         * @param $tpl
         * @param array $data
         * @return bool
         */
        public function view($tpl, array $data = [])
        {
            global $_lang;
            if (is_file($this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php')) {
                require_once $this->basePath . 'lang/' . evo()->getConfig('manager_language', 'uk') . '.php';
            }
            if (is_file(MODX_BASE_PATH . 'assets/modules/seigerlang/lang/' . evo()->getConfig('manager_language', 'uk') . '.php')) {
                require_once MODX_BASE_PATH . 'assets/modules/seigerlang/lang/' . evo()->getConfig('manager_language', 'uk') . '.php';
            }

            $data = array_merge($data, ['modx' => evo(), 'data' => $data, '_lang' => $_lang]);

            View::getFinder()->setPaths([
                $this->basePath . 'views',
                MODX_MANAGER_PATH . 'views'
            ]);
            echo View::make($tpl, $data);
            return true;
        }

        /**
         * Date validation
         *
         * @param string $date
         * @return string
         */
        protected function validateDate(string $date): string
        {
            if ((int)$date) {
                $date = Carbon::parse($date)->startOfMinute()->toDateTimeString();
            } else {
                $date = Carbon::now()->startOfMinute()->toDateTimeString();
            }

            return $date;
        }

        /**
         * Alias validation
         *
         * @param $request
         * @return string
         */
        protected function validateAlias($request)
        {
            if (trim($request->alias)) {
                $alias = Str::slug($request->alias, '-');
            } elseif ($request->has('base.pagetitle') && trim($request->input('base.pagetitle'))) {
                $alias = Str::slug($request->input('base.pagetitle'), '-');
            } elseif ($request->has('en.pagetitle') && trim($request->input('en.pagetitle'))) {
                $alias = Str::slug($request->input('en.pagetitle'), '-');
            } else {
                $s_langDefault = evo()->getConfig('s_lang_default', 'uk');
                $alias = Str::slug($request->input($s_langDefault . '.pagetitle'), '-');
            }

            $siteContent = SiteContent::withTrashed()->get('alias')->pluck('alias')->toArray();
            $postContent = sPostContent::where('id', '<>', (int)$request->post)->get('alias')->pluck('alias')->toArray();
            $aliases = array_merge($siteContent, $postContent);

            if (in_array($alias, $aliases)) {
                $cnt = 1;
                $tempAlias = $alias;
                while (in_array($tempAlias, $aliases)) {
                    $tempAlias = $alias . $cnt;
                    $cnt++;
                }
                $alias = $tempAlias;
            }
            return $alias;
        }

        /**
         * Get automatic translation
         *
         * @param $source
         * @param $target
         * @return string
         */
        public function getAutomaticTranslateTag($source, $target): string
        {
            $result = '';
            $langDefault = $this->langDefault();
            $tag = sPostTag::find($source);

            if ($tag) {
                $text = $tag[$langDefault];
                $result = $this->googleTranslate($text, $langDefault, $target);
            }

            if (trim($result)) {
                $tag->{$target} = $result;
                $tag->save();
            }

            return $result;
        }

        /**
         * Update translation field
         *
         * @param $source
         * @param $target
         * @param $value
         * @return bool
         */
        public function updateTranslateTag($source, $target, $value): bool
        {
            $result = false;
            $tag = sPostTag::find($source);

            if ($tag) {
                $tag->{$target} = $value;
                $tag->update();
                $result = true;
            }

            return $result;
        }

        /**
         * Get additional tag texts
         *
         * @param $tagId
         * @param $lang
         * @return array
         */
        public function getTagTexts($tagId, $lang): array
        {
            $texts = sPostTag::whereId($tagId)->select($lang . '_content')->first()->toArray();

            return $texts ?? [];
        }

        /**
         * Save additional tag texts
         *
         * @param $tagId
         * @param $lang
         * @param $texts
         * @return mixed
         */
        public function setTagTexts($tagId, $lang, $texts)
        {
            $tag = sPostTag::find($tagId);

            foreach ($texts as $field => $text) {
                $tag->{$lang . '_' . $field} = $text;
            }

            return $tag->update();
        }

        public function filterContent($content, $filters = '')
        {
            if (trim($filters)) {
                $filters = explode(',', $filters);
                foreach ($filters as $filter) {
                    switch ($filter) {
                        default:
                            break;
                        case "img": // Images
                            $img = '/<img[^>]+>/';
                            preg_match_all($img, $content, $imgMatchContents);
                            if (isset($imgMatchContents[0]) && is_array($imgMatchContents[0]) && count($imgMatchContents[0])) {
                                foreach ($imgMatchContents[0] as $imgMatchContent) {
                                    preg_match('/(src)=("[^"]*")/si', $imgMatchContent, $src);
                                    $src = trim($src[2], '"');
                                    preg_match('/(<img[^>]* width=")([\d.]+)(.*)/si', $imgMatchContent, $width);
                                    $width = intval(trim($width[2]));
                                    preg_match('/(<img[^>]* height=")([\d.]+)(.*)/si', $imgMatchContent, $height);
                                    $height = intval(trim($height[2]));
                                    preg_match('/(class)=("[^"]*")/si', $imgMatchContent, $class);
                                    $class = trim($class[2], '"');
                                    if ($width && $height) {
                                        $src = str_replace('-' . $width . 'x' . $height, '', $src);
                                    }
                                    if (substr($src, 0, 4) != 'http' && substr($src, 0, 1) != '/') {
                                        $src = MODX_BASE_URL . $src;
                                    }
                                    if (trim($class)) {
                                        $class = 'class="' . $class . '"';
                                    }
                                    $content = str_replace($imgMatchContent, '<img src="' . $src . '" ' . $class . ' />', $content);
                                }
                            }
                            break;
                    }
                }
            }
            return $content;
        }

        /**
         * Get Google Translations
         *
         * @param $text
         * @param string $source
         * @param string $target
         * @return string
         */
        protected function googleTranslate(string $text, string $source = 'ru', string $target = 'uk'): string
        {
            if ($source == 'ind') {
                $source = 'id';
            }
            if ($target == 'ind') {
                $target = 'id';
            }

            if ($source == $target) {
                return $text;
            }

            $out = '';

            // Google translate URL
            $url = 'https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=uk-RU&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e';
            $fields_string = 'sl=' . urlencode($source) . '&tl=' . urlencode($target) . '&q=' . urlencode($text);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 3);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');

            $result = curl_exec($ch);
            $result = json_decode($result, TRUE);

            if (isset($result['sentences'])) {
                foreach ($result['sentences'] as $s) {
                    $out .= isset($s['trans']) ? $s['trans'] : '';
                }
            } else {
                $out = 'No result';
            }

            if (preg_match('%^\p{Lu}%u', $text) && !preg_match('%^\p{Lu}%u', $out)) { // Если оригинал с заглавной буквы то делаем и певерод с заглавной
                $out = mb_strtoupper(mb_substr($out, 0, 1)) . mb_substr($out, 1);
            }

            return $out;
        }

        /**
         * Module link
         *
         * @return string
         */
        protected function moduleUrl(): string
        {
            $module = SiteModule::whereName('sPost')->first();
            return 'index.php?a=112&id=' . $module->id;
        }

        /**
         * Connecting the visual editor to the required fields
         *
         * @param string $ids List of id fields separated by commas
         * @param string $height Window height
         * @param string $editor Which editor to use TinyMCE5, CodeMirror
         * @return string
         */
        public function textEditor(string $ids, string $height = '500px', string $editor = ''): string
        {
            if (!trim($editor)) {
                $editor = evo()->getConfig('which_editor', 'TinyMCE5');
            }
            $elements = [];
            $ids = explode(",", $ids);
            $s_lang = evo()->getConfig('s_lang_config', '');

            if (trim($s_lang)) {
                $s_lang = explode(',', $s_lang);
                foreach ($s_lang as $lang) {
                    foreach ($ids as $id) {
                        $elements[] = trim($lang) . "_" . trim($id);
                    }
                }
            } else {
                foreach ($ids as $id) {
                    $elements[] = trim($id);
                }
            }

            return implode("", evo()->invokeEvent('OnRichTextEditorInit', [
                'editor' => $editor,
                'elements' => $elements,
                'height' => $height
            ]));
        }
    }
}