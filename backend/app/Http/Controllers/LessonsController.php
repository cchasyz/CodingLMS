<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\Lessons;
use App\Models\Options;
use App\Models\Completeds;
use App\Models\Enrollments;
use Illuminate\Http\Request;
use App\Models\LessonContent;

class LessonsController extends Controller
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
    public function create(Request $request){
        $input = $request->validate([
            'name' => ['required'],
            'set_id' => ['required', 'exists:sets,id'],
            'contents' => ['required', 'array'],
            'contents.*.type' => ['required', 'in:learn,quiz'],
            'contents.*.content' => ['required', 'string'],
            'contents.*.options' => ['sometimes', 'array'],
            'contents.*.options.*.option_text' => ['required', 'string'],
            'contents.*.options.*.is_correct' => ['required', 'boolean'],
        ]);

        $oldlesson = Lessons::where('set_id', $request->set_id)->get();
        if($oldlesson->isEmpty()){
            $newlessonorder = 0;
        } else {
            $lastorder = $oldlesson->max('order');
            $newlessonorder = $lastorder + 1;
        }
        $lesson = Lessons::create([
            'name' => $request->name,
            'set_id' => $request->set_id,
            'order' => $newlessonorder
        ]);
        $orderContent = 0;
        foreach ($request->contents as $content) {
            $lessoncontent = LessonContent::create([
                'type' => $content['type'],
                'content' => $content['content'],
                'lesson_id' => $lesson->id,
                'order' => $orderContent
                ]);
                if (isset($content['options'])) {
                    foreach ($content['options'] as $option) {
                        Options::create([
                            'option_text' => $option['option_text'],
                            'is_correct' => $option['is_correct'],
                            'lesson_content_id' => $lessoncontent->id
                        ]);
                    }
                }
                $orderContent + 1;
            }

        return response()->json([
            'message' => 'successfull',
            'data' => $lesson
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function getProgress()
    {
        $user = auth()->user();

        // Fetch enrolled course IDs
        $enrolledCourseIds = Enrollments::where('user_id', $user->id)->pluck('course_id');

        // Fetch unique completed lesson IDs
        $completedLessonIds = Completeds::where('user_id', $user->id)->pluck('lesson_id')->unique()->toArray();

        $progress = [];

        foreach ($enrolledCourseIds as $courseId) {
            $course = Courses::find($courseId);

            if (!$course) {
                continue; // Skip if course is not found
            }

            // Fetch all lessons for the course
            $allLessons = Lessons::whereHas('set', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })->get();

            \Log::info('All Lessons for Course ID ' . $courseId, $allLessons->toArray());

            // Filter completed lessons
            $completedLessons = $allLessons->filter(function ($lesson) use ($completedLessonIds) {
                return in_array($lesson->id, $completedLessonIds);
            })->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                    'order' => $lesson->order,
                ];
            });

            \Log::info('Completed Lessons for Course ID ' . $courseId, $completedLessons->toArray());

            // Add course progress
            $progress[] = [
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'slug' => $course->slug,
                    'description' => $course->description,
                    'is_published' => $course->is_published,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                ],
                'completed_lessons' => $completedLessons,
            ];
        }

        \Log::info('Final Progress:', $progress);

        return response()->json([
            'message' => 'successfully taken progress',
            'data' => [
                'progress' => $progress,
            ],
            'completed_lesson_ids' => $completedLessonIds,
            'enrolled_courses' => $enrolledCourseIds,
        ]);
    }    

    /**
     * Display the specified resource.
     */
    public function registerCourse($slug){
        $user = auth()->user();
        $course = Courses::where('slug', $slug)->first();
        if(!$course){
            return response()->json([
                'message' => 'course not found'
            ], 404);
        }
        if(Enrollments::where('user_id', $user->id)->where('course_id', $course->id)->exists()){
            return response()->json([
                'message' => 'user already registered in this course'
            ], 400);
        }
        Enrollments::create([
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
        return response()->json([
            'message' => 'registered successfully'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function complete($lessonId){
        $user = auth()->user();
        $lesson = Lessons::where('id', $lessonId)->first();
        if(!$lesson){
            return response()->json([
                'message' => 'lesson not found'
            ], 404); 
        }
        Completeds::create([
            'user_id' => $user->id,
            'lesson_id' => $lessonId
        ]);
        return response()->json([
            'message' => 'lesson successfully completed'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function check(Request $request, $lessonId, $contentId){
        $request->validate([
            'option_id' => ['required', 'exists:options,id']
        ]);
        $lesson = Lessons::where('id', $lessonId)->first();
        if(!$lesson){
            return response()->json([
                'message' => 'lesson not found'
            ], 404); 
        }
        $content = LessonContent::where('id', $contentId)->first();
        if(!$content){
            return response()->json([
                'message' => 'lesson content not found'
            ], 404); 
        }
        if($content->type !== 'quiz'){
            return response()->json([
                'message' => 'only for quiz content'
            ], 400);
        }
        $userOption = Options::where('id', $request->option_id)->first();

        $output = [
            'question' => $content->content,
            'user_answer' => $userOption->option_text,
            'is_correct' => $userOption->is_correct
        ];

        return response()->json([
            'message' => 'check answer success',
            'data' => $output
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($lessonId){
        $lesson = Lessons::where('id', $lessonId)->first();
        if(!$lesson){
            return response()->json([
                'message' => 'lesson not found'
            ], 404); 
        }
        $lesson->delete();
        return response()->json([
            'message' => 'successfully deleted lesson'
        ]);
    }
}
