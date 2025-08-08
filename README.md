```php
<?php

// App/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'status', 'role'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function formations()
    {
        return $this->belongsToMany(Formation::class, 'enrollments')
                    ->withPivot(['status', 'payment_status', 'progress_percentage', 'enrollment_date'])
                    ->withTimestamps();
    }

    public function createdFormations()
    {
        return $this->hasMany(Formation::class, 'created_by');
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function answers()
    {
        return $this->hasManyThrough(UserAnswer::class, ExamAttempt::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeInstructors($query)
    {
        return $query->where('role', 'instructor');
    }

    // Helper methods
    public function isEnrolledIn(Formation $formation): bool
    {
        return $this->formations()->where('formation_id', $formation->id)->exists();
    }

    public function hasCompletedFormation(Formation $formation): bool
    {
        return $this->enrollments()
                   ->where('formation_id', $formation->id)
                   ->where('status', 'completed')
                   ->exists();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
}

// App/Models/UserProfile.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'bio', 'profession', 'linkedin', 'website', 
        'birth_date', 'country', 'city', 'preferences'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'preferences' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// App/Models/Formation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Formation extends Model
{
    use HasFactory, SoftDeletes, HasSlug, LogsActivity;

    protected $fillable = [
        'title', 'slug', 'description', 'short_description', 'image', 'price',
        'duration_hours', 'difficulty_level', 'certification_threshold',
        'is_active', 'is_featured', 'tags', 'language', 'created_by'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order_position');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot(['status', 'payment_status', 'progress_percentage'])
                    ->withTimestamps();
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function exam()
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress()
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    // Helper methods
    public function getTotalChaptersCount(): int
    {
        return $this->modules()
                   ->with('sections.chapters')
                   ->get()
                   ->sum(function($module) {
                       return $module->sections->sum(function($section) {
                           return $section->chapters->count();
                       });
                   });
    }

    public function getEstimatedDuration(): int
    {
        return $this->modules()->sum('estimated_duration');
    }

    public function getEnrollmentCount(): int
    {
        return $this->enrollments()->where('payment_status', 'paid')->count();
    }
}

// App/Models/Module.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id', 'title', 'description', 'order_position',
        'estimated_duration', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class)->orderBy('order_position');
    }

    public function exam()
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress()
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getChaptersCount(): int
    {
        return $this->sections()->with('chapters')->get()->sum(function($section) {
            return $section->chapters->count();
        });
    }
}

// App/Models/Section.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id', 'title', 'description', 'order_position',
        'estimated_duration', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('order_position');
    }

    public function exam()
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress()
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

// App/Models/Chapter.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id', 'title', 'content', 'content_type', 'media_url',
        'duration_minutes', 'order_position', 'is_free', 'is_active', 'metadata'
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function exam()
    {
        return $this->morphOne(Exam::class, 'examable');
    }

    public function progress()
    {
        return $this->morphMany(UserProgress::class, 'trackable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }
}

// App/Models/Exam.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'examable_id', 'examable_type', 'title', 'description', 'instructions',
        'duration_minutes', 'passing_score', 'max_attempts', 'randomize_questions',
        'show_results_immediately', 'is_active'
    ];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'show_results_immediately' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function examable()
    {
        return $this->morphTo();
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order_position');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalPoints(): int
    {
        return $this->questions()->sum('points');
    }

    public function getQuestionsCount(): int
    {
        return $this->questions()->count();
    }
}

// App/Models/Question.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'question_text', 'question_type', 'points', 'order_position',
        'explanation', 'image', 'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_position');
    }

    public function answers()
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function getCorrectOptions()
    {
        return $this->options()->where('is_correct', true);
    }
}

// App/Models/QuestionOption.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id', 'option_text', 'is_correct', 'order_position', 'image'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'selected_option_id');
    }
}

// App/Models/Enrollment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id', 'formation_id', 'enrollment_date', 'completion_date',
        'status', 'payment_status', 'amount_paid', 'progress_percentage',
        'last_accessed_at'
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'completion_date' => 'datetime',
        'last_accessed_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'progress_percentage' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function updateProgress(): void
    {
        $totalChapters = $this->formation->getTotalChaptersCount();
        $completedChapters = UserProgress::where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->whereHasMorph('trackable', [Chapter::class], function($query) {
                $query->whereHas('section.module', function($q) {
                    $q->where('formation_id', $this->formation_id);
                });
            })
            ->count();

        $this->progress_percentage = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 100 : 0;
        $this->last_accessed_at = now();
        
        if ($this->progress_percentage >= 100) {
            $this->status = 'completed';
            $this->completion_date = now();
        }

        $this->save();
    }
}

// App/Models/UserProgress.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'trackable_id', 'trackable_type', 'progress_percentage',
        'time_spent', 'status', 'started_at', 'completed_at'
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trackable()
    {
        return $this->morphTo();
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function markAsStarted(): void
    {
        if ($this->status === 'not_started') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => now()
            ]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now()
        ]);

        // Update parent enrollment progress
        if ($this->trackable instanceof Chapter) {
            $formation = $this->trackable->section->module->formation;
            $enrollment = Enrollment::where('user_id', $this->user_id)
                                  ->where('formation_id', $formation->id)
                                  ->first();
            $enrollment?->updateProgress();
        }
    }
}

// App/Models/ExamAttempt.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'exam_id', 'attempt_number', 'score', 'max_score',
        'percentage', 'status', 'answers', 'started_at', 'completed_at', 'time_taken'
    ];

    protected $casts = [
        'answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'percentage' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePassed($query)
    {
        return $query->where('status', 'completed')
                    ->whereColumn('percentage', '>=', 'exams.passing_score');
    }

    public function isPassed(): bool
    {
        return $this->percentage >= $this->exam->passing_score;
    }

    public function calculateScore(): void
    {
        $totalScore = $this->userAnswers()->sum('points_earned');
        $maxScore = $this->exam->getTotalPoints();
        
        $this->update([
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0
        ]);
    }

    public function complete(): void
    {
        $this->calculateScore();
        $this->update([
            'status' => $this->isPassed() ? 'completed' : 'failed',
            'completed_at' => now(),
            'time_taken' => now()->diffInSeconds($this->started_at)
        ]);
    }
}

// App/Models/UserAnswer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_attempt_id', 'question_id', 'selected_option_id',
        'answer_text', 'is_correct', 'points_earned'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    public function examAttempt()
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function checkCorrectness(): void
    {
        if ($this->question->question_type === 'single_choice' || $this->question->question_type === 'multiple_choice') {
            $this->is_correct = $this->selectedOption?->is_correct ?? false;
            $this->points_earned = $this->is_correct ? $this->question->points : 0;
        }
        
        $this->save();
    }
}

// App/Models/Certificate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'formation_id', 'certificate_number', 'issue_date',
        'expiry_date', 'verification_hash', 'status', 'file_path',
        'final_score', 'metadata'
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'expiry_date' => 'datetime',
        'final_score' => 'decimal:2',
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            $certificate->certificate_number = $certificate->generateCertificateNumber();
            $certificate->verification_hash = $certificate->generateVerificationHash();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>', now());
                    });
    }

    public function isValid(): bool
    {
        return $this->status === 'active' && 
               ($this->expiry_date === null || $this->expiry_date->isFuture());
    }

    protected function generateCertificateNumber(): string
    {
        return 'CERT-' . date('Y') . '-' . str_pad(
            Certificate::whereYear('created_at', date('Y'))->count() + 1,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function generateVerificationHash(): string
    {
        return hash('sha256', $this->user_id . $this->formation_id . now()->timestamp . Str::random(10));
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('certificates.download', ['certificate' => $this->certificate_number]);
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('certificates.verify', ['hash' => $this->verification_hash]);
    }
}

// App/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id', 'formation_id', 'transaction_id', 'amount', 'currency',
        'status', 'gateway', 'gateway_response', 'invoice_number', 'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now()
        ]);

        // Update enrollment payment status
        Enrollment::where('user_id', $this->user_id)
                  ->where('formation_id', $this->formation_id)
                  ->update([
                      'payment_status' => 'paid',
                      'amount_paid' => $this->amount
                  ]);
    }

    public function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Ymd') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}

```
