<?php
require "../includes/bootstrap.inc.php";
use Tracy\Debugger;
Debugger::enable();
final class KeysInsertPage extends BaseDBPage{
    const STATE_FORM_REQUESTED = 1;
    const STATE_FORM_SENT = 2;
    const STATE_PROCESSED = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private int $state;
    private int $result = 0;
    private KeyModel $key;

    public function __construct()
    {
        parent::__construct();
        $this->title = "Správa klíčů";
    }

    private function redirect(int $result): void
    {
        $location = strtok($_SERVER['REQUEST_URI'], '?');
        header("location: {$location}?result={$result}");
        exit;
    }
    private function getState(): int
    {

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->state = $this->getState();

        if ($this->state === self::STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS) {
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=keysManage.php'>";
                $this->title = "Klíč byl úspěšně přiřazen";
            } elseif ($this->result === self::RESULT_FAIL) {
                $this->title = "Přiřazení klíče selhalo";
            }
        } elseif ($this->state === self::STATE_FORM_SENT) {
            //načíst data
            $this->key = KeyModel::readPostData();
                //uložit a přesměrovat
                if ($this->key->Insert()) {
                    //přesměruj se zprávou "úspěch"
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    //přesměruj se zprávou "neúspěch"
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                //jít na formulář nebo
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Přiřadit klíč : Neplatný formulář";
            }
    }

    public function body():string{
        if (($_SESSION['admin']==0)||(!$_SESSION)) {
            header('location:index.php', false);
            exit;
        }
        $queryEmployees = 'SELECT name AS employeeName, surname AS employeeSurname, employee_id AS employeeId FROM employee';
        $queryRooms = 'SELECT name AS roomName, room_id AS roomId FROM room';

        $stmtEmployees = DB::getConnection()->prepare($queryEmployees);
        $stmtRooms = DB::getConnection()->prepare($queryRooms);

        $stmtEmployees->execute();
        $stmtRooms->execute();
        if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("keysCreate", ['Employees' => $stmtEmployees, 'Rooms' => $stmtRooms]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render("keysSuccess", ["message" => "Klíč byl úspěšně přiřazen"]);
            } elseif ($this->result === self::RESULT_FAIL) {
                return $this->m->render("keysFail", ["message" => "Přiřazení klíče selhalo"]);
            }
        }
        return "";
    }    
}
(new KeysInsertPage())->render();