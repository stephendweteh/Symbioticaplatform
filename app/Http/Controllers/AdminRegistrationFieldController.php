<?php

namespace App\Http\Controllers;

use App\Models\RegistrationField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminRegistrationFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fields = RegistrationField::orderBy('sort_order')->orderBy('id')->paginate(20);

        return view('admin.registration-fields.index', compact('fields'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.registration-fields.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateField($request);
        $data['is_system'] = false;

        RegistrationField::create($data);

        return redirect()->route('registration-fields.index')->with('success', 'Registration field created.');
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
    public function edit(RegistrationField $registration_field)
    {
        return view('admin.registration-fields.edit', ['field' => $registration_field]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RegistrationField $registration_field)
    {
        $data = $this->validateField($request, $registration_field->id);

        $registration_field->update($data);

        return redirect()->route('registration-fields.index')->with('success', 'Registration field updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegistrationField $registration_field)
    {
        $registration_field->delete();

        return redirect()->route('registration-fields.index')->with('success', 'Registration field deleted.');
    }

    protected function validateField(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'field_key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('registration_fields', 'field_key')->ignore($ignoreId),
            ],
            'field_type' => ['required', 'in:text,email,tel,number,date,textarea,select,consent'],
            'options_input' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $options = null;
        if ($validated['field_type'] === 'select') {
            $rawOptions = preg_split('/\r\n|\r|\n/', (string) ($validated['options_input'] ?? ''));
            $options = array_values(array_filter(array_map(fn ($opt) => trim($opt), $rawOptions)));
        } elseif ($validated['field_type'] === 'consent') {
            // Store full consent text as a single option entry; label becomes checkbox label.
            $text = trim((string) ($validated['options_input'] ?? ''));
            $options = $text !== '' ? [$text] : null;
        }

        return [
            'label' => $validated['label'],
            'field_key' => Str::snake($validated['field_key']),
            'field_type' => $validated['field_type'],
            'options' => $options,
            'sort_order' => $validated['sort_order'],
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

}
