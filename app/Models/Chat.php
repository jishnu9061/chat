<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';

    protected $fillable = [
        'product_id',
        'user_id',
        'enlisted_user_id',
    ];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
