<?php namespace components\facebook; if(!defined('TX')) die('No direct access.');

class Actions extends \dependencies\BaseComponent
{
  
  protected function logout($data)
  {
    
    if(tx('Data')->session->facebook->is_set())
      tx('Data')->session->facebook->un_set();
    
  }
  
}
