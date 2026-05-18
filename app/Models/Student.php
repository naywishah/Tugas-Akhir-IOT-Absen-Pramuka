<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['uid', 'name', 'grade'];

    public function attendanceLogs() {
        return $this->hasMany(AttendanceLog::class);
    }
}
