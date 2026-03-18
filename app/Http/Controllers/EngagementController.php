<?php

namespace App\Http\Controllers;

use App\Models\Engagement;
use App\Models\Member;
use App\Models\Slide;
use App\Models\SlideSet;
use App\Models\SlideSubcategory;
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

        return $this->renderSetSelection($member);
    }

    public function showSets(Member $member)
    {
        return $this->renderSetSelection($member);
    }

    public function showSubcategories(Member $member, SlideSet $slideSet)
    {
        if (! $slideSet->is_active) {
            return redirect()
                ->route('engagement.sets', $member)
                ->withErrors(['slide_set' => 'Selected experience is inactive.']);
        }

        $subcategories = SlideSubcategory::query()
            ->where('slide_set_id', $slideSet->id)
            ->where('is_active', true)
            ->whereHas('slides', fn ($query) => $query->where('is_active', true))
            ->withCount([
                'slides as active_slides_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('order_number')
            ->orderBy('id')
            ->get();

        if ($subcategories->isEmpty()) {
            return redirect()
                ->route('engagement.sets', $member)
                ->withErrors(['slide_set' => 'Selected experience has no active sub categories with slides.']);
        }

        return view('engagement.select-subcategory', [
            'member' => $member,
            'slideSet' => $slideSet,
            'subcategories' => $subcategories,
        ]);
    }

    public function startSubcategory(Member $member, SlideSet $slideSet, SlideSubcategory $slideSubcategory)
    {
        $resolvedSlideSet = $slideSubcategory->slideSet ?: $slideSet;

        if (! $slideSubcategory->is_active) {
            // Recover from inactive links by redirecting to the first valid
            // active subcategory under the resolved experience.
            $fallbackSubcategory = SlideSubcategory::query()
                ->where('slide_set_id', $resolvedSlideSet->id)
                ->where('is_active', true)
                ->whereHas('slides', fn ($query) => $query->where('is_active', true))
                ->orderBy('order_number')
                ->orderBy('id')
                ->first();

            if ($fallbackSubcategory) {
                return redirect()->route('engagement.start-subcategory', [
                    'member' => $member->id,
                    'slideSet' => $resolvedSlideSet->id,
                    'slideSubcategory' => $fallbackSubcategory->id,
                ]);
            }

            return redirect()
                ->route('engagement.subcategories', [$member, $resolvedSlideSet])
                ->withErrors(['slide_set' => 'No active sub category is currently available for this experience.']);
        }

        $slides = Slide::query()
            ->where('slide_subcategory_id', $slideSubcategory->id)
            ->where('is_active', true)
            ->orderBy('order_number')
            ->get();

        if ($slides->isEmpty()) {
            return redirect()
                ->route('engagement.subcategories', [$member, $resolvedSlideSet])
                ->withErrors(['slide_set' => 'Selected sub category has no active slides.']);
        }

        $nextSubcategory = SlideSubcategory::query()
            ->where('slide_set_id', $resolvedSlideSet->id)
            ->where('is_active', true)
            ->where('order_number', '>', $slideSubcategory->order_number)
            ->whereHas('slides', fn ($query) => $query->where('is_active', true))
            ->orderBy('order_number')
            ->orderBy('id')
            ->first();

        $engagement = Engagement::firstOrNew(['member_id' => $member->id]);
        if (! $engagement->exists) {
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
            'slideSet' => $resolvedSlideSet,
            'slideSubcategory' => $slideSubcategory,
            'nextSubcategory' => $nextSubcategory,
            'subcategorySelectionUrl' => route('engagement.subcategories', [$member, $resolvedSlideSet]),
            'experienceSelectionUrl' => route('engagement.sets', $member),
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

    protected function renderSetSelection(Member $member)
    {
        $slideSets = SlideSet::query()
            ->where('is_active', true)
            ->whereHas('subcategories', function ($query) {
                $query->where('is_active', true)
                    ->whereHas('slides', fn ($slides) => $slides->where('is_active', true));
            })
            ->withCount([
                'slides as active_slides_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('order_number')
            ->orderBy('id')
            ->get();

        return view('engagement.select-set', [
            'member' => $member,
            'slideSets' => $slideSets,
        ]);
    }
}
