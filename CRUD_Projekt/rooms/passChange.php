<?php
require "../includes/bootstrap.inc.php";
   final class passChangeForm extends BaseDBPage{
        private passChange $passwordData;
        public string $username; 
        public function __construct()
        {
            parent::__construct();
            $this->title = "Změna hesla";
            $this->username = $_SESSION['user'];
        }
        protected function body(): string
        {
            if(!$_SESSION)
            {
                  header('location:index.php',false);
                  exit;
            }
            $this->passwordData = passChange::readPostData();
            if($_POST){
                
        $isSame = $this->passwordData->checkPasswords();
        if($isSame)
        {
            $updatePassword = $this->passwordData->updatePassword();
            return $this->m->render('passChangeSuccess',['message'=>'Heslo se úspěšně změnilo.']);
            
        }
        else{
            return $this->m->render('passChangeFail',['message'=>'Heslo se nepodařilo změnit.']);
        }
         }
        
        return $this->m->render('passwordChange',['passwordData'=>$this->passwordData,'username'=>$this->username]);
    }
    }
    (new passChangeForm())->render();
