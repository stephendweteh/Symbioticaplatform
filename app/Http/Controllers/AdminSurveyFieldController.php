<?php

namespace App\Http\Controllers;

use App\Models\SurveyField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminSurveyFieldController extends Controller
{
    public function index()
    {
        $fields = SurveyField::orderBy('sort_order')->orderBy('id')->paginate(20);

        return view('admin.survey-fields.index', compact('fields'));
    }

    public function create()
    {
        return view('admin.survey-fields.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateField($request);
        SurveyField::create($data);

        return redirect()->route('survey-fields.index')->with('success', 'Survey field created.');
    }

    public function edit(SurveyField $survey_field)
    {
        return view('admin.survey-fields.edit', ['field' => $survey_field]);
    }

    public function update(Request $request, SurveyField $survey_field)
    {
        $data = $this->validateField($request, $survey_field->id);
        $survey_field->update($data);

        return redirect()->route('survey-fields.index')->with('success', 'Survey field updated.');
    }

    public function destroy(SurveyField $survey_field)
    {
        $survey_field->delete();

        return redirect()->route('survey-fields.index')->with('success', 'Survey field deleted.');
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
                Rule::unique('survey_fields', 'field_key')->ignore($ignoreId),
            ],
            'field_type' => ['required', 'in:text,email,tel,number,date,textarea,select'],
            'options_input' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $options = null;
        if ($validated['field_type'] === 'select') {
            $rawOptions = preg_split('/\r\n|\r|\n/', (string) ($validated['options_input'] ?? ''));
            $options = array_values(array_filter(array_map(fn ($option) => trim($option), $rawOptions)));
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
