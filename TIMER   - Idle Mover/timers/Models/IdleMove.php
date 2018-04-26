<?php
/**
 * Created by PhpStorm.
 * User: Jake
 * Date: 10/08/2016
 * Time: 01:13
 */

namespace Timer\Models;


use Illuminate\Database\Eloquent\Model;

class IdleMove extends Model
{

    protected $fillable = ['unique', 'afk','moved','msg', 'channel' ];

}