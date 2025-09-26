<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SubmitExamRequest extends FormRequest
{
     /**
      * Determine if the user is authorized to make this request.
      */
     public function authorize(): bool
     {
          return true;
     }

     /**
      * Get the validation rules that apply to the request.
      *
      * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
      */
     public function rules(): array
     {
          return [
               'session_token' => 'required|string|min:10|max:255',
               'answers' => 'nullable|array',
               'answers.*' => 'string|max:10', // For multiple choice answers (A, B, C, D, etc.)
               'essay_answers' => 'nullable|array',
               'essay_answers.*' => 'string|max:5000', // For essay answers
               'force_submit' => 'boolean',
               'final_submit' => 'boolean' // To differentiate between auto-save and final submit
          ];
     }

     /**
      * Get custom error messages for validator errors.
      *
      * @return array<string, string>
      */
     public function messages(): array
     {
          return [
               'session_token.required' => 'Token sesi ujian diperlukan',
               'session_token.string' => 'Token sesi ujian harus berupa string',
               'session_token.min' => 'Token sesi ujian tidak valid',
               'session_token.max' => 'Token sesi ujian terlalu panjang',
               'answers.array' => 'Format jawaban tidak valid',
               'answers.*.string' => 'Jawaban harus berupa string',
               'answers.*.max' => 'Jawaban pilihan ganda terlalu panjang',
               'essay_answers.array' => 'Format jawaban essay tidak valid',
               'essay_answers.*.string' => 'Jawaban essay harus berupa string',
               'essay_answers.*.max' => 'Jawaban essay terlalu panjang (maksimal 5000 karakter)',
               'force_submit.boolean' => 'Parameter force_submit harus berupa boolean',
               'final_submit.boolean' => 'Parameter final_submit harus berupa boolean'
          ];
     }

     /**
      * Handle a failed validation attempt.
      *
      * @param  \Illuminate\Contracts\Validation\Validator  $validator
      * @return void
      *
      * @throws \Illuminate\Http\Exceptions\HttpResponseException
      */
     protected function failedValidation(Validator $validator)
     {
          throw new HttpResponseException(
               response()->json([
                    'success' => false,
                    'message' => 'Data yang dikirim tidak valid',
                    'errors' => $validator->errors()
               ], 422)
          );
     }

     /**
      * Get custom attributes for validator errors.
      *
      * @return array<string, string>
      */
     public function attributes(): array
     {
          return [
               'session_token' => 'token sesi ujian',
               'answers' => 'jawaban pilihan ganda',
               'essay_answers' => 'jawaban essay',
               'force_submit' => 'paksa submit',
               'final_submit' => 'submit final'
          ];
     }
}
