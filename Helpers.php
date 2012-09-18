<?php namespace components\facebook; if(!defined('TX')) die('No direct access.');

class Helpers extends \dependencies\BaseComponent
{
  
  public function authenticate($data)
  {
    
    return tx('Getting Facebook authentication', function()use($data){
      
      //Validate data.
      $data = $data->having('app_id', 'app_secret', 'redirect_url', 'scope', 'code', 'error', 'state')
        ->app_id->validate('App ID', array('required', 'string', 'not_empty'))->back()
        ->app_secret->validate('App secret', array('required', 'string', 'not_empty'))->back()
        ->redirect_url->validate('Redirect URL', array('url'))->back()
      ;
      
      //Use Facebook SDK.
      load_plugin('facebook-php-sdk');
      
      //Big try-catch, because we don't like the FacebookApiException class.
      try
      {
        
        //Create Facebook SDK object.
        $facebook = new \Facebook(array(
          'appId' => $data->app_id->get('string'),
          'secret' => $data->app_secret->get('string')
        ));
        
        //If we can get the user information, check for an access token.
        if($facebook->getUser())
        {
          
          $method = new \ReflectionMethod('\Facebook', 'getUserAccessToken');
          $method->setAccessible(true);
          $accessToken = $method->invoke($facebook);
          
          //If there is an accessToken, we are still authenticated.
          if($accessToken)
            return Data(array('facebook_sdk_class' => $facebook));
          
        }
        
        //Otherwise see if we can return a login URL.
        if($data->redirect_url->is_set()){
          return Data(array(
            'login_url' => $facebook->getLoginUrl(array(
              'redirect_uri' => url($data->redirect_url, true)->output->get(),
              'display' => 'popup'
            ))
          ));
        }
        
        //No redirect_url URL means we can't generate a login_url, so just report we're not authenticated.
        else{
          return Data(array('authentication_failed' => true));
        }
        
      }
      
      //Swap FacebookAPIExceptions for Expected exceptions.
      catch(\FacebookApiException $ex){ throw new \exception\Expected($ex); }
      
    });
    
  }
  
}
