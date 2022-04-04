<?php
require "../includes/bootstrap.inc.php";

final class CurrentPage extends BaseDBPage {

    private string $admin;
    protected function setUp(): void
    {
        parent::setUp();
        $this->title = "Seznam zaměstnanců";
        
    }
    protected function body(): string
    {
        if(!$_SESSION)
            {
                  header('location:index.php',false);
                  exit;
            }
        $stmt = $this->pdo->prepare("SELECT employee.employee_id,employee.name,employee.surname,employee.job, room.name AS RoomName, room.phone FROM employee INNER JOIN room ON room.room_id = employee.room ");
        $stmt->execute([]);
        return $this->m->render("employeeListUser", ["employees" => $stmt,'employeeDetail'=>'employeeUser.php']);
    }
}

(new CurrentPage())->render();