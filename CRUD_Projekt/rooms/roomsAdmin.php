<?php
require "../includes/bootstrap.inc.php";
final class CurrentPage extends BaseDBPage {
    protected string $title = "Výpis místností";

    protected function body(): string
    {
        if (($_SESSION['admin']==0)||(!$_SESSION)) {
            header('location:index.php', false);
            exit;
        }
        $stmt = $this->pdo->prepare("SELECT * FROM `room` ORDER BY room_id");
        $stmt->execute([]);

        return $this->m->render("roomListAdmin", ["rooms" => $stmt]);
    }
}

(new CurrentPage())->render();