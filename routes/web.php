<?php

use App\Http\Controllers\Admin\DashboardController as AdminController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\Guru\DashboardController as GuruController;
use App\Http\Controllers\Kepala\DashboardController as KepalaController;
use App\Http\Controllers\Kepala\KepalaExamController;
use App\Http\Controllers\Siswa\DashboardController as SiswaController;
use App\Http\Controllers\Super\DashboardController as SuperController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\BeritaAcaraController;
use App\Http\Controllers\RoomAssignmentController;
use App\Models\School;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// all users page
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// super admin page
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Routing for schools management
    Route::get('schools', [SchoolController::class, 'index'])->name('schools');
    Route::get('inputsekolah', function () {
        return view('admin.inputsekolah');
    })->name('inputsekolah');
    Route::post('inputsekolah', [SchoolController::class, 'store'])->name('sekolahstore');
    Route::get('schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
    Route::put('schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
    Route::delete('schools/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
    Route::post('schools/{school}/manage', [SchoolController::class, 'manage'])->name('schools.manage');
    Route::get('schools/{school}/manage', [SchoolController::class, 'manageView'])->name('schools.manage.view');
    Route::post('schools/nonaktif', [SchoolController::class, 'inactive'])->name('school.inactive');
    Route::get('schools/{school}/active', [SchoolController::class, 'active'])->name('school.active');


    // Routing for student management
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::post('students/update', [StudentController::class, 'update'])->name('students.update');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('siswa.destroy');

    // Routing for teacher management
    Route::post('teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::post('teachers/update', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teacher.destroy');

    // Routing for headmaster management
    Route::post('headmasters', [TeacherController::class, 'storeHeadmaster'])->name('head.store');
    Route::get('headmasters/{headmaster}/edit', [TeacherController::class, 'editHeadmaster'])->name('head.edit');
    Route::post('headmasters/update', [TeacherController::class, 'updateHeadmaster'])->name('head.update');
    Route::delete('headmasters/{headmaster}', [TeacherController::class, 'destroyHeadmaster'])->name('head.destroy');


    // Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('siswa.destroy');
    // Route::post('students/update', [StudentController::class, 'update'])->name('students.update');


    // Routing for grade management
    Route::get('grades', [GradeController::class, 'index'])->name('grades');
    Route::get('grades/create', function () {
        return view('admin.inputgrades');
    })->name('input.grades');
    Route::post('grades/create', [GradeController::class, 'store'])->name('grades.store');
    Route::get('grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
    Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

    // routing for subjects management
    // Route::get('subjects', [SubjectController::class, 'index'])->name('subjects');
    // Route::get('subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
    Route::post('subjects/create', [SubjectController::class, 'store'])->name('subjects.store');
    Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    // Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::post('subjects/update', [SubjectController::class, 'update'])->name('subjects.update');

    // Routing for question types management
    Route::get('question/type', [QuestionController::class, 'typeindex'])->name('questions.types');
    Route::get('question/type/create', function () {
        return view('admin.inputquestiontype');
    })->name('question.type.create');
    Route::post('question/type/create', [QuestionController::class, 'typestore'])->name('question.type.store');
    Route::get('question/type/{type}/edit', [QuestionController::class, 'typeedit'])->name('question.type.edit');
    Route::put('question/type/{type}/update', [QuestionController::class, 'typeupdate'])->name('question.type.update');
    Route::delete('question/type/{type}/destroy', [QuestionController::class, 'typedestroy'])->name('question.type.destroy');

    Route::get('exams', [ExamController::class, 'index'])->name('exams');
    Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('exams/create', [ExamController::class, 'globalstore'])->name('examsglobal.store');
    Route::post('exams/{exam}/manage', [ExamController::class, 'manage'])->name('exams.manage');
    Route::get('exams/{exam}/manage', [ExamController::class, 'manageView'])->name('exams.manage.view');
    Route::post('exams/exam/store', [ExamController::class, 'examstore'])->name('exam.store');


    Route::post('exams/banksoal/{exam}/manage', [ExamController::class, 'examquestion'])->name('exams.question');
    Route::get('exams/banksoal/{exam}/manage', [ExamController::class, 'banksoal'])->name('exams.manage.question');

    Route::post('exams/banksoal/{exam}/store', [QuestionController::class, 'store'])->name('exams.question.store');
    Route::put('exams/banksoal/{exam}/update', [QuestionController::class, 'update'])->name('exams.question.update');
    Route::delete('exams/banksoal/{exam}/destroy', [QuestionController::class, 'destroy'])->name('exams.questions.destroy');
    Route::get('exams/banksoal/exit', [ExamController::class, 'exitbanksoal'])->name('exams.question.exit');





    // Route::get('students', [SchoolController::class, 'students'])->name('students');

    Route::get('questions', [SchoolController::class, 'questions'])->name('questions');
    Route::get('results', [SchoolController::class, 'results'])->name('results');
    Route::get('settings', [SchoolController::class, 'settings'])->name('settings');

    // Berita Acara routes (View and Export only for Admin)
    Route::get('berita-acara', [BeritaAcaraController::class, 'index'])->name('berita-acara.index');
    Route::get('berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'show'])->name('berita-acara.show');
    Route::get('berita-acara/{beritaAcara}/pdf', [BeritaAcaraController::class, 'exportPdf'])->name('berita-acara.pdf');
    Route::get('berita-acara/{beritaAcara}/student-list', [BeritaAcaraController::class, 'printStudentList'])->name('berita-acara.student-list');
});

Route::middleware(['auth', 'role:kepala'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/dashboard', [KepalaController::class, 'index'])->name('dashboard');

    // school data
    Route::get('/school', [KepalaController::class, 'school'])->name('school');
    Route::get('/school/{school}/edit', [KepalaController::class, 'editSchool'])->name('school.edit');
    Route::put('/school/edit', [KepalaController::class, 'updateSchool'])->name('school.update');


    Route::get('/students', [KepalaController::class, 'studentAll'])->name('students');
    Route::get('/students/create', [KepalaController::class, 'createStudent'])->name('student.create');
    Route::post('/students/create', [KepalaController::class, 'storeStudent'])->name('student.store');
    Route::get('/students/{student}/edit', [KepalaController::class, 'editStudent'])->name('student.edit');
    Route::put('/students/{student}/edit', [KepalaController::class, 'updateStudent'])->name('student.update');
    Route::delete('/students/{student}', [KepalaController::class, 'destroyStudent'])->name('student.destroy');

    // Excel import routes
    Route::get('/students/template/download', [KepalaController::class, 'downloadTemplate'])->name('student.template');
    Route::post('/students/import', [KepalaController::class, 'importStudents'])->name('student.import');

    // route for input rooms
    Route::get('/rooms/{examtype}/view', [RoomController::class, 'index'])->name('rooms');
    Route::get('/rooms/create', [RoomController::class, 'roomCreate'])->name('room.create');
    Route::post('/rooms/create', [RoomController::class, 'roomStore'])->name('room.store');

    //route for room and students
    Route::get('/room/{room}/participants', [RoomController::class, 'roomParticipants'])->name('room.participants');
    Route::post('/room/{room}/participants', [RoomController::class, 'roomParticipantsStore'])->name('room.participants.store');

    // Route::delete('/room/{room}/participants/{participant}', [KepalaController::class, 'roomParticipantsDestroy'])->name('room.participants.destroy');



    // routing for teachers in kepala dashboard
    Route::get('/teachers', [KepalaController::class, 'teacherAll'])->name('teachers');
    Route::get('/teachers/create', [KepalaController::class, 'createTeacher'])->name('teacher.create');
    Route::post('/teachers/create', [KepalaController::class, 'storeTeacher'])->name('teacher.store');
    Route::get('/teacher/{teacher}/edit', [KepalaController::class, 'editTeacher'])->name('teacher.edit');
    Route::delete('/teacher/{teacher}/destroy', [KepalaController::class, 'destroyTeacher'])->name('teacher.destroy');
    Route::put('/teacher/update', [KepalaController::class, 'updateTeacher'])->name('teacher.update');

    //routing for exams in kepala dashboard
    Route::get('/exams/{exam}/participants', [KepalaExamController::class, 'participants'])->name('exams.participant');
    Route::post('/exams/{exam}/participants', [KepalaExamController::class, 'storeParticipants'])->name('exams.participants.store');
    Route::post('/exams/{exam}/oneparticipant', [KepalaExamController::class, 'storeOneParticipant'])->name('exams.participants.store.one');
    // new route for registering selected students into a chosen exam
    Route::post('/exams/register', [KepalaExamController::class, 'registerParticipants'])->name('exams.participants.register');
    // ajax: return students + registration status for selected exam_id
    Route::get('/exams/{exam}/students-by-exam', [KepalaExamController::class, 'studentsByExam'])->name('exams.students.by_exam');
    Route::get('/exams/global', [KepalaExamController::class, 'indexAll'])->name('indexall');
    Route::post('/exams/{exam}/manage', [KepalaExamController::class, 'manage'])->name('exams.manage');
    Route::get('/exams/{exam}/manage', [KepalaExamController::class, 'manageView'])->name('exams.manage.view');
    // JSON endpoint to fetch scores for a given exam (subject)
    Route::get('/exams/{exam}/scores', [KepalaExamController::class, 'scores'])->name('exams.scores');

    // Room Assignment routes (Assign students to rooms for exams)
    Route::get('/room-assignment', [RoomAssignmentController::class, 'index'])->name('room-assignment.index');
    Route::post('/room-assignment/assign', [RoomAssignmentController::class, 'assignStudents'])->name('room-assignment.assign');
    Route::post('/room-assignment/auto-assign', [RoomAssignmentController::class, 'autoAssign'])->name('room-assignment.auto-assign');
    Route::delete('/room-assignment/remove', [RoomAssignmentController::class, 'removeStudent'])->name('room-assignment.remove');
    Route::get('/room-assignment/room/{room}', [RoomAssignmentController::class, 'getRoomDetails'])->name('room-assignment.room-details');

    // Berita Acara routes
    Route::get('berita-acara', [BeritaAcaraController::class, 'index'])->name('berita-acara.index');
    Route::get('berita-acara/create', [BeritaAcaraController::class, 'create'])->name('berita-acara.create');
    Route::post('berita-acara', [BeritaAcaraController::class, 'store'])->name('berita-acara.store');
    Route::get('berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'show'])->name('berita-acara.show');
    Route::get('berita-acara/{beritaAcara}/edit', [BeritaAcaraController::class, 'edit'])->name('berita-acara.edit');
    Route::put('berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'update'])->name('berita-acara.update');
    Route::delete('berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'destroy'])->name('berita-acara.destroy');
    Route::post('berita-acara/{beritaAcara}/finalize', [BeritaAcaraController::class, 'finalize'])->name('berita-acara.finalize');
    Route::post('berita-acara/{beritaAcara}/approve', [BeritaAcaraController::class, 'approve'])->name('berita-acara.approve');
    Route::post('berita-acara/{beritaAcara}/archive', [BeritaAcaraController::class, 'archive'])->name('berita-acara.archive');
    Route::get('berita-acara/{beritaAcara}/pdf', [BeritaAcaraController::class, 'exportPdf'])->name('berita-acara.pdf');
    Route::post('berita-acara/auto-fill', [BeritaAcaraController::class, 'autoFill'])->name('berita-acara.autofill');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruController::class, 'index'])->name('dashboard');
    
    // Room Assignment routes (Teachers can help assign students)
    Route::get('/room-assignment', [RoomAssignmentController::class, 'index'])->name('room-assignment.index');
    Route::post('/room-assignment/assign', [RoomAssignmentController::class, 'assignStudents'])->name('room-assignment.assign');
    Route::post('/room-assignment/auto-assign', [RoomAssignmentController::class, 'autoAssign'])->name('room-assignment.auto-assign');
    Route::delete('/room-assignment/remove', [RoomAssignmentController::class, 'removeStudent'])->name('room-assignment.remove');
    Route::get('/room-assignment/room/{room}', [RoomAssignmentController::class, 'getRoomDetails'])->name('room-assignment.room-details');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:super'])->prefix('super')->name('super.')->group(function () {
    Route::get('/dashboard', [SuperController::class, 'index'])->name('dashboard');

    // school management
    Route::get('/school', [SchoolController::class, 'index'])->name('school');
    Route::get('inputsekolah', function () {
        return view('admin.inputsekolah');
    })->name('inputsekolah');
    Route::post('inputsekolah', [SchoolController::class, 'store'])->name('sekolahstore');
    // Route::get('schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
    // Route::put('schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
    // Route::delete('schools/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');

    Route::post('schools/{school}/manage', [SchoolController::class, 'manage'])->name('schools.manage');
    Route::get('schools/{school}/manage', [SchoolController::class, 'manageView'])->name('schools.manage.view');
    Route::post('schools/nonaktif', [SchoolController::class, 'inactive'])->name('school.inactive');
    Route::get('schools/{school}/active', [SchoolController::class, 'active'])->name('school.active');

    // route for grades management
    Route::get('grades', [GradeController::class, 'index'])->name('grades');
    Route::get('grades/create', function () {
        return view('admin.inputgrades');
    })->name('input.grades');
    Route::post('grades/create', [GradeController::class, 'store'])->name('grades.store');
    Route::get('grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
    Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

    // Route for exams management
    Route::get('exams', [ExamController::class, 'index'])->name('exams');
    Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('exams/create', [ExamController::class, 'globalstore'])->name('examsglobal.store');
    Route::post('exams/{exam}/manage', [ExamController::class, 'manage'])->name('exams.manage');
    Route::get('exams/{exam}/manage', [ExamController::class, 'manageView'])->name('exams.manage.view');

    // add subject in an exam
    Route::post('exams/exam/store', [ExamController::class, 'examstore'])->name('exam.store');


    // bank soal
    Route::post('exams/banksoal/{exam}/manage', [ExamController::class, 'examquestion'])->name('exams.question');
    Route::get('exams/banksoal/{exam}/manage', [ExamController::class, 'banksoal'])->name('exams.manage.question');

    Route::post('exams/banksoal/{exam}/store', [QuestionController::class, 'store'])->name('exams.question.store');
    Route::put('exams/banksoal/{exam}/update', [QuestionController::class, 'update'])->name('exams.question.update');
    Route::delete('exams/banksoal/{exam}/destroy', [QuestionController::class, 'destroy'])->name('exams.questions.destroy');
    Route::get('exams/banksoal/exit', [ExamController::class, 'exitbanksoal'])->name('exams.question.exit');

    // Berita Acara routes (View and Export only for Super)
    Route::get('berita-acara', [BeritaAcaraController::class, 'index'])->name('berita-acara.index');
    Route::get('berita-acara/{beritaAcara}', [BeritaAcaraController::class, 'show'])->name('berita-acara.show');
    Route::get('berita-acara/{beritaAcara}/pdf', [BeritaAcaraController::class, 'exportPdf'])->name('berita-acara.pdf');
    Route::get('berita-acara/{beritaAcara}/student-list', [BeritaAcaraController::class, 'printStudentList'])->name('berita-acara.student-list');
});
require __DIR__ . '/auth.php';
