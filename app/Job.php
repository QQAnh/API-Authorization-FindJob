<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $table = 'jobs';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'title', 'job_url', 'job_description','location','user_id'
    ];
}
