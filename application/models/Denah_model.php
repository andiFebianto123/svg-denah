<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Eloquent\Model as Eloquent;

class Denah_model extends Eloquent
{
    protected $table = 'denahs';
    public $timestamps = false;
}
