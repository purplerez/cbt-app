<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BeritaAcara extends Model
{
    use HasFactory;

    protected $table = 'berita_acara';

    protected $fillable = [
        'exam_type_id',
        'exam_id',
        'school_id',
        'room_id',
        'nomor_ba',
        'tanggal_pelaksanaan',
        'waktu_mulai',
        'waktu_selesai',
        'jumlah_peserta_hadir',
        'jumlah_peserta_tidak_hadir',
        'jumlah_peserta_terdaftar',
        'pengawas',
        'catatan_khusus',
        'kondisi_pelaksanaan',
        'kendala',
        'kondisi_ruangan',
        'kondisi_peralatan',
        'status',
        'ttd_pengawas_1',
        'ttd_pengawas_2',
        'ttd_kepala_sekolah',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'pengawas' => 'array',
        'tanggal_pelaksanaan' => 'date',
        'approved_at' => 'datetime',
        'jumlah_peserta_hadir' => 'integer',
        'jumlah_peserta_tidak_hadir' => 'integer',
        'jumlah_peserta_terdaftar' => 'integer',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Boot method untuk auto-generate nomor BA
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($beritaAcara) {
            if (empty($beritaAcara->nomor_ba)) {
                $beritaAcara->nomor_ba = static::generateNomorBA($beritaAcara);
            }
        });
    }

    /**
     * Generate nomor berita acara otomatis
     * Format: BA/SCHOOL_CODE/EXAMTYPE/YYYY/MM/XXXX
     */
    public static function generateNomorBA($beritaAcara)
    {
        $school = School::find($beritaAcara->school_id);
        $examType = Examtype::find($beritaAcara->exam_type_id);
        
        $schoolCode = $school->code ?? 'SCH';
        $examCode = strtoupper(substr($examType->title ?? 'EXAM', 0, 3));
        $year = now()->format('Y');
        $month = now()->format('m');
        
        // Get last number for this month
        $lastBA = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('school_id', $beritaAcara->school_id)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastBA ? (int)substr($lastBA->nomor_ba, -4) + 1 : 1;
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        return "BA/{$schoolCode}/{$examCode}/{$year}/{$month}/{$formattedNumber}";
    }

    /**
     * Relasi ke ExamType
     */
    public function examType()
    {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }

    /**
     * Relasi ke Exam
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Relasi ke School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke Room
     */
    public function room()
    {
        return $this->belongsTo(Rooms::class, 'room_id');
    }

    /**
     * Relasi ke User (Creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User (Approver)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get pengawas users
     */
    public function getPengawasUsersAttribute()
    {
        if (empty($this->pengawas)) {
            return collect();
        }
        
        return User::whereIn('id', $this->pengawas)->get();
    }

    /**
     * Scope untuk filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter by school
     */
    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope untuk filter by exam type
     */
    public function scopeByExamType($query, $examTypeId)
    {
        return $query->where('exam_type_id', $examTypeId);
    }

    /**
     * Scope untuk filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_pelaksanaan', [$startDate, $endDate]);
    }

    /**
     * Check if BA can be edited
     */
    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'finalized']);
    }

    /**
     * Check if BA can be approved
     */
    public function canBeApproved()
    {
        return $this->status === 'finalized';
    }

    /**
     * Approve berita acara
     */
    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        logActivity(
            'berita_acara_approved',
            "Berita Acara {$this->nomor_ba} disetujui",
            $userId
        );
    }

    /**
     * Finalize berita acara (ready for approval)
     */
    public function finalize()
    {
        if ($this->status === 'draft') {
            $this->update(['status' => 'finalized']);
            
            logActivity(
                'berita_acara_finalized',
                "Berita Acara {$this->nomor_ba} diselesaikan",
                auth()->id()
            );
        }
    }

    /**
     * Archive berita acara
     */
    public function archive()
    {
        $this->update(['status' => 'archived']);
        
        logActivity(
            'berita_acara_archived',
            "Berita Acara {$this->nomor_ba} diarsipkan",
            auth()->id()
        );
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'finalized' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'archived' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'draft' => 'Draft',
            'finalized' => 'Selesai',
            'approved' => 'Disetujui',
            'archived' => 'Diarsipkan',
            default => 'Unknown',
        };
    }

    /**
     * Get persentase kehadiran
     */
    public function getPersentaseKehadiranAttribute()
    {
        if ($this->jumlah_peserta_terdaftar == 0) {
            return 0;
        }
        
        return round(($this->jumlah_peserta_hadir / $this->jumlah_peserta_terdaftar) * 100, 2);
    }
}
