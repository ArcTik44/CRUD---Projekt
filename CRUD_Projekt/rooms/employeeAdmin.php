<?php
require "../includes/bootstrap.inc.php";
final class EmployeeDetailAdmin extends BaseDBPage
{
    private ?int $employee_id;
    private ?int $room_id;
    protected string $title;
    public function __construct()
    {
        parent::__construct();
        $this->title = "Karta zaměstnance";
        $this->employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
    }
    protected function body(): string
    {
        if(!$_SESSION)
            {
                  header('location:index.php',false);
                  exit;
            }
        if (!$this->employee_id) {
            http_response_code(400);
            die("Error 400: Bad request");
        } else {
            $stmt = $this->pdo->prepare("SELECT employee.wage AS EmpWage, employee.name AS EmpName ,employee.surname AS EmpSurname,employee.job AS EmpJob,employee.login AS EmpLogin, room.name AS RoomName, room.room_id FROM employee INNER JOIN room ON room.room_id = employee.room WHERE employee.employee_id =:emp_id");
            $stmt->bindParam(':emp_id', $this->employee_id);
            $stmt->execute([$this->employee_id]);
            if ($stmt->rowCount()) {
                
                $stmt2 = $this->pdo->prepare('SELECT room.name AS RName, room.room_id AS RiD FROM 
                ((room INNER JOIN `key` ON room.room_id=`key`.room) 
                INNER JOIN employee ON employee.employee_id = `key`.employee) WHERE `key`.employee =:emp_id');


                $stmt2->bindParam(':emp_id', $this->employee_id);

                $stmt2->execute([$this->employee_id]);
            }
            else{
                http_response_code(404);
                die("Error 404: Not Found");
            }
        }


        return $this->m->render("employeeDetailAdmin", ["employee" => $stmt, 'keys' => $stmt2]);
    }
}
$page = new EmployeeDetailAdmin();
$page->render();
