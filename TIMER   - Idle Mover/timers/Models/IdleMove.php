<?php
/**
 * Created by Notepad++
 * User: HellBz
 */

namespace Timer\Models;


use Illuminate\Database\Eloquent\Model;

class IdleMove extends Model
{

    protected $fillable = ['unique', 'afk','moved','msg', 'channel' ];

}