<?php

namespace App\Http\Controllers;

use App\Models\SlideSet;
use App\Models\SlideSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SlideSubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = SlideSubcategory::with(['slideSet'])
            ->withCount('slides')
            ->orderBy('order_number')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.slide-subcategories.index', compact('subcategories'));
    }

    public function create()
    {
        $slideSets = SlideSet::orderBy('order_number')->orderBy('id')->get();

        return view('admin.slide-subcategories.create', compact('slideSets'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSubcategory($request);
        SlideSubcategory::create($data);

        return redirect()->route('slide-subcategories.index')->with('success', 'Sub category created.');
    }

    public function edit(SlideSubcategory $slide_subcategory)
    {
        $slideSets = SlideSet::orderBy('order_number')->orderBy('id')->get();

        return view('admin.slide-subcategories.edit', [
            'subcategory' => $slide_subcategory,
            'slideSets' => $slideSets,
        ]);
    }

    public function update(Request $request, SlideSubcategory $slide_subcategory)
    {
        $data = $this->validateSubcategory($request, $slide_subcategory);
        $slide_subcategory->update($data);

        return redirect()->route('slide-subcategories.index')->with('success', 'Sub category updated.');
    }

    public function destroy(SlideSubcategory $slide_subcategory)
    {
        if ($slide_subcategory->slides()->exists()) {
            return redirect()
                ->route('slide-subcategories.index')
                ->with('error', 'Cannot delete a sub category that still contains slides.');
        }

        if ($slide_subcategory->thumbnail_path && is_file(public_path($slide_subcategory->thumbnail_path))) {
            @unlink(public_path($slide_subcategory->thumbnail_path));
        }

        $slide_subcategory->delete();

        return redirect()->route('slide-subcategories.index')->with('success', 'Sub category deleted.');
    }

    protected function validateSubcategory(Request $request, ?SlideSubcategory $existing = null): array
    {
        $validated = $request->validate([
            'slide_set_id' => ['required', 'integer', 'exists:slide_sets,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'order_number' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $thumbnailPath = $existing?->thumbnail_path;
        if ($request->hasFile('thumbnail_file')) {
            $uploadDir = public_path('uploads/slide-subcategory-thumbnails');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploaded = $request->file('thumbnail_file');
            $fileName = Str::uuid()->toString() . '.' . $uploaded->getClientOriginalExtension();
            $uploaded->move($uploadDir, $fileName);
            $thumbnailPath = 'uploads/slide-subcategory-thumbnails/' . $fileName;

            if ($existing?->thumbnail_path && is_file(public_path($existing->thumbnail_path))) {
                @unlink(public_path($existing->thumbnail_path));
            }
        }

        return [
            'slide_set_id' => $validated['slide_set_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'thumbnail_path' => $thumbnailPath,
            'order_number' => $validated['order_number'],
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

