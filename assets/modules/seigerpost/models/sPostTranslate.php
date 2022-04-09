<?php namespace sPost\Models;

use Illuminate\Database\Eloquent;

class sPostTranslate extends Eloquent\Model
{
    protected $fillable = ['post', 'lang', 'pagetitle', 'introtext', 'content', 'epilog', 'seotitle', 'seodescription'];
}