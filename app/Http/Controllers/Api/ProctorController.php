<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProctorController extends Controller
{
    public function reactivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
            'nis' => 'nullable|string',
            'exam_id' => 'nullable|integer',
            'session_token' => 'nullable|string',
        ]);

        if (empty($validated['user_id']) && empty($validated['student_id']) && empty($validated['nis'])) {
            return response()->json([
                'success' => false,
                'message' => 'user_id, student_id, atau nis wajib diisi.',
            ], 422);
        }

        $student = null;

        if (!empty($validated['user_id'])) {
            $student = Student::where('user_id', $validated['user_id'])->first();
        } elseif (!empty($validated['student_id'])) {
            $student = Student::whereKey($validated['student_id'])->first();
        } else {
            $student = Student::where('nis', $validated['nis'])->first();
        }

        if (!$student || !$student->user) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        $user = $student->user;
        $user->is_active = true;
        $user->is_logout = false;
        $user->save();

        $query = ExamSession::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'process', 'progress']);

        if (!empty($validated['exam_id'])) {
            $query->where('exam_id', $validated['exam_id']);
        }

        if (!empty($validated['session_token'])) {
            $query->where('session_token', $validated['session_token']);
        }

        $session = $query->latest('id')->first();

        Log::info('Student reactivated by proctor', [
            'actor_id' => $request->user()->id,
            'student_user_id' => $user->id,
            'student_nis' => $student->nis,
            'session_id' => $session?->id,
            'exam_id' => $validated['exam_id'] ?? $session?->exam_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil direaktivasi.',
            'data' => [
                'user_id' => $user->id,
                'student_id' => $student->id,
                'nis' => $student->nis,
                'is_active' => true,
                'is_logout' => false,
                'session_id' => $session?->id,
                'exam_id' => $session?->exam_id ?? $validated['exam_id'] ?? null,
            ],
        ]);
    }
}
