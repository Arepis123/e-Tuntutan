<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblUser extends Model
{
    protected $connection = 'auth_db';

    protected $table = 'tbl_user';

    protected $primaryKey = 'u_id';

    public $timestamps = false;

    protected $fillable = [
        'u_username',
        'u_password',
    ];
}
