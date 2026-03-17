<?php

namespace App\Http\Controllers;

use App\Models\Engagement;
use App\Models\Member;
use App\Models\Slide;
use Illuminate\Http\Request;

class EngagementController extends Controller
{
    public function showForm()
    {
        return view('engagement.index');
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:4'],
        ]);

        $member = Member::where('unique_code', $validated['code'])->first();

        if (! $member) {
            return back()
                ->withErrors(['code' => 'Invalid 4-digit code'])
                ->withInput();
        }

        $slides = Slide::where('is_active', true)
            ->orderBy('order_number')
            ->get();

        $engagement = Engagement::firstOrNew(['member_id' => $member->id]);
        if (!$engagement->exists) {
            $engagement->started_at = now();
            $engagement->status = 'pending';
            $engagement->slides_viewed = 0;
            $engagement->completion_percentage = 0;
            $engagement->star_rating = 0;
        }
        $engagement->total_slides = $slides->count();
        $engagement->save();

        return view('engagement.slider', [
            'member' => $member,
            'engagement' => $engagement,
            'slides' => $slides,
        ]);
    }

    public function updateProgress(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'current_index' => ['required', 'integer', 'min:0'],
            'total_slides' => ['required', 'integer', 'min:0'],
            'ended_early' => ['nullable', 'boolean'],
        ]);

        $engagement = Engagement::firstOrCreate(
            ['member_id' => $validated['member_id']],
            [
                'started_at' => now(),
                'total_slides' => $validated['total_slides'],
                'status' => 'pending',
            ]
        );

        if (is_null($engagement->started_at)) {
            $engagement->started_at = now();
        }

        $slidesViewed = max($engagement->slides_viewed, $validated['current_index'] + 1);
        $completion = $validated['total_slides'] > 0
            ? round(($slidesViewed / $validated['total_slides']) * 100, 2)
            : 0;
        $endedEarly = (bool) ($validated['ended_early'] ?? false);
        $alreadyCompleted = $engagement->status === 'completed';

        $status = 'pending';
        $stars = 0;

        if ($alreadyCompleted) {
            $status = 'completed';
            $stars = 5;
            $engagement->completed_at ??= now();
        } elseif ($endedEarly) {
            if ($slidesViewed > 0) {
                $status = 'partial';
                $completion = min($completion, 99.99);
            }
        } elseif ($completion >= 100 && $validated['current_index'] + 1 >= $validated['total_slides']) {
            $status = 'completed';
            $stars = 5;
            $engagement->completed_at ??= now();
        } elseif ($slidesViewed > 0) {
            $status = 'partial';
        }

        $engagement->fill([
            'slides_viewed' => $slidesViewed,
            'total_slides' => $validated['total_slides'],
            'completion_percentage' => $completion,
            'status' => $status,
            'star_rating' => $stars,
        ])->save();

        return response()->json([
            'status' => $status,
            'completion_percentage' => $completion,
            'star_rating' => $stars,
        ]);
    }
}
