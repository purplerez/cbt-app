<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Preassigned;
use App\Models\User;
use Illuminate\Http\Request;

class KepalaExamApiController extends Controller
{
    /**
     * Return students for current kepala's school with registration flag for exam_id
     */
    public function participants($examId, Request $request)
    {
        // Prefer deriving school from authenticated user (stateless clients), fallback to session
        $schoolId = null;
        $user = $request->user();
        if ($user && method_exists($user, 'student') && $user->student) {
            $schoolId = $user->student->school_id;
        }
        if (!$schoolId) {
            $schoolId = session('school_id');
        }
        if (!$schoolId) {
            return response()->json(['error' => 'school not found in session or user'], 400);
        }

        $users = User::with('student')
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->get();

        // Pre-load all preassigned assignments for this exam to avoid N+1 queries
        $registeredUserIds = Preassigned::where('exam_id', $examId)
            ->pluck('user_id')
            ->toArray();
        $registeredSet = array_flip($registeredUserIds); // O(1) lookup

        $data = $users->map(function ($u) use ($registeredSet) {
            return [
                'id' => $u->id,
                'name' => $u->student->name ?? $u->name,
                'registered' => isset($registeredSet[$u->id]),
            ];
        });

        return response()->json(['students' => $data]);
    }
}
