export interface User {
     id: string;
     name: string;
     email: string;
     role: 'student' | 'teacher' | 'admin';
}

export interface Exam {
     id: string;
     title: string;
     description: string;
     duration: number; // in minutes
     totalQuestions: number;
     startTime: string;
     endTime: string;
     status: 'upcoming' | 'active' | 'completed';
}

export interface Question {
     id: string;
     examId: string;
     question: string;
     type: 'multiple_choice' | 'essay';
     options?: string[];
     correctAnswer?: string;
     points: number;
}

export interface ExamResult {
     id: string;
     examId: string;
     userId: string;
     score: number;
     totalScore: number;
     answers: Record<string, string>;
     submittedAt: string;
}

export interface ApiResponse<T> {
     success: boolean;
     data: T;
     message?: string;
}
