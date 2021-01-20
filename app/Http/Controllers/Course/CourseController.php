<?php

namespace App\Http\Controllers\Course;

use App\Course;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    protected $course;

    protected $command;

    public function __construct(Course $course, Command $command)
    {
        $this->course = $course;

        $this->command = $command;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $courses = $this->course->all();

            $response = response()->json([
                'status' => 200,
                'data' => $courses
            ], 200);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $course = $this->course->create([
                'title' => $request->title,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'price' => $request->price,
                'start_date' => now()->toDateString(),
            ]);

            $response = response()->json([
                'status' => 201,
                'data' => $course
            ]);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Generate courses
     */
    public function generateCourse()
    {
        try {
            Artisan::call('db:seed --class=CourseTableSeeder');

            $response = response()->json([
                'status' => 200,
                'message' => 'Course seeded successfully!'
            ], 200);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $course = $this->course->where('id', $id)->first();

            $response = response()->json([
                'status' => 200,
                'data' => $course
            ], 200);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // User register for course
    public function courseEnrol(Request $request, $course_id)
    {
        try {
            $course = $this->course->where('id', $course_id)->first();

            $enrol = DB::table('course_user')->updateOrInsert(
                ['course_id' => $course->id, 'user_id' => auth()->id()],
                ['course_id' => $course->id, 'user_id' => auth()->id()]
            );

            if ($enrol === false) {
                return response()->json(['error' => 'User Already Enrolled To This Course']);
            }

            $response = response()->json([
                'status' => 200,
                'message' => 'User enrolled successfully!'
            ], 200);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Enrolled Courses
     */
    public function enrolledCourses()
    {
        try {
            $enrolled = auth()->user()->courses;

            $response = response()->json([
                'status' => 200,
                'data' => $enrolled
            ], 200);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Export CSV
     */
    public function exportCSV(Request $request)
    {
        try {
            $extension = $request->export_type;

            if (!empty($extension)) {
                $extension = $extension;
            } else {
                $extension = 'xlsx';
            }

            $courses = $this->course->all();

            $fileName = 'course-export';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'title');
            $sheet->setCellValue('B1', 'slug');
            $sheet->setCellValue('C1', 'description');
            $sheet->setCellValue('D1', 'price');
            $sheet->setCellValue('E1', 'course_image');
            $sheet->setCellValue('F1', 'start_date');
            $sheet->setCellValue('G1', 'published');

            $rowCount = 2;

            foreach ($courses as $course) {
                $sheet->setCellValue('A' . $rowCount, $course['title']);
                $sheet->setCellValue('B' . $rowCount, $course['slug']);
                $sheet->setCellValue('C' . $rowCount, $course['description']);
                $sheet->setCellValue('D' . $rowCount, $course['price']);
                $sheet->setCellValue('E' . $rowCount, $course['course_image']);
                $sheet->setCellValue('F' . $rowCount, $course['start_date']);
                $sheet->setCellValue('G' . $rowCount, $course['published']);
                $rowCount++;
            }

            if ($extension == 'csv') {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $fileName = $fileName . '.csv';
            } elseif($extension == 'xlsx') {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $fileName = $fileName . '.xlsx';
            } else {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                $fileName = $fileName . '.xls';
            }

            $writer->save($fileName);

            $filepath = file_get_contents(public_path($fileName));

            $headers = array(
                'Content-Type' => 'application/vnd.ms-excel',
            );

            $response = response()->download($fileName, 'course.' . $extension, $headers);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }
}
