<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        $courses = Courses::where('is_published', true)->get();

        return response()->json([
            'message' => 'success',
            'data' => $courses
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request){
        $request->validate([
            'name' => ['required'],
            'description' => ['nullable'],
            'slug' => ['required', 'unique:courses,slug']
        ]); 

        $course = Courses::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => $request->slug,
            'is_published' => 0
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $course
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function detail($slug){
        $course = Courses::where('slug', $slug)->with('sets.lessons.contents.options')->first();
        if(!$course){
            return response()->json([
                'message' => 'course not found'
            ], 404);
        }
        return response()->json([
            'message' => 'successfully taken',
            'data' => $course
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Courses $courses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $slug){
        $request->validate([
            'name' => ['sometimes'],
            'description' => ['nullable'],
            'is_published' => ['nullable', 'boolean']
        ]);
        $course = Courses::where('slug', $slug)->first();
        if(!$course){
            return response()->json([
                'message' => 'course not found'
            ], 404);
        }
        $course->update($request->only(['name', 'description', 'is_published']));
        return response()->json([
            'message' => 'successful',
            'data' => $course
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Courses $courses)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug){
        $course = Courses::where('slug', $slug)->first();
        if(!$course){
            return response()->json([
                'message' => 'course not found'
            ], 404);
        }
        $course->delete();
        return response()->json([
            'message' => 'successfully deleted'
        ]);
    }
}
