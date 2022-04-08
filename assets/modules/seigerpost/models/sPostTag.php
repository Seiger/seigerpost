<?php namespace sPost\Models;

use Illuminate\Database\Eloquent;

class sPostTag extends Eloquent\Model
{
    /**
     * The posts that belong to the tag.
     */
    public function posts()
    {
        return $this->belongsToMany(sPostContent::class, 's_post_content_tag', 'tag_id', 'post_id', 'id', 'id');
    }

    /**
     * Get the table columns associated with the model.
     *
     * @return array
     */
    public static function getTableColumns()
    {
        return static::query()->getConnection()->getSchemaBuilder()->getColumnListing(with(new static)->getTable());
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}