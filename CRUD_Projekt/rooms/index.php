<?php
require "../includes/bootstrap.inc.php";
   final class LoginForm extends BaseDBPage{
    private Login $loginData;
    private array $validationErrors = [];
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
    public function __construct()
    {
        parent::__construct();
        $this->title = "Login";
    }
   
    protected function body(): string
    {
        $this->LoginData = Login::readPostData();
        $isOk = $this->LoginData->validate();
        //dump($isOk);
        if($isOk){
            $getInfo = $this->LoginData->Authenticate();
            if($getInfo){
                $_SESSION["user"] = $getInfo[0];
                $_SESSION["admin"] = $getInfo[2];
                $_SESSION['pass'] = $getInfo[1];
                $_SESSION['employee_id'] = $getInfo[3];
                header('location:User.php',false);
                exit;
            }
            else{
              return $this->m->render('loginFail');
            }
        }
       return $this->m->render("loginForm",['LoginData' => $this->LoginData]);            
    }
}
(new LoginForm())->render();
?>