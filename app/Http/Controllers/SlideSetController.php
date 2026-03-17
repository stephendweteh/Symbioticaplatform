<?php

namespace App\Http\Controllers;

use App\Models\SlideSet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SlideSetController extends Controller
{
    public function index()
    {
        $slideSets = SlideSet::withCount('slides')
            ->orderBy('order_number')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.slide-sets.index', compact('slideSets'));
    }

    public function create()
    {
        return view('admin.slide-sets.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateSlideSet($request);
        SlideSet::create($data);

        return redirect()->route('slide-sets.index')->with('success', 'Experience created.');
    }

    public function edit(SlideSet $slideSet)
    {
        return view('admin.slide-sets.edit', compact('slideSet'));
    }

    public function update(Request $request, SlideSet $slideSet)
    {
        $data = $this->validateSlideSet($request, $slideSet);
        $slideSet->update($data);

        return redirect()->route('slide-sets.index')->with('success', 'Experience updated.');
    }

    public function destroy(SlideSet $slideSet)
    {
        if ($slideSet->slides()->exists()) {
            return redirect()
                ->route('slide-sets.index')
                ->with('error', 'Cannot delete an experience that still contains slides.');
        }

        if ($slideSet->thumbnail_path && is_file(public_path($slideSet->thumbnail_path))) {
            @unlink(public_path($slideSet->thumbnail_path));
        }

        $slideSet->delete();

        return redirect()->route('slide-sets.index')->with('success', 'Experience deleted.');
    }

    protected function validateSlideSet(Request $request, ?SlideSet $existing = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'order_number' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $thumbnailPath = $existing?->thumbnail_path;
        if ($request->hasFile('thumbnail_file')) {
            $uploadDir = public_path('uploads/slide-set-thumbnails');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploaded = $request->file('thumbnail_file');
            $fileName = Str::uuid()->toString() . '.' . $uploaded->getClientOriginalExtension();
            $uploaded->move($uploadDir, $fileName);
            $thumbnailPath = 'uploads/slide-set-thumbnails/' . $fileName;

            if ($existing?->thumbnail_path && is_file(public_path($existing->thumbnail_path))) {
                @unlink(public_path($existing->thumbnail_path));
            }
        }

        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'thumbnail_path' => $thumbnailPath,
            'order_number' => $validated['order_number'],
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

