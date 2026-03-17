<?php

namespace App\Http\Controllers;

use App\Models\Engagement;
use App\Models\Member;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index()
    {
        $members = Member::latest()->with(['engagements', 'surveys'])->paginate(20);

        $stats = [
            'total_registrations' => Member::count(),
            'engagement_completed' => Engagement::where('status', 'completed')->count(),
            'engagement_partial' => Engagement::where('status', 'partial')->count(),
            'engagement_pending' => Engagement::where('status', 'pending')->count(),
            'survey_submissions' => Survey::count(),
        ];

        return view('admin.index', compact('members', 'stats'));
    }

    public function profile()
    {
        $admin = Auth::guard('admin')->user();

        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . $admin->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];

        if (!empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            $uploadDir = public_path('uploads/admin-profiles');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploaded = $request->file('profile_picture');
            $fileName = Str::uuid()->toString() . '.' . $uploaded->getClientOriginalExtension();
            $uploaded->move($uploadDir, $fileName);

            if ($admin->profile_picture) {
                $oldPath = public_path($admin->profile_picture);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $admin->profile_picture = 'uploads/admin-profiles/' . $fileName;
        }

        $admin->save();

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }

    public function destroyProfile(Request $request)
    {
        $request->validate([
            'confirm_email' => ['required', 'email'],
        ]);

        $admin = Auth::guard('admin')->user();

        if ($request->confirm_email !== $admin->email) {
            return back()->withErrors([
                'confirm_email' => 'Email confirmation does not match your profile email.',
            ]);
        }

        if ($admin->profile_picture) {
            $profilePicturePath = public_path($admin->profile_picture);
            if (is_file($profilePicturePath)) {
                @unlink($profilePicturePath);
            }
        }

        Auth::guard('admin')->logout();
        $admin->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Profile deleted successfully.');
    }

    public function exportRegistrations(): StreamedResponse
    {
        $fileName = 'registrations.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Full Name', 'Email', 'Phone', 'Gender', 'Organization', 'Role', 'Code', 'Registered At']);

            Member::orderBy('id')->chunk(200, function ($members) use ($handle) {
                foreach ($members as $member) {
                    fputcsv($handle, [
                        $member->id,
                        $member->full_name,
                        $member->email,
                        $member->phone,
                        $member->gender,
                        $member->organization,
                        $member->role,
                        $member->unique_code,
                        $member->created_at,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportEngagements(): StreamedResponse
    {
        $fileName = 'engagements.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Member ID',
                'Member Name',
                'Code',
                'Status',
                'Stars',
                'Slides Viewed',
                'Total Slides',
                'Completion %',
                'Started At',
                'Completed At',
            ]);

            Engagement::with('member')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $engagement) {
                    fputcsv($handle, [
                        $engagement->id,
                        $engagement->member_id,
                        optional($engagement->member)->full_name,
                        optional($engagement->member)->unique_code,
                        $engagement->status,
                        $engagement->star_rating,
                        $engagement->slides_viewed,
                        $engagement->total_slides,
                        $engagement->completion_percentage,
                        $engagement->started_at,
                        $engagement->completed_at,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportSurveys(): StreamedResponse
    {
        $fileName = 'surveys.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Member ID',
                'Member Name',
                'Code',
                'Q1',
                'Q2',
                'Q3',
                'Q4',
                'Q5',
                'Comments',
                'Additional Data',
                'Submitted At',
            ]);

            Survey::with('member')->orderBy('id')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $survey) {
                    fputcsv($handle, [
                        $survey->id,
                        $survey->member_id,
                        optional($survey->member)->full_name,
                        optional($survey->member)->unique_code,
                        $survey->question_1,
                        $survey->question_2,
                        $survey->question_3,
                        $survey->question_4,
                        $survey->question_5,
                        $survey->comments,
                        $survey->additional_data ? json_encode($survey->additional_data) : null,
                        $survey->created_at,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
