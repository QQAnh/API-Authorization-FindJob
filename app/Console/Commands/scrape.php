<?php

namespace App\Console\Commands;

use Goutte;
use Illuminate\Console\Command;
use App\Job;

class scrape extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page_number = range(1, 2);
        foreach ($page_number as $page) {
            $l = 'https://itviec.com/viec-lam-it?page=' . $page;
            $crawler = Goutte::request('GET', $l);
            $linkJob = $crawler->filter('h2.title a')->each(function ($node) {
                return $node->attr('href');
            });
// sleep(10);
            foreach ($linkJob as $link) {
// echo $link."\n";
                $z = 'https://itviec.com' . $link;
                self::scapeJob($z);
            }
        }
    }
    public function scapeJob($url)
    {
        $crawler = Goutte::request('GET', $url);

        $title = $crawler->filter('h1.job_title')->each(function ($node) {
            return $node->text();
        });

        if (isset($title[0])) {
            $title = $title[0];
        } else {
            $title = '';
        }

        $job_url = $crawler->filter('div.logo img')->each(function ($node) {
            return $node->attr('src');
        });

        if (isset($job_url[0])) {
            $job_url = $job_url[0];
        } else {
            $job_url = '';
        }

        $location = $crawler->filter('div.address__full-address span')->each(function ($node) {
            return $node->text();
        });

        if (isset($location[0])) {
            $location = $location[0];
        } else {
            $location = '';
        }

        $company = $crawler->filter('h3.name a')->each(function ($node) {
            return $node->text();
        });

        if (isset($company[0])) {
            $company = $company[0];
        } else {
            $company = '';
        }

        $job_description = $crawler->filter('div.job_description div.description')->each(function ($node) {
            return $node->text();
        });

        if (isset($job_description[0])) {
            $job_description = $job_description[0];
        } else {
            $job_description = '';
        }

        $skills_experience = $crawler->filter('div.skills_experience div.experience')->each(function ($node) {
            return $node->text();
        });

        if (isset($skills_experience[0])) {
            $skills_experience = $skills_experience[0];
        } else {
            $skills_experience = '';
        }

        $love_working_here = $crawler->filter('div.love_working_here div.culture_description')->each(function ($node) {
            return $node->text();
        });

        if (isset($love_working_here[0])) {
            $love_working_here = $love_working_here[0];
        } else {
            $love_working_here = '';
        }
        $salary = '';
        $job_type = '';

        $database = [
            'title' => $title,
            'job_url' => $job_url,
            'location' => $location,
            'company' => $company,
            'salary' => $salary,
            'job_type' => $job_type,
            'job_description' => $job_description,
            'skills_experience' => $skills_experience,
            'love_working_here' => $love_working_here,
        ];

        Job::insert($database);
        echo $title;
    }
}
