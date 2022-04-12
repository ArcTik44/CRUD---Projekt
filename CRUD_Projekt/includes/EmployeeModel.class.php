<?php
final class EmployeeModel{
    public ?int $employee_id;
    public string $name;
    public string $surname;
    public string $job;
    public string $room;
    public string $wage;
    public ?string $login;
    public ?string $pass;
    public ?int $admin;
    public ?string $passHash;

    private array $validationErrors = [];
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

public function __construct(array $employeeData=[])
{
    $id = $employeeData['employee_id'] ?? null;
    if(is_string($id))
    {
        $id = filter_var($id,FILTER_VALIDATE_INT);
    }
    $this->employee_id=$id;
    $this->name=filter_input(INPUT_POST,'name') ?? '';
    $this->surname=filter_input(INPUT_POST,'surname') ?? '';
    $this->job=filter_input(INPUT_POST,'job') ?? '';
    $this->wage=filter_input(INPUT_POST,'wage')?? '';
    $this->room=filter_input(INPUT_POST,'room') ?? '';
    $this->login=filter_input(INPUT_POST,'login') ?? null;
    $this->pass=filter_input(INPUT_POST,'pass')??null;
    $this->admin=filter_input(INPUT_POST,'admin',FILTER_VALIDATE_INT);
    if(isset($this->admin)){
        $this->admin = 1;
    }
    else $this->admin = 0;
    $this->passHash = password_hash($this->pass,PASSWORD_DEFAULT)??null;
}

    public function Validate():bool{
        $isOk = true;
        if(!$this->name){
            $isOk = false;
            $this->validationErrors['name'] = "Name cannot be empty";
        }
        if(!$this->surname){
            $isOk = false;
            $this->validationErrors['surname'] = "Job cannot be empty";
        }
        if(!$this->wage){
            $isOk = false;
            $this->validationErrors['wage'] = "Wage cannot be empty";
        }
        if(!$this->room){
            $isOk = false;
            $this->validationErrors['room'] = "Room cannot be empty";
        }
        if(!$this->admin)
        {
            $this->admin = 0;
        }
        if(!$this->pass){
            $this->pass = null;
        }
        if(!$this->login){
            $this->login = null;
        }
        return $isOk;
    }

    public function Insert():bool{
        $query = 'INSERT INTO employee (name,surname, room,job,wage,login,password,admin) VALUES (:name,:surname,:room,:job,:wage,:login,:pass,:admin)';
        $stmt = DB::getConnection()->prepare($query);
        
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':surname', $this->surname);
        $stmt->bindParam(':room', $this->room);
        $stmt->bindParam(':job', $this->job);
        $stmt->bindParam(':wage',$this->wage);
        $stmt->bindParam(':pass',$this->passHash);
        $stmt->bindParam(':login',$this->login);
        $stmt->bindParam(':admin',$this->admin);


        if (!$stmt->execute())
            return false;

        $this->employee_id = DB::getConnection()->lastInsertId();
        return true;
    }

    public function Update():bool{
        $query = "UPDATE employee SET name=:name, surname=:surname, job=:job,room=:room,login=:login,password=:pass,wage=:wage,admin=:admin WHERE employee_id=:emp_id";


        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':surname', $this->surname);
        $stmt->bindParam(':wage',$this->wage);
        $stmt->bindParam(':room',$this->room);
        $stmt->bindParam(':pass',$this->passHash);
        $stmt->bindParam(':emp_id',$this->employee_id);
        $stmt->bindParam(':job', $this->job);
        $stmt->bindParam(':login',$this->login);
        $stmt->bindParam(':admin',$this->admin);

        return $stmt->execute();
    }
    public function Delete():bool{

        $query = "DELETE FROM employee WHERE employee_id=:employee_id";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':employee_id', $this->employee_id);
        return $stmt->execute();
    }
    public static function findById(int $Emp_id):?EmployeeModel{
        $query = "SELECT * FROM employee WHERE employee_id=:EmpId";
        $stmt = DB::getConnection()->prepare($query);
        $stmt->bindParam(':EmpId', $Emp_id);

        $stmt->execute();
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$dbData)
        {
            return null;
        }
        return new self($dbData);
    }
    
    public static function readPostData(): EmployeeModel{
        return new self($_POST);
    }
}
?>