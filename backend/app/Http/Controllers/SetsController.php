<?php

namespace App\Http\Controllers;

use App\Models\Sets;
use App\Models\Courses;
use Illuminate\Http\Request;

class SetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $slug){
        $request->validate([
            'name' => ['required']
        ]);
        $course = Courses::where('slug', $slug)->first();
        if(!$course){
            return response()->json([
                'message' => 'course not found'
            ], 404);
        }
        $sets = Sets::where('course_id', $course->id)->get();
        if($sets->isEmpty()){
            $neworder = 0;
        } else {
            $lastorder = $sets->max('order');
            $neworder = $lastorder + 1;
        }
        $newset = Sets::create([
            'name' => $request->name,
            'course_id' => $course->id,
            'order' => $neworder
        ]);
        return response()->json([
            'message' => 'success',
            'data' => $newset
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Sets $sets)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sets $sets)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sets $sets)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug, $setId){
        $course = Courses::where('slug', $slug)->first();
        if(!$course){
            return response()->json([
                'message' => 'course not found'
            ], 404);
        }
        $set = Sets::where('id', $setId)->first();
        if(!$set){
            return response()->json([
                'message' => 'set not found'
            ], 404);
        }
        $set->delete();
        return response()->json([
            'message' => 'successfully deleted set'
        ]);
    }
}
