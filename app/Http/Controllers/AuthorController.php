<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{

    public function authorDashbordView(Request $r)
    {
        $user_id = $r->session()->get('user_id');
        $authorOrcidId = DB::table('unique_identifiers')->where('users_uniqueIdentifier_id', $user_id)->value('author_orcidID');
        // dd($authorOrcidId);
        session()->put('authorOrcidId', $authorOrcidId);

        return view('author.pages.author-dashbord');
    }

    public function availableConferenceView()
    {
        $time = date('Y-m-d');
        $data = DB::table('conferences')
            ->where('submission_deadline', '>=', $time)
            ->join('universities', 'universities.id', 'university_id')
            ->select('conferences.id', 'conferences.title', 'conferences.submission_deadline', 'conferences.conference_date', 'universities.name')
            ->get();
        // dd($data);
        return view(
            'author.pages.conference-table',
            [
                'data' => $data
            ]
        );
    }

    public function authorPaperConference($id)
    {
        $data = DB::table('tracks')->where('conference_id', '=', $id)->get();
        // dd($data);
        return view('author.pages.author-paper-submission', ['submission_id' => $id, 'data' => $data]);
    }

    public function authorPaperSubmissionStore(Request $request, $id)
    {
        // dd($request->file->getClientOriginalName());

        $request->validate([

            'file' => 'required|mimes:pdf|max:8192',
        ]);

        $originalName = $request->file->getClientOriginalName();
        $time = time();
        $fileName = $time . '_' . $originalName;
        $title = $request->title;
        $track = $request->track;
        $abstract = $request->abstract;
        $tags = $request->tags;
        $user_id = $request->session()->get('user_id');
        $request->file->move(public_path('uploads'), $fileName);

        $id = DB::table('tracks')->where('id', '=', $track)->first();
        $conference_id = $id->conference_id;

        $author_names = $request->author_name;
        $author_emails = $request->author_email;
        $author_orcidids = $request->author_orcidid;

        DB::table('submissions')->insert([
            'title' => $title,
            'abstract' => $abstract,
            'tags' => $tags,
            'file_name' => $fileName,
            'submissions_conference_id' => $conference_id,
            'track_id' => $track,
            'user_id' => $user_id
        ]);

        $submission_id = DB::table('submissions')->latest('id')->select('id')->first();


        $isValid = array();
        for ($i = 0; $i < count($author_orcidids); $i++) {
            $data = DB::table('unique_identifiers')
                ->join('users', 'unique_identifiers.users_uniqueIdentifier_id', '=', 'users.id')
                ->where('unique_identifiers.author_orcidID', $author_orcidids[$i])
                ->where('users.email', $author_emails[$i])
                ->get();

            if (count($data) == 0) {
                array_push($isValid, 0);
                break;
            } else {
                array_push($isValid, 1);
            }
        }

        $flag = false;
        for ($i = 0; $i < count($isValid); $i++) {
            if ($isValid[$i] == 0) {
                $flag = false;
                DB::table('submissions')->where('id', '=', $submission_id->id)->delete();
                return back()->with('error', 'Please use valid email and orcid id.');
                break;
            } else {
                $flag = true;
            }
        }
        // dd($flag);


        if ($flag == true) {
            for ($i = 0; $i < count($author_names); $i++) {
                DB::table('submission_teams')->insert([
                    'name' => $author_names[$i],
                    'submission_teams_email' => $author_emails[$i],
                    'submission_teams_orcidID' => $author_orcidids[$i],
                    'submission_paper_id' => $submission_id->id,
                ]);
            }
            return back()->with('success', 'You have successfully upload file.')->with('file', $fileName);
        }
        
    }

    public function submissionTableView(Request $request)
    {
        $user_id = $request->session()->get('user_id');
        $data = DB::table('submissions')
            ->join('tracks', 'tracks.id', '=', 'submissions.track_id')
            ->join('conferences', 'conferences.id', '=', 'submissions.submissions_conference_id')
            ->select('submissions.id', 'submissions.title', 'submissions.abstract', 'submissions.tags', 'submissions.file_name', 'tracks.name as tracks_name', 'conferences.title as conferences_title')
            ->where('user_id', '=', $user_id)
            ->get();

        // $conference = array();
        // foreach ($data as $d) {
        //     $conference_data = DB::table('conferences')->where('id', '=', $d->conference_id)->get();
        //     array_push($conference, $conference_data);
        // }
        // $a = array_combine($data, $conference);

        return view('author.pages.submission-table', [
            'data' => $data,
        ]);
        // dd($data);
    }
}
