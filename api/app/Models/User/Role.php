<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'eden.roles';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'name'];
    public $timestamps = false;
}
