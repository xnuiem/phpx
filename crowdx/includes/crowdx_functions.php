<?php

/**
 * The functions for crowdX
 *
 * @package WordPress
 * @author  Xnuiem
 */

class crowdx_functions {
    
    var $options;
    var $wpdb;
    
    

    /**
    * The construct function for the crowdX class. 
    *
    * @param NULL
    * @return NULL
    */

    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        
    }
    function crowdx_createClient(){
        
        $this->client = new SoapClient($this->options['server'] . 'services/SecurityServer?wsdl');
        if ($this->client){ return true; }
        else { return false; }

    }
    
    function crowdx_login(){
        if ($_POST['wp-submit'] != 'Log In'){ return false; }
        if ($this->crowdx_createClient()){
            $this->crowdx_soapLogin();
            //remove_action('authenticate', 'wp_authenticate_username_password', 20);
            return false;
        }
        else if ($this->options['fallback'] == 1){
            $this->crowdx_fallBackLogin();
            return false;
            
        }
        else {
            remove_action('authenticate', 'wp_authenticate_username_password', 20);
            return false;
        }

    }
    
    function crowdx_fallBackLogin(){
        
        if ($this->options['all_users'] == 0){
            //check metadata on this user
            $user = get_user_by('login', sanitize_user($_POST['log']));            
            $crowd = get_user_meta($user->ID, 'crowdx', true);
            if ($crowd == false){
                remove_action('authenticate', 'wp_authenticate_username_password', 20);
                return false;
            }
            //allow to login below
        }
        $rem = ($_POST['rememberme'] == 'forever') ? true : false;
        $secure = ($_SERVER["SERVER_PORT"] == "443") ? true : false;
        $userArray = array('user_login' => $_POST['log'], 'user_password' => $_POST['pwd'], 'remember' => $rem);
        $user = wp_authenticate_username_password('', $_POST['log'], $_POST['pwd']);
        if (is_wp_error($user)){
            remove_action('authenticate', 'wp_authenticate_username_password', 20);
            return false;
        }
        return true;
    }
   
    function crowdx_soapLogin(){
        $param = array('in0' => array('credential' => array('credential' => $this->options['app_pass']), 'name' => $this->options['app_name']));
        $resp = $this->client->authenticateApplication($param);
        try {
            $resp = $this->client->authenticateApplication($param);
        }
        catch (SoapFault $fault) {
            if ($this->options['all_users'] == 0){
                
                remove_action('authenticate', 'wp_authenticate_username_password', 20);
                return false;           
                
            }
            else if ($this->options['fallback'] == 1){
                
                $this->crowdx_fallBackLogin();
                return true;
            }
            return false;             
        }
        
    
          
        $param1 = array('in0' => array('name'               => $this->options['app_name'],
                                      'token'               => $resp->out->token),
                       'in1' => array('application'         => $this->options['app_name'],
                                      'credential'          => array('credential' => $_POST['pwd']),
                                      'name'                => $_POST['log'],
                                      'validationFactors'   => array()));

                                                                       
        try {
            $resp1 = $this->client->authenticatePrincipal($param1);
        }
        catch (SoapFault $fault) {
            
            if ($this->options['all_users'] == 0){
                
                remove_action('authenticate', 'wp_authenticate_username_password', 20);
                return false;           
                
            }
            else if ($this->options['fallback'] == 1){
                
                $this->crowdx_fallBackLogin();
                return true;
            }
            return false; 
        }
        
        $username = sanitize_user($_POST['log']);
        $user = get_user_by('login', $username);


        if (!$user && $this->options['add_users'] == 1){
            $param2 = array('in0' => array('name'               => $this->options['app_name'],
                                           'token'               => $resp->out->token),
                            'in1' => $resp1->out);
            $resp2 = $this->client->findPrincipalByToken($param2);
            foreach($resp2->out->attributes->SOAPAttribute as $attr){
                $fieldName = $attr->name;
                $$fieldName = $attr->values->string;
            }
            

            $userArray = array('user_pass' => $_POST['pwd'],    
                                'user_login' => $_POST['log'], 
                                'user_nicename' => $displayName, 
                                'user_email' => $mail, 
                                'display_name' => $displayName, 
                                'first_name' => $givenName, 
                                'last_name' => $sn, 
                                'description' => 'Added by CrowdX',
                                'role' => $this->options['default_user_role']
                                 );
            $id = wp_insert_user($userArray);
            add_user_meta($id, 'crowdx', true);
            $user = get_user_by('login', $username);
        }
        else if (!$user){
            remove_action('authenticate', 'wp_authenticate_username_password', 20);
            return false;
        }
        else {
            //keeps wordpress password up to date
            $userArray = array('ID' => $user->ID, 'user_pass' => wp_hash_password($_POST['pwd']));
            wp_insert_user($userArray);
        }                  
        
        //if ($_SERVER["SERVER_PORT"] == "443"){ $secure = true; }
        //else { $secure = false; }
        
        
        //wp_set_auth_cookie($user->ID, true, $secure);
        //do_action('wp_login', $user->user_login);                
        
        //$url = ($_POST['redirect_to'] != '') ? urldecode($_POST['redirect_to']) : 'wp-admin/index.php';
        
           
        //header("Location: $url");
        //exit();          
    }
}

?>
