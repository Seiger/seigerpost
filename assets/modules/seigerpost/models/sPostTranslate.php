<?php namespace sPost\Models;

use Illuminate\Database\Eloquent;

class sPostTranslate extends Eloquent\Model
{
    protected $fillable = ['post', 'lang', 'pagetitle', 'introtext', 'content', 'epilog','seo_description', 'seo_title'];

    /**
     * Get the post that owns the text.
     */
    /*public function post()
    {
        return $this->belongsTo(sPostContent::class, 'id', 'post');
    }*/
}