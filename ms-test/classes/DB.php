<?php

namespace classes;

class DB 
{
    protected $db_name = 'test';
    protected $db_user = 'sasha';
    protected $db_pass = 'пароль';
    protected $db_host = 'localhast';

    //? открываем соединениек БД
    public function connect()
    {
        $connection = mysqli_connect($this->db_host, $this->db_user, );
    }
}