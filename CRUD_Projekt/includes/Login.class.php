<?php
//session_start();
class Login
{
    public string $login;
    public string $password;
    public string $admin;

    private array $validationErrors = [];
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function __construct(array $roomData = [])
    {
        $this->login = filter_input(INPUT_POST,'user') ?? "";
        $this->password = filter_input(INPUT_POST,'pass') ?? "";
    }

    public function validate(): bool
    {
        $isOk = true;
        if ($this->login == "") {
            $isOk = false;
        }
        if ($this->password == "") {
            $isOk = false;
        }
        return $isOk;
    }

    public function Authenticate() : array
    {
        $query = "SELECT login,password,admin,employee_id FROM employee WHERE login=:login";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':login', $this->login);
        $stmt->execute();
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);
        //dump($dbData['password']);
        if (password_verify($this->password, $dbData['password'])) {
            return [$dbData['login'],$dbData['password'],$dbData['admin'],$dbData['employee_id']];
        }
        return [];
    }

    public static function readPostData(): Login
    {
        return new self($_POST);            //není úplně košer, nefiltruju
    }
}