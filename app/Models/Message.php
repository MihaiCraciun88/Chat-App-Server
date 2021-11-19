<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'own', 'message',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}