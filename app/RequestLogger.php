<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequestLogger extends Model
{
    protected $table = 'requests_logger';

    /* public function logSuggRequest($req, $resp) 
    {
        DB::table('users_info')->insert([
            'request'       => $req,
            'response'      => json_encode($resp)
        ]);
    } */
}
