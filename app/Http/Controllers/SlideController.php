<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use App\Models\SlideSet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SlideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $slides = Slide::with('slideSet')
            ->orderBy('order_number')
            ->paginate(20);

        return view('admin.slides.index', compact('slides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $slideSets = SlideSet::orderBy('order_number')->orderBy('id')->get();

        return view('admin.slides.create', compact('slideSets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'slide_set_id' => ['required', 'integer', 'exists:slide_sets,id'],
            'title' => ['required', 'string', 'max:255'],
            'image_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'description' => ['nullable', 'string'],
            'order_number' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['image_path'] = $this->storeSlideImage($request);
        unset($data['image_file']);

        Slide::create($data);

        return redirect()->route('slides.index')->with('success', 'Slide created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slide $slide)
    {
        $slideSets = SlideSet::orderBy('order_number')->orderBy('id')->get();

        return view('admin.slides.edit', compact('slide', 'slideSets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Slide $slide)
    {
        $data = $request->validate([
            'slide_set_id' => ['required', 'integer', 'exists:slide_sets,id'],
            'title' => ['required', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'description' => ['nullable', 'string'],
            'order_number' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if ($request->hasFile('image_file')) {
            if ($slide->image_path && is_file(public_path($slide->image_path))) {
                @unlink(public_path($slide->image_path));
            }
            $data['image_path'] = $this->storeSlideImage($request);
        }
        unset($data['image_file']);

        $slide->update($data);

        return redirect()->route('slides.index')->with('success', 'Slide updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slide $slide)
    {
        if ($slide->image_path && is_file(public_path($slide->image_path))) {
            @unlink(public_path($slide->image_path));
        }

        $slide->delete();

        return redirect()->route('slides.index')->with('success', 'Slide deleted.');
    }

    protected function storeSlideImage(Request $request): string
    {
        $uploadDir = public_path('uploads/slides');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploaded = $request->file('image_file');
        $fileName = Str::uuid()->toString() . '.' . $uploaded->getClientOriginalExtension();
        $uploaded->move($uploadDir, $fileName);

        return 'uploads/slides/' . $fileName;
    }
}
