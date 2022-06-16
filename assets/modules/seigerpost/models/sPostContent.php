<?php namespace sPost\Models;

use Carbon\Carbon;
use EvolutionCMS\Facades\UrlProcessor;
use Illuminate\Database\Eloquent;

class sPostContent extends Eloquent\Model
{
    const TYPE_ARTICLE = 0;
    const TYPE_NEWS = 1;

    /**
     * Return list of types codes and labels
     *
     * @return array
     */
    public static function listTypes(): array
    {
        global $_lang;

        return [
            self::TYPE_ARTICLE => $_lang['spost_' . self::TYPE_ARTICLE],
            self::TYPE_NEWS => $_lang['spost_' . self::TYPE_NEWS],
        ];
    }

    /**
     * Return list of types codes and proxies
     *
     * @return array
     */
    public static function listProxies(): array
    {
        global $_lang;

        return [
            self::TYPE_ARTICLE => 'post-article',
            self::TYPE_NEWS => 'post-news',
        ];
    }

    /**
     * Returns label of actual type
     *
     * @return string
     */
    public function typeLabel(): string
    {
        $list = self::listStatus();

        // little validation here just in case someone mess things
        // up and there's a ghost status saved in DB
        return $list[$this->type] ?? $this->type;
    }

    /**
     * Get the translates for the post.
     */
    public function texts()
    {
        return $this->hasMany(sPostTranslate::class, 'post', 'post');
    }

    /**
     * The tags that belong to the post.
     */
    public function tags()
    {
        return $this->belongsToMany(sPostTag::class, 's_post_content_tag', 'post_id', 'tag_id', 'post');
    }

    /**
     * Get the post item with lang
     *
     * @param $query
     * @param $locale
     * @return mixed
     */
    public function scopeLang($query, $locale)
    {
        return $this->leftJoin('s_post_translates', 's_post_contents.id', '=', 's_post_translates.post')->where('lang', '=', $locale);
    }

    /**
     * Get the author Name
     *
     * @return mixed
     */
    public function getAuthorNameAttribute()
    {
        $lang = evo()->getConfig('lang', 'uk');
        $lang = str_replace('ind', 'id', $lang);
        $sAuthors = new sAuthors();
        $author = $sAuthors->getAuthor($this->author, $lang);
        return $author->author_name ?? '';
    }

    /**
     * Get the author Link
     *
     * @return mixed
     */
    public function getAuthorLinkAttribute()
    {
        $lang = evo()->getConfig('lang', 'uk');
        $lang = str_replace('ind', 'id', $lang);
        $sAuthors = new sAuthors();
        $author = $sAuthors->getAuthor($this->author, $lang);
        $base_url = evo()->getConfig('base_url', MODX_SITE_URL);
        $link = $base_url . $author->alias . '/';
        return $link;
    }

    /**
     * Get the published day on human format
     *
     * isoFormat('D MMM, YYYY') - 24 MAY, 2022
     *
     * @return string
     */
    public function getPubFormatAttribute()
    {
        $lang = evo()->getConfig('lang', 'en');
        $lang = str_replace('ind', 'id', $lang);
        $pub_date = Carbon::parse($this->pub_date)->locale($lang);
        return $pub_date->isoFormat('D MMMM YYYY');
    }

    /**
     * Get the published day ago on human format
     *
     * @return string
     */
    public function getAgoFormatAttribute()
    {
        $lang = evo()->getConfig('lang', 'en');
        $lang = str_replace('ind', 'id', $lang);
        $pub_date = Carbon::parse($this->pub_date)->locale($lang);
        return $pub_date->diffForHumans();
    }

    /**
     * Get the post link
     *
     * @return string
     */
    public function getLinkAttribute()
    {
        $base_url = evo()->getConfig('base_url', '/');
        if ($base_url == '/') {
            $base_url = MODX_SITE_URL;
        }
        $suffix_url = evo()->getConfig('friendly_url_suffix', '');
        if ($this->type == 0) {
            $link = $base_url . $this->alias . $suffix_url;
        } else {
            $link = $base_url . $this->alias . $suffix_url;
        }
        return $link;
    }

    /**
     * Get the post cover src link
     *
     * @return string cover_src
     */
    public function getCoverSrcAttribute()
    {
        if (!empty($this->cover) && is_file(MODX_BASE_PATH . $this->cover)) {
            $coverSrc = MODX_SITE_URL . $this->cover;
        } else {
            $coverSrc = MODX_SITE_URL . 'assets/images/noimage.png';
        }

        return $coverSrc;
    }
}