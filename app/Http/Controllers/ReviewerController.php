<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Session;
use Illuminate\Contracts\View\View;
use Illuminate\View\ViewFinderInterface;

class ReviewerController extends Controller
{
    public function reviewerDashbordView(Request $request){
        $user_name = $request->session()->get('user_name');
        return view('reviewer.pages.dashbord', ['user_name' => $user_name]);
    }

    public function assignedPaperView(Request $request){
        $user_id = $request->session()->get('user_id');
        $user_name = $request->session()->get('user_name');
        

        $data = DB::table('reviews')->where('review_user_id', '=', $user_id)
        ->join('submissions', 'submissions.id', '=', 'reviews.review_submission_id')
        ->select('reviews.id', 'reviews.msg', 'submissions.id as submission_id', 'submissions.title as submission_title', 'submissions.file_name', 'submissions.tags')
        ->get();
        // dd($user_name);
        return View('reviewer.pages.assign-paper-table', ['data' => $data, 'user_name' => $user_name]);
    }

    public function paperDownload($id){
        $data = DB::table('submissions')->where('id', '=', $id)->first();

        $file_name = $data->file_name;
        $file_path = public_path()."/uploads/".$file_name;
        
        $headers = [
            'Content-Type' => 'application/pdf',
         ];

         return response()->download($file_path, $file_name.'.pdf', $headers);
    }
}
