<?php

require "../includes/bootstrap.inc.php";
class UserPage extends BaseDBPage
{
       
      public string $username;
      public string $password;
      public string $admin;
      public function __construct()
      {
            parent::__construct();
            $this->title = $_SESSION['user'] ??"";
            $this->username = $_SESSION['user']??"";
            $this->password = $_SESSION['pass']??"";
            $this->admin = $_SESSION['admin']??"";
      }
      protected function body():string
      {
            if(!$_SESSION)
            {
                  header('location:index.php',false);
                  exit;
            }
            if ($this->admin == 1) {
                  return $this->m->render("adminAcc", ['login' => $this->username]);
            } else {
                  return $this->m->render("userAcc", ['login' => $this->username]);
            }
      }
}
(new UserPage())->render();