<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'description', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an admin action.
     */
    public static function record(string $action, ?string $entityType = null, ?int $entityId = null, string $description = ''): void
    {
        static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'description' => $description,
            'ip_address'  => request()->ip(),
        ]);
    }
}
