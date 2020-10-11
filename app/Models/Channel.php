<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $table = "channels";

    protected $fillable = [
        'name',
        'description',
        'workspace_id'
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
