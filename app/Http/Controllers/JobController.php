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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $keyword = $request->get('keyword');
        $location = $request->get('location');
        $category =$request->get('category');
        $data = Job::where([
            ['jobs.location', 'like', '%' . $location . '%'],
            ['jobs.title', 'like', '%' . $keyword . '%'],
            ['jobs.job_type', 'like', '%' . $category . '%']

        ])
            ->select('jobs.*');
        $data = $data->paginate(10);
        if (count($data) == 0) {
            return response()->json([
                'location'=>$location,
                'keyword'=>$keyword,
                'error_message' => "No item found"], 200);
        } else {
            $data->appends($request->query());
            return [
                'data' => $data,
                'job_type'=>$category,
                'location'=>$location,
                'keyword'=>$keyword,
                'paginate_view' => View::make('pagination', compact('data'))->render()
            ];
        }
    }
    public function create(Request $request)
    {
        try {
            $user = JWTAuth::toUser($request->header('authorization'));
            $data = $request->selection1;
            foreach ($data as $dataJson) {
                $data = new Job();
                $data->title = $dataJson['title'];
                $data->job_url = $dataJson['job_url'];
                $data->location = $dataJson['location'];
                $data->job_description = $dataJson['job_description'];
                $data->company = $dataJson['company'];
                $data->salary = $dataJson['salary'];
                $data->job_type = $dataJson['job_type'];
                $data->skills_experience = $dataJson['skills_experience'];
                $data->love_working_here = $dataJson['love_working_here'];
                $data->user_id = $user->id;
                $data->created_at = Carbon::now();
                $data->updated_at = Carbon::now();
                $data->save();

            }
            $result = Job::all();
            return response()->json($result, 200);
        } catch (Exception $exception) {
            return response()->json($exception->getMessage(), 500);
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


    public function getJobByUser(Request $request)
    {
        $user = JWTAuth::toUser($request->header('authorization'));
        $data = Job::Where('user_id' , '=' , $user->id)->paginate(10);
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * Need Token
     */
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
            return response()->json([
                'message' => false,
                'errors'=>$validator->errors()], 400);
        }
        $job = new Job();
        $job->title = $request->title;
        $job->job_url = $request->job_url;
        $job->job_description = $request->job_description;
        $job->location = $request->location;
        $job->company = $request->company;
        $job->salary = $request->salary;
        $job->job_type = $request->job_type;
        $job->skills_experience = $request->skills_experience;
        $job->love_working_here = $request->love_working_here;
        $job->user_id = $user->id;
        $success = $job->save();
        if ($success) {
            return response()->json([
                'success' => true,
                'id' => $job->id

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, Job could not be create'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     *
     * Need Token
     */

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
            return response()->json([
                'message' => false,
                'errors'=>$validator->errors()], 400);
        }
        $job->title = $request->input('title');
        $job->job_url = $request->input('job_url');
        $job->location = $request->input('location');
        $job->company = $request->input('company');
        $job->salary = $request->input('salary');
        $job->job_type = $request->input('job_type');
        $job->job_description = $request->input('job_description');
        $job->skills_experience = $request->input('skills_experience');
        $job->love_working_here = $request->input('love_working_here');
        $job->updated_at = Carbon::now();
        $success = $job->save();
        if ($success) {
            return response()->json([
                'success' => true,
                'id' => $job->id

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, Job could not be updated'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */

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
