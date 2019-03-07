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
        'title', 'job_url', 'job_description', 'location', 'user_id'
    ];

    public static function insert($request)
    {
        $job = new Job();
        $job->title = $request['title'];
        $job->job_url = $request['job_url'];
        $job->job_description = $request['job_description'];
        $job->location = $request['location'];
        $job->company = $request['company'];
        $job->salary = $request['salary'];
        $job->job_type = $request['job_type'];
        $job->skills_experience = $request['skills_experience'];
        $job->love_working_here = $request['love_working_here'];
        $job->user_id = 1;
        $success = $job->save();
        return $success;
    }
}