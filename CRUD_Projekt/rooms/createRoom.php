<?php
require "../includes/bootstrap.inc.php";
final class CreateRoomPage extends BaseDBPage {
    const STATE_FORM_REQUESTED = 1;
    const STATE_FORM_SENT = 2;
    const STATE_PROCESSED = 3;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private int $state;
    private int $result = 0;
    private RoomModel $room;

    public function __construct()
    {
        parent::__construct();
        $this->title = "Nová místnost";
    }

    protected function setUp(): void
    {
        parent::setUp();
       
        $this->state = $this->getState();
         
        if ($this->state === self::STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS) {
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=roomsAdmin.php'>";
                $this->title = "Místnost založena";
            } elseif ($this->result === self::RESULT_FAIL) {
                $this->title = "Založení místnosti selhalo";
            }
        } elseif ($this->state === self::STATE_FORM_SENT) {
            //načíst data
            $this->room = RoomModel::readPostData(); 
                   
            $isOk = $this->room->Validate();
            //validovat data
            if ($isOk){
                //uložit a přesměrovat
                if ($this->room->Insert()) {
                    //přesměruj se zprávou "úspěch"
                    $this->redirect(self::RESULT_SUCCESS);
                } else {
                    //přesměruj se zprávou "neúspěch"
                    $this->redirect(self::RESULT_FAIL);
                }
            } else {
                //jít na formulář nebo
                $this->state = self::STATE_FORM_REQUESTED;
                $this->title = "Založit místnost : Neplatný formulář";
            }
        } else {
            //přejít na formulář
            $this->state = self::STATE_FORM_REQUESTED;
            $this->title = "Založit místnost";
            $this->room = new RoomModel();
        }
    }

    protected function body(): string
    {
        if (($_SESSION['admin']==0)||(!$_SESSION)) {
            header('location:index.php', false);
            exit;
        }
        if ($this->state === self::STATE_FORM_REQUESTED) {
            return $this->m->render("roomForm", ['create' => true, 'room' => $this->room,'errors' => $this->room->getValidationErrors()]);
        } elseif ($this->state === self::STATE_PROCESSED) {
            if ($this->result === self::RESULT_SUCCESS) {
                return $this->m->render("roomSuccess", ["message" => "Místnost byla úspěšně vytvořena."]);
            } elseif ($this->result === self::RESULT_FAIL) {
                return $this->m->render("roomFail", ["message" => "Vytvoření místnosti selhalo"]);
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
$page = new CreateRoomPage();
$page->render();