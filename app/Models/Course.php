<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Course extends Model
{
    use \App\Http\Traits\UsesUuid;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'teacher_id',
        'uuid',
        'title',
        'description',
        'level',
        'cover',
        'show',
        'status',
    ];

    public function teacher()
    {
        return $this->hasOne('App\Models\Teacher');
    }
    public function lessons()
    {
        return $this->hasMany('App\Models\Lesson');
    }
}