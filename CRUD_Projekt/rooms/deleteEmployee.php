<?php
require "../includes/bootstrap.inc.php";

final class DeleteEmployeePage extends BaseDBPage {

    const STATE_DELETE_REQUESTED = 1;
    const STATE_PROCESSED = 2;

    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 2;

    private int $state;
    private int $result = 0;
    private ?int $employee_id;

    protected function setUp(): void
    {
        parent::setUp();
        session_start();

        $this->state = $this->getState();

        if ($this->state === self::STATE_PROCESSED) {
            //je hotovo, reportujeme
            if ($this->result === self::RESULT_SUCCESS) {
                $this->extraHeaders[] = "<meta http-equiv='refresh' content='5;url=./'>";
                $this->title = "Zaměstnanec smazán";
            } elseif ($this->result === self::RESULT_FAIL) {
                $this->title = "Smazání zaměstnance selhalo";
            }
        } elseif ($this->state === self::STATE_DELETE_REQUESTED) {
            //načíst data
            $this->employee_id = $this->readPost();
            //validovat data
            if (!$this->employee_id) {
                throw new RequestException(400);
            }

            //smazat a přesměrovat
            $token = random_bytes(20);

            if ($this->delete($this->employee_id)) {
                //přesměruj se zprávou "úspěch"
                $_SESSION[$token] = ['result' => self::RESULT_SUCCESS];
//                $this->redirect(self::RESULT_SUCCESS);
            } else {
                //přesměruj se zprávou "neúspěch"
                $_SESSION[$token] = ['result' => self::RESULT_FAIL];
//                $this->redirect(self::RESULT_FAIL);
            }
            $this->redirect($token);
        }
    }

    protected function body(): string
    {
        if(!$_SESSION)
            {
                  header('location:index.php',false);
                  exit;
            }
        if ($this->result === self::RESULT_SUCCESS) {
            return $this->m->render("employeeSuccess", ["message" => "Zaměstnanec byl úspěšně smazána."]);
        } elseif ($this->result === self::RESULT_FAIL) {
            return $this->m->render("employeeFail", ["message" => "Smazání zaměstnance selhalo."]);
        }
    }

    private function getState() : int {
        //rozpoznání processed
        $state = filter_input(INPUT_GET, 'state', FILTER_VALIDATE_INT);

        if ($state === self::STATE_PROCESSED) {
            $token = filter_input(INPUT_GET, 'token');

            if (!isset($_SESSION[$token]))
                throw new RequestException(400);

            $result = $_SESSION[$token]['result'];

            if ($result === self::RESULT_SUCCESS) {
                $this->result = self::RESULT_SUCCESS;
                return self::STATE_PROCESSED;
            } elseif ($result === self::RESULT_FAIL) {
                $this->result = self::RESULT_FAIL;
                return self::STATE_PROCESSED;
            }

            throw new RequestException(400);
        }

        return self::STATE_DELETE_REQUESTED;
    }

    private function readPost() : ?int {
        $employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
        return $employee_id;
    }

    private function delete(int $emp_id) {
        $query = "DELETE FROM employee WHERE employee_id = :emp_id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':emp_id', $emp_id);

        return $stmt->execute();
    }

    private function redirect(string $token) : void {
        $location = strtok($_SERVER['REQUEST_URI'], '?');
        $query = http_build_query(['state' => self::STATE_PROCESSED, 'token' => $token]);
        header("Location: {$location}?$query");
        exit;
    }

}

$page = new DeleteEmployeePage();
$page->render();

