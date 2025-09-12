type Role = 'admin' | 'kepala' | 'guru' | 'siswa';

type Gender = 'L' | 'P';

type SchoolStatus = 'active' | 'inactive';

export interface User {
     id: number;
     name: string;
     email: string;
     token: string;
     role: Role;
     is_active: boolean;
}

export interface School {
     id: number;
     name: string;
     npsn: string;
     address: string;
     phone: string;
     email: string;
     code: string;
     logo: string;
     status: SchoolStatus;
}

export interface AssignedExam {
     exam_id: number;
     title: string;
     duration: number;
     total_quest: number;
}

export interface Student {
     id: number;
     user_id: number;
     school_id: number;
     nis: string;
     name: string;
     gender: Gender;
     grade_id: number;
     p_birth: string;
     d_birth: string;
     address: string;
     photo: string;
     user: User;
     school: School;
}

export interface Question {
     id: number;
     exam_id: number;
     question_type_id: string; // "0": Multiple Choice Complex, "1": Multiple Choice Single, "2": Essay, etc.
     question_text: string;
     choices: string; // JSON string: {"A": "option A", "B": "option B", ...}
     answer_key: string; // JSON string array: "[\"A\",\"C\"]" for multiple choice
     points: string; // String format: "15.00"
     created_by: number; // User ID
     created_at: string;
     updated_at: string;
}

export interface ParsedQuestion {
     id: number;
     exam_id: number;
     question_type_id: string;
     question_text: string;
     choices: Record<string, string>; // Parsed choices object
     answer_key: string[]; // Parsed answer key array
     points: number; // Parsed points as number
     created_by: number;
     created_at: string;
     updated_at: string;
}

export interface ExamSession {
     id: number;
     student_id: number;
     exam_id: number;
     start_time: string;
     end_time?: string;
     remaining_time: number; // in seconds
     status: 'active' | 'completed' | 'expired';
}

export interface StudentAnswer {
     question_id: number;
     answer: string | string[]; // Can be single answer or multiple for complex questions
     is_flagged?: boolean; // For marking questions for review
}

export interface DashboardExam {
     success: boolean;
     student: Student;
     assigned: AssignedExam[];
}

export interface Exam {
     success: boolean;
     exam: Question[];
}

export interface ExamData {
     success: boolean;
     exam: ParsedQuestion[];
     session: ExamSession;
}

export interface ApiResponse<T> {
     success: boolean;
     data: T;
     message?: string;
}
