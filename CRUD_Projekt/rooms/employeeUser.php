<?php
require "../includes/bootstrap.inc.php";
final class EmployeeDetailUser extends BaseDBPage
{
    private ?int $employee_id;
    protected string $title;
    public function __construct()
    {
        parent::__construct();
        $this->employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
        $this->title = "Karta zaměstnance";
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
            $stmt = $this->pdo->prepare("SELECT employee.wage, employee.name,employee.surname,employee.job, room.name AS RoomName, room.room_id FROM employee INNER JOIN room ON room.room_id = employee.room WHERE employee.employee_id =:emp_id");
            $stmt->bindParam(':emp_id', $this->employee_id);
            $stmt->execute([$this->employee_id]);
            if ($stmt->rowCount()) {
                
                $stmt2 = $this->pdo->prepare('SELECT room.name AS RName, room.room_id AS RiD FROM 
                ((room INNER JOIN `key` ON room.room_id=`key`.room) 
                INNER JOIN employee ON employee.employee_id = `key`.employee) WHERE `key`.employee =:emp_id');


                $stmt2->bindParam(':emp_id', $this->employee_id);

                $stmt2->execute([$this->employee_id]);
            }
            else {
                http_response_code(404);
                die("Error 404: Not found");
            }
        }
        return $this->m->render("employeeDetailUser", ["employee" => $stmt, 'keys' => $stmt2]);
    }
}
$page = new EmployeeDetailUser();
$page->render();
