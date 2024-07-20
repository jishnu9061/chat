<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
        'file_url',
    ];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
