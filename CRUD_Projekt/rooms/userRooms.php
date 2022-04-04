<?php
require "../includes/bootstrap.inc.php";


final class ListRoomsPage extends BaseDBPage {

    protected function setUp(): void
    {
        parent::setUp();
        $this->title = "Seznam místností";
    }

    protected function body(): string
    {
        if(!$_SESSION)
            {
                  header('location:index.php',false);
                  exit;
            }
        $stmt = $this->pdo->prepare("SELECT * FROM `room` ORDER BY `room_id`");
        $stmt->execute([]);
        return $this->m->render("roomListUser", ["rooms" => $stmt]);
    }

}

$page = new ListRoomsPage();
$page->render();