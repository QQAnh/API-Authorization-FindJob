<?php

namespace App\Http\Controllers;

use App\Job;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use JWTAuth;
use Illuminate\Http\Request;


class JobController extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $location = $request->get('location');
        $data = Job::select('jobs.*');
        if ($keyword) {
            $data = $data = Job::where('jobs.title', 'like', '%' . $keyword . '%')
                ->orWhere('jobs.job_description', 'like', '%' . $keyword . '%')
                ->orWhere('jobs.skills_experience', 'like', '%' . $keyword . '%')
                ->orWhere('jobs.love_working_here', 'like', '%' . $keyword . '%')
                ->select('jobs.*');
        }
        if ($location) {
            $data = $data = Job::where('jobs.location', 'like', '%' . $location . '%')
                ->select('jobs.*');
        }
        $data = $data->paginate(10);
        if (count($data) == 0) {
            return response()->json(['error_message' => "No item found"], 200);
        } else {
            $data->appends($request->query());
            return [
                'data' => $data,
                'paginate_view' => View::make('pagination', compact('data'))->render()
            ];
        }
    }

    public function show($id)
    {
        $data = Job::find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, Job with id ' . $id . ' cannot be found'
            ], 400);
        }
        return $data;
    }

    public function store(Request $request)
    {
        $user = JWTAuth::toUser($request->header('authorization'));
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'job_url' => 'required',
            'job_description' => 'required',
            'location' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $job = new Job();
        $job->title = $request->title;
        $job->job_url = $request->job_url;
        $job->job_description = $request->job_description;
        $job->location = $request->location;
        $job->skills_experience = $request->skills_experience;
        $job->love_working_here = $request->love_working_here;
        $job->user_id = $user->id;
        $job->save();
        return response()->json($job->id, 200);
    }

    public function update(Request $request, $id)
    {
        $job = Job::find($id);

        $user = JWTAuth::toUser($request->header('authorization'));
        if ($job->user_id != $user->id) {
            return response()->json('Permission denied', 403);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'job_url' => 'required',
            'job_description' => 'required',
            'location' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $job->title = $request->input('title');
        $job->job_url = $request->input('job_url');
        $job->location = $request->input('location');
        $job->job_description = $request->input('job_description');
        $job->skills_experience = $request->input('skills_experience');
        $job->love_working_here = $request->input('love_working_here');
        $job->updated_at = Carbon::now();
        $update = $job->save();
        if ($update) {
            return response()->json([
                'success' => true,
                'id' => $job->id

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product could not be updated'
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $job = Job::find($id);

        $user = JWTAuth::toUser($request->header('authorization'));
        if ($job->user_id != $user->id) {
            return response()->json('Permission denied', 403);
        }

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, product with id ' . $id . ' cannot be found'
            ], 400);
        }

        if ($job->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product could not be deleted'
            ], 500);
        }
    }
}
