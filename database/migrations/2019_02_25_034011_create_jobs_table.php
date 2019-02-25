<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('job_url');
                $table->string('location');
                $table->text('job_description');
                $table->text('skills_experience')->nullable();
                $table->text('love_working_here')->nullable();
                $table->unsignedInteger('user_id');
                $table->foreign("user_id")->references("id")->on("users");
                $table->timestamps();
                $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}
