<?php
class passChange {
    public string $username;
    public string $newPass;
    public string $newPassConfirm;
    public string $empId;
    private function __construct(array $passwords = []){
        $this->username = $_SESSION['user']??"";
        $this->newPass = filter_input(INPUT_POST,'newpass')??"";
        $this->newPassConfirm = FILTER_INPUT(INPUT_POST,'newpassConfirm')??"";
        $this->empId = $_SESSION['employee_id']??"";
    }
    public function checkPasswords():bool{
        if(($this->newPass===$this->newPassConfirm))
        {
            return true;
        }
        else return false;
    }
    public function updatePassword(){
        
        $passHash = password_hash($this->newPassConfirm,PASSWORD_DEFAULT); 
        $query = "UPDATE employee SET password = :passHash WHERE login = :username";
        $stmtChange = DB::getConnection()->prepare($query);
        $stmtChange->bindParam(':passHash',$passHash);
        $stmtChange->bindParam(':username', $this->username);
        $stmtChange->execute();
        
    }
    public static function readPostData(): passChange{

        return new self($_POST);            //není úplně košer, nefiltruju
    }
}
?>