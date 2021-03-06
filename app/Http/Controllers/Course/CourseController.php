<?php

namespace App\Http\Controllers\Course;

use Illuminate\Http\Request;
use \App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


use Inertia\Inertia;
use Auth;
use DB;
use Str;
use Image;
use \App\Models\Student;
use \App\Models\Course;
use \App\Models\Teacher;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $teacher = Teacher::where("user_id", Auth::user()->id)->first();
        $highlights = [];
        $last = Course::take(4)->orderBy('updated_at', 'desc')->where("teacher_id", $teacher->id)->get();
        foreach ($last as $last) {

            $reg = [
                'uuid'        => $last->uuid,
                'id'          => $last->id,
                'title'       => $last->title,
                'description' => $last->description,
                'status'      => $last->status,
                'title'       => $last->title,
                'cover'       => url('/uploads/teachers/covers/thumbnail/' . $last->cover)
            ];
            $highlights[] = $reg;
        }

        return Inertia::render('Course', [
            "courses" => Course::with("classrooms")->with("students")->select("courses.*",  DB::raw("DATE_FORMAT(courses.created_at, '%d/%m/%Y') as date"))->where("teacher_id", $teacher->id)->get(),
            'highlights' => $highlights
        ]);
    }

    public function create()
    {
        return Inertia::render('Course/Create', []);
    }

    public function store(Request $request)
    {
        $teacher = Teacher::where("user_id", Auth::user()->id)->first();
        $request->validate([
            'title'        => 'required|max:255|unique:courses,title,' . $teacher->id . ',teacher_id',
            'level'        => 'required',
            'price'        => 'required',
            'weeks'        => 'required',
            'hours'        => 'required',
            'timetable'    => 'required',
            'age'          => 'required',
            'size'         => 'required',
            'description'  => 'required',
            'image'        => 'required'
        ]);

        //upload image
        $image = Self::upload($request->file("image"));

        $data = $request->all();
        $data += ["teacher_id" => $teacher->id];
        $data += ["cover" => $image ? $image : NULL];

        $course = Course::create($data);

        $request->session()->flash('message', 'Saved successfully!');

        return Redirect::route('courses-edit', $course->uuid);
    }


    public function edit($uuid)
    {
        $course = Course::where("uuid", $uuid)->first();
        return Inertia::render('Course/Edit', [
            'course' => [
                "register" => $course,
                "cover" => url("uploads/teachers/covers/", $course->cover),
            ]
        ]);
    }


    public function update(Request $request)
    {
        $teacher = Teacher::where("user_id", Auth::user()->id)->first();
        $course = Course::where('id', $request->input("id"))->first();
        $validation = $request->validate([
            'title'        => 'required|max:255|unique:courses,title,' . $teacher->id . ',teacher_id',
            'level'        => 'required',
            'description'  => 'required'
        ]);

        //upload image
        $image = Self::upload($request->file("image"));

        $data = $request->all();
        $data += ["cover" => $image ? $image : $course->cover];

        $course->update($data);


        $request->session()->flash('message', 'Saved successfully!');

        return Redirect::route('courses-edit', $course->uuid);
    }

    public function status(Request $request)
    {
        $course = Course::where('id', $request->input("id"))->first();

        $status = $course->status == 0 ? 1 : 0;
        $course->update(["status" => $status]);

        return Redirect::route('courses');
    }

    public function show(Request $request)
    {
        $course = Course::where('id', $request->input("id"))->first();

        $show = $course->show == 0 ? 1 : 0;
        $course->update(["show" => $show]);

        return Redirect::route('courses');
    }

    public function upload($file)
    {
        if ($file) {
            $imagename = time() . '.' . $file->extension();
            $destinationPath = public_path('/uploads/teachers/covers/thumbnail');

            //create thumb
            $img = Image::make($file->path());
            $img->resize(400, 150, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imagename);

            //create full image
            $destinationPath = public_path('/uploads/teachers/covers');
            $file->move($destinationPath, $imagename);
        } else {
            $imagename = NULL;
        }

        return $imagename;
    }
}
