<?php
require "../includes/bootstrap.inc.php";
final class RoomDetailAdmin extends BaseDBPage
{
    private ?int $room_id;
    protected string $title;
    public ErrorPage $error;

    public function __construct()
    {
        parent::__construct();
        $this->title = "Karta Místnosti";
        $this->room_id = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT);
    }
    protected function body(): string
    {
        if (($_SESSION['admin']==0)||(!$_SESSION)) {
            header('location:index.php', false);
            exit;
        }
        if (!$this->room_id) {
            http_response_code(400);
            die("Error 400: Bad request");

        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM room WHERE room_id =:roomId");
            $stmt->bindParam(':roomId', $this->room_id);
            $stmt->execute([$this->room_id]);
            if ($stmt->rowCount()) {

                $stmtKeys = $this->pdo->prepare('SELECT employee.employee_id AS EmpId, employee.name AS EmpName,employee.surname AS EmpSurname FROM `key` LEFT JOIN employee ON `key`.employee=employee.employee_id WHERE `key`.room=:roomId');
                $stmtEmp = $this->pdo->prepare('SELECT employee.name AS EmNa, employee.surname AS EmSu, employee.employee_id AS EmId FROM employee RIGHT JOIN room ON employee.room = room.room_id WHERE room_id =:roomId');
                $queryAvg = $this->pdo->prepare("SELECT AVG(wage) AS AvgWage FROM employee INNER JOIN room ON employee.room = room.room_id WHERE room_id =:roomId ");

                $stmtEmp->bindParam(':roomId', $this->room_id);
                $queryAvg->bindParam(':roomId', $this->room_id);
                $stmtKeys->bindParam(':roomId', $this->room_id);

                $queryAvg->execute([$this->room_id]);
                $stmtEmp->execute([$this->room_id]);
                $stmtKeys->execute([$this->room_id]);

                return $this->m->render("roomDetailAdmin", ["room" => $stmt, 'keys' => $stmtKeys, 'employees' => $stmtEmp, 'AVG' => $queryAvg]);
            }
            else{
                http_response_code(404);
                die("Error 404: Not found");
            }
        }
    }
}
(new RoomDetailAdmin())->render();
