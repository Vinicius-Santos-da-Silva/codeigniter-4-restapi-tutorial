<?php namespace App\Models;

use CodeIgniter\Model;

class ContentModel extends Model{
  protected $table = 'content';
  protected $primaryKey = 'id';
  protected $allowedFields = ['title','body'];

  protected $beforeInsert = ['beforeInsert'];
  protected $beforeUpdate = ['beforeUpdate'];


  protected function beforeInsert(array $data){
    // $data = $this->passwordHash($data);
    return $data;
  }

  protected function beforeUpdate(array $data){
    // $data = $this->passwordHash($data);
    return $data;
  }


  protected function passwordHash(array $data){
    // if(isset($data['data']['password']))
    //   $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

    return $data;
  }
}
