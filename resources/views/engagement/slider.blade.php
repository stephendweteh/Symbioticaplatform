@extends('layouts.app')

@section('title', 'Engagement Slider')
@section('body_class', 'min-h-screen bg-gradient-to-b from-violet-500 to-white py-10')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div x-data="engagementSlider({{ $member->id }}, {{ $slides->count() }}, @js($nextSet?->id), @js(route('engagement.sets', $member)), @js($nextSet ? route('engagement.start-set', ['member' => $member->id, 'slideSet' => $nextSet->id]) : null))"
         class="max-w-[1200px] w-full bg-white rounded-2xl shadow-xl p-8 mx-4">
        <div class="flex justify-between items-center mb-4 text-sm text-slate-600">
            <div>
                @if(isset($slideSet))
                    <p class="text-xs uppercase tracking-wide text-violet-700 font-semibold">{{ $slideSet->title }}</p>
                @endif
                <p class="font-semibold text-slate-900">{{ $member->full_name }}</p>
                <p>Code: <span class="font-mono">{{ $member->unique_code }}</span></p>
            </div>
            <div>
                <p>Progress: <span id="progressText">0%</span></p>
                <p>Status: <span id="statusText" class="font-medium">Pending</span></p>
                <button type="button"
                        @click="endPresentation"
                        x-show="totalSlides > 0"
                        class="mt-2 inline-flex items-center justify-center rounded-lg border border-amber-500 bg-amber-500 text-white py-1.5 px-3 text-xs font-medium shadow-sm hover:bg-amber-600 hover:border-amber-600">
                    End Presentation
                </button>
            </div>
        </div>

        <div class="space-y-4">
            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 h-[900px] max-h-[90vh] flex items-center justify-center"
                 @touchstart.passive="touchStart($event)"
                 @touchend.passive="touchEnd($event)">
                @if($slides->isEmpty())
                    <p class="text-slate-500 text-center px-4">
                        No slides configured yet. Please ask an admin to add slides.
                    </p>
                @else
                    @foreach($slides as $index => $slide)
                        <div x-show="currentIndex === {{ $index }}" class="text-center px-8">
                            <h2 class="text-xl font-semibold text-slate-900 mb-4">{{ $slide->title }}</h2>
                            @if($slide->image_path)
                                <img src="{{ asset($slide->image_path) }}" alt="{{ $slide->title }}"
                                     class="mx-auto mb-4 max-w-[1200px] w-full max-h-[780px] object-contain">
                            @endif
                            @if($slide->description)
                                <p class="text-slate-600">{{ $slide->description }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            @if($slides->isNotEmpty())
                <p class="text-center text-xs text-slate-500">
                    Swipe left/right on the slide to navigate.
                </p>
            @endif

            <div class="flex items-center justify-between">
                <button type="button"
                        @click="prev"
                        :disabled="currentIndex === 0"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 py-2 px-4 text-sm font-medium shadow-sm disabled:opacity-40 disabled:cursor-not-allowed">
                    Previous
                </button>

                <div class="text-sm text-slate-500">
                    Slide <span x-text="currentIndex + 1"></span> of {{ $slides->count() }}
                </div>

                <button type="button"
                        @click="next"
                        x-show="currentIndex < totalSlides - 1"
                        class="inline-flex items-center justify-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-6 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600">
                    Next
                </button>

                <button type="button"
                        @click="finish"
                        x-show="totalSlides > 0 && currentIndex === totalSlides - 1 && !showCompletionActions"
                        class="inline-flex items-center justify-center rounded-xl border border-emerald-500 bg-emerald-500 text-white py-2 px-6 text-sm font-medium shadow-sm hover:bg-emerald-600 hover:border-emerald-600">
                    Finish
                </button>
            </div>

            <div x-show="showCompletionActions"
                 x-cloak
                 class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm font-medium text-emerald-800 mb-3">
                    This experience is completed. What would you like to do next?
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="goToNextSet"
                            x-show="nextSetId"
                            class="inline-flex items-center justify-center rounded-xl border border-violet-500 bg-violet-500 text-white py-2 px-4 text-sm font-medium shadow-sm hover:bg-violet-600 hover:border-violet-600">
                        View Next Experience
                    </button>
                    <button type="button"
                            @click="goToSetSelection"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 py-2 px-4 text-sm font-medium shadow-sm hover:bg-slate-50">
                        Choose Another Experience
                    </button>
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-700 py-2 px-4 text-sm font-medium shadow-sm hover:bg-slate-50">
                        Back Home
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function engagementSlider(memberId, totalSlides, nextSetId, setSelectionUrl, nextSetUrl) {
        return {
            memberId,
            totalSlides,
            nextSetId,
            setSelectionUrl,
            nextSetUrl,
            currentIndex: 0,
            showCompletionActions: false,
            touchXStart: 0,
            touchXEnd: 0,
            async update(endedEarly = false) {
                try {
                    const response = await fetch('{{ route('engagement.progress') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            member_id: this.memberId,
                            current_index: this.currentIndex,
                            total_slides: this.totalSlides,
                            ended_early: endedEarly,
                        }),
                    });

                    if (!response.ok) return false;

                    const data = await response.json();
                    document.getElementById('progressText').textContent = data.completion_percentage + '%';
                    document.getElementById('statusText').textContent = data.status === 'completed'
                        ? 'Completed (5 stars)'
                        : (data.status === 'partial' ? 'Partially Done' : 'Pending');
                    return true;
                } catch (e) {
                    console.error(e);
                    return false;
                }
            },
            next() {
                this.showCompletionActions = false;
                if (this.currentIndex < this.totalSlides - 1) {
                    this.currentIndex++;
                    this.update(false);
                }
            },
            prev() {
                this.showCompletionActions = false;
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    this.update(false);
                }
            },
            async finish() {
                await this.update(false);
                this.showCompletionActions = true;
            },
            async endPresentation() {
                await this.update(true);
                window.location.href = '{{ route('home') }}';
            },
            goToSetSelection() {
                window.location.href = this.setSelectionUrl;
            },
            goToNextSet() {
                if (this.nextSetUrl) {
                    window.location.href = this.nextSetUrl;
                } else {
                    this.goToSetSelection();
                }
            },
            touchStart(event) {
                this.touchXStart = event.changedTouches[0].screenX;
            },
            touchEnd(event) {
                this.touchXEnd = event.changedTouches[0].screenX;
                const swipeDistance = this.touchXEnd - this.touchXStart;

                if (swipeDistance <= -50) {
                    this.next();
                } else if (swipeDistance >= 50) {
                    this.prev();
                }
            },
            init() {
                if (this.totalSlides > 0) {
                    this.update(false);
                }
            }
        };
    }
</script>
@endpush

