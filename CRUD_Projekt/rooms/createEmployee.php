<?php
require "../includes/bootstrap.inc.php";
use Tracy\Debugger;
Debugger::enable();
final class CreateEmployeePage extends BaseDBPage {
    
    const STATE_FORM_REQUESTED = 1;
    const STATE_FORM_SENT = 2;
    const STATE_PROCESSED = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private int $state;
    private int $result = 0;
    private EmployeeModel $employee;

    public function __construct()
    {
        parent::__construct();
        $this->title = "Nový zaměstnanec";
    }
    protected function setUp(): void
    {
        parent::setUp();

        $this->state = $this->getState();

        if ($this->state === self::STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS) {
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=employeesAdmin.php'>";
                $this->title = "Zaměstnanec vytvořen";
            } elseif ($this->result === self::RESULT_FAIL) {
                $this->title = "Vytvoření nového zaměstnance selhalo";
            }
        } elseif ($this->state === self::STATE_FORM_SENT) {
            //načíst data
            $this->employee = EmployeeModel::readPostData();
            
            $isOk = $this->employee->Validate();
            dump($isOk);
            //validovat data
            if ($isOk){
                //uložit a přesměrovat
                if ($this->employee->Insert()) {
                    //přesměruj se zprávou "úspěch"
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    //přesměruj se zprávou "neúspěch"
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                //jít na formulář nebo
                $this->state = self::STATE_FORM_REQUESTED;
                $this->employee = new EmployeeModel();
                $this->title = "Vytvořit zaměstnance : Neplatný formulář";
            }
        } else {
            //přejít na formulář
            $this->state = self::STATE_FORM_REQUESTED;
            $this->title = "Vytvořit zaměstnance";
            $this->employee = new EmployeeModel();
        }

    }

    protected function body(): string
    {
        
            if(!$_SESSION)
            {
                  header('location:index.php',false);
                  exit;
            }
            
            $queryRoomN = "SELECT name AS RName FROM room WHERE room_id =:room";
            $stmt2 = DB::getConnection()->prepare($queryRoomN);
            $stmt2->bindParam(':room',$this->employee->room);
            $stmt2->execute();

        if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("employeeForm", ['create' => true,'update' => false, 'employee' => $this->employee,'rooms'=>$stmt2]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render("employeeSuccess", ["message" => "Zaměstnanec byl úspěšně vytvořen."]);
            } elseif ($this->result === self::RESULT_FAIL) {
                return $this->m->render("employeeFail", ["message" => "Vytvoření zaměstnance selhalo"]);
            }
        }
        return"";
    }

    private function getState() : int {
        
        $result = filter_input(INPUT_GET, 'result', FILTER_VALIDATE_INT);

        if ($result === self::RESULT_SUCCESS) {
            $this->result = self::RESULT_SUCCESS;
            return self::STATE_PROCESSED;
        } elseif ($result === self::RESULT_FAIL) {
            $this->result = self::RESULT_FAIL;
            return self::STATE_PROCESSED;
        }

        $action = filter_input(INPUT_POST, 'action');
        if ($action === 'create') {
            return self::STATE_FORM_SENT;
        }

        return self::STATE_FORM_REQUESTED;
    }

    private function redirect(int $result) : void {
        $location = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: {$location}?result={$result}");
        exit;
    }
}
$page = new CreateEmployeePage();
$page->render();