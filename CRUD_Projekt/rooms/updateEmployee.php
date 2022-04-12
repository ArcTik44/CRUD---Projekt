<?php
require "../includes/bootstrap.inc.php";

final class UpdateEmployeePage extends BaseDBPage {

    const STATE_FORM_REQUESTED = 1;
    const STATE_FORM_SENT = 2;
    const STATE_PROCESSED = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private int $state;
    private int $result = 0;
    private array $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->state = $this->getState();

        if ($this->state === self::STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS) {
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=employeeListAdmin.php'>";
                $this->title = "Zaměstnanec upraven";
            } elseif ($this->result === self::RESULT_FAIL) {
                $this->title = "Aktualizace zaměstnance selhala";
            }
        } elseif ($this->state === self::STATE_FORM_SENT) {
            //načíst data
            $this->employee = $this->readPost();
            
            //validovat data
            if ($this->isDataValid($this->employee)){
                //uložit a přesměrovat
                if ($this->update($this->employee)) {
                    //přesměruj se zprávou "úspěch"
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    //přesměruj se zprávou "neúspěch"
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                //jít na formulář nebo
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Aktualizovat zaměstnance : Neplatný formulář";
            }
        } else {
            //přejít na formulář
            $this->title = "Aktualizovat zaměstnance";
            $employee_id = $this->findId();
            if (!$employee_id)
                throw new RequestException(400);
            $this->employee = $this->readDB($employee_id);
            if (!$this->employee)
                throw new RequestException(404);
        }

    }
    protected function body(): string
    {
        $queryRoomN = "SELECT name AS RName, room_id AS RoomId FROM room";
        $stmt2 = DB::getConnection()->prepare($queryRoomN);
        $stmt2->execute();
       
        if (($_SESSION['admin']==0)||(!$_SESSION)) {
            header('location:index.php', false);
            exit;
        }
        if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("employeeForm", ['Employees' => $this->employee,'rooms'=>$stmt2 ]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render("employeeSuccess", ["message" => "Zaměstnanec byl úspěšně aktualizován."]);
            } elseif ($this->result === self::RESULT_FAIL) {
                return $this->m->render("employeeFail", ["message" => "Aktualizace místnosti selhala"]);
            }
        }
    }

    private function getState() : int {
        //rozpoznání processed
        $result = filter_input(INPUT_GET, 'result', FILTER_VALIDATE_INT);

        if ($result === self::RESULT_SUCCESS) {
            $this->result = self::RESULT_SUCCESS;
            return self::STATE_PROCESSED;
        } elseif ($result === self::RESULT_FAIL) {
            $this->result = self::RESULT_FAIL;
            return self::STATE_PROCESSED;
        }

        $action = filter_input(INPUT_POST, 'action');
        if ($action === 'update') {
            return self::STATE_FORM_SENT;
        }

        return self::STATE_FORM_REQUESTED;
    }

    private function findId() : ?int {
        $employee_id = filter_input(INPUT_GET, 'employee_id', FILTER_VALIDATE_INT);
        return $employee_id;
    }

    private function readPost() : array {
        $employee = [];
        
        $employee['employee_id'] = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        $employee['name'] = filter_input(INPUT_POST, 'name');
        $employee['surname'] = filter_input(INPUT_POST, 'surname');
        $employee['job'] = filter_input(INPUT_POST, 'job');
        $employee['pass'] = password_hash(filter_input(INPUT_POST,'pass'),PASSWORD_DEFAULT);
        $employee['login'] = filter_input(INPUT_POST,'login');
        $employee['room'] = filter_input(INPUT_POST, 'room');
        $employee['wage'] = filter_input(INPUT_POST,'wage');
        $employee['admin'] = filter_input(INPUT_POST,'admin',FILTER_VALIDATE_INT);
        if(isset($employee['admin'])){
            $employee['admin'] = 1;
        }
        else $employee['admin'] = 0;
        return $employee;
    }

    private function readDB(int $employee_id) : array {
        $query = "SELECT employee_id, name,surname, job, room, wage, login, password, admin AS RoomName FROM employee WHERE employee_id = :employee_id;";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();

        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        return $employee;
    }

    private function isDataValid(array $employee) : bool {
        if (!$employee['name'])
            return false;

        if (!$employee['wage'])
            return false;
        
        if (!$employee['surname'])
            return false;
            
        if (!$employee['job'])
            return false;
        
        if(!$employee['admin'])
        {
            $employee['admin'] = 0;
        }
        return true;
    }

    private function update(array $employee) {
        $query = "UPDATE employee SET name = :name,surname=:surname, job = :job, room = :room,password=:pass,login=:login WHERE employee_id = :employee_id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':employee_id', $employee['employee_id']);
        $stmt->bindParam(':pass',$employee['pass']);
        $stmt->bindParam(':name', $employee['name']);
        $stmt->bindParam(':surname', $employee['surname']);
        $stmt->bindParam(':job', $employee['job']);
        $stmt->bindParam(':room', $employee['room']);
        $stmt->bindParam(':login',$employee['login']);
        return $stmt->execute();
    }

    private function redirect(int $result) : void {
        $location = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: {$location}?result={$result}");
        exit;
    }

}
$page = new UpdateEmployeePage();
$page->render();

