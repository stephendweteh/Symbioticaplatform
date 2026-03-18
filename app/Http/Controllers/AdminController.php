<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Engagement;
use App\Models\Member;
use App\Models\Survey;
use App\Models\SurveyField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        [$sortBy, $sortDir] = $this->resolveMemberSort($request);

        $members = $this->membersQuery($sortBy, $sortDir)
            ->with(['engagements', 'surveys'])
            ->paginate(20)
            ->appends([
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ]);

        $stats = [
            'total_registrations' => Member::count(),
            'engagement_completed' => Engagement::where('status', 'completed')->count(),
            'engagement_partial' => Engagement::where('status', 'partial')->count(),
            'engagement_pending' => Engagement::where('status', 'pending')->count(),
            'survey_submissions' => Survey::count(),
        ];

        $sortOptions = $this->memberSortOptions();

        return view('admin.index', compact('members', 'stats', 'sortBy', 'sortDir', 'sortOptions'));
    }

    public function surveys(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $isSuperAdmin = $admin?->role === 'super_admin';
        [$sortBy, $sortDir] = $this->resolveSurveySort($request);

        $surveyFields = SurveyField::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $surveys = $this->surveysQuery($sortBy, $sortDir)
            ->paginate(20)
            ->appends([
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ]);

        $surveySortOptions = $this->surveySortOptions();

        return view('admin.surveys.index', compact('surveys', 'surveyFields', 'sortBy', 'sortDir', 'surveySortOptions', 'isSuperAdmin'));
    }

    public function destroySurvey(Survey $survey)
    {
        $this->ensureSuperAdmin();
        $survey->delete();

        return redirect()
            ->route('admin.surveys.index')
            ->with('success', 'Survey submission deleted successfully.');
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

    public function exportRegistrations(Request $request)
    {
        [$sortBy, $sortDir] = $this->resolveMemberSort($request);
        $format = Str::lower((string) $request->query('format', 'csv'));
        if (! in_array($format, ['csv', 'xls', 'json', 'pdf'], true)) {
            $format = 'csv';
        }

        if ($format === 'json') {
            $rows = $this->membersQuery($sortBy, $sortDir)
                ->get()
                ->map(function ($member) {
                    $county = $member->county
                        ?: data_get($member->additional_data, 'county')
                        ?: data_get($member->additional_data, 'regions');
                    return [
                        'id' => $member->id,
                        'full_name' => $member->full_name,
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'gender' => $member->gender,
                        'county' => $county,
                        'organization' => $member->organization,
                        'role' => $member->role,
                        'unique_code' => $member->unique_code,
                        'registered_at' => (string) $member->created_at,
                    ];
                });

            return response($rows->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="registrations.json"',
            ]);
        }

        if ($format === 'pdf') {
            $rows = $this->membersQuery($sortBy, $sortDir)->get();
            $pdf = Pdf::loadView('admin.exports.registrations-pdf', [
                'rows' => $rows,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape');

            return $pdf->download('registrations.pdf');
        }

        $delimiter = $format === 'xls' ? "\t" : ',';
        $fileName = 'registrations.' . $format;
        $contentType = $format === 'xls'
            ? 'application/vnd.ms-excel'
            : 'text/csv';

        $callback = function () use ($sortBy, $sortDir, $delimiter) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Full Name', 'Email', 'Phone', 'Gender', 'County', 'Organization', 'Role', 'Code', 'Registered At'], $delimiter);

            $this->membersQuery($sortBy, $sortDir)->chunk(200, function ($members) use ($handle, $delimiter) {
                foreach ($members as $member) {
                    $county = $member->county
                        ?: data_get($member->additional_data, 'county')
                        ?: data_get($member->additional_data, 'regions');
                    fputcsv($handle, [
                        $member->id,
                        $member->full_name,
                        $member->email,
                        $member->phone,
                        $member->gender,
                        $county,
                        $member->organization,
                        $member->role,
                        $member->unique_code,
                        $member->created_at,
                    ], $delimiter);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => $contentType,
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

    public function exportSurveys(Request $request)
    {
        [$sortBy, $sortDir] = $this->resolveSurveySort($request);
        $format = Str::lower((string) $request->query('format', 'csv'));
        if (! in_array($format, ['csv', 'xls', 'pdf'], true)) {
            $format = 'csv';
        }

        $rows = $this->surveysQuery($sortBy, $sortDir)->get();
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.exports.surveys-pdf', [
                'rows' => $rows,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape');

            return $pdf->download('surveys.pdf');
        }

        $delimiter = $format === 'xls' ? "\t" : ',';
        $fileName = 'surveys.' . $format;
        $contentType = $format === 'xls'
            ? 'application/vnd.ms-excel'
            : 'text/csv';

        $callback = function () use ($sortBy, $sortDir, $delimiter) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Member ID',
                'Member Name',
                'Code',
                'Member Email',
                'Q1',
                'Q2',
                'Q3',
                'Q4',
                'Q5',
                'Comments',
                'Additional Data',
                'Submitted At',
            ], $delimiter);

            $this->surveysQuery($sortBy, $sortDir)->chunk(200, function ($rows) use ($handle, $delimiter) {
                foreach ($rows as $survey) {
                    fputcsv($handle, [
                        $survey->id,
                        $survey->member_id,
                        optional($survey->member)->full_name,
                        optional($survey->member)->unique_code,
                        optional($survey->member)->email,
                        $survey->question_1,
                        $survey->question_2,
                        $survey->question_3,
                        $survey->question_4,
                        $survey->question_5,
                        $survey->comments,
                        $survey->additional_data ? json_encode($survey->additional_data) : null,
                        $survey->created_at,
                    ], $delimiter);
                }
            });

            fclose($handle);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => $contentType,
        ]);
    }

    protected function membersQuery(string $sortBy, string $sortDir): Builder
    {
        return Member::query()->orderBy($sortBy, $sortDir);
    }

    protected function resolveMemberSort(Request $request): array
    {
        $sortOptions = $this->memberSortOptions();
        $sortBy = (string) $request->query('sort_by', 'created_at');
        if (! array_key_exists($sortBy, $sortOptions)) {
            $sortBy = 'created_at';
        }

        $sortDir = Str::lower((string) $request->query('sort_dir', 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        return [$sortBy, $sortDir];
    }

    protected function memberSortOptions(): array
    {
        return [
            'created_at' => 'Registration Date',
            'full_name' => 'Name',
            'unique_code' => 'Code',
            'email' => 'Email',
            'county' => 'County',
            'organization' => 'Place of Practice (Institution)',
        ];
    }

    protected function surveysQuery(string $sortBy, string $sortDir): Builder
    {
        $query = Survey::query()->with('member');

        if (in_array($sortBy, ['member_name', 'member_code'], true)) {
            $query->leftJoin('members', 'members.id', '=', 'surveys.member_id')
                ->select('surveys.*');

            if ($sortBy === 'member_name') {
                $query->orderBy('members.full_name', $sortDir);
            } else {
                $query->orderBy('members.unique_code', $sortDir);
            }

            return $query->orderBy('surveys.id', 'desc');
        }

        return $query->orderBy('surveys.' . $sortBy, $sortDir);
    }

    protected function resolveSurveySort(Request $request): array
    {
        $sortOptions = $this->surveySortOptions();
        $sortBy = (string) $request->query('sort_by', 'created_at');
        if (! array_key_exists($sortBy, $sortOptions)) {
            $sortBy = 'created_at';
        }

        $sortDir = Str::lower((string) $request->query('sort_dir', 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        return [$sortBy, $sortDir];
    }

    protected function surveySortOptions(): array
    {
        return [
            'created_at' => 'Submission Date',
            'id' => 'Submission ID',
            'member_name' => 'Member Name',
            'member_code' => 'Member Code',
        ];
    }

    protected function ensureSuperAdmin(): void
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin || $admin->role !== 'super_admin') {
            abort(403);
        }
    }
}
