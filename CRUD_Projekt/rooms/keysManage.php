<?php
require "../includes/bootstrap.inc.php";
use Tracy\Debugger;
Debugger::enable();
final class KeysManagePage extends BaseDBPage{
    public function __construct()
    {
        parent::__construct();
        $this->title = "Správa klíčů";
    }
    public function body():string{
        if (($_SESSION['admin']==0)||(!$_SESSION)) {
            header('location:index.php', false);
            exit;
        }
        return $this->m->render('keysManage');
    }
}  
$page = new KeysManagePage();
$page->render();