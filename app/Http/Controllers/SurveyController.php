<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Survey;
use App\Models\SurveyField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    public function showForm()
    {
        $surveyFields = SurveyField::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('survey.index', compact('surveyFields'));
    }

    public function store(Request $request)
    {
        $surveyFields = SurveyField::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $rules = [
            'code' => ['required', 'string', 'size:4'],
        ];

        foreach ($surveyFields as $field) {
            $fieldRules = [$field->is_required ? 'required' : 'nullable'];
            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:255';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'select':
                    $fieldRules[] = Rule::in($field->options ?? []);
                    break;
                default:
                    $fieldRules[] = 'string';
                    break;
            }

            $rules['responses.' . $field->field_key] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $member = Member::where('unique_code', $validated['code'])->first();

        if (! $member) {
            return back()
                ->withErrors(['code' => 'Invalid 4-digit code'])
                ->withInput();
        }

        $responses = $validated['responses'] ?? [];
        $legacyKeys = ['question_1', 'question_2', 'question_3', 'question_4', 'question_5', 'comments'];
        $additionalData = array_diff_key($responses, array_flip($legacyKeys));

        Survey::create([
            'member_id' => $member->id,
            'question_1' => $responses['question_1'] ?? null,
            'question_2' => $responses['question_2'] ?? null,
            'question_3' => $responses['question_3'] ?? null,
            'question_4' => $responses['question_4'] ?? null,
            'question_5' => $responses['question_5'] ?? null,
            'comments' => $responses['comments'] ?? null,
            'additional_data' => ! empty($additionalData) ? $additionalData : null,
        ]);

        return redirect()
            ->route('survey.index')
            ->with('success', 'Thank you for completing the survey.');
    }
}
